<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;

class OltSnmpService
{
    private string $host;
    private int $port;
    private string $communityRo;
    private string $communityRw;
    private int $timeoutMicros;
    private int $retries;

    // Standard ZTE Enterprise GPON OIDs
    const OID_SYS_DESCR = '.1.3.6.1.2.1.1.1.0';
    const OID_SYS_NAME  = '.1.3.6.1.2.1.1.5.0';
    const OID_IF_NAME   = '.1.3.6.1.2.1.31.1.1.1.1'; // Interface names e.g. gpon-olt_1/2/1

    // Configured ONU Serial Numbers
    const OID_ONU_SN_V21 = '.1.3.6.1.4.1.3902.1012.3.28.1.1.5';
    const OID_ONU_SN_V22 = '.1.3.6.1.4.1.3902.1082.500.10.2.2.1.5';

    // ONU Phase State (Status)
    const OID_ONU_PHASE_STATE_V21 = '.1.3.6.1.4.1.3902.1012.3.28.2.1.4';
    const OID_ONU_PHASE_STATE_ALT = '.1.3.6.1.4.1.3902.1012.3.28.2.1.3';
    const OID_ONU_PHASE_STATE_V22 = '.1.3.6.1.4.1.3902.1082.500.10.2.2.2.1.4';

    // ONU Rx Optical Power
    const OID_ONU_RX_POWER_V21 = '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10';
    const OID_ONU_RX_POWER_V22 = '.1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.10';

    // Unconfigured ONU Table (Auto-Find)
    const OID_UNCFG_ONU_SN_V21 = '.1.3.6.1.4.1.3902.1012.3.28.4.1.3';
    const OID_UNCFG_ONU_SN_ALT = '.1.3.6.1.4.1.3902.1012.3.28.4.1.5';
    const OID_UNCFG_ONU_SN_V22 = '.1.3.6.1.4.1.3902.1082.500.10.2.2.5.1.2';

    public function __construct()
    {
        $this->host = Setting::get('olt_host', Config::get('olt.host', '10.10.10.2'));
        $this->port = (int) Setting::get('olt_snmp_port', Config::get('olt.snmp_port', 161));
        $this->communityRo = Setting::get('olt_snmp_community_ro', Config::get('olt.snmp_ro', 'radbooxro'));
        $this->communityRw = Setting::get('olt_snmp_community_rw', Config::get('olt.snmp_rw', 'radbooxrw'));
        $this->timeoutMicros = (int) (Config::get('olt.snmp_timeout', 2) * 1000000); // 2s
        $this->retries = 1;
    }

    /**
     * Uji konektivitas SNMP ke OLT
     */
    public function testConnection(): array
    {
        $start = microtime(true);
        try {
            $sysName = $this->get(self::OID_SYS_NAME);
            $sysDescr = $this->get(self::OID_SYS_DESCR);
            $elapsed = round((microtime(true) - $start) * 1000, 2);

            if ($sysName !== null || $sysDescr !== null) {
                $nameClean = trim(str_replace(['STRING:', '"'], '', (string) ($sysName ?: $sysDescr)));
                return [
                    'success' => true,
                    'message' => "SNMP OLT Aktif (Community: {$this->communityRo}) - {$nameClean} [{$elapsed}ms]",
                    'elapsed' => $elapsed,
                ];
            }

            return [
                'success' => false,
                'message' => "Tidak ada respons SNMP dari OLT {$this->host}:{$this->port} (Community: {$this->communityRo}). Cek konfigurasi OLT atau firewall UDP 161.",
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => "Koneksi SNMP Gagal: " . $e->getMessage(),
            ];
        }
    }

    /**
     * Ambil data lengkap dashboard OLT via SNMP (Instan, efisien, ringan)
     */
    public function getDashboardData(?string $targetPort = null): array
    {
        $startTime = microtime(true);

        // 1. Dapatkan mapping PON Port IfIndex -> Nama Port (e.g. 268501248 -> '1/2/8')
        $portMap = $this->getPortInterfaceMap();

        // 2. Tarik Serial Number ONU terkonfigurasi
        $snWalk = $this->walk(self::OID_ONU_SN_V21);
        if (empty($snWalk)) {
            // Coba fallback OID V2.2 jika firmware baru
            $snWalk = $this->walk(self::OID_ONU_SN_V22);
        }

        // 3. Tarik Status / Phase State
        $stateWalk = $this->walk(self::OID_ONU_PHASE_STATE_V21);
        if (empty($stateWalk)) {
            $stateWalk = $this->walk(self::OID_ONU_PHASE_STATE_ALT);
        }
        if (empty($stateWalk)) {
            $stateWalk = $this->walk(self::OID_ONU_PHASE_STATE_V22);
        }

        // 4. Tarik RX Optical Power
        $powerWalk = $this->walk(self::OID_ONU_RX_POWER_V21);
        if (empty($powerWalk)) {
            $powerWalk = $this->walk(self::OID_ONU_RX_POWER_V22);
        }

        // 5. Tarik Unconfigured ONUs (Auto-Find)
        $uncfgWalk = $this->walk(self::OID_UNCFG_ONU_SN_V21);
        if (empty($uncfgWalk)) {
            $uncfgWalk = $this->walk(self::OID_UNCFG_ONU_SN_ALT);
        }
        if (empty($uncfgWalk)) {
            $uncfgWalk = $this->walk(self::OID_UNCFG_ONU_SN_V22);
        }

        // Susun State Map [ "ponIndex:onuId" => "WORKING" / "LOS" / dsb ]
        $stateMap = [];
        foreach ($stateWalk as $k => $v) {
            $idx = $this->extractIndices($k, '3\.(?:28\.2\.1\.[34]|10\.2\.2\.2\.1\.4)');
            if ($idx) {
                $key = "{$idx['pon_index']}:{$idx['onu_id']}";
                $stateMap[$key] = $this->parsePhaseState($v);
            }
        }

        // Susun Power Map [ "ponIndex:onuId" => "-19.45" ]
        $powerMap = [];
        foreach ($powerWalk as $k => $v) {
            $idx = $this->extractIndices($k, '(?:3\.50\.12\.1\.1\.10|500\.20\.2\.2\.2\.1\.10)');
            if ($idx) {
                $key = "{$idx['pon_index']}:{$idx['onu_id']}";
                $powerMap[$key] = $this->parseRxPower($v);
            }
        }

        // Susun Configured ONU List
        $configured = [];
        foreach ($snWalk as $k => $v) {
            $idx = $this->extractIndices($k, '(?:3\.28\.1\.1\.5|500\.10\.2\.2\.1\.5)');
            if (!$idx) {
                continue;
            }

            $ponIndex = $idx['pon_index'];
            $onuId = $idx['onu_id'];
            $portName = $portMap[$ponIndex] ?? $this->decodeIfIndex($ponIndex);

            // Filter jika target port diminta (e.g. '1/2/8')
            if ($targetPort && $portName !== $targetPort) {
                continue;
            }

            $key = "{$ponIndex}:{$onuId}";
            $snClean = $this->parseZteSn($v);
            $status = $stateMap[$key] ?? 'OFFLINE';
            $rxPower = $powerMap[$key] ?? '-';

            // Jika status LOS/OFFLINE, pastikan rxPower '-'
            if ($status !== 'WORKING' && $rxPower === '-') {
                $rxPower = '-';
            }

            $configured[] = [
                'port' => $portName,
                'onu_id' => $onuId,
                'sn' => $snClean,
                'status' => $status,
                'rx_power' => $rxPower,
            ];
        }

        // Susun Unconfigured List
        $unconfigured = [];
        foreach ($uncfgWalk as $k => $v) {
            $idx = $this->extractIndices($k, '(?:3\.28\.4\.1\.[35]|500\.10\.2\.2\.5\.1\.2)');
            if (!$idx) {
                continue;
            }

            $ponIndex = $idx['pon_index'];
            $portName = $portMap[$ponIndex] ?? $this->decodeIfIndex($ponIndex);

            if ($targetPort && $portName !== $targetPort) {
                continue;
            }

            $unconfigured[] = [
                'port' => $portName,
                'sn' => $this->parseZteSn($v),
                'status' => 'auto-find',
            ];
        }

        $elapsed = round((microtime(true) - $startTime), 2);
        $totalConfigured = count($configured);
        $totalUncfg = count($unconfigured);

        $rawOutput = "--- SNMP FAST SYNC (ZTE GPON) ---\n" .
            "Host: {$this->host}:{$this->port}\n" .
            "Community: {$this->communityRo}\n" .
            "Total Configured ONU: {$totalConfigured}\n" .
            "Total Unconfigured ONU: {$totalUncfg}\n" .
            "Execution Time: {$elapsed} seconds\n" .
            "Status: SINKRONISASI BERHASIL (SNMP INSTANT)";

        return [
            'success' => true,
            'driver' => 'snmp',
            'type' => $targetPort ? 'configured' : 'all',
            'port' => $targetPort,
            'configured' => $configured,
            'unconfigured' => $unconfigured,
            'raw_output' => $rawOutput,
            'elapsed_sec' => $elapsed,
        ];
    }

    /**
     * Dapatkan peta nomor PON Port ifIndex ke nama port terbaca manusia
     * Contoh: 268501248 => '1/2/8'
     */
    public function getPortInterfaceMap(): array
    {
        return Cache::remember('olt_snmp_port_map_' . md5($this->host), 3600, function () {
            $ifWalk = $this->walk(self::OID_IF_NAME);
            $map = [];

            foreach ($ifWalk as $k => $v) {
                // Key format: .1.3.6.1.2.1.31.1.1.1.1.268501248
                if (preg_match('/\.(\d+)$/', $k, $m)) {
                    $ifIndex = (int) $m[1];
                    $valClean = trim(str_replace(['STRING:', '"'], '', (string) $v));

                    // Hanya ambil port gpon-olt (bukan gpon-onu atau port lain)
                    // Format: gpon-olt_1/2/8
                    if (preg_match('/gpon-olt_([\d\/]+)/i', $valClean, $portMatch)) {
                        $map[$ifIndex] = $portMatch[1];
                    }
                }
            }

            return $map;
        });
    }

    /**
     * Decode ZTE Type 1 composite ifIndex secara bitwise jika map ifName tidak tersedia
     */
    public function decodeIfIndex(int $ifIndex): string
    {
        $shelf = ($ifIndex >> 16) & 0xFF;
        $slot  = ($ifIndex >> 8) & 0xFF;
        $port  = $ifIndex & 0xFF;

        if ($shelf === 0 && $slot === 0 && $port === 0) {
            return (string) $ifIndex;
        }

        return "{$shelf}/{$slot}/{$port}";
    }

    /**
     * Ekstrak [ 'pon_index' => ..., 'onu_id' => ... ] dari suffix OID
     */
    private function extractIndices(string $key, string $basePattern): ?array
    {
        if (preg_match("/{$basePattern}\.(.*)$/", $key, $m)) {
            $parts = explode('.', $m[1]);
            if (count($parts) >= 2) {
                return [
                    'pon_index' => (int) $parts[0],
                    'onu_id'    => (int) $parts[1],
                    'sub_id'    => $parts[2] ?? null,
                ];
            }
        }

        // Fallback: ambil 2 angka terakhir dari string OID
        if (preg_match('/\.(\d+)\.(\d+)(?:\.\d+)?$/', $key, $m)) {
            return [
                'pon_index' => (int) $m[1],
                'onu_id'    => (int) $m[2],
            ];
        }

        return null;
    }

    /**
     * Parsing Serial Number ZTE/Huawei/Generic
     */
    public function parseZteSn(string $val): string
    {
        $val = trim($val);
        $val = trim($val, "\"'\t\n\r\0\x0B");

        if (stripos($val, 'Hex-STRING:') === 0) {
            $val = trim(substr($val, 11));
        } elseif (stripos($val, 'STRING:') === 0) {
            $val = trim(substr($val, 7));
            $val = trim($val, "\"'\t\n\r\0\x0B");
        }

        // Format hex bytes: "5A 54 45 47 C1 23 45 67"
        if (preg_match('/^([0-9a-fA-F]{2}[\s\:\-])+[0-9a-fA-F]{2}$/', $val) || (strlen($val) >= 16 && ctype_xdigit(str_replace(' ', '', $val)))) {
            $hex = str_replace([' ', ':', '-'], '', $val);
            if (strlen($hex) >= 16) {
                $vendorHex = substr($hex, 0, 8);
                $serialHex = substr($hex, 8, 8);
                $vendor = @hex2bin($vendorHex);
                if ($vendor && ctype_print($vendor)) {
                    return $vendor . strtoupper($serialHex);
                }
                return strtoupper(substr($hex, 0, 16));
            }
        }

        // String ASCII biasa: "ZTEGC1234567"
        if (preg_match('/^[A-Za-z0-9]{8,16}$/', $val)) {
            return strtoupper($val);
        }

        return $val;
    }

    /**
     * Parsing nilai Optical RX Power ke format dBm
     */
    public function parseRxPower(string $val): string
    {
        if (preg_match('/(?:INTEGER:\s*)?(-?\d+)/i', $val, $m)) {
            $raw = (int) $m[1];
        } else {
            $raw = (int) $val;
        }

        if ($raw === 65535 || $raw <= 0 || $raw >= 2147483647) {
            return '-';
        }

        if ($raw > 30000) {
            $power = (($raw - 65536) * 0.002) - 30;
        } else {
            $power = ($raw * 0.002) - 30;
        }

        if ($power < -50 || $power > 0) {
            return '-';
        }

        return sprintf('%.2f', $power);
    }

    /**
     * Parsing kode integer Phase State ke status deskriptif
     */
    public function parsePhaseState(string $val): string
    {
        if (preg_match('/(?:INTEGER:\s*)?(\d+)/i', $val, $m)) {
            $code = (int) $m[1];
        } else {
            $code = (int) $val;
        }

        return match ($code) {
            1 => 'logging',
            2 => 'LOS',
            3 => 'syncMib',
            4 => 'WORKING',
            5 => 'DyingGasp',
            6 => 'authFailed',
            7 => 'OFFLINE',
            default => 'UNKNOWN',
        };
    }

    /**
     * SNMP GET helper
     */
    private function get(string $oid): ?string
    {
        if (function_exists('snmp2_get')) {
            $target = "{$this->host}:{$this->port}";
            $res = @snmp2_get($target, $this->communityRo, $oid, $this->timeoutMicros, $this->retries);
            if ($res !== false && $res !== null) {
                return (string) $res;
            }
        }

        // Fallback ke CLI snmpget
        $cmd = sprintf(
            'snmpget -v 2c -c %s -t %d -r %d %s:%d %s 2>/dev/null',
            escapeshellarg($this->communityRo),
            max(1, (int) ($this->timeoutMicros / 1000000)),
            $this->retries,
            escapeshellarg($this->host),
            $this->port,
            escapeshellarg($oid)
        );
        $output = @shell_exec($cmd);
        return $output ? trim($output) : null;
    }

    /**
     * SNMP WALK helper
     */
    private function walk(string $oid): array
    {
        if (function_exists('snmp2_real_walk')) {
            $target = "{$this->host}:{$this->port}";
            $walk = @snmp2_real_walk($target, $this->communityRo, $oid, $this->timeoutMicros, $this->retries);
            if (is_array($walk) && !empty($walk)) {
                return $walk;
            }
        }

        // Fallback ke CLI snmpwalk
        $cmd = sprintf(
            'snmpwalk -v 2c -c %s -t %d -r %d %s:%d %s 2>/dev/null',
            escapeshellarg($this->communityRo),
            max(1, (int) ($this->timeoutMicros / 1000000)),
            $this->retries,
            escapeshellarg($this->host),
            $this->port,
            escapeshellarg($oid)
        );
        $output = @shell_exec($cmd);
        if (!$output) {
            return [];
        }

        $lines = explode("\n", trim($output));
        $result = [];
        foreach ($lines as $line) {
            if (str_contains($line, '=')) {
                [$k, $v] = explode('=', $line, 2);
                $result[trim($k)] = trim($v);
            }
        }

        return $result;
    }
}

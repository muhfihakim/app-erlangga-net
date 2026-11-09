<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Setting;
use App\Models\Customer;

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
    const OID_ONU_RX_POWER_V21 = '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10'; // ZTE C300/C320 GPON ONT Rx (zxGponPonRxOpticalLevel)
    const OID_ONU_RX_POWER_OLT = '.1.3.6.1.4.1.3902.1015.1010.11.2.1.2';  // ZTE OLT Transceiver Rx (zxAnOLTRxOpticalPower)
    const OID_ONU_RX_POWER_V22 = '.1.3.6.1.4.1.3902.1082.500.20.2.2.2.1.10'; // ZTE Titan C600 (zxAnGponRmAniRxOptLevel)

    // Unconfigured ONU Table (Auto-Find)
    const OID_UNCFG_ONU_SN_V21 = '.1.3.6.1.4.1.3902.1012.3.28.4.1.3';
    const OID_UNCFG_ONU_SN_ALT = '.1.3.6.1.4.1.3902.1012.3.28.4.1.5';
    const OID_UNCFG_ONU_SN_V22 = '.1.3.6.1.4.1.3902.1082.500.10.2.2.5.1.2';

    // Remote ONU WAN IP Host Table (zxGponIPHostConfigDataTable - V2.1)
    const OID_WAN_IP_HOST_IP   = '.1.3.6.1.4.1.3902.1012.3.50.16.1.1.5';
    const OID_WAN_IP_HOST_MASK = '.1.3.6.1.4.1.3902.1012.3.50.16.1.1.6';
    const OID_WAN_IP_HOST_GW   = '.1.3.6.1.4.1.3902.1012.3.50.16.1.1.7';
    const OID_WAN_IP_HOST_NAME = '.1.3.6.1.4.1.3902.1012.3.50.16.1.1.4';

    // Remote ONU WAN Configuration Table (zxAnGponRmWanIpConfTable - V2.2)
    const OID_RM_WAN_IP_MODE     = '.1.3.6.1.4.1.3902.1082.500.20.2.17.2.1.2';
    const OID_RM_WAN_IP_ADDR     = '.1.3.6.1.4.1.3902.1082.500.20.2.17.2.1.5';
    const OID_RM_WAN_CVLAN       = '.1.3.6.1.4.1.3902.1082.500.20.2.17.2.1.6';
    const OID_RM_WAN_PPPOE_USER  = '.1.3.6.1.4.1.3902.1082.500.20.2.17.2.1.10';

    // ONU Name, Description & Distance
    const OID_ONU_TYPE_NAME = '.1.3.6.1.4.1.3902.1012.3.28.1.1.1';
    const OID_ONU_NAME     = '.1.3.6.1.4.1.3902.1012.3.28.1.1.2';
    const OID_ONU_DESC     = '.1.3.6.1.4.1.3902.1012.3.28.1.1.3';
    const OID_ONU_DISTANCE = '.1.3.6.1.4.1.3902.1012.3.28.2.1.8';

    // IF-MIB Traffic Counters
    const OID_IF_HC_IN_OCTETS  = '.1.3.6.1.2.1.31.1.1.1.6';
    const OID_IF_HC_OUT_OCTETS = '.1.3.6.1.2.1.31.1.1.1.10';
    const OID_IF_IN_OCTETS     = '.1.3.6.1.2.1.2.2.1.10';
    const OID_IF_OUT_OCTETS    = '.1.3.6.1.2.1.2.2.1.16';

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
     * Konfigurasi format output library Net-SNMP agar konsisten numerik
     */
    private function configureSnmpEnvironment(): void
    {
        if (function_exists('snmp_set_oid_output_format') && defined('SNMP_OID_OUTPUT_NUMERIC')) {
            @snmp_set_oid_output_format(SNMP_OID_OUTPUT_NUMERIC);
        }
        if (function_exists('snmp_set_quick_print')) {
            @snmp_set_quick_print(1);
        }
        if (function_exists('snmp_set_enum_print')) {
            @snmp_set_enum_print(0);
        }
        if (function_exists('snmp_set_valueretrieval') && defined('SNMP_VALUE_PLAIN')) {
            @snmp_set_valueretrieval(SNMP_VALUE_PLAIN);
        }
    }

    /**
     * Cek apakah ekstensi PHP SNMP terpasang
     */
    public function isExtensionLoaded(): bool
    {
        return extension_loaded('snmp') || function_exists('snmp2_real_walk') || function_exists('snmp2_get');
    }

    /**
     * Cek apakah OLT merespons SNMP secara cepat
     */
    public function ping(?string $targetHost = null, ?int $targetPort = null, ?string $community = null): bool
    {
        if (!$this->isExtensionLoaded()) {
            return false;
        }

        $this->configureSnmpEnvironment();
        $host = $targetHost ?: $this->host;
        $port = $targetPort ?: $this->port;
        $comm = $community ?: $this->communityRo;
        $target = "{$host}:{$port}";
        $timeout = $this->timeoutMicros ?: 2000000;

        // Probe 1: sysDescr
        $res = @snmp2_get($target, $comm, self::OID_SYS_DESCR, $timeout, 1);
        if ($res !== false && $res !== null) return true;

        // Probe 2: sysName
        $res = @snmp2_get($target, $comm, self::OID_SYS_NAME, $timeout, 1);
        if ($res !== false && $res !== null) return true;

        // Probe 3: ZTE Enterprise tree walk (1st entry)
        $walk = @snmp2_real_walk($target, $comm, '.1.3.6.1.4.1.3902', $timeout, 1);
        if (!empty($walk)) return true;

        // Probe 4: Standard ifName tree walk
        $walk = @snmp2_real_walk($target, $comm, self::OID_IF_NAME, $timeout, 1);
        if (!empty($walk)) return true;

        // Probe 5: Standard system tree walk
        $walk = @snmp2_real_walk($target, $comm, '.1.3.6.1.2.1.1', $timeout, 1);
        if (!empty($walk)) return true;

        return false;
    }

    /**
     * Uji konektivitas SNMP ke OLT (dapat menerima parameter form langsung)
     */
    public function testConnection(?string $targetHost = null, ?int $targetPort = null, ?string $community = null): array
    {
        if (!$this->isExtensionLoaded()) {
            return [
                'success' => false,
                'message' => "Ekstensi PHP 'snmp' belum terpasang di aaPanel. Silakan pasang paket 'php-snmp' di aaPanel.",
            ];
        }
        $this->configureSnmpEnvironment();
        $host = $targetHost ?: $this->host;
        $port = $targetPort ?: $this->port;
        $comm = $community ?: $this->communityRo;
        $target = "{$host}:{$port}";

        $start = microtime(true);
        try {
            $sysName = @snmp2_get($target, $comm, self::OID_SYS_NAME, 1500000, 1);
            $sysDescr = @snmp2_get($target, $comm, self::OID_SYS_DESCR, 1500000, 1);

            if ($sysName === false && $sysDescr === false) {
                // Coba ambil via real walk jika get scalar diblokir
                $walk = @snmp2_real_walk($target, $comm, '.1.3.6.1.2.1.1', 1500000, 1);
                if (!empty($walk)) {
                    $sysDescr = reset($walk);
                }
            }

            if ($sysName === false && $sysDescr === false) {
                // Coba cek MIB ZTE langsung
                $walk = @snmp2_real_walk($target, $comm, '.1.3.6.1.4.1.3902', 1500000, 1);
                if (!empty($walk)) {
                    $sysDescr = "ZTE Enterprise GPON Device";
                }
            }

            $elapsed = round((microtime(true) - $start) * 1000, 2);

            if ($sysName !== false || $sysDescr !== false) {
                $rawVal = (string) ($sysName ?: $sysDescr);
                $nameClean = trim(str_replace(['STRING:', '"'], '', $rawVal)) ?: 'ZTE OLT';
                return [
                    'success' => true,
                    'message' => "SNMP OLT Aktif (Community: {$comm}) - {$nameClean} [{$elapsed}ms]",
                    'elapsed' => $elapsed,
                ];
            }

            return [
                'success' => false,
                'message' => "Tidak ada respons SNMP dari OLT {$host}:{$port} (Community: {$comm}). Pastikan port UDP 161 terbuka di firewall/router.",
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
        $this->configureSnmpEnvironment();

        // 0. Cek ketersediaan ekstensi PHP SNMP
        if (!$this->isExtensionLoaded()) {
            return [
                'success' => false,
                'error' => "Ekstensi PHP 'snmp' belum terpasang di aaPanel. Silakan pasang paket 'php-snmp' di aaPanel.",
            ];
        }

        // 1. Cek cepat apakah perangkat merespons SNMP
        if (!$this->ping()) {
            return [
                'success' => false,
                'error' => "OLT {$this->host}:{$this->port} tidak merespons SNMP (Community: {$this->communityRo}). Pastikan port UDP 161 terbuka di firewall/router.",
            ];
        }

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
        // Prioritas 1: ZTE C300/C320 GPON ONT Rx Optical Power (zxGponTransceiverInfoTable)
        $powerWalk = $this->walk(self::OID_ONU_RX_POWER_V21);
        if (empty($powerWalk)) {
            // Prioritas 2: ZTE OLT-side Rx Optical Power (zxAnOLTRxOpticalPower)
            $powerWalk = $this->walk(self::OID_ONU_RX_POWER_OLT);
        }
        if (empty($powerWalk)) {
            // Prioritas 3: ZTE Titan C600 (zxAnGponRmAniRxOptLevel)
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
            $idx = $this->extractIndices((string) $k);
            if ($idx) {
                $key = "{$idx['pon_index']}:{$idx['onu_id']}";
                $stateMap[$key] = $this->parsePhaseState((string) $v);
            }
        }

        // Susun Power Map [ "ponIndex:onuId" => "-19.45" ]
        $powerMap = [];
        $validPowerCount = 0;
        foreach ($powerWalk as $k => $v) {
            $idx = $this->extractIndices((string) $k);
            if ($idx) {
                $key = "{$idx['pon_index']}:{$idx['onu_id']}";
                $parsed = $this->parseRxPower((string) $v);
                $powerMap[$key] = $parsed;
                if ($parsed !== '-') {
                    $validPowerCount++;
                }
            }
        }

        // Fallback: Jika OID_ONU_RX_POWER_V21 tidak mengembalikan data valid (misal OMCI belum ter-trigger), coba OID_ONU_RX_POWER_OLT
        if ($validPowerCount === 0) {
            $powerWalkOlt = $this->walk(self::OID_ONU_RX_POWER_OLT);
            if (!empty($powerWalkOlt)) {
                foreach ($powerWalkOlt as $k => $v) {
                    $idx = $this->extractIndices((string) $k);
                    if ($idx) {
                        $key = "{$idx['pon_index']}:{$idx['onu_id']}";
                        $powerMap[$key] = $this->parseRxPower((string) $v);
                    }
                }
            }
        }

        // Susun Configured ONU List
        $configured = [];
        foreach ($snWalk as $k => $v) {
            $idx = $this->extractIndices((string) $k);
            if (!$idx) {
                continue;
            }

            $ponIndex = $idx['pon_index'];
            $onuId = $idx['onu_id'];
            $portName = $portMap[$ponIndex] ?? $this->decodeIfIndex($ponIndex);

            // Filter jika target port diminta (e.g. '1/2/8')
            if ($targetPort && $portName !== $targetPort && !str_ends_with($portName, $targetPort)) {
                continue;
            }

            $key = "{$ponIndex}:{$onuId}";
            $snClean = $this->parseZteSn((string) $v);
            $status = $stateMap[$key] ?? 'WORKING';
            $rxPower = $powerMap[$key] ?? '-';

            // Jika status LOS/OFFLINE/authFailed, pastikan rxPower '-'
            if (in_array($status, ['LOS', 'OFFLINE', 'authFailed'])) {
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
            $idx = $this->extractIndices((string) $k);
            if (!$idx) {
                continue;
            }

            $ponIndex = $idx['pon_index'];
            $portName = $portMap[$ponIndex] ?? $this->decodeIfIndex($ponIndex);

            if ($targetPort && $portName !== $targetPort && !str_ends_with($portName, $targetPort)) {
                continue;
            }

            $unconfigured[] = [
                'port' => $portName,
                'sn' => $this->parseZteSn((string) $v),
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
     * Ambil rincian detail ONU spesifik (WAN IP, PPPoE User, CVLAN, RX Power, Trafik) via SNMP
     */
    public function getOnuDetails(string $port, int|string $onuId, ?string $hintSn = null): array
    {
        $this->configureSnmpEnvironment();
        $onuId = (int) $onuId;
        $portMap = $this->getPortInterfaceMap();

        // 1. Dapatkan ponIndex dari port map
        $ponIndex = array_search($port, $portMap);
        if ($ponIndex === false) {
            foreach ($portMap as $idx => $pName) {
                if ($pName === $port || str_ends_with($pName, $port)) {
                    $ponIndex = $idx;
                    break;
                }
            }
        }

        $wanUser = '-';
        $wanIp = '-';
        $wanVlan = '-';
        $wanStatus = 'DISCONNECTED';
        $rxPower = '-';
        $distance = '-';
        $onuSn = $hintSn ?: '-';
        $onuType = '-';

        // 2. Ambil dari cache dashboard (instan & akurat)
        $cachedDashboard = Cache::get('olt_snmp_dashboard_data');
        if (is_array($cachedDashboard) && !empty($cachedDashboard['configured'])) {
            foreach ($cachedDashboard['configured'] as $item) {
                if (($item['port'] === $port || str_ends_with($item['port'], $port)) && (int) $item['onu_id'] === $onuId) {
                    if ($onuSn === '-' && !empty($item['sn'])) {
                        $onuSn = $item['sn'];
                    }
                    $rxPower = $item['rx_power'] ?? '-';
                    $phaseState = $item['status'] ?? 'OFFLINE';
                    if ($phaseState === 'WORKING') {
                        $wanStatus = 'CONNECTED';
                    }
                    break;
                }
            }
        }

        // 3. Query OID SNMP ZTE jika ponIndex tersedia
        if ($ponIndex !== false) {
            $prefixSuffix = ".{$ponIndex}.{$onuId}";

            // Optical Rx Power jika belum ada di cache
            if ($rxPower === '-' || $rxPower === 'No Signal' || $rxPower === 'no signal') {
                $rawPower = $this->getScalar(self::OID_ONU_RX_POWER_V21 . $prefixSuffix)
                    ?: $this->getScalar(self::OID_ONU_RX_POWER_OLT . $prefixSuffix)
                    ?: $this->getScalar(self::OID_ONU_RX_POWER_V22 . $prefixSuffix);
                if ($rawPower) {
                    $rxPower = $this->parseRxPower($rawPower);
                }
            }

            // Serial Number jika belum ada
            if ($onuSn === '-') {
                $rawSn = $this->getScalar(self::OID_ONU_SN_V21 . $prefixSuffix)
                    ?: $this->getScalar(self::OID_ONU_SN_V22 . $prefixSuffix);
                if ($rawSn) {
                    $onuSn = $this->parseZteSn($rawSn);
                }
            }

            // WAN IP (zxGponIPHostConfigDataTable / zxAnGponRmWanIpAddr)
            $rawIp = $this->getScalar(self::OID_WAN_IP_HOST_IP . $prefixSuffix)
                ?: $this->getScalar(self::OID_RM_WAN_IP_ADDR . $prefixSuffix);
            if ($rawIp) {
                $parsedIp = $this->cleanIp($rawIp);
                if ($parsedIp) {
                    $wanIp = $parsedIp;
                }
            }

            // PPPoE Username / Host Name / ONU Name
            $rawUser = $this->getScalar(self::OID_RM_WAN_PPPOE_USER . $prefixSuffix)
                ?: $this->getScalar(self::OID_WAN_IP_HOST_NAME . $prefixSuffix);
            if (!$rawUser || str_starts_with($rawUser, '0.0.0.0')) {
                $rawUser = $this->getScalar(self::OID_ONU_NAME . $prefixSuffix)
                    ?: $this->getScalar(self::OID_ONU_DESC . $prefixSuffix);
            }
            if ($rawUser) {
                $cleaned = $this->cleanString($rawUser);
                if ($cleaned !== '-' && !str_starts_with($cleaned, '0.0.0.0')) {
                    $wanUser = $cleaned;
                }
            }

            // CVLAN
            $rawVlan = $this->getScalar(self::OID_RM_WAN_CVLAN . $prefixSuffix)
                ?: $this->getScalar(self::OID_WAN_CVLAN_V21 . $prefixSuffix);
            if ($rawVlan) {
                $vlanId = $this->cleanVlan($rawVlan);
                if ($vlanId > 0) {
                    $wanVlan = (string) $vlanId;
                }
            }

            // Jarak ONU (Distance)
            $rawDist = $this->getScalar(self::OID_ONU_DISTANCE . $prefixSuffix);
            if ($rawDist) {
                $distVal = (int) preg_replace('/\D/', '', $rawDist);
                if ($distVal > 0) {
                    $distance = $distVal . ' m';
                }
            }

            // Tipe ONT (zxGponOntDevMgmtTypeName)
            $rawType = $this->getScalar(self::OID_ONU_TYPE_NAME . $prefixSuffix);
            if ($rawType) {
                $cleanedType = $this->cleanString($rawType);
                if ($cleanedType !== '-' && !in_array(strtolower($cleanedType), ['sn', 'password'])) {
                    $onuType = $cleanedType;
                }
            }
        }

        // 4. Smart Matching dengan Database Customer & FreeRADIUS
        if (($wanUser === '-' || $wanIp === '-') && $onuSn !== '-') {
            try {
                $customer = Customer::where('sn_perangkat', $onuSn)
                    ->orWhere('sn_perangkat', 'like', "%{$onuSn}%")
                    ->first();

                if ($customer) {
                    if ($wanUser === '-' && !empty($customer->pppoe_username)) {
                        $wanUser = $customer->pppoe_username;
                    }
                    if ($onuType === '-' && !empty($customer->jenis_modem)) {
                        $onuType = $customer->jenis_modem;
                    }
                }
            } catch (\Throwable $e) {
                // Ignore DB error
            }
        }

        // Jika ada Username, cari Framed-IP-Address aktif di FreeRADIUS radacct
        if ($wanUser !== '-' && ($wanIp === '-' || $wanIp === '0.0.0.0')) {
            try {
                $activeSession = DB::connection('radius')
                    ->table('radacct')
                    ->where('username', $wanUser)
                    ->whereNull('acctstoptime')
                    ->orderByDesc('radacctid')
                    ->first(['framedipaddress']);

                if ($activeSession && !empty($activeSession->framedipaddress)) {
                    $wanIp = $activeSession->framedipaddress;
                    $wanStatus = 'CONNECTED';
                }
            } catch (\Throwable $e) {
                // Ignore radius fallback
            }
        }

        if ($wanIp !== '-' && $wanIp !== '0.0.0.0') {
            $wanStatus = 'CONNECTED';
        }

        // 5. Statistik Trafik
        $traffic = $this->getOnuTrafficStats($port, $onuId, $wanUser);

        $rawOutput = "--- SNMP ONU DETAIL (ZTE GPON) ---\n" .
            "Interface: gpon-onu_{$port}:{$onuId}\n" .
            "Serial Number: {$onuSn}\n" .
            "RX Power: {$rxPower} dBm\n" .
            "PPPoE Username: {$wanUser}\n" .
            "Current IP: {$wanIp}\n" .
            "CVLAN: {$wanVlan}\n" .
            "WAN Status: {$wanStatus}\n" .
            "Distance: {$distance}\n" .
            "Total In (Download): {$traffic['input_total']}\n" .
            "Total Out (Upload): {$traffic['output_total']}";

        return [
            'success' => true,
            'driver' => 'snmp',
            'onu_index' => "gpon-onu_{$port}:{$onuId}",
            'wan_info' => [
                'username' => $wanUser,
                'ip' => $wanIp,
                'vlan' => $wanVlan,
                'status' => $wanStatus,
                'distance' => $distance,
                'onu_type' => $onuType,
            ],
            'stats' => $traffic,
            'rx_power' => $rxPower,
            'raw_output' => $rawOutput,
        ];
    }

    /**
     * Dapatkan statistik trafik ONU (FreeRADIUS radacct - Instan & Ringan)
     */
    public function getOnuTrafficStats(string $port, int $onuId, ?string $wanUser = null): array
    {
        $stats = [
            'input_rate' => '0 Bps',
            'output_rate' => '0 Bps',
            'input_total' => '0 Bytes',
            'output_total' => '0 Bytes',
            'mac_session' => '-',
        ];

        // Ambil dari FreeRADIUS radacct jika wanUser tersedia (Super Cepat < 2ms)
        if ($wanUser && $wanUser !== '-') {
            try {
                $session = DB::connection('radius')
                    ->table('radacct')
                    ->where('username', $wanUser)
                    ->orderByDesc('radacctid')
                    ->first(['acctinputoctets', 'acctoutputoctets', 'callingstationid', 'acctsessionid']);

                if ($session) {
                    if (!empty($session->acctinputoctets)) {
                        $stats['input_total'] = $this->formatBytes($session->acctinputoctets);
                    }
                    if (!empty($session->acctoutputoctets)) {
                        $stats['output_total'] = $this->formatBytes($session->acctoutputoctets);
                    }
                    $stats['mac_session'] = $session->callingstationid ?: ($session->acctsessionid ?? '-');
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        return $stats;
    }

    /**
     * SNMP GET single scalar helper
     */
    public function getScalar(string $oid): ?string
    {
        $this->configureSnmpEnvironment();
        if (!$this->isExtensionLoaded()) {
            return null;
        }

        $target = "{$this->host}:{$this->port}";
        try {
            $val = @snmp2_get($target, $this->communityRo, $oid, $this->timeoutMicros, 1);
            if ($val !== false && $val !== null) {
                return trim((string) $val);
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // Coba walk 1 level jika OID butuh sub-index .1
        try {
            $walk = @snmp2_real_walk($target, $this->communityRo, $oid, $this->timeoutMicros, 1);
            if (is_array($walk) && !empty($walk)) {
                return trim((string) reset($walk));
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return null;
    }

    /**
     * Sanitasi string output SNMP
     */
    public function cleanString(?string $val): string
    {
        if (!$val) return '-';
        $val = trim($val);
        $val = trim($val, "\"'\t\n\r\0\x0B ");

        if (stripos($val, 'Hex-STRING:') === 0) {
            $hex = preg_replace('/[^0-9a-fA-F]/', '', substr($val, 11));
            $bin = @hex2bin($hex);
            if ($bin && ctype_print($bin)) {
                return trim($bin);
            }
        } elseif (stripos($val, 'STRING:') === 0) {
            $val = trim(substr($val, 7));
            $val = trim($val, "\"'\t\n\r\0\x0B ");
        }

        if (mb_check_encoding($val, 'UTF-8') && preg_match('/^[\x20-\x7E]+$/', $val)) {
            return $val ?: '-';
        }

        return '-';
    }

    /**
     * Bersihkan alamat IP dari respon SNMP
     */
    public function cleanIp(?string $val): ?string
    {
        if (!$val) return null;
        $val = trim($val, "\"'\t\n\r\0\x0B ");

        // Format 1: Teks IP standard "100.64.15.89"
        if (preg_match('/\b(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\b/', $val, $m)) {
            if ($m[1] !== '0.0.0.0') {
                return $m[1];
            }
        }

        // Format 2: Heksadesimal "64 40 0F 59"
        $valClean = str_ireplace(['Hex-STRING:', 'STRING:', 'IpAddress:'], '', $val);
        $hex = preg_replace('/[^0-9a-fA-F]/', '', $valClean);
        if (strlen($hex) === 8) {
            $parts = [
                hexdec(substr($hex, 0, 2)),
                hexdec(substr($hex, 2, 2)),
                hexdec(substr($hex, 4, 2)),
                hexdec(substr($hex, 6, 2)),
            ];
            $ip = implode('.', $parts);
            if ($ip !== '0.0.0.0') {
                return $ip;
            }
        }

        return null;
    }

    /**
     * Parsing integer VLAN ID
     */
    public function cleanVlan(?string $val): int
    {
        if (!$val) return 0;
        if (preg_match('/(?:INTEGER:\s*)?(\d+)/i', $val, $m)) {
            $v = (int) $m[1];
            if ($v > 0 && $v < 4095) {
                return $v;
            }
        }
        return 0;
    }

    /**
     * Format byte ke string terbaca
     */
    public function formatBytes(float|int $bytes): string
    {
        $bytes = (float) $bytes;
        if ($bytes >= 1099511627776) return number_format($bytes / 1099511627776, 2) . ' TB';
        if ($bytes >= 1073741824) return number_format($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return number_format($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return number_format($bytes / 1024, 2) . ' KB';
        return $bytes . ' Bytes';
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
                if (preg_match('/\.(\d+)$/', (string) $k, $m)) {
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
    private function extractIndices(string $key): ?array
    {
        // Format ZTE PON ifIndex biasanya 9 digit (>= 268435456) diikuti ONU ID (1-128)
        if (preg_match('/\.(\d{6,12})\.(\d{1,3})(?:\.(\d+))?$/', $key, $m)) {
            return [
                'pon_index' => (int) $m[1],
                'onu_id'    => (int) $m[2],
                'sub_id'    => isset($m[3]) ? (int) $m[3] : null,
            ];
        }

        // Generic fallback: ambil 2 angka terakhir
        if (preg_match('/\.(\d+)\.(\d+)(?:\.(\d+))?$/', $key, $m)) {
            return [
                'pon_index' => (int) $m[1],
                'onu_id'    => (int) $m[2],
                'sub_id'    => isset($m[3]) ? (int) $m[3] : null,
            ];
        }

        return null;
    }

    /**
     * Parsing Serial Number ZTE/Huawei/Generic
     */
    public function parseZteSn(string $val): string
    {
        // 1. Raw 8-byte SNMP Octet String (e.g. 4 bytes ASCII vendor + 4 bytes binary serial)
        if (strlen($val) === 8) {
            $vendor = substr($val, 0, 4);
            $serialHex = strtoupper(bin2hex(substr($val, 4, 4)));
            if (ctype_print($vendor)) {
                return $vendor . $serialHex;
            }
            return strtoupper(bin2hex($val));
        }

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

        // Fallback jika masih mengandung karakter non-printable / binary bytes
        if (!mb_check_encoding($val, 'UTF-8') || preg_match('/[^\x20-\x7E]/', $val)) {
            return strtoupper(bin2hex($val));
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
            $raw = (int) preg_replace('/[^\d\-]/', '', $val);
        }

        // Invalid / Down / No Signal markers
        if (
            $raw === 65535 ||
            $raw === 65535000 ||
            $raw === -80000 ||
            $raw === 2147483647 ||
            $raw === -2147483648 ||
            $raw === 0
        ) {
            return '-';
        }

        // Format 1: Negative value (e.g. -14396 => -14.40 dBm from OLT Rx, or -2150 => -21.50 dBm from Titan)
        if ($raw < 0) {
            if ($raw <= -70000) {
                return '-';
            }
            if ($raw <= -1000) {
                $power = $raw / 1000;
            } elseif ($raw <= -100) {
                $power = $raw / 100;
            } else {
                $power = (float) $raw;
            }
        }
        // Format 2: Standard ZTE C300/C320 unsigned integer: dBm = (raw * 0.002) - 30
        elseif ($raw > 0 && $raw < 30000) {
            $power = ($raw * 0.002) - 30;
        } elseif ($raw >= 30000 && $raw < 65535) {
            $power = (($raw - 65536) * 0.002) - 30;
        } else {
            return '-';
        }

        if ($power < -45 || $power > 5) {
            return '-';
        }

        return sprintf('%.2f', $power);
    }

    /**
     * Parsing kode integer Phase State ke status deskriptif
     * ZXGPON-SERVICE-MIB (0-indexed enum):
     * 0 => logging
     * 1 => los
     * 2 => syncMib
     * 3 => working
     * 4 => dyinggasp
     * 5 => authFailed
     * 6 => offline
     */
    public function parsePhaseState(string $val): string
    {
        $valLower = strtolower(trim($val));

        // Direct string contains check first
        if (str_contains($valLower, 'working')) {
            return 'WORKING';
        }
        if (str_contains($valLower, 'syncmib') || str_contains($valLower, 'sync-mib') || str_contains($valLower, 'sync mib')) {
            return 'syncMib';
        }
        if (str_contains($valLower, 'los')) {
            return 'LOS';
        }
        if (str_contains($valLower, 'dyinggasp') || str_contains($valLower, 'dying-gasp')) {
            return 'DyingGasp';
        }
        if (str_contains($valLower, 'authfailed') || str_contains($valLower, 'auth-failed')) {
            return 'authFailed';
        }
        if (str_contains($valLower, 'offline')) {
            return 'OFFLINE';
        }
        if (str_contains($valLower, 'logging')) {
            return 'logging';
        }

        if (preg_match('/(?:INTEGER:\s*)?(-?\d+)/i', $val, $m)) {
            $code = (int) $m[1];
            return match ($code) {
                0 => 'logging',
                1 => 'LOS',
                2 => 'syncMib',
                3 => 'WORKING',
                4 => 'DyingGasp',
                5 => 'authFailed',
                6 => 'OFFLINE',
                default => 'UNKNOWN',
            };
        }

        return 'UNKNOWN';
    }

    /**
     * SNMP WALK helper
     */
    private function walk(string $oid): array
    {
        $this->configureSnmpEnvironment();

        if (function_exists('snmp2_real_walk')) {
            $target = "{$this->host}:{$this->port}";
            $walk = @snmp2_real_walk($target, $this->communityRo, $oid, $this->timeoutMicros, $this->retries);
            if (is_array($walk) && !empty($walk)) {
                return $walk;
            }
        }

        // Fallback ke CLI snmpwalk
        $cmd = sprintf(
            'snmpwalk -v 2c -c %s -O n -t %d -r %d %s:%d %s 2>/dev/null',
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

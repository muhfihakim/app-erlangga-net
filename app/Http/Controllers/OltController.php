<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OltTelnetService;
use Exception;

class OltController extends Controller
{
    public function __construct(
        private OltTelnetService $oltService
    ) {}

    public function index()
    {
        return view('monitoring.olt');
    }

    public function checkNextOnuId(Request $request)
    {
        $port = $request->input('olt_port');
        if (!$port) {
            return response()->json(['success' => false, 'error' => 'Port OLT harus diisi'], 400);
        }

        try {
            $output = $this->oltService->execute("show gpon onu state gpon-olt_{$port}");
            
            // Parsing Logic: Mencari ONU ID yang sudah terpakai
            // Baris biasanya: gpon-onu_1/2/8:1  ready  ...
            preg_match_all('/gpon-onu_[\d\/]+:(\d+)/', $output, $matches);
            $usedIds = array_map('intval', $matches[1] ?? []);
            sort($usedIds);

            // Cari ID terkecil yang belum dipakai (1-128)
            $nextId = 1;
            for ($i = 1; $i <= 128; $i++) {
                if (!in_array($i, $usedIds)) {
                    $nextId = $i;
                    break;
                }
            }

            return response()->json([
                'success' => true,
                'next_id' => $nextId,
                'used_ids' => $usedIds,
                'raw_output' => $output
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function executeScript(Request $request)
    {
        $script = $request->input('script');
        if (!$script) {
            return response()->json(['success' => false, 'error' => 'Skrip kosong'], 400);
        }

        try {
            $lines = explode("\n", str_replace("\r", "", $script));
            $fullLog = "";

            foreach ($lines as $line) {
                $trimmed = trim($line);
                if ($trimmed === "" || $trimmed === "!") continue;
                
                $output = $this->oltService->execute($trimmed);
                $fullLog .= "> " . $trimmed . "\n" . $output . "\n";
            }

            return response()->json([
                'success' => true,
                'log' => $fullLog
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getDashboardData(Request $request)
    {
        $port = $request->query('port');

        try {
            if ($port) {
                // 1. Specific Port Query
                $output = $this->oltService->execute("show gpon onu baseinfo gpon-olt_{$port}");
                $data = $this->parseOnuBaseInfo($output);

                // Fetch power for this specific port
                $powerOutput = $this->oltService->execute("show pon power olt-rx gpon-olt_{$port}");
                $powerMap = $this->parseOnuPower($powerOutput);

                foreach ($data as &$onu) {
                    $key = "{$onu['port']}:{$onu['onu_id']}";
                    $onu['rx_power'] = $powerMap[$key] ?? '-';
                }

                return response()->json([
                    'success' => true,
                    'type' => 'configured',
                    'port' => $port,
                    'data' => $data,
                    'raw_output' => $output . "\n\n" . $powerOutput
                ]);
            } else {
                // 2. Deep Global Sync
                $stateOutput = $this->oltService->execute("show gpon onu state");
                $initialList = $this->parseOnuState($stateOutput);

                $activePorts = [];
                foreach ($initialList as $onu) {
                    $activePorts[$onu['port']] = true;
                }
                $activePorts = array_keys($activePorts);

                $allConfigured = [];
                $fullRawOutput = "--- CONFIGURED (GLOBAL STATE) ---\n" . $stateOutput . "\n\n";

                foreach ($activePorts as $p) {
                    usleep(100000); // 100ms
                    // Get Base Info (SN)
                    $portBaseOutput = $this->oltService->execute("show gpon onu baseinfo gpon-olt_{$p}");
                    $portData = $this->parseOnuBaseInfo($portBaseOutput);
                    
                    // Get Power (RX)
                    usleep(50000); // 50ms
                    $portPowerOutput = $this->oltService->execute("show pon power olt-rx gpon-olt_{$p}");
                    $powerMap = $this->parseOnuPower($portPowerOutput);

                    foreach ($portData as &$onu) {
                        $key = "{$onu['port']}:{$onu['onu_id']}";
                        $onu['rx_power'] = $powerMap[$key] ?? '-';
                    }

                    $allConfigured = array_merge($allConfigured, $portData);
                    $fullRawOutput .= "--- PORT {$p} ---\n" . $portBaseOutput . "\n" . $portPowerOutput . "\n\n";
                }

                $uncfgOutput = $this->oltService->execute("show gpon onu uncfg");
                $unconfigured = $this->parseUnconfigured($uncfgOutput);

                return response()->json([
                    'success' => true,
                    'type' => 'all',
                    'configured' => $allConfigured,
                    'unconfigured' => $unconfigured,
                    'raw_output' => $fullRawOutput
                ]);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function getOnuDetails(Request $request)
    {
        $port = $request->query('port');
        $onuId = $request->query('onu_id');

        if (!$port || !$onuId) {
            return response()->json(['success' => false, 'error' => 'Port dan ONU ID harus diisi'], 400);
        }

        $onuIndex = "gpon-onu_{$port}:{$onuId}";

        try {
            // 1. Get WAN IP & PPPoE Info
            $wanRaw = $this->oltService->execute("show gpon remote-onu wan-ip {$onuIndex}");
            
            usleep(150000); // 150ms

            // 2. Get Traffic Statistics
            $statsRaw = $this->oltService->execute("show interface {$onuIndex}");

            usleep(150000); // 150ms
            
            // 3. Get Power Info specifically for this ONU
            $powerRaw = $this->oltService->execute("show pon power olt-rx {$onuIndex}");
            $powerMap = $this->parseOnuPower($powerRaw);

            return response()->json([
                'success' => true,
                'onu_index' => $onuIndex,
                'wan_info' => $this->parseWanInfo($wanRaw),
                'stats' => $this->parseInterfaceStats($statsRaw),
                'rx_power' => $powerMap[$port . ":" . $onuId] ?? 'No Signal',
                'raw_output' => "--- WAN INFO ---\n" . $wanRaw . "\n\n--- STATISTICS ---\n" . $statsRaw . "\n\n--- POWER ---\n" . $powerRaw
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    private function parseOnuPower($output)
    {
        $map = [];
        // Format 1: gpon-onu_1/2/1:1    -23.963(dbm)
        // Format 2: gpon-onu_1/2/1:9    no signal
        preg_match_all('/gpon-onu_([\d\/]+):(\d+)\s+([\-\d\.]+\(dbm\)|no signal)/i', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $key = "{$match[1]}:{$match[2]}";
            $map[$key] = trim($match[3]);
        }
        return $map;
    }

    private function parseWanInfo($raw)
    {
        $info = [
            'username' => '-',
            'status' => 'disconnected',
            'ip' => '-',
            'vlan' => '-'
        ];

        if (preg_match('/Username:\s+([^\s\n]+)/i', $raw, $m)) $info['username'] = $m[1];
        if (preg_match('/Status:\s+([^\s\n]+)/i', $raw, $m)) $info['status'] = strtoupper($m[1]);
        if (preg_match('/Current IP:\s+([^\s\n]+)/i', $raw, $m)) $info['ip'] = $m[1];
        if (preg_match('/CVLAN:\s+(\d+)/i', $raw, $m)) $info['vlan'] = $m[1];

        return $info;
    }

    private function parseInterfaceStats($raw)
    {
        $stats = [
            'input_rate' => '0 Bps',
            'output_rate' => '0 Bps',
            'input_total' => '0 Bytes',
            'output_total' => '0 Bytes'
        ];

        // Rate parsing - specific for ZTE "Number Bps" format
        if (preg_match('/Input rate\s*:\s*(\d+\s*Bps)/i', $raw, $m)) $stats['input_rate'] = $m[1];
        if (preg_match('/Output rate\s*:\s*(\d+\s*Bps)/i', $raw, $m)) $stats['output_rate'] = $m[1];
        
        // Total Bytes parsing - look for sections under Total statistic
        // Input section
        if (preg_match('/Total statistic:\s+Input:\s+Bytes:(\d+)/is', $raw, $m)) {
            $stats['input_total'] = $this->formatBytes($m[1]);
        }
        
        // Output section
        if (preg_match('/Total statistic:.*?Output:\s+Bytes:(\d+)/is', $raw, $m)) {
            $stats['output_total'] = $this->formatBytes($m[1]);
        }

        return $stats;
    }

    private function formatBytes($bytes) {
        $bytes = (float) $bytes;
        if ($bytes >= 1099511627776) $bytes = number_format($bytes / 1099511627776, 2) . ' TB';
        elseif ($bytes >= 1073741824) $bytes = number_format($bytes / 1073741824, 2) . ' GB';
        elseif ($bytes >= 1048576) $bytes = number_format($bytes / 1048576, 2) . ' MB';
        elseif ($bytes >= 1024) $bytes = number_format($bytes / 1024, 2) . ' KB';
        else $bytes = $bytes . ' Bytes';
        return $bytes;
    }

    private function parseOnuBaseInfo($output)
    {
        $data = [];
        // Match lines like: gpon-onu_1/2/1:11   ALL-GPON    sn      SN:ZTEGD9D1276F         ready
        // Regex components: Port, OnuID, SN, State
        preg_match_all('/gpon-onu_([\d\/]+):(\d+)\s+[\w\-]+\s+\w+\s+SN:([A-Z0-9]{12})\s+(\w+)/i', $output, $matches, PREG_SET_ORDER);
        
        foreach ($matches as $match) {
            $data[] = [
                'port' => $match[1],
                'onu_id' => $match[2],
                'sn' => $match[3],
                'status' => strtoupper($match[4])
            ];
        }
        return $data;
    }

    private function parseOnuState($output)
    {
        $data = [];
        // Match lines like: 1/2/1:1     enable       enable      working      1(GPON)
        preg_match_all('/(\d+\/\d+\/\d+):(\d+)\s+\w+\s+\w+\s+(\w+)/i', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $data[] = [
                'port' => $match[1],
                'onu_id' => $match[2],
                'status' => strtoupper($match[3]),
                'sn' => '-' 
            ];
        }
        return $data;
    }

    private function parseUnconfigured($output)
    {
        $data = [];
        preg_match_all('/gpon-olt_([\d\/]+(?::\d+)?)\s+([A-Z0-9]{12})\s+/', $output, $matches, PREG_SET_ORDER);
        foreach ($matches as $match) {
            $port = $match[1];
            if (str_contains($port, ':')) {
                $port = explode(':', $port)[0];
            }
            $data[] = [
                'port' => $port,
                'sn' => $match[2],
                'status' => 'UNCONFIGURED'
            ];
        }
        return $data;
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\OltTelnetService;
use App\Services\OltSnmpService;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Exception;

class OltController extends Controller
{
    public function __construct(
        private OltTelnetService $oltService,
        private OltSnmpService $snmpService
    ) {}

    public function index()
    {
        if (auth()->user()->jabatan === 'Sales') {
            abort(403, 'Akses khusus Administrator dan Teknisi.');
        }
        return view('monitoring.olt');
    }

    public function checkNextOnuId(Request $request)
    {
        $port = $request->input('olt_port');
        if (!$port) {
            return response()->json(['success' => false, 'error' => 'Port OLT harus diisi'], 400);
        }

        // Cek via SNMP jika mode SNMP aktif
        $driver = Setting::get('olt_mode', config('olt.driver', 'snmp'));
        if ($driver === 'snmp') {
            try {
                $snmpResult = $this->snmpService->getDashboardData($port);
                if (!empty($snmpResult['success'])) {
                    $usedIds = [];
                    foreach ($snmpResult['configured'] as $onu) {
                        if (($onu['port'] === $port || str_ends_with($onu['port'], $port)) && !empty($onu['onu_id'])) {
                            $usedIds[] = (int) $onu['onu_id'];
                        }
                    }
                    $usedIds = array_unique($usedIds);
                    sort($usedIds);

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
                        'driver' => 'snmp',
                        'raw_output' => $snmpResult['raw_output'] ?? 'SNMP Query',
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('SNMP checkNextOnuId failed, falling back to Telnet: ' . $e->getMessage());
            }
        }

        try {
            // Gunakan baseinfo karena biasanya lebih cepat dan datanya lebih ringkas untuk SN/ID
            $output = $this->oltService->execute("show gpon onu baseinfo gpon-olt_{$port}");
            
            // Parsing Logic: Mencari ONU ID yang sudah terpakai
            // Format: gpon-onu_1/2/8:1
            preg_match_all('/:(\d+)\s+/', $output, $matches);
            $usedIds = array_unique(array_map('intval', $matches[1] ?? []));
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

    public function connectTerminal()
    {
        try {
            $banner = $this->oltService->getBanner();
            return response()->json([
                'success' => true,
                'banner' => $banner
            ]);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function verifyLogin(Request $request)
    {
        $user = $request->input('username');
        $pass = $request->input('password');

        if ($this->oltService->verifyCredentials($user, $pass)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'error' => '%Error 20209: No username or bad password']);
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
        if (auth()->user()->jabatan === 'Sales') abort(403);
        $port = $request->query('port');
        $force = filter_var($request->query('force', false), FILTER_VALIDATE_BOOLEAN);

        $driver = Setting::get('olt_mode', config('olt.driver', 'snmp'));
        $snmpFallbackReason = null;

        // 1. Prioritaskan SNMP jika aktif (Rekomendasi - Cepat & Ringan)
        if ($driver === 'snmp') {
            try {
                $cacheKey = $port ? "olt_snmp_port_{$port}" : 'olt_snmp_dashboard_data';
                if (!$force && Cache::has($cacheKey)) {
                    $cached = Cache::get($cacheKey);
                    if (is_array($cached) && ($cached['driver'] ?? '') === 'snmp') {
                        return response()->json($cached);
                    }
                    Cache::forget($cacheKey);
                }

                $snmpResult = $this->snmpService->getDashboardData($port);
                if (!empty($snmpResult['success'])) {
                    Cache::put($cacheKey, $snmpResult, 300); // Cache 5 menit
                    return response()->json($snmpResult);
                }

                $snmpFallbackReason = $snmpResult['error'] ?? 'SNMP tidak merespons';
                Log::warning('OLT SNMP data empty or failed, falling back to Telnet: ' . $snmpFallbackReason);
            } catch (\Throwable $e) {
                $snmpFallbackReason = $e->getMessage();
                Log::warning('OLT SNMP exception, falling back to Telnet: ' . $e->getMessage());
            }
        }

        // 2. Fallback Telnet (Jika driver telnet atau SNMP tidak merespons)
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
                    'driver' => 'telnet',
                    'fallback_reason' => $snmpFallbackReason,
                    'type' => 'configured',
                    'port' => $port,
                    'data' => $data,
                    'raw_output' => $output . "\n\n" . $powerOutput
                ]);
            } else {
                // 2. Deep Global Sync (using cache or background sync)
                $data = $this->syncGlobalData($force);
                if ($snmpFallbackReason && is_array($data)) {
                    $data['fallback_reason'] = $snmpFallbackReason;
                }
                return response()->json($data);
            }
        } catch (Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function syncGlobalData($forceRefresh = false)
    {
        $driver = Setting::get('olt_mode', config('olt.driver', 'snmp'));

        if ($driver === 'snmp') {
            $cacheKey = 'olt_snmp_dashboard_data';
            if (!$forceRefresh && Cache::has($cacheKey)) {
                $cached = Cache::get($cacheKey);
                if (is_array($cached) && ($cached['driver'] ?? '') === 'snmp') {
                    return $cached;
                }
                Cache::forget($cacheKey);
            }

            try {
                $snmpResult = $this->snmpService->getDashboardData();
                if (!empty($snmpResult['success'])) {
                    Cache::put($cacheKey, $snmpResult, 360);
                    return $snmpResult;
                }
            } catch (\Throwable $e) {
                Log::warning('syncGlobalData SNMP error, fallback to Telnet: ' . $e->getMessage());
            }
        }

        if (!$forceRefresh && Cache::has('olt_dashboard_data')) {
            return Cache::get('olt_dashboard_data');
        }

        try {
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
                $portBaseOutput = $this->oltService->execute("show gpon onu baseinfo gpon-olt_{$p}");
                $portData = $this->parseOnuBaseInfo($portBaseOutput);
                
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

            $result = [
                'success' => true,
                'type' => 'all',
                'configured' => $allConfigured,
                'unconfigured' => $unconfigured,
                'raw_output' => $fullRawOutput . "--- UNCONFIGURED ---\n" . $uncfgOutput
            ];

            // Cache for 6 minutes (Background worker will refresh it every 5 minutes)
            Cache::put('olt_dashboard_data', $result, 360);

            return $result;
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function getOnuDetails(Request $request)
    {
        if (auth()->user()->jabatan === 'Sales') abort(403);
        $port = $request->query('port');
        $onuId = $request->query('onu_id');
        $sn = $request->query('sn');

        if (!$port || !$onuId) {
            return response()->json(['success' => false, 'error' => 'Port dan ONU ID harus diisi'], 400);
        }

        $cacheKey = "olt_onu_detail_{$port}_{$onuId}";
        if (!$request->boolean('force') && Cache::has($cacheKey)) {
            return response()->json(Cache::get($cacheKey));
        }

        $onuIndex = "gpon-onu_{$port}:{$onuId}";
        $rawLogs = [];

        // 1. Dapatkan data awal dari Cache Dashboard (SNMP Fast)
        $cachedDashboard = Cache::get('olt_snmp_dashboard_data') ?? Cache::get('olt_dashboard_data');
        $dashboardStatus = 'WORKING';
        $rxPower = '-';
        if (is_array($cachedDashboard) && !empty($cachedDashboard['configured'])) {
            foreach ($cachedDashboard['configured'] as $item) {
                if (($item['port'] === $port || str_ends_with($item['port'], $port)) && (int)$item['onu_id'] === (int)$onuId) {
                    if (empty($sn) || $sn === '-') {
                        $sn = $item['sn'] ?? '';
                    }
                    $rxPower = $item['rx_power'] ?? '-';
                    $dashboardStatus = $item['status'] ?? 'WORKING';
                    break;
                }
            }
        }

        $wanInfo = [
            'username' => '-',
            'ip' => '-',
            'vlan' => '-',
            'status' => ($dashboardStatus === 'WORKING' ? 'CONNECTED' : 'DISCONNECTED'),
            'distance' => '-',
            'customer_name' => '-',
            'customer_id' => '-',
            'onu_type' => '-',
        ];

        $stats = [
            'input_rate' => '0 Bps',
            'output_rate' => '0 Bps',
            'input_total' => '0 Bytes',
            'output_total' => '0 Bytes',
            'mac_session' => '-',
        ];

        // 2. Hubungkan dengan database Customer jika Serial Number tersedia
        $customer = null;
        if (!empty($sn) && $sn !== '-') {
            $customer = $this->findCustomerBySn($sn);
            if ($customer) {
                $wanInfo['customer_name'] = $customer->nama_lengkap ?? '-';
                $wanInfo['customer_id'] = $customer->id_pelanggan ?? '-';
                if (!empty($customer->pppoe_username)) {
                    $wanInfo['username'] = $customer->pppoe_username;
                }
                if ($customer->paket && !empty($customer->paket->vlan)) {
                    $wanInfo['vlan'] = (string) $customer->paket->vlan;
                }
                if (!empty($customer->jenis_modem)) {
                    $wanInfo['onu_type'] = $customer->jenis_modem;
                }
            }
        }

        // 3. HYBRID ON-DEMAND TELNET: Ambil rincian detail dari CLI OLT
        try {
            // A. show gpon onu detail-info (Berlaku untuk semua tipe ONT, baik Bridge maupun Route)
            $detailRaw = $this->oltService->execute("show gpon onu detail-info {$onuIndex}");
            if ($detailRaw) {
                $rawLogs[] = "--- TELNET CLI (DETAIL INFO) ---\n" . $detailRaw;
                $telnetDetail = $this->parseOnuDetailInfo($detailRaw);

                if (!empty($telnetDetail['distance']) && $telnetDetail['distance'] !== '-') {
                    $wanInfo['distance'] = $telnetDetail['distance'];
                }
                if (!empty($telnetDetail['type']) && $telnetDetail['type'] !== '-') {
                    $wanInfo['onu_type'] = $telnetDetail['type'];
                }
                if ($telnetDetail['phase_state'] !== '-') {
                    $pState = strtoupper($telnetDetail['phase_state']);
                    if (str_contains($pState, 'WORKING') || str_contains($pState, 'READY')) {
                        $wanInfo['status'] = 'CONNECTED';
                    }
                }
                if ((empty($sn) || $sn === '-') && !empty($telnetDetail['sn']) && $telnetDetail['sn'] !== '-') {
                    $sn = $telnetDetail['sn'];
                }

                // Jika customer belum ditemukan, coba cocokkan dengan Name atau Description dari OLT
                if (!$customer && (!empty($telnetDetail['name']) || !empty($telnetDetail['description']))) {
                    $candidateId = trim($telnetDetail['name']);
                    $customer = \App\Models\Customer::where('id_pelanggan', $candidateId)
                        ->orWhere('pppoe_username', $candidateId)
                        ->first();
                    if ($customer) {
                        $wanInfo['customer_name'] = $customer->nama_lengkap ?? '-';
                        $wanInfo['customer_id'] = $customer->id_pelanggan ?? '-';
                        if (!empty($customer->pppoe_username)) {
                            $wanInfo['username'] = $customer->pppoe_username;
                        }
                        if (!empty($customer->jenis_modem) && $wanInfo['onu_type'] === '-') {
                            $wanInfo['onu_type'] = $customer->jenis_modem;
                        }
                    }
                }
            }

            // B. show gpon remote-onu wan-ip (Untuk ONT dalam mode Route/PPPoE client)
            $wanRaw = $this->oltService->execute("show gpon remote-onu wan-ip {$onuIndex}");
            if ($wanRaw) {
                $rawLogs[] = "--- TELNET CLI (WAN INFO) ---\n" . $wanRaw;
                $telnetWan = $this->parseWanInfo($wanRaw);

                if ($telnetWan['username'] !== '-') {
                    $wanInfo['username'] = $telnetWan['username'];
                }
                if ($telnetWan['ip'] !== '-' && $telnetWan['ip'] !== '0.0.0.0') {
                    $wanInfo['ip'] = $telnetWan['ip'];
                    $wanInfo['status'] = 'CONNECTED';
                }
                if ($telnetWan['vlan'] !== '-') {
                    $wanInfo['vlan'] = $telnetWan['vlan'];
                }
                if ($telnetWan['status'] === 'CONNECTED') {
                    $wanInfo['status'] = 'CONNECTED';
                }
            }

            // C. show interface {$onuIndex} (Statistik Trafik & Rate)
            $statsRaw = $this->oltService->execute("show interface {$onuIndex}");
            if ($statsRaw) {
                $rawLogs[] = "--- TELNET CLI (INTERFACE STATS) ---\n" . $statsRaw;
                $ifStats = $this->parseInterfaceStats($statsRaw);
                if (!empty($ifStats['input_rate']) && $ifStats['input_rate'] !== '0 Bps') {
                    $stats['input_rate'] = $ifStats['input_rate'];
                }
                if (!empty($ifStats['output_rate']) && $ifStats['output_rate'] !== '0 Bps') {
                    $stats['output_rate'] = $ifStats['output_rate'];
                }
                if (!empty($ifStats['input_total']) && $ifStats['input_total'] !== '0 Bytes') {
                    $stats['input_total'] = $ifStats['input_total'];
                }
                if (!empty($ifStats['output_total']) && $ifStats['output_total'] !== '0 Bytes') {
                    $stats['output_total'] = $ifStats['output_total'];
                }
            }

            // D. show mac gpon onu {$onuIndex} (Ambil Physical MAC perangkat jika ada)
            $macRaw = $this->oltService->execute("show mac gpon onu {$onuIndex}");
            if ($macRaw) {
                $rawLogs[] = "--- TELNET CLI (MAC ADDRESS) ---\n" . $macRaw;
                if (preg_match('/([0-9a-fA-F]{4}\.[0-9a-fA-F]{4}\.[0-9a-fA-F]{4})/i', $macRaw, $mm)) {
                    $cleanMac = strtolower(str_replace('.', '', $mm[1]));
                    $stats['mac_session'] = strtoupper(implode(':', str_split($cleanMac, 2)));
                } elseif (preg_match('/([0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2}[:-][0-9a-fA-F]{2})/i', $macRaw, $mm)) {
                    $stats['mac_session'] = strtoupper(str_replace('-', ':', $mm[1]));
                }
            }

            // E. show pon power attenuation jika Rx Power belum ada
            if ($rxPower === '-' || $rxPower === 'No Signal' || $rxPower === 'no signal' || $rxPower === '0 dBm') {
                $powerRaw = $this->oltService->execute("show pon power attenuation {$onuIndex}");
                if ($powerRaw) {
                    $rawLogs[] = "--- TELNET CLI (POWER ATTENUATION) ---\n" . $powerRaw;
                    $parsedPower = $this->parseAttenuationPower($powerRaw);
                    if ($parsedPower !== '-') {
                        $rxPower = $parsedPower;
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Telnet hybrid detail error: " . $e->getMessage());
            $rawLogs[] = "--- TELNET NOTICE ---\n" . $e->getMessage();
        }

        // 4. Query FreeRADIUS radacct untuk Live IP, Session, & Total Traffic jika Username tersedia
        if ($wanInfo['username'] !== '-') {
            try {
                $activeSession = DB::connection('radius')
                    ->table('radacct')
                    ->where('username', $wanInfo['username'])
                    ->whereNull('acctstoptime')
                    ->orderByDesc('radacctid')
                    ->first();

                if ($activeSession) {
                    if (!empty($activeSession->framedipaddress)) {
                        $wanInfo['ip'] = $activeSession->framedipaddress;
                    }
                    $wanInfo['status'] = 'CONNECTED';
                    if (!empty($activeSession->acctinputoctets)) {
                        $stats['input_total'] = $this->formatBytes($activeSession->acctinputoctets);
                    }
                    if (!empty($activeSession->acctoutputoctets)) {
                        $stats['output_total'] = $this->formatBytes($activeSession->acctoutputoctets);
                    }
                    $stats['mac_session'] = $activeSession->callingstationid ?: ($activeSession->acctsessionid ?? ($stats['mac_session'] !== '-' ? $stats['mac_session'] : '-'));
                } else {
                    $lastSession = DB::connection('radius')
                        ->table('radacct')
                        ->where('username', $wanInfo['username'])
                        ->orderByDesc('radacctid')
                        ->first();

                    if ($lastSession) {
                        if ($wanInfo['ip'] === '-' && !empty($lastSession->framedipaddress)) {
                            $wanInfo['ip'] = $lastSession->framedipaddress . ' (Offline)';
                        }
                        if (!empty($lastSession->acctinputoctets)) {
                            $stats['input_total'] = $this->formatBytes($lastSession->acctinputoctets);
                        }
                        if (!empty($lastSession->acctoutputoctets)) {
                            $stats['output_total'] = $this->formatBytes($lastSession->acctoutputoctets);
                        }
                        $stats['mac_session'] = $lastSession->callingstationid ?: ($lastSession->acctsessionid ?? ($stats['mac_session'] !== '-' ? $stats['mac_session'] : '-'));
                    }
                }
            } catch (\Throwable $e) {
                // Ignore FreeRADIUS query error
            }
        } elseif ($stats['mac_session'] !== '-') {
            // Fallback: Cari sesi FreeRADIUS berdasarkan MAC jika username belum terdeteksi
            try {
                $cleanMac = strtolower(preg_replace('/[^0-9a-fA-F]/', '', $stats['mac_session']));
                $radSession = DB::connection('radius')
                    ->table('radacct')
                    ->where(function ($q) use ($stats, $cleanMac) {
                        $q->where('callingstationid', $stats['mac_session'])
                            ->orWhere('callingstationid', 'LIKE', "%{$cleanMac}%");
                    })
                    ->orderByDesc('radacctid')
                    ->first();

                if ($radSession) {
                    if (!empty($radSession->username)) {
                        $wanInfo['username'] = $radSession->username;
                        if (!$customer) {
                            $customer = \App\Models\Customer::where('pppoe_username', $radSession->username)->first();
                            if ($customer) {
                                $wanInfo['customer_name'] = $customer->nama_lengkap ?? '-';
                                $wanInfo['customer_id'] = $customer->id_pelanggan ?? '-';
                                if (!empty($customer->jenis_modem) && $wanInfo['onu_type'] === '-') {
                                    $wanInfo['onu_type'] = $customer->jenis_modem;
                                }
                            }
                        }
                    }
                    if (!empty($radSession->framedipaddress) && ($wanInfo['ip'] === '-' || $wanInfo['ip'] === '0.0.0.0')) {
                        $wanInfo['ip'] = $radSession->framedipaddress . ($radSession->acctstoptime ? ' (Offline)' : '');
                    }
                    if (empty($radSession->acctstoptime)) {
                        $wanInfo['status'] = 'CONNECTED';
                    }
                    if (!empty($radSession->acctinputoctets) && $stats['input_total'] === '0 Bytes') {
                        $stats['input_total'] = $this->formatBytes($radSession->acctinputoctets);
                    }
                    if (!empty($radSession->acctoutputoctets) && $stats['output_total'] === '0 Bytes') {
                        $stats['output_total'] = $this->formatBytes($radSession->acctoutputoctets);
                    }
                }
            } catch (\Throwable $e) {
                // Ignore FreeRADIUS fallback query error
            }
        }

        $result = [
            'success' => true,
            'mode' => 'hybrid',
            'onu_index' => $onuIndex,
            'sn' => $sn,
            'wan_info' => $wanInfo,
            'stats' => $stats,
            'rx_power' => $rxPower,
            'raw_output' => implode("\n\n", $rawLogs)
        ];

        // Cache 60 detik agar klik berulang pada ONU yang sama terbuka instan 0ms
        Cache::put($cacheKey, $result, 60);

        return response()->json($result);
    }

    private function findCustomerBySn(?string $sn): ?\App\Models\Customer
    {
        if (empty($sn) || $sn === '-') return null;

        $snClean = strtoupper(trim(preg_replace('/[^A-Za-z0-9]/', '', $sn)));
        if (empty($snClean)) return null;

        // 1. Exact or partial contains on sn_perangkat
        $customer = \App\Models\Customer::where('sn_perangkat', $sn)
            ->orWhere('sn_perangkat', $snClean)
            ->orWhere('sn_perangkat', 'LIKE', "%{$snClean}%")
            ->first();
        if ($customer) return $customer;

        // 2. Match last 8 characters (hex serial portion)
        if (strlen($snClean) >= 8) {
            $suffix8 = substr($snClean, -8);
            $customer = \App\Models\Customer::where('sn_perangkat', 'LIKE', "%{$suffix8}%")->first();
            if ($customer) return $customer;
        }

        // 3. Match last 6 characters
        if (strlen($snClean) >= 6) {
            $suffix6 = substr($snClean, -6);
            $customer = \App\Models\Customer::where('sn_perangkat', 'LIKE', "%{$suffix6}%")->first();
            if ($customer) return $customer;
        }

        return null;
    }

    private function parseOnuDetailInfo(string $raw): array
    {
        $info = [
            'name' => '-',
            'type' => '-',
            'description' => '-',
            'distance' => '-',
            'phase_state' => '-',
            'sn' => '-',
            'online_duration' => '-',
        ];

        if (preg_match('/(?:^|\n|\r)\s*Name\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['name'] = trim($m[1]);
        if (preg_match('/(?:^|\n|\r)\s*(?:ONU\s+)?(?:Type|Model|Equipment-ID)\s*:\s*([^\r\n]+)/i', $raw, $m)) {
            $parsedType = trim($m[1]);
            if (!in_array(strtolower($parsedType), ['sn', 'password', 'hybrid', 'sn+password'])) {
                $info['type'] = $parsedType;
            }
        }
        if (preg_match('/(?:^|\n|\r)\s*Description\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['description'] = trim($m[1]);
        if (preg_match('/(?:^|\n|\r)\s*(?:ONU\s+)?Distance\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['distance'] = trim($m[1]);
        if (preg_match('/(?:^|\n|\r)\s*Phase\s+state\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['phase_state'] = trim($m[1]);
        if (preg_match('/(?:^|\n|\r)\s*(?:Serial\s+number|SN)\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['sn'] = trim($m[1]);
        if (preg_match('/(?:^|\n|\r)\s*Online\s+Duration\s*:\s*([^\r\n]+)/i', $raw, $m)) $info['online_duration'] = trim($m[1]);

        return $info;
    }

    private function parseAttenuationPower(string $raw): string
    {
        if (preg_match('/no\s+signal/i', $raw)) {
            return 'No Signal';
        }

        // Format 1: Explicit "ONU Rx : -23.120(dbm)" or "ONU Rx optical power: -23.120(dbm)"
        if (preg_match('/ONU\s+Rx(?:\s+optical\s+power)?\s*:\s*([\-\d\.]+)/i', $raw, $m)) {
            return sprintf('%.2f dBm', (float) $m[1]);
        }

        // Format 2: ZTE Downstream row in "show pon power attenuation":
        // "down       -                   -                   -23.120(dbm)        2.120(dbm)"
        if (preg_match('/down\s+[\S]+\s+[\S]+\s+([\-\d\.]+)\s*(?:\(dbm\)|dbm)?/i', $raw, $m)) {
            return sprintf('%.2f dBm', (float) $m[1]);
        }

        // Format 3: Explicit "OLT Rx : -21.564(dbm)"
        if (preg_match('/OLT\s+Rx\s*:\s*([\-\d\.]+)/i', $raw, $m)) {
            return sprintf('%.2f dBm', (float) $m[1]);
        }

        // Format 4: Generic fallback to first negative dBm
        if (preg_match('/(-\d+\.\d+)\s*(?:\(dbm\)|dbm)/i', $raw, $m)) {
            return sprintf('%.2f dBm', (float) $m[1]);
        }

        return '-';
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
            'status' => 'DISCONNECTED',
            'ip' => '-',
            'vlan' => '-'
        ];

        if (preg_match('/Username\s*:\s*([^\s\n\r]+)/i', $raw, $m)) $info['username'] = trim($m[1]);
        if (preg_match('/Status\s*:\s*([^\s\n\r]+)/i', $raw, $m)) {
            $st = strtoupper(trim($m[1]));
            if (in_array($st, ['CONNECTED', 'BOUND', 'UP', 'WORKING'])) {
                $info['status'] = 'CONNECTED';
            } else {
                $info['status'] = $st;
            }
        }
        if (preg_match('/Current\s+IP\s*:\s*([^\s\n\r]+)/i', $raw, $m)) {
            $ipClean = trim($m[1]);
            if ($ipClean !== '0.0.0.0' && $ipClean !== '-') {
                $info['ip'] = $ipClean;
                $info['status'] = 'CONNECTED';
            }
        }
        if (preg_match('/(?:CVLAN|VLAN)\s*:\s*(\d+)/i', $raw, $m)) $info['vlan'] = trim($m[1]);

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

        // Rate parsing (e.g. "Input rate : 1411 Bps      9 pps")
        if (preg_match('/Input\s+rate\s*:\s*([^\r\n,]+)/i', $raw, $m)) {
            $val = trim($m[1]);
            if (preg_match('/^([\d\.]+\s*(?:[KMGT]?bps|Bytes\/s|Bps))/i', $val, $rm)) {
                $stats['input_rate'] = trim($rm[1]);
            } else {
                $stats['input_rate'] = trim(preg_replace('/\s+[\d\.]+\s*pps.*/i', '', $val));
            }
        }
        if (preg_match('/Output\s+rate\s*:\s*([^\r\n,]+)/i', $raw, $m)) {
            $val = trim($m[1]);
            if (preg_match('/^([\d\.]+\s*(?:[KMGT]?bps|Bytes\/s|Bps))/i', $val, $rm)) {
                $stats['output_rate'] = trim($rm[1]);
            } else {
                $stats['output_rate'] = trim(preg_replace('/\s+[\d\.]+\s*pps.*/i', '', $val));
            }
        }
        
        // Total Bytes parsing
        if (preg_match('/Total statistic:\s+Input:\s+Bytes:\s*(\d+)/is', $raw, $m) || preg_match('/Input:.*?Bytes:\s*(\d+)/is', $raw, $m)) {
            $stats['input_total'] = $this->formatBytes($m[1]);
        }
        
        if (preg_match('/Total statistic:.*?Output:\s+Bytes:\s*(\d+)/is', $raw, $m) || preg_match('/Output:.*?Bytes:\s*(\d+)/is', $raw, $m)) {
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
        // Match: gpon-onu_1/2/12:2        ZTEGDBADA8D1        unknown
        // Or: gpon-olt_1/2/12            ZTEGDBADA8D1        ...
        preg_match_all('/(?:gpon-onu_|gpon-olt_)?([\d\/]+(?::\d+)?)\s+([A-Z0-9]{12})/i', $output, $matches, PREG_SET_ORDER);
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

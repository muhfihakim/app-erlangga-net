<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\GenieAcsService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class GenieAcsController extends Controller
{
    protected GenieAcsService $acs;

    public function __construct(GenieAcsService $acs)
    {
        $this->acs = $acs;
    }

    /**
     * List all ONUs / CPEs from GenieACS
     */
    public function index(Request $request)
    {
        $forceRefresh = $request->boolean('refresh');
        $allDevices = $this->acs->getAllDevices($forceRefresh);

        $totalCount = count($allDevices);
        $onlineCount = 0;
        $offlineCount = 0;

        foreach ($allDevices as $d) {
            if (!empty($d['is_online'])) {
                $onlineCount++;
            } else {
                $offlineCount++;
            }
        }

        // DataTables Server-Side AJAX response
        if ($request->ajax()) {
            $statusFilter = $request->input('status', 'all');
            $search = $request->input('search.value', $request->input('search', ''));
            $searchValue = is_string($search) ? trim($search) : '';

            $filtered = $allDevices;

            // Status filter
            if ($statusFilter === 'online') {
                $filtered = array_filter($filtered, fn($d) => !empty($d['is_online']));
            } elseif ($statusFilter === 'offline') {
                $filtered = array_filter($filtered, fn($d) => empty($d['is_online']));
            }

            // Search filter
            if ($searchValue !== '') {
                $s = strtolower($searchValue);
                $filtered = array_filter($filtered, function ($d) use ($s) {
                    return str_contains(strtolower($d['serial_number'] ?? ''), $s)
                        || str_contains(strtolower($d['pppoe_username'] ?? ''), $s)
                        || str_contains(strtolower($d['ssid'] ?? ''), $s)
                        || str_contains(strtolower($d['ip_address'] ?? ''), $s)
                        || str_contains(strtolower($d['model'] ?? ''), $s)
                        || str_contains(strtolower($d['manufacturer'] ?? ''), $s);
                });
            }

            $filteredCount = count($filtered);

            // Pagination
            $start = max(0, intval($request->input('start', 0)));
            $length = intval($request->input('length', 25));
            if ($length > 0) {
                $pageDevices = array_slice(array_values($filtered), $start, $length);
            } else {
                $pageDevices = array_values($filtered);
            }

            $data = [];
            foreach ($pageDevices as $d) {
                $data[] = $this->formatRowForDataTables($d);
            }

            return response()->json([
                'draw' => intval($request->input('draw', 1)),
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $data,
                'stats' => [
                    'total' => number_format($totalCount, 0, ',', '.'),
                    'online' => number_format($onlineCount, 0, ',', '.'),
                    'offline' => number_format($offlineCount, 0, ',', '.'),
                ],
            ]);
        }

        return view('genieacs.index', [
            'totalCount' => $totalCount,
            'onlineCount' => $onlineCount,
            'offlineCount' => $offlineCount,
        ]);
    }

    /**
     * Format a device row into HTML columns for DataTables
     */
    protected function formatRowForDataTables(array $d): array
    {
        // 1. Status
        if (!empty($d['is_online'])) {
            $statusHtml = '
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-500 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    ONLINE
                </span>';
        } else {
            $statusHtml = '
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500">
                    OFFLINE
                </span>';
        }

        // 2. Device (ONT)
        $sn = htmlspecialchars($d['serial_number'] ?? '-', ENT_QUOTES, 'UTF-8');
        $mfg = htmlspecialchars($d['manufacturer'] ?? '-', ENT_QUOTES, 'UTF-8');
        $model = htmlspecialchars($d['model'] ?? '-', ENT_QUOTES, 'UTF-8');
        $deviceHtml = '
            <div class="font-bold text-slate-900 font-mono text-xs flex items-center gap-1.5">
                <i class="fa-solid fa-router text-slate-400"></i>
                <span>' . $sn . '</span>
            </div>
            <div class="text-[11px] text-slate-500 mt-0.5">
                ' . $mfg . ' &bull; ' . $model . '
            </div>';

        // 3. PPPoE
        if (!empty($d['pppoe_username']) && $d['pppoe_username'] !== '-') {
            $pppoeHtml = '<div class="font-bold text-primary font-mono text-xs">' . htmlspecialchars($d['pppoe_username'], ENT_QUOTES, 'UTF-8') . '</div>';
        } else {
            $pppoeHtml = '<span class="text-slate-400 text-xs italic">Belum dikonfigurasi</span>';
        }

        // 4. IP
        if (!empty($d['ip_address']) && $d['ip_address'] !== '-') {
            $ipHtml = '<span class="font-mono text-xs text-slate-700 font-semibold bg-slate-100 px-2 py-0.5 rounded">' . htmlspecialchars($d['ip_address'], ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            $ipHtml = '<span class="text-slate-400 text-xs">-</span>';
        }

        // 5. Wi-Fi SSID
        if (!empty($d['ssid']) && $d['ssid'] !== '-') {
            $ssidEsc = htmlspecialchars($d['ssid'], ENT_QUOTES, 'UTF-8');
            $wifiHtml = '
                <div class="text-xs font-semibold text-slate-800 flex items-center gap-1.5">
                    <i class="fa-solid fa-wifi text-sky-500 text-[11px]"></i>
                    <span class="truncate max-w-[150px] inline-block" title="' . $ssidEsc . '">' . $ssidEsc . '</span>
                </div>';
        } else {
            $wifiHtml = '<span class="text-slate-400 text-xs">-</span>';
        }

        // 6. RX Power
        if (!empty($d['rx_power'])) {
            $cleanRx = trim(str_ireplace('dBm', '', $d['rx_power']));
            $numVal = floatval($cleanRx);
            $isWarning = $numVal < -27 || $numVal > -10;
            $rxClass = $isWarning 
                ? 'bg-amber-50 text-amber-600 border border-amber-200' 
                : 'bg-emerald-50 text-emerald-600 border border-emerald-200';
            $rxHtml = '
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded text-xs font-mono font-bold whitespace-nowrap ' . $rxClass . '">
                    <i class="fa-solid fa-wave-square text-[10px]"></i>
                    <span>' . htmlspecialchars($cleanRx, ENT_QUOTES, 'UTF-8') . ' dBm</span>
                </span>';
        } else {
            $rxHtml = '<span class="text-slate-400 text-xs">-</span>';
        }

        // 7. Temperature
        if (!empty($d['temperature']) && $d['temperature'] !== '-') {
            $tempVal = intval(str_replace('°C', '', $d['temperature']));
            $isHot = $tempVal > 60;
            $tempClass = $isHot 
                ? 'bg-rose-50 text-rose-600 border border-rose-200' 
                : 'bg-slate-50 text-slate-600 border border-slate-200';
            $tempHtml = '
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded text-xs font-mono font-bold whitespace-nowrap ' . $tempClass . '">
                    <i class="fa-solid fa-temperature-half text-[10px]"></i>
                    <span>' . htmlspecialchars($d['temperature'], ENT_QUOTES, 'UTF-8') . '</span>
                </span>';
        } else {
            $tempHtml = '<span class="text-slate-400 text-xs">-</span>';
        }

        // 8. Last Inform
        $lastInformText = !empty($d['last_inform_human']) && $d['last_inform_human'] !== '-' 
            ? $d['last_inform_human'] 
            : ($d['last_inform'] ?? '-');
        $lastInformHtml = '<span class="text-xs text-slate-500 whitespace-nowrap" title="' . htmlspecialchars($d['last_inform'] ?? '', ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars($lastInformText, ENT_QUOTES, 'UTF-8') . '</span>';

        // 9. Actions
        $showUrl = route('genieacs.show', $d['id']);
        $devId = htmlspecialchars($d['id'], ENT_QUOTES, 'UTF-8');
        $serialEsc = htmlspecialchars($d['serial_number'] ?? '', ENT_QUOTES, 'UTF-8');
        $ssidEsc = htmlspecialchars($d['ssid'] !== '-' ? $d['ssid'] : '', ENT_QUOTES, 'UTF-8');

        $actionHtml = '
            <div class="flex items-center justify-center gap-1.5">
                <a href="' . $showUrl . '"
                    class="btn-sm-action !p-2 rounded-lg bg-sky-50 text-sky-600 hover:bg-sky-600 hover:text-white transition-colors"
                    title="Detail & Diagnostik">
                    <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
                </a>
                <button type="button"
                    class="btn-quick-wifi btn-sm-action !p-2 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-600 hover:text-white transition-colors"
                    data-device-id="' . $devId . '"
                    data-serial="' . $serialEsc . '"
                    data-ssid="' . $ssidEsc . '"
                    title="Ubah Nama & Password Wi-Fi">
                    <i class="fa-solid fa-key text-xs"></i>
                </button>
                <button type="button"
                    class="btn-quick-reboot btn-sm-action !p-2 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-colors"
                    data-device-id="' . $devId . '"
                    data-serial="' . $serialEsc . '"
                    title="Reboot ONT">
                    <i class="fa-solid fa-power-off text-xs"></i>
                </button>
            </div>';

        return [
            'status' => $statusHtml,
            'device' => $deviceHtml,
            'pppoe' => $pppoeHtml,
            'ip' => $ipHtml,
            'wifi' => $wifiHtml,
            'rx_power' => $rxHtml,
            'temperature' => $tempHtml,
            'last_inform' => $lastInformHtml,
            'action' => $actionHtml,
        ];
    }

    /**
     * Show single ONT details
     */
    public function show(string $id)
    {
        $device = $this->acs->getDevice($id);

        if (!$device) {
            return redirect()->route('genieacs.index')->with('error', 'Data ONT tidak ditemukan di GenieACS atau koneksi terputus.');
        }

        // Link with local customer data if matching pppoe username
        $customer = null;
        if (!empty($device['pppoe_username']) && $device['pppoe_username'] !== '-') {
            $customer = Customer::where('pppoe_username', $device['pppoe_username'])->first();
        }

        return view('genieacs.show', [
            'device' => $device,
            'customer' => $customer,
        ]);
    }

    /**
     * Update SSID & Wi-Fi Password
     */
    public function updateWifi(Request $request, string $id)
    {
        $request->validate([
            'ssid' => 'required|string|min:1|max:32',
            'password' => 'nullable|string|min:8|max:63',
            'wlan_index' => 'nullable|integer',
        ]);

        $wlanIndex = (int) $request->input('wlan_index', 1);
        $result = $this->acs->updateWifi($id, $request->ssid, $request->password, $wlanIndex);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Reboot ONT
     */
    public function reboot(Request $request, string $id)
    {
        $result = $this->acs->rebootDevice($id);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }

    /**
     * Refresh ONT parameters
     */
    public function refresh(Request $request, string $id)
    {
        $object = $request->input('object', '');
        $result = $this->acs->refreshDevice($id, $object);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }

        return redirect()->back()->with('error', $result['message']);
    }
}

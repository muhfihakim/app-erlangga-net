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
        $search = $request->input('search');
        $statusFilter = $request->input('status', 'all'); // 'all', 'online', 'offline'

        $allDevices = $this->acs->getDevices($search);

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

        // Filter by status if specified
        if ($statusFilter === 'online') {
            $filteredDevices = array_filter($allDevices, fn($d) => !empty($d['is_online']));
        } elseif ($statusFilter === 'offline') {
            $filteredDevices = array_filter($allDevices, fn($d) => empty($d['is_online']));
        } else {
            $filteredDevices = $allDevices;
        }

        // In-memory pagination
        $perPage = 25;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $currentItems = array_slice($filteredDevices, ($currentPage - 1) * $perPage, $perPage);
        $paginatedDevices = new LengthAwarePaginator(
            $currentItems,
            count($filteredDevices),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        return view('genieacs.index', [
            'devices' => $paginatedDevices,
            'totalCount' => $totalCount,
            'onlineCount' => $onlineCount,
            'offlineCount' => $offlineCount,
            'search' => $search,
            'statusFilter' => $statusFilter,
        ]);
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

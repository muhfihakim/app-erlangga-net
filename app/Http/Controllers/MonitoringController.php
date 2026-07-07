<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use App\Services\MikroTikService;

class MonitoringController extends Controller
{
    public function index(MikroTikService $service)
    {
        $trafficData = Cache::get('mikrotik_traffic_data_all');
        $interfaces = [];
        if ($trafficData && isset($trafficData['success']) && $trafficData['success']) {
            $interfaces = array_keys($trafficData['data']);
        } else {
            $ifaceRaw = $service->runRawQuery('/interface/print');
            foreach ($ifaceRaw as $iface) {
                $name = $iface['name'] ?? '';
                if ($name && !str_starts_with(strtolower($name), '<pppoe-')) {
                    $interfaces[] = $name;
                }
            }
        }
        
        return view('monitoring.index', compact('interfaces'));
    }

    public function traffic(Request $request): JsonResponse
    {
        // Use cached data from persistent monitor
        $data = Cache::get('mikrotik_traffic_data_all');
        $interface = $request->query('interface');

        if (!$data || !isset($data['success']) || !$data['success']) {
            return response()->json([
                'online' => false,
                'error' => 'Data tidak tersedia di cache'
            ]);
        }

        if ($interface && isset($data['data'][$interface])) {
            return response()->json([
                'success' => true,
                'data' => $data['data'][$interface]
            ]);
        }

        // Default to first interface if not found or not specified
        $firstInterface = reset($data['data']);
        if ($firstInterface) {
            return response()->json([
                'success' => true,
                'data' => $firstInterface
            ]);
        }

        return response()->json([
            'online' => false,
            'error' => 'Data interface tidak ditemukan'
        ]);
    }

    public function status(): JsonResponse
    {
        // Use cached data from persistent monitor
        $data = Cache::get('mikrotik_status_data');

        if (!$data) {
            return response()->json([
                'online' => false,
                'error' => 'Data tidak tersedia di cache'
            ]);
        }

        return response()->json($data);
    }
}

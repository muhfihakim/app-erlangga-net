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

    public function pppoeLogs(MikroTikService $service): JsonResponse
    {
        try {
            $logsRaw = $service->runRawQuery('/log/print', ['?topics' => 'pppoe,info']);
            
            if (empty($logsRaw)) {
                $logsRaw = $service->runRawQuery('/log/print');
            }
            
            $pppoeLogs = [];
            foreach (array_reverse($logsRaw) as $log) {
                $topics = $log['topics'] ?? '';
                $message = $log['message'] ?? '';
                
                if (str_contains(strtolower($topics), 'pppoe') || str_contains(strtolower($message), 'pppoe')) {
                    $pppoeLogs[] = [
                        'time' => $log['time'] ?? '',
                        'topics' => $topics,
                        'message' => $message,
                    ];
                }
                
                if (count($pppoeLogs) >= 50) break; // Limit to 50 logs
            }
            
            return response()->json([
                'success' => true,
                'data' => $pppoeLogs
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ]);
        }
    }
}

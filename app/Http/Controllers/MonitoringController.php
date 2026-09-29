<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use App\Models\Setting;
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

        $defaultInterface = Setting::get('mikrotik_interface', config('mikrotik.interface', 'ether2'));
        $routerHost = $service->getHost();
        $nas = Nas::where('nasname', $routerHost)->first() ?? Nas::first();
        $routerName = $nas?->shortname ?? 'MikroTik Router';
        
        return view('monitoring.index', compact('interfaces', 'defaultInterface', 'routerHost', 'routerName'));
    }

    public function traffic(Request $request, MikroTikService $service): JsonResponse
    {
        // Ambil dari cache, jika kosong baca langsung dari router
        $data = Cache::get('mikrotik_traffic_data_all');

        if (!$data || !isset($data['success']) || !$data['success']) {
            $data = $service->getAllInterfacesTraffic();
            if ($data && isset($data['success']) && $data['success']) {
                Cache::put('mikrotik_traffic_data_all', $data, 3);
            }
        }

        $interface = $request->query('interface');

        if (!$data || !isset($data['success']) || !$data['success']) {
            return response()->json([
                'online' => false,
                'error' => $data['error'] ?? 'Data tidak tersedia dari router'
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

    public function status(MikroTikService $service): JsonResponse
    {
        // Ambil dari cache, jika kosong baca langsung dari router
        $data = Cache::get('mikrotik_status_data');

        if (!$data || !isset($data['online'])) {
            $data = $service->getStatus();
            if ($data && isset($data['online'])) {
                Cache::put('mikrotik_status_data', $data, 5);
            }
        }

        return response()->json($data ?? ['online' => false, 'error' => 'Gagal membaca status router']);
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

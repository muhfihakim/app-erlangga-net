<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    public function index()
    {
        return view('monitoring.index');
    }

    public function traffic(): JsonResponse
    {
        // Use cached data from persistent monitor
        $data = Cache::get('mikrotik_traffic_data');

        if (!$data) {
            return response()->json([
                'online' => false,
                'error' => 'Data tidak tersedia di cache'
            ]);
        }

        return response()->json($data);
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

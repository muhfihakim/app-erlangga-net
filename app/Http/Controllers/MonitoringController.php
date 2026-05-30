<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MonitoringController extends Controller
{
    public function __construct(
        private MikroTikService $mikroTikService
    ) {}

    public function index()
    {
        return view('monitoring.index');
    }

    public function traffic(): JsonResponse
    {
        // Prioritize cache from persistent monitor
        $data = Cache::get('mikrotik_traffic_data');

        if (!$data) {
            $data = $this->mikroTikService->getTraffic();
        }

        return response()->json($data);
    }

    public function status(): JsonResponse
    {
        // Prioritize cache from persistent monitor
        $data = Cache::get('mikrotik_status_data');

        if (!$data) {
            $data = $this->mikroTikService->getStatus();
        }

        return response()->json($data);
    }
}

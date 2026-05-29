<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;

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
        return response()->json($this->mikroTikService->getTraffic());
    }

    public function status(): JsonResponse
    {
        return response()->json($this->mikroTikService->getStatus());
    }
}

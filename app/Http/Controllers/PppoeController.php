<?php

namespace App\Http\Controllers;

use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PppoeController extends Controller
{
    public function __construct(
        private MikroTikService $mikroTikService
    ) {}

    public function index()
    {
        return view('monitoring.pppoe');
    }

    /**
     * Server-side DataTables endpoint.
     */
    public function data(Request $request): JsonResponse
    {
        // Use cached data from persistent monitor if available
        $response = Cache::get('mikrotik_pppoe_active');
        if (!$response) {
            $response = $this->mikroTikService->getPppoeActive();
        }

        if (!$response['success']) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => $response['error'] ?? 'Gagal memuat data'
            ]);
        }

        $data = $response['data'];
        
        // Simple search
        $search = $request->search['value'];
        if ($search) {
            $data = array_filter($data, function($item) use ($search) {
                return str_contains(strtolower($item['name'] ?? ''), strtolower($search)) || 
                       str_contains(strtolower($item['address'] ?? ''), strtolower($search));
            });
        }

        $totalRecords = count($response['data']);
        $filteredRecords = count($data);

        // Sort (simple sort by name for now)
        usort($data, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));

        // Pagination
        $start = intval($request->start);
        $length = intval($request->length);
        if ($length > 0) {
            $data = array_slice($data, $start, $length);
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => array_values($data),
        ]);
    }

    /**
     * Get real-time traffic for specific users (for charts).
     */
    public function traffic(Request $request): JsonResponse
    {
        $usernames = $request->users ?? [];
        if (empty($usernames)) return response()->json([]);

        $ifaces = array_map(fn($u) => "<pppoe-{$u}>", $usernames);
        $traffic = $this->mikroTikService->getPppoeTraffic($ifaces);

        return response()->json($traffic);
    }

    public function active(): JsonResponse
    {
        // Fallback for full list if still used elsewhere
        $data = Cache::get('mikrotik_pppoe_active');
        if (!$data) {
            $data = $this->mikroTikService->getPppoeActive();
        }
        return response()->json($data);
    }
}

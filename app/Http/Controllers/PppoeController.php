<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PppoeController extends Controller
{
    public function index()
    {
        // Use cached data from persistent monitor if available
        $response = Cache::get('mikrotik_pppoe_active');
        
        $pppoeActive = [];
        if ($response && isset($response['success']) && $response['success']) {
            $pppoeActive = $response['data'];
            
            // Ambil semua customer untuk mapping sales
            $customers = \App\Models\Customer::with('sales')->whereNotNull('pppoe_username')->get()->keyBy('pppoe_username');

            foreach ($pppoeActive as $key => $item) {
                $name = $item['name'] ?? '';
                $address = $item['address'] ?? '';
                $pppoeActive[$key]['is_isolir'] = str_starts_with($address, '172.30.');
                $pppoeActive[$key]['sales_name'] = isset($customers[$name]) && $customers[$name]->sales ? $customers[$name]->sales->nama_lengkap : '-';
            }
        }

        return view('monitoring.pppoe', compact('pppoeActive'));
    }

    /**
     * Server-side DataTables endpoint.
     */
    public function data(Request $request): JsonResponse
    {
        // Use cached data from persistent monitor if available
        $response = Cache::get('mikrotik_pppoe_active');

        if (!$response || !isset($response['success']) || !$response['success']) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Data tidak tersedia di cache (Monitor mungkin belum berjalan)'
            ]);
        }

        $data = $response['data'];
        
        // Ambil semua customer yang memiliki pppoe_username dan sales
        $customers = Customer::with('sales')->whereNotNull('pppoe_username')->get()->keyBy('pppoe_username');

        // Mapping status isolir dan sales
        foreach ($data as $key => $item) {
            $name = $item['name'] ?? '';
            $address = $item['address'] ?? '';
            
            $data[$key]['is_isolir'] = str_starts_with($address, '172.30.');
            $data[$key]['sales_name'] = isset($customers[$name]) && $customers[$name]->sales ? $customers[$name]->sales->nama_lengkap : '-';
        }
        
        // Simple search
        $search = $request->search['value'];
        if ($search) {
            $data = array_filter($data, function($item) use ($search) {
                return str_contains(strtolower($item['name'] ?? ''), strtolower($search)) || 
                       str_contains(strtolower($item['address'] ?? ''), strtolower($search)) ||
                       str_contains(strtolower($item['sales_name'] ?? ''), strtolower($search));
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

    public function active(): JsonResponse
    {
        // Fallback for full list if still used elsewhere
        $data = Cache::get('mikrotik_pppoe_active');
        if (!$data) {
            return response()->json([
                'success' => false,
                'error' => 'Data tidak tersedia'
            ]);
        }
        return response()->json($data);
    }

    /**
     * Create temporary remote tunnel for ONT/ONU using Mikrotik dst-nat
     */
    public function remoteTunnel(Request $request)
    {
        $request->validate([
            'ip' => 'required|ip',
            'port' => 'nullable|integer'
        ]);

        $ip = $request->ip;
        $port = $request->port ?? 80;

        $log_file = "/tmp/tunnel_" . str_replace('.', '_', $ip) . ".log";

        // Bersihkan tunnel lama untuk IP ini jika masih berjalan
        shell_exec("pkill -f '/usr/local/bin/cloudflared tunnel --url http://{$ip}:{$port}'");
        @unlink($log_file);

        // Eksekusi cloudflared di latar belakang
        shell_exec("nohup /usr/local/bin/cloudflared tunnel --url http://{$ip}:{$port} > {$log_file} 2>&1 &");

        // Tunggu maksimal 5 detik untuk mendapatkan URL dari log
        $url = "";
        for ($i = 0; $i < 5; $i++) {
            sleep(1);
            $log_content = @file_get_contents($log_file);
            if ($log_content && preg_match('/https:\/\/[a-zA-Z0-9.-]+\.trycloudflare\.com/', $log_content, $matches)) {
                $url = $matches[0];
                break;
            }
        }

        if ($url) {
            return response()->json([
                'success' => true,
                'url' => $url,
                'message' => 'Tunnel Cloudflare berhasil dibuat.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mendapatkan tautan dari Cloudflare. Periksa log server.'
        ], 500);
    }
}

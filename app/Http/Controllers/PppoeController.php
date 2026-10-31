<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Nas;
use App\Models\Setting;
use App\Services\MikroTikService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PppoeController extends Controller
{
    public function index(Request $request, MikroTikService $mikrotikService)
    {
        $routerHost = $mikrotikService->getHost();
        $nas = Nas::where('nasname', $routerHost)->first() ?? Nas::first();
        $routerName = $nas?->shortname ?? 'Router MikroTik';

        // Ambil data dari cache jika sudah ada untuk stats awal
        $cached = Cache::get('mikrotik_pppoe_active');
        $totalActive = isset($cached['data']) ? count($cached['data']) : 0;
        $totalIsolir = 0;
        if (!empty($cached['data'])) {
            foreach ($cached['data'] as $item) {
                if (str_starts_with($item['address'] ?? '', '172.30.')) {
                    $totalIsolir++;
                }
            }
        }
        $totalNormal = max(0, $totalActive - $totalIsolir);

        return view('monitoring.pppoe', compact('routerHost', 'routerName', 'totalActive', 'totalNormal', 'totalIsolir'));
    }

    /**
     * Server-side DataTables endpoint dengan live search & pagination cepat.
     */
    public function data(Request $request, MikroTikService $mikrotikService): JsonResponse
    {
        $force = $request->boolean('refresh');
        if ($force) {
            Cache::forget('mikrotik_pppoe_active');
        }

        $response = Cache::get('mikrotik_pppoe_active');
        if (!$response || !isset($response['success']) || !$response['success']) {
            $response = $mikrotikService->getPppoeActive();
            if ($response && isset($response['success']) && $response['success']) {
                Cache::put('mikrotik_pppoe_active', $response, 30);
            }
        }

        if (!$response || !isset($response['success']) || !$response['success']) {
            return response()->json([
                'draw' => intval($request->draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'totalActive' => 0,
                'totalNormal' => 0,
                'totalIsolir' => 0,
                'data' => [],
                'error' => 'Gagal membaca data dari router: ' . ($response['error'] ?? 'Koneksi router gagal')
            ]);
        }

        $rawData = $response['data'] ?? [];
        $totalRecords = count($rawData);

        // Ambil mapping sales (di-cache 60 detik agar hemat query database)
        $customers = Cache::remember('customers_sales_map', 60, function () {
            return Customer::with('sales')->whereNotNull('pppoe_username')->get()->keyBy('pppoe_username');
        });

        // Hitung total status global
        $totalIsolir = 0;
        $processedData = [];

        foreach ($rawData as $item) {
            $name = $item['name'] ?? '';
            $address = $item['address'] ?? '';
            $isIsolir = str_starts_with($address, '172.30.');
            if ($isIsolir) {
                $totalIsolir++;
            }

            $salesName = isset($customers[$name]) && $customers[$name]->sales ? $customers[$name]->sales->nama_lengkap : '-';

            $item['is_isolir'] = $isIsolir;
            $item['sales_name'] = $salesName;
            $processedData[] = $item;
        }

        $totalNormal = max(0, $totalRecords - $totalIsolir);

        // 1. Filter Status (Semua, Normal, Isolir)
        $filterStatus = $request->input('status');
        if ($filterStatus === 'isolir') {
            $processedData = array_filter($processedData, fn($item) => $item['is_isolir']);
        } elseif ($filterStatus === 'normal') {
            $processedData = array_filter($processedData, fn($item) => !$item['is_isolir']);
        }

        // 2. Live Search Server-Side
        $search = strtolower(trim($request->input('search.value', '')));
        if ($search !== '') {
            $processedData = array_filter($processedData, function ($item) use ($search) {
                return str_contains(strtolower($item['name'] ?? ''), $search) ||
                    str_contains(strtolower($item['address'] ?? ''), $search) ||
                    str_contains(strtolower($item['sales_name'] ?? ''), $search) ||
                    str_contains(strtolower($item['caller-id'] ?? ''), $search);
            });
        }

        $filteredRecords = count($processedData);

        // 3. Sorting Dinamis
        $orderColIndex = $request->input('order.0.column');
        $orderDir = strtolower($request->input('order.0.dir', 'asc'));

        $columnMap = [
            1 => 'name',
            2 => 'sales_name',
            3 => 'uptime',
            4 => 'address',
            5 => 'caller-id',
        ];

        if (isset($columnMap[$orderColIndex])) {
            $sortKey = $columnMap[$orderColIndex];
            usort($processedData, function ($a, $b) use ($sortKey, $orderDir) {
                $valA = $a[$sortKey] ?? '';
                $valB = $b[$sortKey] ?? '';
                $cmp = strcmp((string) $valA, (string) $valB);
                return $orderDir === 'desc' ? -$cmp : $cmp;
            });
        } else {
            // Default sort by name ascending
            usort($processedData, fn($a, $b) => strcmp($a['name'] ?? '', $b['name'] ?? ''));
        }

        // 4. Server-side Pagination
        $start = max(0, intval($request->start));
        $length = intval($request->length);
        if ($length > 0) {
            $slicedData = array_slice($processedData, $start, $length);
        } else {
            $slicedData = array_slice($processedData, 0, 10);
        }

        // 5. Format Output Baris DataTables
        $rows = [];
        foreach ($slicedData as $index => $item) {
            $name = $item['name'] ?? '-';
            $address = $item['address'] ?? '-';
            $isIsolir = !empty($item['is_isolir']);
            $salesName = $item['sales_name'] ?? '-';
            $uptime = $item['uptime'] ?? '';
            $uptimeHms = function_exists('format_uptime_hms') ? format_uptime_hms($uptime) : $uptime;
            $callerId = $item['caller-id'] ?? '-';
            $service = $item['service'] ?? 'pppoe';
            $bytesIn = $item['bytes-in'] ?? 0;
            $bytesOut = $item['bytes-out'] ?? 0;

            $statusBadge = $isIsolir
                ? '<span style="background:#fee2e2; color:#be123c; border:1px solid #fecdd3; font-size:10px; padding:2px 6px; border-radius:6px; font-weight:800; margin-left:6px;"><i class="fa-solid fa-ban mr-1"></i>ISOLIR</span>'
                : '<span style="background:#dcfce7; color:#15803d; border:1px solid #bbf7d0; font-size:10px; padding:2px 6px; border-radius:6px; font-weight:800; margin-left:6px;"><i class="fa-solid fa-circle-check mr-1"></i>AKTIF</span>';

            $userHtml = '<div style="font-weight: 700; color: #0f172a; display:inline-flex; align-items:center;">' .
                htmlspecialchars($name, ENT_QUOTES) . $statusBadge . '</div>';

            $salesHtml = '<span style="font-weight: 600; color: #475569; font-size: 11px;">' . htmlspecialchars($salesName, ENT_QUOTES) . '</span>';

            $uptimeHtml = '<span style="font-family: monospace; font-size: 11px; color: #64748b; font-weight: 600;">' . htmlspecialchars($uptimeHms, ENT_QUOTES) . '</span>';

            $addressHtml = '<span style="font-family: monospace; font-weight: 700; color: ' . ($isIsolir ? '#e11d48' : '#0284c7') . '; font-size: 12px;">' . htmlspecialchars($address, ENT_QUOTES) . '</span>';

            $callerHtml = '<span style="color: #64748b; font-size: 11px; font-family: monospace;">' . htmlspecialchars($callerId, ENT_QUOTES) . '</span>';

            $detailPayload = htmlspecialchars(json_encode([
                'name' => $name,
                'sales' => $salesName,
                'address' => $address,
                'uptime' => $uptime,
                'caller' => $callerId,
                'service' => $service,
                'bytes_in' => $bytesIn,
                'bytes_out' => $bytesOut,
                'is_isolir' => $isIsolir,
            ]), ENT_QUOTES);

            $opsiHtml = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Detail Koneksi & Remote ONT" onclick=\'showDetail(' . $detailPayload . ')\'><i class="fa-solid fa-eye text-primary"></i></button>';

            $rows[] = [
                'DT_RowIndex' => $start + $index + 1,
                'user_html' => $userHtml,
                'sales_html' => $salesHtml,
                'uptime_html' => $uptimeHtml,
                'address_html' => $addressHtml,
                'caller_html' => $callerHtml,
                'opsi' => $opsiHtml,
            ];
        }

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'totalActive' => $totalRecords,
            'totalNormal' => $totalNormal,
            'totalIsolir' => $totalIsolir,
            'data' => $rows,
        ]);
    }

    public function active(MikroTikService $mikrotikService): JsonResponse
    {
        $data = Cache::get('mikrotik_pppoe_active');
        if (!$data || !isset($data['success']) || !$data['success']) {
            $data = $mikrotikService->getPppoeActive();
            if ($data && isset($data['success']) && $data['success']) {
                Cache::put('mikrotik_pppoe_active', $data, 25);
            }
        }
        if (!$data) {
            return response()->json([
                'success' => false,
                'error' => 'Data tidak tersedia dari router'
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

        // Eksekusi cloudflared di latar belakang dengan batasan waktu 5 menit (300 detik)
        shell_exec("nohup timeout 5m /usr/local/bin/cloudflared tunnel --url http://{$ip}:{$port} > {$log_file} 2>&1 &");

        // Tunggu maksimal 10 detik untuk mendapatkan URL dari log
        $url = "";
        for ($i = 0; $i < 10; $i++) {
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

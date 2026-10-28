<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Pppoe;
use App\Models\Paket;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Radcheck;
use App\Models\Radusergroup;
use App\Models\Radreply;
use App\Models\Userinfo;
use App\Services\MikroTikService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index()
    {
        if (auth()->user()->jabatan !== 'Teknisi' && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $user = auth()->user();

        /** * PENTING: Menggunakan withTrashed()
         * Ini memastikan PO yang sudah pernah diaktivasi (walaupun pelanggannya sudah dihapus/dismantle)
         * tidak akan muncul kembali di daftar tugas teknisi.
         */
        $activatedPOIds = Customer::withTrashed()->pluck('pre_order_id');

        // Daftar teknisi untuk dropdown Partner
        $teknisiUsers = User::where('jabatan', 'Teknisi')->get();
        $baseQuery = PreOrder::whereNotIn('id', $activatedPOIds);

        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        
        $totalThisMonth = PreOrder::whereNotIn('id', $activatedPOIds)
            ->whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();
            
        $totalThisWeek = PreOrder::whereNotIn('id', $activatedPOIds)
            ->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();

        $totalPending = $baseQuery->count();
        $nextIdPelanggan = Customer::generateNextIdPelanggan();

        if ($user->jabatan === 'Teknisi') {
            // Tugas Umum (Bisa diambil siapa saja)
            $generalTasks = $baseQuery->clone()
                ->orderBy('created_at', 'asc')
                ->get();

            return view('customers.index', compact(
                'generalTasks', 
                'teknisiUsers',
                'totalThisMonth',
                'totalThisWeek',
                'totalPending',
                'nextIdPelanggan'
            ));
        } else {
            // Administrator View: Menampilkan semua PO yang menunggu aktivasi
            $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get();

            return view('customers.index', compact(
                'allPreOrders', 
                'teknisiUsers',
                'totalThisMonth',
                'totalThisWeek',
                'totalPending',
                'nextIdPelanggan'
            ));
        }
    }

    public function activeList(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login.');
        }

        $user = auth()->user();

        // --- 1. MAPPING STATUS ONLINE/OFFLINE (CACHED 60s) ---
        $pppoeData = \Illuminate\Support\Facades\Cache::get('mikrotik_pppoe_active');
        if (!$pppoeData || !isset($pppoeData['success']) || !$pppoeData['success']) {
            try {
                $pppoeData = app(\App\Services\MikroTikService::class)->getPppoeActive();
                if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
                    \Illuminate\Support\Facades\Cache::put('mikrotik_pppoe_active', $pppoeData, 60);
                }
            } catch (\Throwable $e) {}
        }
        $activeUsernames = ($pppoeData && isset($pppoeData['success']) && $pppoeData['success'])
            ? collect($pppoeData['data'])->pluck('name')->toArray()
            : [];

        // --- 2. DATATABLES SERVER SIDE (LIGHTWEIGHT & FAST) ---
        if ($request->ajax()) {
            $query = Customer::withTrashed()
                ->select([
                    'customers.id',
                    'customers.pre_order_id',
                    'customers.id_pelanggan',
                    'customers.nama_lengkap',
                    'customers.no_whatsapp',
                    'customers.pppoe_username',
                    'customers.pppoe_password',
                    'customers.jenis_modem',
                    'customers.sn_perangkat',
                    'customers.pembayaran',
                    'customers.keterangan',
                    'customers.foto_petugas',
                    'customers.foto_terpasang',
                    'customers.foto_speedtest',
                    'customers.id_teknisi',
                    'customers.id_sales',
                    'customers.paket_id',
                    'customers.created_at',
                    'customers.deleted_at'
                ])
                ->with([
                    'teknisiUtama:id,nama_lengkap',
                    'sales:id,nama_lengkap',
                    'paket:id,nama_paket,harga',
                    'preOrder:id,no_identitas,alamat_email,titik_koordinat,alamat_lengkap,created_at'
                ]);

            if ($user->jabatan === 'Sales') {
                $query->where('customers.id_sales', $user->id);
            }

            // Only fetch active isolir records (lightweight!)
            $isolirMap = \App\Models\Pppoe::where('is_isolir', 1)
                ->get(['username', 'isolir_reason', 'isolir_at'])
                ->keyBy('username');

            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('customers.id_pelanggan', 'like', "%{$search}%")
                              ->orWhere('customers.nama_lengkap', 'like', "%{$search}%")
                              ->orWhere('customers.pppoe_username', 'like', "%{$search}%")
                              ->orWhere('customers.no_whatsapp', 'like', "%{$search}%")
                              ->orWhereHas('paket', function ($pq) use ($search) {
                                  $pq->where('nama_paket', 'like', "%{$search}%");
                              })
                              ->orWhereHas('teknisiUtama', function ($tq) use ($search) {
                                  $tq->where('nama_lengkap', 'like', "%{$search}%");
                              });
                        });
                    }
                })
                ->addColumn('id_formatted', function($customer) {
                    return '<div class="text-center font-mono text-xs font-semibold text-slate-700">' . htmlspecialchars($customer->id_pelanggan ?: '-') . '</div>';
                })
                ->addColumn('nama_lengkap_formatted', function($customer) {
                    $html = '<div class="text-left font-bold text-slate-900 text-[13px] leading-snug">' . htmlspecialchars($customer->nama_lengkap) . '</div>';
                    $subInfo = [];
                    if ($customer->no_whatsapp) {
                        $subInfo[] = '<a href="https://wa.me/' . htmlspecialchars($customer->no_whatsapp, ENT_QUOTES) . '" target="_blank" class="text-slate-500 hover:text-emerald-600 inline-flex items-center gap-1"><i class="fa-brands fa-whatsapp text-emerald-600 text-[11px]"></i> ' . htmlspecialchars($customer->no_whatsapp) . '</a>';
                    }
                    if ($customer->pppoe_username) {
                        $subInfo[] = '<span class="text-slate-400">PPPoE: <span class="text-slate-600 font-medium">' . htmlspecialchars($customer->pppoe_username) . '</span></span>';
                    }
                    if (!empty($subInfo)) {
                        $html .= '<div class="text-left text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">' . implode('<span class="text-slate-300">•</span>', $subInfo) . '</div>';
                    }
                    return $html;
                })
                ->addColumn('paket_formatted', function($customer) {
                    $paketName = $customer->paket ? $customer->paket->nama_paket : '-';
                    return '<div class="text-center"><span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-weight: 600;">' . htmlspecialchars($paketName) . '</span></div>';
                })
                ->addColumn('teknisi_formatted', function($customer) {
                    $teknisi = $customer->teknisiUtama->nama_lengkap ?? '-';
                    return '<div class="text-left text-xs font-medium text-slate-700 whitespace-nowrap"><i class="fa-regular fa-user text-[11px] text-slate-400 mr-1.5"></i>' . htmlspecialchars($teknisi) . '</div>';
                })
                ->addColumn('tgl_formatted', function($customer) {
                    return '<div class="text-center font-mono text-xs text-slate-500">' . \Carbon\Carbon::parse($customer->created_at)->format('d/m/Y') . '</div>';
                })
                ->addColumn('status_opsi', function($customer) use ($activeUsernames, $isolirMap, $user) {
                    $isOnline = in_array($customer->pppoe_username, $activeUsernames);
                    $pIsolir = $customer->pppoe_username ? $isolirMap->get($customer->pppoe_username) : null;
                    $isIsolir = (bool)$pIsolir;

                    // Subtle neutral status badge (similar to /pre-orders)
                    if ($customer->trashed()) {
                        $badgeStatus = '<span class="badge badge-archived text-[10px] font-semibold py-0.5 px-2">Non-Aktif</span>';
                    } elseif ($isIsolir) {
                        $badgeStatus = '<span class="badge text-[10px] font-semibold py-0.5 px-2 bg-rose-50 text-rose-700 border border-rose-200">Isolir</span>';
                    } elseif ($isOnline) {
                        $badgeStatus = '<span class="badge text-[10px] font-semibold py-0.5 px-2 bg-emerald-50 text-emerald-700 border border-emerald-200">Online</span>';
                    } else {
                        $badgeStatus = '<span class="badge text-[10px] font-semibold py-0.5 px-2 bg-slate-50 text-slate-500 border border-slate-200">Offline</span>';
                    }

                    $isAdmin = $user->isAdmin();
                    $isolirReason = $pIsolir ? ($pIsolir->isolir_reason ?? '') : '';
                    $isolirAt = $pIsolir && $pIsolir->isolir_at ? \Carbon\Carbon::parse($pIsolir->isolir_at)->format('d-m-Y H:i') : '';

                    // Compact action buttons (exact same size & style as /pre-orders)
                    $btnDetail = '
                        <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Detail Informasi"
                            onclick="openCustomerDetailModal(this)"
                            data-id="' . $customer->id . '"
                            data-customer-id="' . htmlspecialchars($customer->id_pelanggan ?? '', ENT_QUOTES) . '"
                            data-nama-lengkap="' . htmlspecialchars($customer->nama_lengkap ?? '', ENT_QUOTES) . '"
                            data-pppoe-user="' . htmlspecialchars($customer->pppoe_username ?? '', ENT_QUOTES) . '"
                            data-pppoe-pass="' . htmlspecialchars($customer->pppoe_password ?? '', ENT_QUOTES) . '"
                            data-is-isolir="' . ($isIsolir ? '1' : '0') . '"
                            data-isolir-reason="' . htmlspecialchars($isolirReason, ENT_QUOTES) . '"
                            data-isolir-at="' . htmlspecialchars($isolirAt, ENT_QUOTES) . '"
                            data-isolir-url="' . route('customers.isolir', $customer->id) . '"
                            data-buka-isolir-url="' . route('customers.buka_isolir', $customer->id) . '"
                            data-jenis-modem="' . htmlspecialchars($customer->jenis_modem ?? '', ENT_QUOTES) . '"
                            data-sn-perangkat="' . htmlspecialchars($customer->sn_perangkat ?? '', ENT_QUOTES) . '"
                            data-pembayaran="' . htmlspecialchars($customer->pembayaran ?? '', ENT_QUOTES) . '"
                            data-tgl-aktivasi="' . \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y') . '"
                            data-tgl-aktivasi-raw="' . \Carbon\Carbon::parse($customer->created_at)->format('Y-m-d') . '"
                            data-po-paket="' . htmlspecialchars($customer->paket ? $customer->paket->nama_paket : '', ENT_QUOTES) . '"
                            data-paket-id="' . htmlspecialchars($customer->paket_id ?? '', ENT_QUOTES) . '"
                            data-po-sales="' . htmlspecialchars($customer->sales->nama_lengkap ?? 'N/A', ENT_QUOTES) . '"
                            data-id-sales="' . htmlspecialchars($customer->id_sales ?? '', ENT_QUOTES) . '"
                            data-po-nik="' . htmlspecialchars($customer->preOrder->no_identitas ?? 'N/A', ENT_QUOTES) . '"
                            data-po-email="' . htmlspecialchars($customer->preOrder->alamat_email ?? 'N/A', ENT_QUOTES) . '"
                            data-po-alamat="' . htmlspecialchars($customer->preOrder->alamat_lengkap ?? ($customer->alamat_lengkap ?? ''), ENT_QUOTES) . '"
                            data-po-wa="' . htmlspecialchars($customer->no_whatsapp ?? '', ENT_QUOTES) . '"
                            data-po-tgl-po="' . \Carbon\Carbon::parse($customer->preOrder->created_at ?? $customer->created_at)->format('d-m-Y') . '"
                            data-tgl-po-raw="' . \Carbon\Carbon::parse($customer->preOrder->created_at ?? $customer->created_at)->format('Y-m-d') . '"
                            data-po-tikor="' . htmlspecialchars($customer->preOrder->titik_koordinat ?? 'N/A', ENT_QUOTES) . '"
                            data-foto-petugas="' . ($customer->foto_petugas ? \Storage::url($customer->foto_petugas) : '') . '"
                            data-foto-terpasang="' . ($customer->foto_terpasang ? \Storage::url($customer->foto_terpasang) : '') . '"
                            data-foto-speedtest="' . ($customer->foto_speedtest ? \Storage::url($customer->foto_speedtest) : '') . '"
                            data-keterangan="' . htmlspecialchars($customer->keterangan ?? 'Tidak ada keterangan.', ENT_QUOTES) . '">
                            <i class="fa-solid fa-circle-info text-slate-500"></i>
                        </button>
                    ';

                    $btnAcs = '';
                    if (!$customer->trashed()) {
                        $btnAcs = '
                            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Status Modem & Wi-Fi (ACS)"
                                onclick="openCustomerAcsModal(' . $customer->id . ', \'' . htmlspecialchars(addslashes($customer->nama_lengkap), ENT_QUOTES) . '\', \'' . htmlspecialchars($customer->id_pelanggan ?? '', ENT_QUOTES) . '\')">
                                <i class="fa-solid fa-satellite-dish text-slate-500"></i>
                            </button>
                        ';
                    }

                    $btnMap = '';
                    if (!$customer->trashed() && $customer->preOrder && $customer->preOrder->titik_koordinat) {
                        $btnMap = '
                            <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Lihat di Peta"
                                onclick="focusOnCustomer(\'' . $customer->preOrder->titik_koordinat . '\', \'' . $customer->id_pelanggan . '\')">
                                <i class="fa-solid fa-location-dot text-slate-500"></i>
                            </button>
                        ';
                    }

                    $btnIsolir = '';
                    if ($isAdmin && !$customer->trashed() && !empty($customer->pppoe_username)) {
                        if ($isIsolir) {
                            $btnIsolir = '
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Buka Isolir Pelanggan"
                                    onclick="openBukaIsolirCustomerModal(\'' . route('customers.buka_isolir', $customer->id) . '\', \'' . htmlspecialchars(addslashes($customer->nama_lengkap), ENT_QUOTES) . '\', \'' . htmlspecialchars(addslashes($customer->pppoe_username), ENT_QUOTES) . '\')">
                                    <i class="fa-solid fa-lock-open text-emerald-600"></i>
                                </button>
                            ';
                        } else {
                            $btnIsolir = '
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Isolir Pelanggan"
                                    onclick="openIsolirCustomerModal(\'' . route('customers.isolir', $customer->id) . '\', \'' . htmlspecialchars(addslashes($customer->nama_lengkap), ENT_QUOTES) . '\', \'' . htmlspecialchars(addslashes($customer->pppoe_username), ENT_QUOTES) . '\')">
                                    <i class="fa-solid fa-ban text-amber-600"></i>
                                </button>
                            ';
                        }
                    }

                    $btnAdmin = '';
                    if ($isAdmin) {
                        if ($customer->trashed()) {
                            $btnAdmin = '
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Aktifkan"
                                    onclick="confirmGlobalDelete(\'' . route('customers.restore', $customer->id) . '\', \'' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . ' (Restore)\')">
                                    <i class="fa-solid fa-rotate-left text-slate-500"></i>
                                </button>
                            ';
                        } else {
                            $btnAdmin = '
                                <button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50" title="Non-aktifkan"
                                    onclick="confirmGlobalDelete(\'' . route('customers.destroy', $customer->id) . '\', \'' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . '\')">
                                    <i class="fa-solid fa-trash text-slate-400 hover:text-rose-500"></i>
                                </button>
                            ';
                        }
                    }

                    return '<div class="flex items-center justify-center gap-1.5">' . $badgeStatus . '<div class="flex items-center gap-1">' . $btnDetail . $btnAcs . $btnMap . $btnIsolir . $btnAdmin . '</div></div>';
                })
                ->rawColumns(['id_formatted', 'nama_lengkap_formatted', 'paket_formatted', 'teknisi_formatted', 'tgl_formatted', 'status_opsi'])
                ->make(true);
        }

        // --- 3. INITIAL PAGE LOAD DATA (OPTIMIZED) ---
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $statsQuery = Customer::query();
        if ($user->jabatan === 'Sales') {
            $statsQuery->where('id_sales', $user->id);
        }

        $totalActive = (clone $statsQuery)->count();
        $newThisMonth = (clone $statsQuery)->whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();
        $newThisWeek = (clone $statsQuery)->whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();
        $totalArchived = (clone $statsQuery)->onlyTrashed()->count();

        // Data Pelanggan Isolir
        $pelangganIsolir = collect();
        $dbIsolirUsernames = \App\Models\Pppoe::where('is_isolir', true)->pluck('username')->toArray();
        $mikrotikIsolirUsernames = [];
        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $mikrotikIsolirUsernames = collect($pppoeData['data'])
                ->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
                ->pluck('name')
                ->toArray();
        }
        $allIsolirUsernames = array_unique(array_merge($dbIsolirUsernames, $mikrotikIsolirUsernames));

        // Hanya load koleksi data isolir jika bukan Admin (misal: Sales)
        if (!$user->isAdmin() && !empty($allIsolirUsernames)) {
            $isolirQuery = Customer::with(['preOrder:id,titik_koordinat', 'sales:id,nama_lengkap', 'paket:id,nama_paket'])
                ->whereIn('pppoe_username', $allIsolirUsernames);
            if ($user->jabatan === 'Sales') {
                $isolirQuery->where('id_sales', $user->id);
            }
            $pelangganIsolir = $isolirQuery->get();
        }

        // Map Data: ONLY query active customers with coordinates!
        $activeData = ($pppoeData && isset($pppoeData['success']) && $pppoeData['success'])
            ? collect($pppoeData['data'])->keyBy('name')->toArray()
            : [];

        $mapCustomers = Customer::withoutTrashed()
            ->whereHas('preOrder', fn($q) => $q->whereNotNull('titik_koordinat')->where('titik_koordinat', '!=', ''))
            ->select(['id', 'pre_order_id', 'nama_lengkap', 'pppoe_username', 'paket_id', 'alamat_lengkap'])
            ->with(['paket:id,nama_paket', 'preOrder:id,titik_koordinat'])
            ->get();

        $mapData = [];
        foreach ($mapCustomers as $c) {
            $tikor = $c->preOrder ? $c->preOrder->titik_koordinat : null;
            if ($tikor) {
                $isOnline = in_array($c->pppoe_username, $activeUsernames);
                $uptime = $isOnline ? ($activeData[$c->pppoe_username]['uptime'] ?? '-') : '-';
                $address = $isOnline ? ($activeData[$c->pppoe_username]['address'] ?? '-') : '-';
                $bytesIn = $isOnline ? ($activeData[$c->pppoe_username]['bytes-in'] ?? null) : null;
                $bytesOut = $isOnline ? ($activeData[$c->pppoe_username]['bytes-out'] ?? null) : null;

                $mapData[] = [
                    'id' => $c->id,
                    'nama' => $c->nama_lengkap,
                    'username' => $c->pppoe_username,
                    'paket' => $c->paket ? $c->paket->nama_paket : '',
                    'alamat' => $c->alamat_lengkap,
                    'koordinat' => $tikor,
                    'is_online' => $isOnline,
                    'uptime' => $uptime,
                    'ip_address' => $address,
                    'bytes_in' => $bytesIn,
                    'bytes_out' => $bytesOut,
                ];
            }
        }

        $statsMap = [
            'total' => count($mapData),
            'online' => collect($mapData)->where('is_online', true)->count(),
            'offline' => collect($mapData)->where('is_online', false)->count(),
        ];

        // Stats Online/Offline/Isolir Keseluruhan (Admin)
        $totalOnlineAll = 0;
        $totalOfflineAll = 0;
        $totalIsolirAll = 0;

        if ($user->isAdmin()) {
            if (!empty($activeUsernames)) {
                $totalOnlineAll = Customer::withoutTrashed()
                    ->whereIn('pppoe_username', $activeUsernames)
                    ->count();
            } else {
                $totalOnlineAll = 0;
            }
            $totalOfflineAll = max(0, $totalActive - $totalOnlineAll);
            $totalIsolirAll = !empty($allIsolirUsernames)
                ? Customer::withoutTrashed()->whereIn('pppoe_username', $allIsolirUsernames)->count()
                : 0;
        }

        $salesUsers = User::where('jabatan', 'Sales')->orderBy('nama_lengkap')->get(['id', 'nama_lengkap']);

        return view('customers.active_list', compact(
            'totalActive',
            'newThisMonth',
            'newThisWeek',
            'totalArchived',
            'pelangganIsolir',
            'mapData',
            'statsMap',
            'totalOnlineAll',
            'totalOfflineAll',
            'totalIsolirAll',
            'salesUsers'
        ));
    }

    public function exportIsolir(Request $request)
    {
        $user = auth()->user();
        if ($user->jabatan !== 'Sales' && !$user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $pppoeData = \Illuminate\Support\Facades\Cache::get('mikrotik_pppoe_active');
        $pelangganIsolir = collect();

        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $isolirUsernames = collect($pppoeData['data'])
                ->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
                ->pluck('name')
                ->toArray();

            if (!empty($isolirUsernames)) {
                $isolirQuery = Customer::with('sales')->whereIn('pppoe_username', $isolirUsernames);

                if ($user->jabatan === 'Sales') {
                    $isolirQuery->where('id_sales', $user->id);
                } else if ($request->sales_id) {
                    $isolirQuery->where('id_sales', $request->sales_id);
                }

                $pelangganIsolir = $isolirQuery->get();
            }
        }

        $salesFilter = null;
        if ($request->sales_id && $user->isAdmin()) {
            $salesFilter = User::find($request->sales_id);
        }

        // Summary isolir per sales (mengabaikan administrator)
        $salesSummary = [];
        $salesGroups = $pelangganIsolir->groupBy('id_sales');
        foreach ($salesGroups as $salesId => $groupCustomers) {
            if ($salesId) {
                $salesUser = User::find($salesId);
                // Hanya masukkan jika jabatan adalah Sales
                if ($salesUser && $salesUser->jabatan === 'Sales') {
                    $salesSummary[] = [
                        'nama' => $salesUser->nama_lengkap,
                        'total' => $groupCustomers->count()
                    ];
                }
            }
        }
        
        // Urutkan summary berdasarkan total isolir terbanyak
        usort($salesSummary, function($a, $b) {
            return $b['total'] <=> $a['total'];
        });

        $pdf = \PDF::loadView('customers.export-isolir', [
            'pelangganIsolir' => $pelangganIsolir,
            'sales' => $salesFilter,
            'total' => $pelangganIsolir->count(),
            'salesSummary' => $salesSummary
        ]);

        $fileName = 'laporan-isolir-' . now()->format('Y-m-d') . '.pdf';
        return $pdf->download($fileName);
    }

    public function store(Request $request)
    {
        if (auth()->user()->jabatan !== 'Teknisi' && !auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $fotoPetugasPath = null;
        $fotoTerpasangPath = null;
        $fotoSpeedtestPath = null;

        $po = PreOrder::findOrFail($request->pre_order_id);

        if (empty($request->id_pelanggan)) {
            $request->merge(['id_pelanggan' => Customer::generateNextIdPelanggan()]);
        }
        if (empty($request->pppoe_username)) {
            $request->merge(['pppoe_username' => Customer::generateSuggestedPppoeUsername($po->nama_lengkap)]);
        }
        if (empty($request->pppoe_password)) {
            $request->merge(['pppoe_password' => '123']);
        }

        try {
            $validatedData = $request->validate([
                'pre_order_id' => 'required|exists:pre_orders,id',
                'id_pelanggan' => 'required|string|unique:customers,id_pelanggan',
                'id_teknisi' => 'required|exists:users,id',
                'pppoe_username' => 'required|string',
                'pppoe_password' => 'required|string',
                'jenis_modem' => 'required|string',
                'sn_perangkat' => 'required|string',
                'pembayaran' => 'required|in:Cash,Aplikasi,Transfer',
                'partner_ids' => 'nullable|array',
                'partner_ids.*' => 'exists:users,id',
                'keterangan' => 'nullable|string',
                'foto_petugas' => 'required|image|max:20480',
                'foto_terpasang' => 'required|image|max:20480',
                'foto_speedtest' => 'required|image|max:20480',
            ]);

            $partnerNames = "";
            if ($request->has('partner_ids') && !empty($request->partner_ids)) {
                $partnerNames = User::whereIn('id', $request->partner_ids)
                    ->pluck('nama_lengkap')
                    ->implode(', ');
            }

            $fotoPetugasPath = $request->file('foto_petugas')->store('customers/foto_petugas');
            $fotoTerpasangPath = $request->file('foto_terpasang')->store('customers/foto_terpasang');
            $fotoSpeedtestPath = $request->file('foto_speedtest')->store('customers/foto_speedtest');

            $customer = Customer::create([
                'pre_order_id' => $po->id,
                'id_sales' => $po->id_sales,
                'id_teknisi' => $validatedData['id_teknisi'],
                'nama_lengkap' => $po->nama_lengkap,
                'alamat_lengkap' => $po->alamat_lengkap,
                'no_whatsapp' => $po->no_whatsapp,
                'paket_id' => $po->paket_id,
                'id_pelanggan' => $validatedData['id_pelanggan'],
                'pppoe_username' => $validatedData['pppoe_username'],
                'pppoe_password' => $validatedData['pppoe_password'],
                'jenis_modem' => $validatedData['jenis_modem'],
                'sn_perangkat' => $validatedData['sn_perangkat'],
                'pembayaran' => $validatedData['pembayaran'],
                'partner' => $partnerNames,
                'partner_ids' => $request->partner_ids,
                'keterangan' => $validatedData['keterangan'],
                'foto_petugas' => $fotoPetugasPath,
                'foto_terpasang' => $fotoTerpasangPath,
                'foto_speedtest' => $fotoSpeedtestPath,
            ]);

            // Sinkronkan ke Akun PPPoE & FreeRADIUS
            $isPaid = ($validatedData['pembayaran'] === 'Cash');
            $pppoe = Pppoe::updateOrCreate(
                ['username' => $validatedData['pppoe_username']],
                [
                    'pre_order_id' => $po->id,
                    'password' => $validatedData['pppoe_password'],
                    'paket_id' => $po->paket_id,
                    'tipe_pembayaran' => 'prepaid',
                    'tanggal_aktif' => now(),
                    'periode_billing' => '1',
                    'pajak' => $po->paket->pajak ?? 0,
                    'diskon' => $po->paket->diskon ?? 0,
                    'status_invoice' => $isPaid ? 'terbayar' : 'belum_bayar',
                    'generate_invoice' => true,
                    'is_isolir' => false,
                ]
            );

            // FreeRADIUS sync
            try {
                Radcheck::updateOrCreate(
                    ['username' => $pppoe->username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $pppoe->password]
                );

                $paket = $po->paket ?? Paket::find($po->paket_id);
                if ($paket) {
                    Radusergroup::updateOrCreate(
                        ['username' => $pppoe->username],
                        ['groupname' => $paket->nama_paket, 'priority' => 1]
                    );
                }

                Userinfo::updateOrCreate(
                    ['username' => $pppoe->username],
                    [
                        'firstname' => $po->nama_lengkap ?? 'Pelanggan',
                        'lastname' => '',
                        'email' => $po->alamat_email ?? '',
                        'address' => $po->alamat_lengkap ?? '',
                        'mobilephone' => $po->no_whatsapp ?? '',
                        'creationdate' => date('Y-m-d H:i:s'),
                        'updatedate' => date('Y-m-d H:i:s'),
                        'creationby' => auth()->user()->nama_lengkap ?? 'system',
                        'updateby' => auth()->user()->nama_lengkap ?? 'system',
                    ]
                );
            } catch (\Throwable $e) {
                Log::warning("Radius sync error on Customer Activation ({$pppoe->username}): " . $e->getMessage());
            }

            // Generate First Invoice & Transaction jika lunas
            try {
                $paket = $po->paket ?? Paket::find($po->paket_id);
                if ($paket) {
                    $amount = (float) $paket->harga;
                    $tax = (float) ($paket->pajak ?? 0);
                    $discount = (float) ($paket->diskon ?? 0);
                    $total = max(0, $amount + $tax - $discount);
                    $dueDate = Carbon::now()->addDays(7);

                    $invoice = Invoice::create([
                        'pppoe_id' => $pppoe->id,
                        'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                        'amount' => $amount,
                        'tax' => $tax,
                        'discount' => $discount,
                        'total' => $total,
                        'due_date' => $dueDate->format('Y-m-d'),
                        'status' => $isPaid ? 'paid' : 'unpaid',
                        'payment_method' => $isPaid ? 'cash' : null,
                    ]);

                    if ($isPaid) {
                        Transaction::create([
                            'invoice_id' => $invoice->id,
                            'payment_method' => 'cash',
                            'amount_paid' => $total,
                            'payment_date' => now(),
                            'status' => 'success',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("Invoice generation error on Customer Activation: " . $e->getMessage());
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity_type' => 'aktivasi_pelanggan',
                'description' => 'Mengaktivasi pelanggan: ' . $po->nama_lengkap,
                'logged_at' => now(),
            ]);

            return redirect()->route('customers.index')->with('success', 'Aktivasi pelanggan berhasil!');
        } catch (ValidationException $e) {
            $errorMessages = implode('<br>', $e->validator->errors()->all());
            return redirect()->back()->withInput()->withErrors($e->validator)->with('error', $errorMessages);
        } catch (\Exception $e) {
            if (isset($fotoPetugasPath)) \Storage::delete($fotoPetugasPath);
            if (isset($fotoTerpasangPath)) \Storage::delete($fotoTerpasangPath);
            if (isset($fotoSpeedtestPath)) \Storage::delete($fotoSpeedtestPath);
            return redirect()->back()->withInput()->with('error', 'Gagal Aktivasi: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return redirect()->route('customers.active-list')->with('error', 'Unauthorized action.');
        }

        $customer = Customer::findOrFail($id);

        $request->validate([
            'id_pelanggan' => 'required|string|unique:customers,id_pelanggan,' . $customer->id,
            'nama_lengkap' => 'required|string',
            'no_identitas' => 'nullable|string',
            'alamat_email' => 'nullable|email',
            'id_sales' => 'nullable|exists:users,id',
            'tgl_po' => 'nullable|date',
            'titik_koordinat' => 'nullable|string',
            'alamat_lengkap' => 'required|string',
            'no_whatsapp' => 'required|string',
            'paket_id' => 'required|exists:pakets,id',
            'pembayaran' => 'required|string',
            'pppoe_username' => 'nullable|string',
            'pppoe_password' => 'nullable|string',
            'jenis_modem' => 'nullable|string',
            'sn_perangkat' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'tgl_aktivasi' => 'nullable|date',
            'foto_petugas' => 'nullable|image|max:20480',
            'foto_terpasang' => 'nullable|image|max:20480',
            'foto_speedtest' => 'nullable|image|max:20480',
        ]);

        if ($customer->preOrder) {
            $newSalesId = $request->id_sales ?? $customer->preOrder->id_sales;
            $newSalesName = $newSalesId ? User::find($newSalesId)->nama_lengkap : $customer->preOrder->nama_sales;

            $customer->preOrder->update([
                'nama_lengkap' => $request->nama_lengkap,
                'no_identitas' => $request->no_identitas,
                'alamat_email' => $request->alamat_email,
                'id_sales' => $newSalesId,
                'nama_sales' => $newSalesName,
                'no_whatsapp' => $request->no_whatsapp,
                'alamat_lengkap' => $request->alamat_lengkap,
                'titik_koordinat' => $request->titik_koordinat,
                'paket_id' => $request->paket_id,
                'created_at' => $request->tgl_po ? \Carbon\Carbon::parse($request->tgl_po) : $customer->preOrder->created_at,
            ]);
        }

        $updateData = [
            'id_pelanggan' => $request->id_pelanggan,
            'id_sales' => $request->id_sales ?? $customer->id_sales,
            'nama_lengkap' => $request->nama_lengkap,
            'alamat_lengkap' => $request->alamat_lengkap,
            'no_whatsapp' => $request->no_whatsapp,
            'paket_id' => $request->paket_id,
            'pembayaran' => $request->pembayaran,
            'pppoe_username' => $request->pppoe_username,
            'pppoe_password' => $request->pppoe_password,
            'jenis_modem' => $request->jenis_modem,
            'sn_perangkat' => $request->sn_perangkat,
            'keterangan' => $request->keterangan,
        ];

        if ($request->tgl_aktivasi) {
            $updateData['created_at'] = \Carbon\Carbon::parse($request->tgl_aktivasi);
        }

        if ($request->hasFile('foto_petugas')) {
            if ($customer->foto_petugas) \Storage::delete($customer->foto_petugas);
            $updateData['foto_petugas'] = $request->file('foto_petugas')->store('customers/foto_petugas');
        }
        if ($request->hasFile('foto_terpasang')) {
            if ($customer->foto_terpasang) \Storage::delete($customer->foto_terpasang);
            $updateData['foto_terpasang'] = $request->file('foto_terpasang')->store('customers/foto_terpasang');
        }
        if ($request->hasFile('foto_speedtest')) {
            if ($customer->foto_speedtest) \Storage::delete($customer->foto_speedtest);
            $updateData['foto_speedtest'] = $request->file('foto_speedtest')->store('customers/foto_speedtest');
        }

        $oldUsername = $customer->pppoe_username;
        $customer->update($updateData);

        // Sinkronkan ke Pppoe model & FreeRADIUS
        $pppoe = Pppoe::where('username', $oldUsername)
            ->orWhere('pre_order_id', $customer->pre_order_id)
            ->first();

        if ($pppoe) {
            $pppoe->update([
                'username' => $customer->pppoe_username,
                'password' => $customer->pppoe_password,
                'paket_id' => $customer->paket_id,
            ]);
        } else {
            $pppoe = Pppoe::create([
                'pre_order_id' => $customer->pre_order_id,
                'username' => $customer->pppoe_username,
                'password' => $customer->pppoe_password,
                'paket_id' => $customer->paket_id,
                'tanggal_aktif' => $customer->created_at ?? now(),
                'tipe_pembayaran' => 'prepaid',
                'generate_invoice' => true,
            ]);
        }

        try {
            if ($oldUsername && $oldUsername !== $customer->pppoe_username) {
                Radcheck::where('username', $oldUsername)->delete();
                Radusergroup::where('username', $oldUsername)->delete();
                Radreply::where('username', $oldUsername)->delete();
                Userinfo::where('username', $oldUsername)->delete();
            }

            Radcheck::updateOrCreate(
                ['username' => $customer->pppoe_username, 'attribute' => 'Cleartext-Password'],
                ['op' => ':=', 'value' => $customer->pppoe_password]
            );

            $paket = Paket::find($customer->paket_id);
            if ($paket) {
                Radusergroup::updateOrCreate(
                    ['username' => $customer->pppoe_username],
                    ['groupname' => $paket->nama_paket, 'priority' => 1]
                );
            }

            Userinfo::updateOrCreate(
                ['username' => $customer->pppoe_username],
                [
                    'firstname' => $customer->nama_lengkap,
                    'lastname' => '',
                    'email' => $customer->alamat_email ?? ($customer->preOrder->alamat_email ?? ''),
                    'address' => $customer->alamat_lengkap ?? '',
                    'mobilephone' => $customer->no_whatsapp ?? '',
                    'updatedate' => date('Y-m-d H:i:s'),
                ]
            );
        } catch (\Throwable $e) {
            Log::warning("Radius sync error on Customer update ({$customer->pppoe_username}): " . $e->getMessage());
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_update',
            'description' => "Memperbarui semua data pelanggan: " . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Semua data pelanggan berhasil diperbarui.');
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore(); 

        $username = $customer->pppoe_username;
        $pppoe = Pppoe::where('username', $username)
            ->orWhere('pre_order_id', $customer->pre_order_id)
            ->first();

        if ($pppoe) {
            $pppoe->update([
                'is_isolir' => false,
                'isolir_reason' => null,
                'isolir_at' => null,
            ]);
        }

        // Restore kredensial FreeRADIUS
        try {
            if ($customer->pppoe_password) {
                Radcheck::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Cleartext-Password'],
                    ['op' => ':=', 'value' => $customer->pppoe_password]
                );
            }
            $paket = $customer->paket ?? Paket::find($customer->paket_id);
            if ($paket) {
                Radusergroup::updateOrCreate(
                    ['username' => $username],
                    ['groupname' => $paket->nama_paket, 'priority' => 1]
                );
            }
        } catch (\Throwable $e) {
            Log::warning("Radius restore error on Customer restore ({$username}): " . $e->getMessage());
        }

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_restore',
            'description' => "Mengaktifkan kembali pelanggan: " . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan berhasil diaktifkan kembali dan kredensial FreeRADIUS dipulihkan.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $username = $customer->pppoe_username;

        // Nonaktifkan akun PPPoE
        $pppoe = Pppoe::where('username', $username)
            ->orWhere('pre_order_id', $customer->pre_order_id)
            ->first();

        if ($pppoe) {
            $pppoe->update([
                'is_isolir' => true,
                'isolir_reason' => 'Pelanggan Dinonaktifkan (Dihapus)',
                'isolir_at' => now(),
            ]);
        }

        // Hapus kredensial login dari FreeRADIUS & kick dari router
        try {
            Radcheck::where('username', $username)->delete();
            app(MikroTikService::class)->kickPppoeUser($username);
        } catch (\Throwable $e) {
            Log::warning("Radius disconnect error on Customer delete ({$username}): " . $e->getMessage());
        }

        $customer->delete(); 

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_delete',
            'description' => "Menonaktifkan pelanggan: " . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan berhasil dinonaktifkan, akun PPPoE dan FreeRADIUS dinonaktifkan.");
    }

    /**
     * Ambil status live ACS (GenieACS TR-069) untuk pelanggan tertentu
     */
    public function acsStatus(Customer $customer)
    {
        /** @var \App\Services\GenieAcsService $acsService */
        $acsService = app(\App\Services\GenieAcsService::class);
        $device = $acsService->findDeviceForCustomer($customer);

        if (!$device) {
            return response()->json([
                'success' => false,
                'message' => 'Perangkat ONT belum terdaftar di ACS atau belum terhubung.',
                'customer' => [
                    'id' => $customer->id,
                    'nama_lengkap' => $customer->nama_lengkap,
                    'id_pelanggan' => $customer->id_pelanggan,
                    'sn_perangkat' => $customer->sn_perangkat,
                    'pppoe_username' => $customer->pppoe_username,
                    'jenis_modem' => $customer->jenis_modem,
                ],
            ]);
        }

        $hosts = $device['hosts'] ?? [];
        $activeHosts = array_values(array_filter($hosts, fn($h) => !empty($h['active'])));

        return response()->json([
            'success' => true,
            'customer' => [
                'id' => $customer->id,
                'nama_lengkap' => $customer->nama_lengkap,
                'id_pelanggan' => $customer->id_pelanggan,
                'sn_perangkat' => $customer->sn_perangkat,
                'pppoe_username' => $customer->pppoe_username,
            ],
            'device' => [
                'id' => $device['id'],
                'serial_number' => $device['serial_number'] ?? $customer->sn_perangkat,
                'model' => $device['model'] ?? '-',
                'manufacturer' => $device['manufacturer'] ?? '-',
                'is_online' => (bool) ($device['is_online'] ?? false),
                'rx_power' => $device['rx_power'] ?? null,
                'temperature' => $device['temperature'] ?? null,
                'pon_mode' => $device['pon_mode'] ?? 'GPON/EPON',
                'last_inform' => $device['last_inform'] ?? '-',
                'ip_address' => $device['ip_address'] ?? '-',
                'vlan' => $device['vlan'] ?? null,
                'genieacs_url' => route('genieacs.show', $device['id']),
            ],
            'wlan1' => $device['wlan1'] ?? null,
            'wlan5' => $device['wlan5'] ?? null,
            'hosts' => $hosts,
            'total_connected' => count($activeHosts),
        ]);
    }

    public function isolir(Request $request, Customer $customer, \App\Services\IsolirService $isolirService)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        if (empty($customer->pppoe_username)) {
            $msg = 'Pelanggan ini belum memiliki akun PPPoE yang terhubung.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $reason = $request->input('reason', 'Isolir manual oleh Admin');
        $result = $isolirService->isolate($customer->pppoe_username, $reason, true);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function bukaIsolir(Request $request, Customer $customer, \App\Services\IsolirService $isolirService)
    {
        if (empty($customer->pppoe_username)) {
            $msg = 'Pelanggan ini belum memiliki akun PPPoE yang terhubung.';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $result = $isolirService->unIsolate($customer->pppoe_username);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }
}

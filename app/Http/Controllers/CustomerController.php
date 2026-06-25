<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\ActivityLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

        // --- QUERY DASAR: PO yang BENAR-BENAR BELUM pernah diaktivasi ---
        $baseQuery = PreOrder::whereNotIn('id', $activatedPOIds);

        // --- STATISTIK ---
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

        // --- FILTER KHUSUS TEKNISI ---
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
                'totalPending'
            ));
        } else {
            // Administrator View: Menampilkan semua PO yang menunggu aktivasi
            $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get();

            return view('customers.index', compact(
                'allPreOrders', 
                'teknisiUsers',
                'totalThisMonth',
                'totalThisWeek',
                'totalPending'
            ));
        }
    }

    public function activeList(Request $request)
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Silakan login.');
        }

        $user = auth()->user();

        // Menggunakan withTrashed() agar pelanggan yang sudah di-dismantle tetap muncul dengan badge non-aktif
        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales', 'preOrder']);

        if ($user->jabatan === 'Sales') {
            $query->where('id_sales', $user->id);
        }

        $query->orderBy('id', 'desc');

        // --- MAPPING STATUS ONLINE/OFFLINE ---
        $pppoeData = \Illuminate\Support\Facades\Cache::get('mikrotik_pppoe_active');
        $activeUsernames = [];
        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $activeUsernames = collect($pppoeData['data'])->pluck('name')->toArray();
        }

        // --- DATATABLES SERVER SIDE ---
        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('is_online', function ($customer) use ($activeUsernames) {
                    return in_array($customer->pppoe_username, $activeUsernames);
                })
                ->addColumn('titik_koordinat', function ($customer) {
                    return $customer->preOrder ? $customer->preOrder->titik_koordinat : null;
                })
                ->addColumn('nama_lengkap_formatted', function($customer) use ($activeUsernames) {
                    $isOnline = in_array($customer->pppoe_username, $activeUsernames);
                    $badgeOnline = $isOnline 
                        ? '<span class="badge" style="background: #dcfce7; color: #15803d; font-size: 8px; padding: 2px 6px; border-radius: 4px; font-weight: 800;">ONLINE</span>'
                        : '<span class="badge" style="background: #f1f5f9; color: #64748b; font-size: 8px; padding: 2px 6px; border-radius: 4px; font-weight: 800;">OFFLINE</span>';
                    $badgeTrashed = $customer->trashed() 
                        ? '<span class="badge badge-archived" style="font-size: 9px; margin-top: 2px;">NON-AKTIF</span>' 
                        : '';
                    $pppoe = $customer->pppoe_username ?? '-';
                    return '
                        <div style="display: flex; align-items: center; justify-content: center; gap: 8px;">
                            <div style="font-weight: 700; color: #0f172a;">' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . '</div>
                            ' . $badgeOnline . '
                        </div>
                        ' . $badgeTrashed . '
                        <div style="font-size: 10px; color: #94a3b8; font-weight: 600; margin-top: 2px;">
                            PPPoE: ' . htmlspecialchars($pppoe, ENT_QUOTES) . '
                        </div>
                    ';
                })
                ->addColumn('wa_formatted', function($customer) {
                    return '
                        <a href="https://wa.me/' . htmlspecialchars($customer->no_whatsapp, ENT_QUOTES) . '" target="_blank"
                            style="color: #10b981; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                            <i class="fa-brands fa-whatsapp"></i> ' . htmlspecialchars($customer->no_whatsapp, ENT_QUOTES) . '
                        </a>
                    ';
                })
                ->addColumn('teknisi_formatted', function($customer) {
                    $teknisi = $customer->teknisiUtama->nama_lengkap ?? 'N/A';
                    $partner = $customer->partner ? '<div style="font-size: 10px; color: #94a3b8;">Partner: ' . htmlspecialchars($customer->partner, ENT_QUOTES) . '</div>' : '';
                    return '
                        <div style="font-weight: 600; color: #475569;">' . htmlspecialchars($teknisi, ENT_QUOTES) . '</div>
                        ' . $partner . '
                    ';
                })
                ->addColumn('tgl_formatted', function($customer) {
                    return \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y');
                })
                ->addColumn('action', function($customer) {
                    $isAdmin = auth()->user()->isAdmin();
                    $btnDetail = '
                        <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Detail"
                            onclick="openCustomerDetailModal(this)"
                            data-customer-id="' . htmlspecialchars($customer->id_pelanggan, ENT_QUOTES) . '"
                            data-nama-lengkap="' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . '"
                            data-pppoe-user="' . htmlspecialchars($customer->pppoe_username, ENT_QUOTES) . '"
                            data-pppoe-pass="' . htmlspecialchars($customer->pppoe_password, ENT_QUOTES) . '"
                            data-jenis-modem="' . htmlspecialchars($customer->jenis_modem, ENT_QUOTES) . '"
                            data-sn-perangkat="' . htmlspecialchars($customer->sn_perangkat, ENT_QUOTES) . '"
                            data-pembayaran="' . htmlspecialchars($customer->pembayaran, ENT_QUOTES) . '"
                            data-tgl-aktivasi="' . \Carbon\Carbon::parse($customer->created_at)->format('d-m-Y') . '"
                            data-po-paket="' . htmlspecialchars($customer->paket_internet, ENT_QUOTES) . '"
                            data-po-sales="' . htmlspecialchars($customer->sales->nama_lengkap ?? 'N/A', ENT_QUOTES) . '"
                            data-po-nik="' . htmlspecialchars($customer->preOrder->no_identitas ?? 'N/A', ENT_QUOTES) . '"
                            data-po-email="' . htmlspecialchars($customer->preOrder->alamat_email ?? 'N/A', ENT_QUOTES) . '"
                            data-po-alamat="' . htmlspecialchars($customer->alamat_lengkap, ENT_QUOTES) . '"
                            data-po-wa="' . htmlspecialchars($customer->no_whatsapp, ENT_QUOTES) . '"
                            data-po-tgl-po="' . \Carbon\Carbon::parse($customer->preOrder->created_at ?? $customer->created_at)->format('d-m-Y') . '"
                            data-po-tikor="' . htmlspecialchars($customer->preOrder->titik_koordinat ?? 'N/A', ENT_QUOTES) . '"
                            data-foto-petugas="' . ($customer->foto_petugas ? \Storage::url($customer->foto_petugas) : '') . '"
                            data-foto-terpasang="' . ($customer->foto_terpasang ? \Storage::url($customer->foto_terpasang) : '') . '"
                            data-foto-speedtest="' . ($customer->foto_speedtest ? \Storage::url($customer->foto_speedtest) : '') . '"
                            data-keterangan="' . htmlspecialchars($customer->keterangan ?? 'Tidak ada keterangan.', ENT_QUOTES) . '">
                            <i class="fa-solid fa-circle-info text-primary"></i>
                        </button>
                    ';

                    $btnMap = '';
                    if (!$customer->trashed() && $customer->preOrder && $customer->preOrder->titik_koordinat) {
                        $btnMap = '
                            <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Lihat di Peta"
                                onclick="focusOnCustomer(\'' . $customer->preOrder->titik_koordinat . '\', \'' . $customer->id_pelanggan . '\')">
                                <i class="fa-solid fa-location-dot text-emerald-500"></i>
                            </button>
                        ';
                    }

                    $btnAdmin = '';
                    if ($isAdmin) {
                        if ($customer->trashed()) {
                            $btnAdmin = '
                                <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Aktifkan"
                                    onclick="confirmGlobalDelete(\'' . route('customers.restore', $customer->id) . '\', \'' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . ' (Restore)\')">
                                    <i class="fa-solid fa-rotate-left text-emerald-500"></i>
                                </button>
                            ';
                        } else {
                            $btnAdmin = '
                                <button type="button" class="topbar-icon-btn !w-9 !h-9 !rounded-xl" title="Non-aktifkan"
                                    onclick="confirmGlobalDelete(\'' . route('customers.destroy', $customer->id) . '\', \'' . htmlspecialchars($customer->nama_lengkap, ENT_QUOTES) . '\')">
                                    <i class="fa-solid fa-trash text-rose-500"></i>
                                </button>
                            ';
                        }
                    }

                    return '<div style="display: flex; align-items: center; justify-content: center; gap: 6px; flex-wrap: wrap;">' . $btnDetail . $btnMap . $btnAdmin . '</div>';
                })
                ->rawColumns(['nama_lengkap_formatted', 'wa_formatted', 'teknisi_formatted', 'tgl_formatted', 'action'])
                ->make(true);
        }

        $customers = $query->orderBy('id', 'desc')->get();

        foreach ($customers as $customer) {
            $customer->is_online = in_array($customer->pppoe_username, $activeUsernames);
        }

        // --- STATISTIK ---
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

        // --- DATA PELANGGAN ISOLIR ---
        $pelangganIsolir = collect();
        if ($user->jabatan === 'Sales' || $user->isAdmin()) {
            if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
                $isolirUsernames = collect($pppoeData['data'])
                    ->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
                    ->pluck('name')
                    ->toArray();

                if (!empty($isolirUsernames)) {
                    $isolirQuery = Customer::with('preOrder')
                        ->whereIn('pppoe_username', $isolirUsernames);
                        
                    if ($user->jabatan === 'Sales') {
                        $isolirQuery->where('id_sales', $user->id);
                    }
                    
                    $pelangganIsolir = $isolirQuery->get();
                }
            }
        }

        // --- MAP DATA ---
        $activeData = [];
        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $activeData = collect($pppoeData['data'])->keyBy('name')->toArray();
        }

        $mapData = [];
        foreach ($customers as $c) {
            $tikor = $c->preOrder ? $c->preOrder->titik_koordinat : null;
            if ($tikor && !$c->trashed()) {
                $isOnline = $c->is_online;
                $uptime = $isOnline ? ($activeData[$c->pppoe_username]['uptime'] ?? '-') : '-';
                $address = $isOnline ? ($activeData[$c->pppoe_username]['address'] ?? '-') : '-';
                $bytesIn = $isOnline ? ($activeData[$c->pppoe_username]['bytes-in'] ?? null) : null;
                $bytesOut = $isOnline ? ($activeData[$c->pppoe_username]['bytes-out'] ?? null) : null;
                
                $mapData[] = [
                    'id' => $c->id,
                    'nama' => $c->nama_lengkap,
                    'username' => $c->pppoe_username,
                    'paket' => $c->paket_internet,
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

        // --- STATS ONLINE/OFFLINE/ISOLIR KESELURUHAN (ADMIN) ---
        $totalOnlineAll = 0;
        $totalOfflineAll = 0;
        $totalIsolirAll = 0;

        if ($user->isAdmin()) {
            $totalOnlineAll = $customers->whereNull('deleted_at')->where('is_online', true)->count();
            $totalOfflineAll = $customers->whereNull('deleted_at')->where('is_online', false)->count();
            $totalIsolirAll = $pelangganIsolir->count();
        }

        return view('customers.active_list', compact(
            'customers',
            'totalActive',
            'newThisMonth',
            'newThisWeek',
            'totalArchived',
            'pelangganIsolir',
            'mapData',
            'statsMap',
            'totalOnlineAll',
            'totalOfflineAll',
            'totalIsolirAll'
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

        try {
            // 1. Validasi data input
            $validatedData = $request->validate([
                'pre_order_id' => 'required|exists:pre_orders,id|unique:customers,pre_order_id',
                'id_sales' => 'required|exists:users,id',
                'id_teknisi' => 'required|exists:users,id',
                'pppoe_username' => 'required|string|max:255|unique:customers,pppoe_username',
                'pppoe_password' => 'required|string|min:1',
                'id_pelanggan' => 'required|string|max:255|unique:customers,id_pelanggan',
                'jenis_modem' => 'required|in:EPON,GPON',
                'sn_perangkat' => 'required|string|max:255',
                'pembayaran' => 'required|in:Cash,Aplikasi',
                'partner_ids' => 'nullable|array',
                'partner_ids.*' => 'exists:users,id',
                'keterangan' => 'nullable|string',
                'foto_petugas' => 'required|image|max:20480',
                'foto_terpasang' => 'required|image|max:20480',
                'foto_speedtest' => 'required|image|max:20480',
            ]);

            $po = PreOrder::findOrFail($request->pre_order_id);

            $partnerNames = "";
            if ($request->has('partner_ids') && !empty($request->partner_ids)) {
                $partnerNames = User::whereIn('id', $request->partner_ids)
                    ->pluck('nama_lengkap')
                    ->implode(', ');
            }

            $fotoPetugasPath = $request->file('foto_petugas')->store('customers/foto_petugas');
            $fotoTerpasangPath = $request->file('foto_terpasang')->store('customers/foto_terpasang');
            $fotoSpeedtestPath = $request->file('foto_speedtest')->store('customers/foto_speedtest');

            Customer::create([
                'pre_order_id' => $po->id,
                'id_sales' => $po->id_sales,
                'id_teknisi' => $validatedData['id_teknisi'],
                'nama_lengkap' => $po->nama_lengkap,
                'alamat_lengkap' => $po->alamat_lengkap,
                'no_whatsapp' => $po->no_whatsapp,
                'paket_internet' => $po->paket_internet,
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
            if (isset($fotoPetugasPath)) Storage::delete($fotoPetugasPath);
            if (isset($fotoTerpasangPath)) Storage::delete($fotoTerpasangPath);
            if (isset($fotoSpeedtestPath)) Storage::delete($fotoSpeedtestPath);
            return redirect()->back()->withInput()->with('error', 'Gagal Aktivasi: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore(); 

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_restore',
            'description' => "Mengaktifkan kembali pelanggan: " . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan berhasil diaktifkan kembali.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete(); 

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_delete',
            'description' => "Menonaktifkan pelanggan: " . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan berhasil dinonaktifkan.");
    }
}

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
        if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
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

        // --- FILTER KHUSUS TEKNISI ---
        if ($user->jabatan === 'Teknisi') {
            // 1. Tugas Delegasi Khusus (Prioritas)
            $delegatedTasks = $baseQuery->clone()
                ->where('id_teknisi_delegasi', $user->id)
                ->whereNotNull('delegated_at')
                ->orderBy('delegated_at', 'asc')
                ->get()
                ->map(function ($po) {
                    $po->is_locked = false;
                    $po->deadline_status = 'AKTIF';

                    if ($po->delegated_at) {
                        $deadline = Carbon::parse($po->delegated_at)->addDays(2);
                        $po->deadline_date = $deadline->format('d-m-Y H:i');

                        if (Carbon::now()->greaterThan($deadline)) {
                            $po->deadline_status = 'EXPIRED';
                            $po->is_locked = true;
                        } elseif (Carbon::now()->diffInHours($deadline) <= 24) {
                            $po->deadline_status = 'SEGERA';
                        }
                    }
                    return $po;
                });

            // 2. Tugas Umum (Bisa diambil siapa saja)
            $generalTasks = $baseQuery->clone()
                ->whereNull('id_teknisi_delegasi')
                ->orderBy('created_at', 'asc')
                ->get();

            return view('customers.index', compact('delegatedTasks', 'generalTasks', 'teknisiUsers'));
        } else {
            // Administrator View: Menampilkan semua PO yang menunggu aktivasi
            $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get()->map(function ($po) {
                $po->delegated_to = $po->id_teknisi_delegasi ? (User::find($po->id_teknisi_delegasi)->nama_lengkap ?? 'Unknown') : 'Belum Ditunjuk';
                return $po;
            });

            return view('customers.index', compact('allPreOrders', 'teknisiUsers'));
        }
    }


    // public function index()
    // {
    //     if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
    //         abort(403, 'Akses ditolak.');
    //     }

    //     $user = auth()->user();
    //     $activatedPOIds = Customer::pluck('pre_order_id');

    //     // Mengambil daftar teknisi untuk dropdown Partner (Multi-select)
    //     // Kita ambil user yang jabatannya Teknisi
    //     $teknisiUsers = User::where('jabatan', 'Teknisi')->get();

    //     // --- QUERY DASAR: PO yang BELUM diaktivasi ---
    //     $baseQuery = PreOrder::whereNotIn('id', $activatedPOIds);

    //     // --- FILTER KHUSUS TEKNISI ---
    //     if ($user->jabatan === 'Teknisi') {
    //         $delegatedTasks = $baseQuery->clone()
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->whereNotNull('delegated_at')
    //             ->orderBy('delegated_at', 'asc')
    //             ->get()
    //             ->map(function ($po) {
    //                 $po->is_locked = false;
    //                 $po->deadline_status = 'AKTIF';

    //                 if ($po->delegated_at) {
    //                     $deadline = Carbon::parse($po->delegated_at)->addDays(2);
    //                     $po->deadline_date = $deadline->format('d-m-Y H:i');

    //                     if (Carbon::now()->greaterThan($deadline)) {
    //                         $po->deadline_status = 'EXPIRED';
    //                         $po->is_locked = true;
    //                     } elseif (Carbon::now()->diffInHours($deadline) <= 24) {
    //                         $po->deadline_status = 'SEGERA';
    //                     }
    //                 }
    //                 return $po;
    //             });

    //         $generalTasks = $baseQuery->clone()
    //             ->whereNull('id_teknisi_delegasi')
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         return view('customers.index', compact('delegatedTasks', 'generalTasks', 'teknisiUsers'));
    //     } else {
    //         // Administrator View
    //         $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get()->map(function ($po) {
    //             $po->delegated_to = $po->id_teknisi_delegasi ? (User::find($po->id_teknisi_delegasi)->nama_lengkap ?? 'Unknown') : 'Belum Ditunjuk';
    //             return $po;
    //         });

    //         return view('customers.index', compact('allPreOrders', 'teknisiUsers'));
    //     }
    // }

    // public function activeList()
    // {
    //     if (!auth()->check()) {
    //         return redirect('/login')->with('error', 'Silakan login.');
    //     }

    //     $user = auth()->user();
    //     $query = Customer::query();

    //     if ($user->jabatan === 'Sales') {
    //         $query->where('id_sales', $user->id);
    //     }

    //     $customers = $query->get();
    //     return view('customers.active_list', compact('customers'));
    // }

    public function activeList()
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

        // $customers = $query->orderBy('created_at', 'desc')->get();
        // Ubah pengurutan ke ID DESC agar data terbaru (ID terbesar) muncul paling atas
        $customers = $query->orderBy('id', 'desc')->get();

        // --- MAPPING STATUS ONLINE/OFFLINE ---
        $pppoeData = \Illuminate\Support\Facades\Cache::get('mikrotik_pppoe_active');
        $activeUsernames = [];
        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $activeUsernames = collect($pppoeData['data'])->pluck('name')->toArray();
        }

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
        if ($user->jabatan === 'Sales' || $user->jabatan === 'Administrator') {
            $pppoeData = \Illuminate\Support\Facades\Cache::get('mikrotik_pppoe_active');

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

        return view('customers.active_list', compact(
            'customers',
            'totalActive',
            'newThisMonth',
            'newThisWeek',
            'totalArchived',
            'pelangganIsolir'
        ));
    }

    public function store(Request $request)
    {
        if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
            abort(403, 'Akses ditolak.');
        }

        $fotoPetugasPath = null;
        $fotoTerpasangPath = null;
        $fotoSpeedtestPath = null;

        try {
            // 1. Validasi data input dengan pesan kustom untuk data unik/exist
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

                // Validasi Partner Tambahan
                'partner_ids' => 'nullable|array',
                'partner_ids.*' => 'exists:users,id',

                'keterangan' => 'nullable|string',
                'foto_petugas' => 'required|image|max:20480',
                'foto_terpasang' => 'required|image|max:20480',
                'foto_speedtest' => 'required|image|max:20480',
            ], [
                // Custom pesan error jika data sudah ada (Exist)
                'pre_order_id.unique' => 'Pelanggan ini sudah pernah diaktivasi sebelumnya.',
                'pppoe_username.unique' => 'Akun PPPoE sudah digunakan oleh pelanggan lain.',
                'id_pelanggan.unique' => 'ID Pelanggan sudah terdaftar di sistem billing.',
            ]);

            $po = PreOrder::findOrFail($request->pre_order_id);

            // 2. LOGIKA OTOMATIS: Ambil Nama Partner berdasarkan ID yang dipilih
            $partnerNames = "";
            if ($request->has('partner_ids') && !empty($request->partner_ids)) {
                $partnerNames = User::whereIn('id', $request->partner_ids)
                    ->pluck('nama_lengkap')
                    ->implode(', ');
            }

            // 3. Handle Unggahan Foto
            $fotoPetugasPath = $request->file('foto_petugas')->store('customers/foto_petugas', 'public');
            $fotoTerpasangPath = $request->file('foto_terpasang')->store('customers/foto_terpasang', 'public');
            $fotoSpeedtestPath = $request->file('foto_speedtest')->store('customers/foto_speedtest', 'public');

            // 4. Simpan data Customer
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

                'partner'        => $partnerNames,      // Simpan string nama
                'partner_ids'    => $request->partner_ids, // Simpan array ID

                'keterangan' => $validatedData['keterangan'],
                'foto_petugas' => $fotoPetugasPath,
                'foto_terpasang' => $fotoTerpasangPath,
                'foto_speedtest' => $fotoSpeedtestPath,
            ]);

            // 5. Log Aktivitas
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity_type' => 'aktivasi_pelanggan',
                'description' => 'Mengaktivasi pelanggan: ' . $po->nama_lengkap . ' dengan partner: ' . ($partnerNames ?: '-'),
                'logged_at' => now(),
            ]);

            return redirect()->route('customers.index')->with('success', 'Aktivasi pelanggan berhasil!');
        } catch (ValidationException $e) {
            // Ambil semua pesan error dan gabungkan menjadi string untuk SweetAlert
            $errorMessages = implode('<br>', $e->validator->errors()->all());

            // Mengembalikan error validasi ke bag $errors DAN mengirim flash message 'error' untuk SweetAlert
            return redirect()->back()
                ->withInput()
                ->withErrors($e->validator)
                ->with('error', $errorMessages);
        } catch (\Exception $e) {
            // Hapus file jika database gagal simpan
            if (isset($fotoPetugasPath)) Storage::disk('public')->delete($fotoPetugasPath);
            if (isset($fotoTerpasangPath)) Storage::disk('public')->delete($fotoTerpasangPath);
            if (isset($fotoSpeedtestPath)) Storage::disk('public')->delete($fotoSpeedtestPath);

            return redirect()->back()->withInput()->with('error', 'Gagal Aktivasi: ' . $e->getMessage());
        }
    }

    public function restore($id)
    {
        // Mengambil hanya data yang sudah di-softdelete
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $nama = $customer->nama_lengkap;

        $customer->restore(); // Mengembalikan data (mengosongkan deleted_at)

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'aktivasi_kembali_pelanggan',
            'description' => "Mengaktifkan kembali pelanggan: $nama",
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan $nama berhasil diaktifkan kembali.");
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $nama = $customer->nama_lengkap;

        $customer->delete(); // Ini akan melakukan Soft Delete

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'hapus_pelanggan',
            'description' => "Menonaktifkan pelanggan: $nama",
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', "Pelanggan $nama berhasil dinonaktifkan.");
    }
}

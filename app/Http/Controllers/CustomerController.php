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
    // public function index()
    // {
    //     if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
    //         abort(403, 'Akses ditolak.');
    //     }

    //     $user = auth()->user();
    //     $activatedPOIds = Customer::pluck('pre_order_id');

    //     // --- QUERY DASAR: PO yang BELUM diaktivasi ---
    //     $baseQuery = PreOrder::whereNotIn('id', $activatedPOIds);

    //     // --- FILTER KHUSUS TEKNISI ---
    //     if ($user->jabatan === 'Teknisi') {
    //         // Ambil PO yang ditugaskan secara KHUSUS kepada Teknisi INI
    //         $delegatedTasks = $baseQuery->clone()
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->whereNotNull('delegated_at')
    //             ->orderBy('delegated_at', 'asc')
    //             ->get()
    //             ->map(function ($po) {
    //                 // Logika Status Locked dan Deadline (Sama seperti sebelumnya)
    //                 $po->is_locked = false;
    //                 $po->deadline_status = 'AKTIF';
    //                 $po->bg_color = 'bg-info';

    //                 if ($po->delegated_at) {
    //                     $delegationDate = Carbon::parse($po->delegated_at);
    //                     $deadline = $delegationDate->copy()->addDays(3);
    //                     $now = Carbon::now();

    //                     $po->deadline_date = $deadline->format('d-m-Y H:i');

    //                     if ($now->greaterThan($deadline)) {
    //                         $po->deadline_status = 'EXPIRED';
    //                         $po->is_locked = true;
    //                     } elseif ($now->diffInHours($deadline) <= 24) {
    //                         $po->deadline_status = 'SEGERA';
    //                     }
    //                 }
    //                 return $po;
    //             });

    //         // Ambil PO UMUM (yang belum ditugaskan siapa-siapa)
    //         $generalTasks = $baseQuery->clone()
    //             ->whereNull('id_teknisi_delegasi') // Hanya yang belum ditugaskan
    //             ->orderBy('created_at', 'asc')
    //             ->get();

    //         // Teknisi hanya melihat dua koleksi terpisah ini
    //         return view('customers.index', compact('delegatedTasks', 'generalTasks'));
    //     } else {
    //         // --- AKSES GLOBAL UNTUK ADMINISTRATOR ---
    //         // Administrator melihat SEMUA PO yang siap aktivasi (Global View)
    //         $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get()->map(function ($po) {
    //             // Tambahkan status delegasi untuk Admin
    //             $po->delegated_to = $po->id_teknisi_delegasi ? (User::find($po->id_teknisi_delegasi)->nama_lengkap ?? 'Unknown') : 'Belum Ditunjuk';
    //             return $po;
    //         });

    //         // Kirim semua PO ke view dalam satu variabel tunggal
    //         return view('customers.index', compact('allPreOrders'));
    //     }
    // }

    // // Fungsi ACTIVE LIST: Menampilkan daftar Pelanggan Aktif
    // public function activeList()
    // {
    //     // Cek otentikasi tetap diperlukan
    //     if (!auth()->check()) {
    //         return redirect('/login')->with('error', 'Silakan login untuk mengakses data pelanggan.');
    //     }

    //     $user = auth()->user();
    //     $query = Customer::query();

    //     // Logika Akses: Semua role bisa masuk, tetapi Sales difilter
    //     if ($user->jabatan === 'Sales') {
    //         // Sales: Hanya melihat pelanggan yang sesuai dengan ID Sales mereka.
    //         $query->where('id_sales', $user->id);
    //     }
    //     // Administrator dan Teknisi akan melewati kondisi ini dan melihat semua data (no filter)

    //     $customers = $query->get();

    //     return view('customers.active_list', compact('customers'));
    // }

    // // Proses Aktivasi menjadi Customer
    // public function store(Request $request)
    // {
    //     // Hapus dd($request->all()); agar kode bisa dilanjutkan.

    //     if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
    //         abort(403, 'Akses ditolak.');
    //     }

    //     $fotoPetugasPath = null;
    //     $fotoTerpasangPath = null;
    //     $fotoSpeedtestPath = null;

    //     try {
    //         // Validasi data input
    //         $validatedData = $request->validate([
    //             'pre_order_id' => 'required|exists:pre_orders,id|unique:customers,pre_order_id',
    //             'id_sales' => 'required|exists:users,id',
    //             'id_teknisi' => 'required|exists:users,id',
    //             'pppoe_username' => 'required|string|max:255|unique:customers,pppoe_username',
    //             'pppoe_password' => 'required|string|min:1',
    //             'id_pelanggan' => 'required|string|max:255|unique:customers,id_pelanggan',
    //             'jenis_modem' => 'required|in:EPON,GPON',
    //             'sn_perangkat' => 'required|string|max:255',
    //             'pembayaran' => 'required|in:Cash,Transfer,Aplikasi',
    //             'partner' => 'nullable|string|max:255',
    //             'keterangan' => 'nullable|string',
    //             'foto_petugas' => 'required|image|max:2048',
    //             'foto_terpasang' => 'required|image|max:2048',
    //             'foto_speedtest' => 'required|image|max:2048',
    //         ]);

    //         // Ambil data PO untuk diisi otomatis
    //         $po = PreOrder::findOrFail($request->pre_order_id);

    //         // Handle Unggahan Foto
    //         $fotoPetugasPath = $request->file('foto_petugas')->store('customers/foto_petugas', 'public');
    //         $fotoTerpasangPath = $request->file('foto_terpasang')->store('customers/foto_terpasang', 'public');
    //         $fotoSpeedtestPath = $request->file('foto_speedtest')->store('customers/foto_speedtest', 'public');

    //         // Simpan data Customer
    //         Customer::create([
    //             'pre_order_id' => $po->id,
    //             'id_sales' => $po->id_sales,
    //             'id_teknisi' => $validatedData['id_teknisi'],
    //             'nama_lengkap' => $po->nama_lengkap,
    //             'alamat_lengkap' => $po->alamat_lengkap,
    //             'no_whatsapp' => $po->no_whatsapp,
    //             'paket_internet' => $po->paket_internet,

    //             // Data Teknis
    //             'id_pelanggan' => $validatedData['id_pelanggan'],
    //             'pppoe_username' => $validatedData['pppoe_username'],
    //             'pppoe_password' => $validatedData['pppoe_password'],
    //             'jenis_modem' => $validatedData['jenis_modem'],
    //             'sn_perangkat' => $validatedData['sn_perangkat'],

    //             // Data Administrasi
    //             'pembayaran' => $validatedData['pembayaran'],
    //             'partner' => $validatedData['partner'],
    //             'keterangan' => $validatedData['keterangan'],

    //             // Path Foto
    //             'foto_petugas' => $fotoPetugasPath,
    //             'foto_terpasang' => $fotoTerpasangPath,
    //             'foto_speedtest' => $fotoSpeedtestPath,
    //         ]);

    //         ActivityLog::create([
    //             'user_id' => auth()->id(),
    //             'activity_type' => 'aktivasi_pelanggan',
    //             'description' => 'Mengaktivasi pelanggan: ' . $po->nama_lengkap . ' (PO ID: ' . $po->id . ')',
    //             'logged_at' => now(),
    //         ]);

    //         return redirect()->route('customers.index')->with('success', 'Aktivasi pelanggan berhasil!');
    //     } catch (ValidationException $e) {
    //         // Tampilkan pesan error validasi di frontend
    //         return redirect()->back()->withInput()->withErrors($e->validator);
    //     } catch (\Exception $e) {
    //         // === BAGIAN KRUSIAL UNTUK DEBUGGING ===
    //         // Hapus file yang mungkin sudah terunggah sebelum kegagalan penyimpanan DB
    //         if (isset($fotoPetugasPath) && Storage::disk('public')->exists($fotoPetugasPath)) Storage::disk('public')->delete($fotoPetugasPath);
    //         if (isset($fotoTerpasangPath) && Storage::disk('public')->exists($fotoTerpasangPath)) Storage::disk('public')->delete($fotoTerpasangPath);
    //         if (isset($fotoSpeedtestPath) && Storage::disk('public')->exists($fotoSpeedtestPath)) Storage::disk('public')->delete($fotoSpeedtestPath);

    //         // Tampilkan pesan error lengkap, termasuk baris kode kegagalan
    //         $errorMessage = 'Gagal Aktivasi: ' . $e->getMessage() . ' | Line: ' . $e->getLine();

    //         return redirect()->back()->with('error', $errorMessage);
    //     }
    // }





    public function index()
    {
        if (auth()->user()->jabatan !== 'Teknisi' && auth()->user()->jabatan !== 'Administrator') {
            abort(403, 'Akses ditolak.');
        }

        $user = auth()->user();
        $activatedPOIds = Customer::pluck('pre_order_id');

        // Mengambil daftar teknisi untuk dropdown Partner (Multi-select)
        // Kita ambil user yang jabatannya Teknisi
        $teknisiUsers = User::where('jabatan', 'Teknisi')->get();

        // --- QUERY DASAR: PO yang BELUM diaktivasi ---
        $baseQuery = PreOrder::whereNotIn('id', $activatedPOIds);

        // --- FILTER KHUSUS TEKNISI ---
        if ($user->jabatan === 'Teknisi') {
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

            $generalTasks = $baseQuery->clone()
                ->whereNull('id_teknisi_delegasi')
                ->orderBy('created_at', 'asc')
                ->get();

            return view('customers.index', compact('delegatedTasks', 'generalTasks', 'teknisiUsers'));
        } else {
            // Administrator View
            $allPreOrders = $baseQuery->orderBy('created_at', 'asc')->get()->map(function ($po) {
                $po->delegated_to = $po->id_teknisi_delegasi ? (User::find($po->id_teknisi_delegasi)->nama_lengkap ?? 'Unknown') : 'Belum Ditunjuk';
                return $po;
            });

            return view('customers.index', compact('allPreOrders', 'teknisiUsers'));
        }
    }

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

        $customers = $query->orderBy('created_at', 'desc')->get();

        return view('customers.active_list', compact('customers'));
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
                'foto_petugas' => 'required|image|max:2048',
                'foto_terpasang' => 'required|image|max:2048',
                'foto_speedtest' => 'required|image|max:2048',
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

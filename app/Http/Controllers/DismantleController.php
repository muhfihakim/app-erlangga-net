<?php

namespace App\Http\Controllers;

use App\Models\Dismantle;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DismantleController extends Controller
{
    /**
     * Menampilkan daftar tugas dismantle
     */
    public function index()
    {
        $user = auth()->user();

        // Memuat relasi lengkap untuk data detail (Pelanggan, PO, Sales, Teknisi)
        $query = Dismantle::with(['customer.preOrder', 'customer.sales', 'teknisiEksekutor']);

        // Data untuk Admin (Semua Penugasan)
        $allDismantles = (clone $query)
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // Data Khusus Teknisi
        $pendingTasks = [];
        
        if ($user->jabatan === 'Teknisi') {
            $pendingTasks = (clone $query)
                ->where('status', 'Pending')
                ->get();
        }

        $customers = Customer::all();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // --- STATISTIK ---
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;
        
        $totalThisMonth = Dismantle::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();
            
        $totalThisWeek = Dismantle::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        $totalCompletedThisMonth = Dismantle::where('status', 'Selesai')
            ->whereMonth('completed_at', $thisMonth)
            ->whereYear('completed_at', $thisYear)
            ->count();

        return view('dismantles.index', compact(
            'allDismantles',
            'pendingTasks',
            'customers',
            'teknisis',
            'totalThisMonth',
            'totalThisWeek',
            'totalCompletedThisMonth'
        ));
    }

    /**
     * Mengambil data pelanggan via AJAX untuk auto-fill form Admin
     */
    public function getCustomerData($id)
    {
        $c = Customer::with(['preOrder', 'sales'])->findOrFail($id);
        return response()->json([
            'alamat' => $c->alamat_lengkap,
            'wa' => $c->no_whatsapp,
            'tikor' => $c->preOrder->titik_koordinat ?? 'N/A',
            'paket' => $c->paket_internet,
            'sales' => $c->sales->nama_lengkap ?? 'N/A',
            'id_pel' => $c->id_pelanggan,
            'pppoe' => $c->pppoe_username
        ]);
    }

    /**
     * Menyimpan penugasan dismantle baru oleh Admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'keterangan_admin' => 'nullable|string'
        ]);

        $d = Dismantle::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'keterangan_admin' => $request->keterangan_admin,
            'status' => 'Pending'
        ]);

        // Create Activity Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'pendaftaran_dismantle',
            'description' => "Menugaskan dismantle untuk pelanggan: " . $d->customer->nama_lengkap . " (ID Pelanggan: " . $d->customer->id_pelanggan . ")",
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tugas dismantle berhasil dibuat.');
    }

    /**
     * Menyelesaikan tugas dismantle oleh Teknisi
     */
    public function complete(Request $request, $id)
    {
        $request->validate([
            'foto_pencabutan' => 'required|image|max:20480'
        ]);

        $d = Dismantle::findOrFail($id);
        $path = $request->file('foto_pencabutan')->store('dismantles');

        $d->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'foto_pencabutan' => $path,
            'status' => 'Selesai',
            'completed_at' => now()
        ]);

        // Create Activity Log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'eksekusi_dismantle',
            'description' => "Pencabutan perangkat selesai dilaporkan untuk pelanggan: " . $d->customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Pencabutan perangkat selesai dilaporkan.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $d = Dismantle::findOrFail($id);
        $nama = $d->customer->nama_lengkap;

        // Hapus file terkait
        if ($d->foto_pencabutan) Storage::delete($d->foto_pencabutan);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'batal_dismantle',
            'description' => "Membatalkan tugas dismantle pelanggan: $nama",
            'logged_at' => now(),
        ]);

        $d->delete(); 

        return redirect()->back()->with('success', 'Tugas dismantle berhasil dibatalkan.');
    }

    public function deleteCustomer($id)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $c = Customer::findOrFail($id);
        $nama = $c->nama_lengkap;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'hapus_pelanggan_dismantle',
            'description' => "Menghapus pelanggan $nama dari daftar aktif melalui proses dismantle",
            'logged_at' => now(),
        ]);

        $c->delete();

        return redirect()->back()->with('success', "Pelanggan $nama telah dinonaktifkan (Soft Delete). Data laporan tetap aman.");
    }
}

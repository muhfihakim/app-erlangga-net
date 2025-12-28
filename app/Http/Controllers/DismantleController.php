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
        $query = Dismantle::with(['customer.preOrder', 'customer.sales', 'teknisiDelegasi', 'teknisiEksekutor']);

        if ($user->jabatan === 'Teknisi') {
            $query->where(function ($q) use ($user) {
                $q->where('id_teknisi_delegasi', $user->id)
                    ->orWhereNull('id_teknisi_delegasi');
            })->where('status', 'Pending');
        }

        $dismantles = $query->orderBy('status', 'asc')->orderBy('created_at', 'desc')->get();
        $customers = Customer::all();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        return view('dismantles.index', compact('dismantles', 'customers', 'teknisis'));
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
        // DEBUG: Cek apakah data masuk ke sini
        // dd($request->all());

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'keterangan_admin' => 'nullable|string'
        ]);

        $d = Dismantle::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'id_teknisi_delegasi' => $request->id_teknisi_delegasi,
            'keterangan_admin' => $request->keterangan_admin,
            'delegated_at' => $request->id_teknisi_delegasi ? now() : null,
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
            'foto_pencabutan' => 'required|image|max:2048'
        ]);

        $d = Dismantle::findOrFail($id);
        $path = $request->file('foto_pencabutan')->store('dismantles', 'public');

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
        if (auth()->user()->jabatan !== 'Administrator') abort(403);

        $d = Dismantle::findOrFail($id);
        $nama = $d->customer->nama_lengkap;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'batal_dismantle',
            'description' => "Membatalkan tugas dismantle pelanggan: $nama",
            'logged_at' => now(),
        ]);

        $d->delete(); // Hard delete record dismantle (karena tabel dismantle tidak pakai softdelete)

        return redirect()->back()->with('success', 'Tugas dismantle berhasil dibatalkan.');
    }

    public function deleteCustomer($id)
    {
        if (auth()->user()->jabatan !== 'Administrator') abort(403);

        $c = Customer::findOrFail($id);
        $nama = $c->nama_lengkap;

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'hapus_pelanggan_dismantle',
            'description' => "Menghapus pelanggan $nama dari daftar aktif melalui proses dismantle",
            'logged_at' => now(),
        ]);

        // Karena model Customer menggunakan SoftDeletes, ini hanya akan mengisi kolom deleted_at
        $c->delete();

        return redirect()->back()->with('success', "Pelanggan $nama telah dinonaktifkan (Soft Delete). Data laporan tetap aman.");
    }
}

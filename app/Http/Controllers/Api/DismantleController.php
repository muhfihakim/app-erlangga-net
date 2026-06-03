<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dismantle;
use App\Models\Customer;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DismantleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Dismantle::with(['customer', 'teknisiEksekutor']);

        if ($user->jabatan === 'Teknisi') {
            $query->where('status', 'Pending');
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'keterangan_admin' => 'nullable|string',
        ]);

        $dismantle = Dismantle::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'keterangan_admin' => $request->keterangan_admin,
            'status' => 'Pending',
        ]);

        return response()->json(['message' => 'Tugas dismantle berhasil dibuat', 'data' => $dismantle], 201);
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'required',
            'foto_perangkat' => 'required|image',
            'foto_rumah' => 'required|image',
        ]);

        $d = Dismantle::findOrFail($id);
        $customer = Customer::findOrFail($d->customer_id);

        $d->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'keterangan' => $request->keterangan,
            'foto_perangkat' => $request->file('foto_perangkat')->store('dismantles/devices'),
            'foto_rumah' => $request->file('foto_rumah')->store('dismantles/houses'),
            'completed_at' => now(),
            'status' => 'Selesai'
        ]);

        $customer->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'dismantle_complete',
            'description' => "Menyelesaikan dismantle: " . $customer->nama_lengkap,
            'logged_at' => now()
        ]);

        return response()->json(['message' => 'Laporan dismantle berhasil disimpan']);
    }

    public function destroy($id)
    {
        $d = Dismantle::findOrFail($id);
        
        // Hapus file terkait
        if ($d->foto_perangkat) Storage::delete($d->foto_perangkat);
        if ($d->foto_rumah) Storage::delete($d->foto_rumah);

        $d->delete();
        return response()->json(['message' => 'Tugas dismantle berhasil dihapus']);
    }
}

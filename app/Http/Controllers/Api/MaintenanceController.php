<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Maintenance;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MaintenanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Maintenance::with(['customer', 'teknisiEksekutor', 'teknisiDelegasi']);

        if ($user->jabatan === 'Teknisi') {
            $query->where('id_teknisi_delegasi', $user->id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'id_teknisi_delegasi' => 'required|exists:users,id',
            'keterangan_admin' => 'required|string',
            'foto_kendala_awal' => 'nullable|image|max:20480',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_kendala_awal')) {
            $fotoPath = $request->file('foto_kendala_awal')->store('maintenances/problems', 'public');
        }

        $maintenance = Maintenance::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'id_teknisi_delegasi' => $request->id_teknisi_delegasi,
            'keterangan_admin' => $request->keterangan_admin,
            'foto_kendala_awal' => $fotoPath,
            'status' => 'Pending',
            'delegated_at' => now(),
        ]);

        return response()->json(['message' => 'Tugas maintenance berhasil dibuat', 'data' => $maintenance], 201);
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'solusi_kendala' => 'required',
            'kategori_kendala' => 'required|in:ONT,Kabel Fiber Optic,Lainnya',
            'sn_baru' => 'required_if:kategori_kendala,ONT',
            'foto_sebelum' => 'required|image',
            'foto_sesudah' => 'required|image',
            'foto_speedtest' => 'required|image',
            'foto_selfie' => 'required|image',
        ]);

        $m = Maintenance::findOrFail($id);
        $customer = Customer::findOrFail($m->customer_id);

        if ($request->kategori_kendala === 'ONT') {
            $customer->update(['sn_perangkat' => $request->sn_baru]);
        }

        $m->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'partner_ids' => $request->partner_ids,
            'kategori_kendala' => $request->kategori_kendala,
            'sn_baru' => $request->sn_baru,
            'sn_lama' => $customer->getOriginal('sn_perangkat'),
            'solusi_kendala' => $request->solusi_kendala,
            'foto_sebelum_perbaikan' => $request->file('foto_sebelum')->store('maintenances/before', 'public'),
            'foto_setelah_perbaikan' => $request->file('foto_sesudah')->store('maintenances/after', 'public'),
            'foto_speedtest' => $request->file('foto_speedtest')->store('maintenances/speedtest', 'public'),
            'foto_selfie_teknisi' => $request->file('foto_selfie')->store('maintenances/selfie', 'public'),
            'completed_at' => now(),
            'status' => 'Selesai'
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'maintenance_complete',
            'description' => "Menyelesaikan maintenance: " . $customer->nama_lengkap,
            'logged_at' => now()
        ]);

        return response()->json(['message' => 'Laporan maintenance berhasil disimpan']);
    }

    public function reassign(Request $request, $id)
    {
        $request->validate(['id_teknisi' => 'required|exists:users,id']);
        $m = Maintenance::findOrFail($id);
        $m->update([
            'id_teknisi_delegasi' => $request->id_teknisi,
            'delegated_at' => now()
        ]);
        return response()->json(['message' => 'Tugas berhasil ditugaskan ulang']);
    }

    public function resetDelegation($id)
    {
        $m = Maintenance::findOrFail($id);
        $m->update([
            'id_teknisi_delegasi' => null,
            'delegated_at' => null
        ]);
        return response()->json(['message' => 'Delegasi maintenance berhasil direset']);
    }

    public function destroy($id)
    {
        $m = Maintenance::findOrFail($id);
        $m->delete();
        return response()->json(['message' => 'Tugas maintenance berhasil dihapus']);
    }
}

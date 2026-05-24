<?php

namespace App\Http\Controllers;

use App\Models\Maintenance;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MaintenanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Memuat relasi customer dan preOrder (untuk WA & Tikor) secara eager loading
        $query = Maintenance::with(['customer.preOrder', 'teknisiDelegasi', 'admin', 'teknisiEksekutor']);

        // 1. Data untuk Admin (Semua Penugasan)
        $allMaintenances = (clone $query)
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Data Khusus Teknisi (Tugas yang didelegasikan ke dia)
        $delegatedTasks = (clone $query)
            ->where('id_teknisi_delegasi', $user->id)
            ->where('status', 'Pending')
            ->get();

        // 3. Data Umum Teknisi (Tugas yang belum ditunjuk ke siapapun)
        $generalTasks = (clone $query)
            ->whereNull('id_teknisi_delegasi')
            ->where('status', 'Pending')
            ->get();

        $customers = Customer::all();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        return view('maintenances.index', compact(
            'allMaintenances',
            'delegatedTasks',
            'generalTasks',
            'customers',
            'teknisis',
            'totalThisMonth',
            'totalThisWeek',
            'totalCompletedThisMonth'
        ));
    }

    public function getCustomerData($id)
    {
        $customer = Customer::with('preOrder')->findOrFail($id);
        return response()->json([
            'pppoe' => $customer->pppoe_username,
            'alamat' => $customer->alamat_lengkap,
            'tikor' => $customer->preOrder->titik_koordinat ?? '-',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'keterangan_kendala' => 'required',
        ]);

        $fotoPath = $request->file('foto_kendala_awal')
            ? $request->file('foto_kendala_awal')->store('maintenances/issues', 'public')
            : null;

        $maintenance = Maintenance::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'id_teknisi_delegasi' => $request->id_teknisi_delegasi,
            'keterangan_kendala' => $request->keterangan_kendala,
            'foto_kendala_awal' => $fotoPath,
            'delegated_at' => $request->id_teknisi_delegasi ? now() : null,
            'status' => 'Pending'
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'penugasan_maintenance',
            'description' => "Menugaskan maintenance pelanggan: " . $maintenance->customer->nama_lengkap,
            'logged_at' => now()
        ]);

        return redirect()->back()->with('success', 'Tugas maintenance berhasil dikirim.');
    }

    public function reassign(Request $request, $id)
    {
        if (auth()->user()->jabatan !== 'Administrator') abort(403);
        $request->validate(['id_teknisi_delegasi' => 'required|exists:users,id']);

        $m = Maintenance::findOrFail($id);
        $m->update([
            'id_teknisi_delegasi' => $request->id_teknisi_delegasi,
            'delegated_at' => now(), // Reset Timer
            'status' => 'Pending'
        ]);

        return redirect()->back()->with('success', 'Maintenance berhasil didelegasikan ulang.');
    }

    public function destroy($id)
    {
        if (auth()->user()->jabatan !== 'Administrator') abort(403);
        $m = Maintenance::findOrFail($id);
        $m->delete();
        return redirect()->back()->with('success', 'Tugas maintenance berhasil dihapus.');
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'solusi_kendala' => 'required',
            'foto_sebelum' => 'required|image',
            'foto_sesudah' => 'required|image',
            'foto_speedtest' => 'required|image',
            'foto_selfie' => 'required|image',
        ]);

        $m = Maintenance::findOrFail($id);
        $partnerNames = $request->has('partner_ids') ? User::whereIn('id', $request->partner_ids)->pluck('nama_lengkap')->implode(', ') : "";

        $m->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'partner_ids' => $request->partner_ids,
            'partner' => $partnerNames,
            'solusi_kendala' => $request->solusi_kendala,
            'foto_sebelum_perbaikan' => $request->file('foto_sebelum')->store('maintenances/before', 'public'),
            'foto_setelah_perbaikan' => $request->file('foto_sesudah')->store('maintenances/after', 'public'),
            'foto_speedtest' => $request->file('foto_speedtest')->store('maintenances/speedtest', 'public'),
            'foto_selfie_teknisi' => $request->file('foto_selfie')->store('maintenances/selfie', 'public'),
            'completed_at' => now(),
            'status' => 'Selesai'
        ]);

        return redirect()->back()->with('success', 'Laporan maintenance berhasil disimpan.');
    }
}

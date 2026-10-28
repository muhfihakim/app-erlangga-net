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
        $query = Maintenance::with(['customer.preOrder', 'admin', 'teknisiEksekutor']);

        // 1. Data untuk Admin (Semua Penugasan)
        $allMaintenances = (clone $query)
            ->orderBy('status', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        // 2. Data untuk Teknisi (Tugas Pending)
        $pendingTasks = (clone $query)
            ->where('status', 'Pending')
            ->get();

        $customers = Customer::all();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        if (request()->ajax()) {
            $type = request()->query('type');
            if ($type === 'admin') {
                return \Yajra\DataTables\Facades\DataTables::of($allMaintenances)
                    ->addColumn('no', function ($m) { return ''; })
                    ->addColumn('pelanggan', function($m) {
                        $nama = htmlspecialchars($m->customer->nama_lengkap ?? 'N/A');
                        $id_pel = htmlspecialchars($m->customer->id_pelanggan ?? '-');
                        $subInfo = ['<span class="text-slate-400">ID: <span class="text-slate-600 font-medium">' . $id_pel . '</span></span>'];
                        if ($m->customer && $m->customer->no_whatsapp) {
                            $subInfo[] = '<a href="https://wa.me/' . htmlspecialchars($m->customer->no_whatsapp, ENT_QUOTES) . '" target="_blank" class="text-slate-500 hover:text-emerald-600 inline-flex items-center gap-1"><i class="fa-brands fa-whatsapp text-emerald-600 text-[11px]"></i> ' . htmlspecialchars($m->customer->no_whatsapp) . '</a>';
                        }
                        return '<div class="text-left font-bold text-slate-900 text-[13px] leading-snug">' . $nama . '</div><div class="text-left text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">' . implode('<span class="text-slate-300">•</span>', $subInfo) . '</div>';
                    })
                    ->editColumn('status', function($m) {
                        if ($m->status === 'Selesai') {
                            return '<div class="text-center"><span class="badge text-[10px] font-semibold py-0.5 px-2 bg-emerald-50 text-emerald-700 border border-emerald-200">Selesai</span></div>';
                        }
                        return '<div class="text-center"><span class="badge text-[10px] font-semibold py-0.5 px-2 bg-amber-50 text-amber-700 border border-amber-200">Pending</span></div>';
                    })
                    ->addColumn('teknisi', function($m) {
                        if ($m->teknisiEksekutor) {
                            $html = '<div class="text-left text-xs font-medium text-slate-700 whitespace-nowrap"><i class="fa-regular fa-user text-[11px] text-slate-400 mr-1.5"></i>' . htmlspecialchars($m->teknisiEksekutor->nama_lengkap) . '</div>';
                            if ($m->partner) {
                                $html .= '<div class="text-left text-[11px] text-slate-400 mt-0.5">Team: ' . htmlspecialchars($m->partner) . '</div>';
                            }
                            return $html;
                        }
                        return '<div class="text-left text-xs text-slate-400 italic">Belum diambil</div>';
                    })
                    ->editColumn('completed_at', function($m) {
                        $date = $m->completed_at ? \Carbon\Carbon::parse($m->completed_at)->format('d/m/Y H:i') : '-';
                        return '<div class="text-center font-mono text-xs text-slate-500">' . $date . '</div>';
                    })
                    ->addColumn('opsi', function($m) {
                        return view('maintenances.action_buttons_admin', compact('m'))->render();
                    })
                    ->rawColumns(['pelanggan', 'status', 'teknisi', 'completed_at', 'opsi'])
                    ->make(true);
            } else {
                return \Yajra\DataTables\Facades\DataTables::of($pendingTasks)
                    ->addColumn('no', function ($m) { return ''; })
                    ->addColumn('pelanggan', function($m) {
                        $nama = htmlspecialchars($m->customer->nama_lengkap ?? 'N/A');
                        $id_pel = htmlspecialchars($m->customer->id_pelanggan ?? '-');
                        $subInfo = ['<span class="text-slate-400">ID: <span class="text-slate-600 font-medium">' . $id_pel . '</span></span>'];
                        if ($m->customer && $m->customer->no_whatsapp) {
                            $subInfo[] = '<a href="https://wa.me/' . htmlspecialchars($m->customer->no_whatsapp, ENT_QUOTES) . '" target="_blank" class="text-slate-500 hover:text-emerald-600 inline-flex items-center gap-1"><i class="fa-brands fa-whatsapp text-emerald-600 text-[11px]"></i> ' . htmlspecialchars($m->customer->no_whatsapp) . '</a>';
                        }
                        return '<div class="text-left font-bold text-slate-900 text-[13px] leading-snug">' . $nama . '</div><div class="text-left text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-1.5 flex-wrap">' . implode('<span class="text-slate-300">•</span>', $subInfo) . '</div>';
                    })
                    ->editColumn('keterangan_kendala', function($m) {
                        return '<div class="text-left text-xs text-slate-700 leading-snug">' . htmlspecialchars($m->keterangan_kendala) . '</div>';
                    })
                    ->addColumn('opsi', function($m) {
                        return view('maintenances.action_buttons_pending', compact('m'))->render();
                    })
                    ->rawColumns(['pelanggan', 'keterangan_kendala', 'opsi'])
                    ->make(true);
            }
        }

        // --- STATISTIK ---
        $thisMonth = Carbon::now()->month;
        $thisYear = Carbon::now()->year;

        $totalThisMonth = Maintenance::whereMonth('created_at', $thisMonth)
            ->whereYear('created_at', $thisYear)
            ->count();

        $totalThisWeek = Maintenance::whereBetween('created_at', [
            Carbon::now()->startOfWeek(),
            Carbon::now()->endOfWeek()
        ])->count();

        $totalCompletedThisMonth = Maintenance::where('status', 'Selesai')
            ->whereMonth('completed_at', $thisMonth)
            ->whereYear('completed_at', $thisYear)
            ->count();

        return view('maintenances.index', compact(
            'allMaintenances',
            'pendingTasks',
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
            ? $request->file('foto_kendala_awal')->store('maintenances/issues')
            : null;

        $maintenance = Maintenance::create([
            'customer_id' => $request->customer_id,
            'id_admin' => auth()->id(),
            'keterangan_kendala' => $request->keterangan_kendala,
            'foto_kendala_awal' => $fotoPath,
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

    public function destroy($id)
    {
        if (auth()->user()->isAdmin()) {
            $m = Maintenance::findOrFail($id);

            // Hapus file-file terkait
            if ($m->foto_kendala_awal) Storage::delete($m->foto_kendala_awal);
            if ($m->foto_sebelum_perbaikan) Storage::delete($m->foto_sebelum_perbaikan);
            if ($m->foto_setelah_perbaikan) Storage::delete($m->foto_setelah_perbaikan);
            if ($m->foto_speedtest) Storage::delete($m->foto_speedtest);
            if ($m->foto_selfie_teknisi) Storage::delete($m->foto_selfie_teknisi);

            $m->delete();
            return redirect()->back()->with('success', 'Tugas maintenance berhasil dihapus.');
        }
        abort(403);
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

        $partnerNames = $request->has('partner_ids') ? User::whereIn('id', $request->partner_ids)->pluck('nama_lengkap')->implode(', ') : "";

        // Jika kategori ONT, update SN di tabel customer
        if ($request->kategori_kendala === 'ONT') {
            $customer->update([
                'sn_perangkat' => $request->sn_baru
            ]);
        }

        $m->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'partner_ids' => $request->partner_ids,
            'partner' => $partnerNames,
            'kategori_kendala' => $request->kategori_kendala,
            'sn_baru' => $request->sn_baru,
            'sn_lama' => $customer->getOriginal('sn_perangkat'),
            'solusi_kendala' => $request->solusi_kendala,
            'foto_sebelum_perbaikan' => $request->file('foto_sebelum')->store('maintenances/before'),
            'foto_setelah_perbaikan' => $request->file('foto_sesudah')->store('maintenances/after'),
            'foto_speedtest' => $request->file('foto_speedtest')->store('maintenances/speedtest'),
            'foto_selfie_teknisi' => $request->file('foto_selfie')->store('maintenances/selfie'),
            'completed_at' => now(),
            'status' => 'Selesai'
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'maintenance_complete',
            'description' => "Menyelesaikan maintenance pelanggan: " . $customer->nama_lengkap . ($request->kategori_kendala === 'ONT' ? " (Pergantian ONT)" : ""),
            'logged_at' => now()
        ]);

        return redirect()->back()->with('success', 'Laporan pemeliharaan berhasil disimpan.');
    }
}

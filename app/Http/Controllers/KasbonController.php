<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\FinanceSetting;
use App\Models\Kasbon;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KasbonController extends Controller
{
    /**
     * Tampilan Modul Kasbon (Generalisasi Teknisi, Sales, Admin, Super Admin)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();
        $isTeknisi = $user->jabatan === 'Teknisi';
        $isSales = $user->jabatan === 'Sales';

        if (!$isAdmin && !$isTeknisi && !$isSales) {
            abort(403, "Akses ditolak");
        }

        // Definisi Siklus Mingguan Teknisi (Sabtu - Jumat)
        $startOfWeekSat = Carbon::now()->isSaturday() ? Carbon::now()->startOfDay() : Carbon::now()->previous(Carbon::SATURDAY)->startOfDay();
        $endOfWeekFri = $startOfWeekSat->copy()->addDays(6)->endOfDay();

        // Definisi Siklus Bulanan Sales (Bulan Berjalan)
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // Parameter Filter
        $userId = $isAdmin ? ($request->input('user_id') ?: $request->input('teknisi_id')) : $user->id;
        $roleFilter = $isAdmin ? $request->input('role') : null;
        $statusFilter = $request->input('status');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        // Jika filter tanggal tidak diisi, gunakan default rentang waktu
        if (!$start && !$end) {
            if ($isTeknisi) {
                // Default teknisi: 2 siklus (siklus saat ini + sebelumnya)
                $start = $startOfWeekSat->copy()->subDays(7)->format('Y-m-d');
                $end = $endOfWeekFri->format('Y-m-d');
            } else {
                // Default sales / admin: awal bulan ini s/d akhir bulan ini (atau siklus berjalan)
                $start = $startOfMonth->format('Y-m-d');
                $end = $endOfMonth->format('Y-m-d');
            }
        }

        // Query Utama Kasbon
        $kasbonsQuery = Kasbon::with(['user', 'approver']);

        if (!$isAdmin) {
            $kasbonsQuery->where('user_id', $user->id);
        } else {
            if ($userId) {
                $kasbonsQuery->where('user_id', $userId);
            }
            if ($roleFilter && in_array($roleFilter, ['Teknisi', 'Sales'])) {
                $kasbonsQuery->whereHas('user', function ($q) use ($roleFilter) {
                    $q->where('jabatan', $roleFilter);
                });
            }
        }

        if ($statusFilter && in_array($statusFilter, ['pending', 'approved', 'rejected'])) {
            $kasbonsQuery->where('status', $statusFilter);
        }

        if ($start) $kasbonsQuery->whereDate('tanggal', '>=', $start);
        if ($end) $kasbonsQuery->whereDate('tanggal', '<=', $end);

        $kasbons = $kasbonsQuery->orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // Base Fee Aktivasi untuk Perhitungan Pendapatan Teknisi
        $setting = FinanceSetting::first();
        $baseFee = $setting ? (float)$setting->activation_fee : 150000;

        // Cache perhitungan performa per user agar query database efisien
        $teknisiPendapatanMap = [];
        $salesAktivasiMap = [];

        foreach ($kasbons as $k) {
            $applicant = $k->user;
            if (!$applicant) continue;

            if ($applicant->jabatan === 'Teknisi') {
                if (!isset($teknisiPendapatanMap[$applicant->id])) {
                    $teknisiPendapatanMap[$applicant->id] = $this->calculateTeknisiPendapatan(
                        $applicant->id,
                        $startOfWeekSat,
                        $endOfWeekFri,
                        $baseFee
                    );
                }
                $k->performa_teknisi_pendapatan = $teknisiPendapatanMap[$applicant->id];
            } elseif ($applicant->jabatan === 'Sales') {
                if (!isset($salesAktivasiMap[$applicant->id])) {
                    $salesAktivasiMap[$applicant->id] = $this->calculateSalesAktivasi(
                        $applicant->id,
                        $startOfMonth,
                        $endOfMonth
                    );
                }
                $k->performa_sales_aktivasi = $salesAktivasiMap[$applicant->id];
            }
        }

        // --- STATISTIK & METRIK KHUSUS PER ROLE ---
        $stats = [];

        if ($isAdmin) {
            // Admin Metrics
            $stats['total_approved_nominal'] = Kasbon::where('status', 'approved')
                ->when($start, fn($q) => $q->whereDate('tanggal', '>=', $start))
                ->when($end, fn($q) => $q->whereDate('tanggal', '<=', $end))
                ->sum('nominal');

            $stats['total_pending_count'] = Kasbon::where('status', 'pending')->count();
            $stats['total_pending_nominal'] = Kasbon::where('status', 'pending')->sum('nominal');

            // Kasbon Teknisi Minggu Ini (Sabtu - Jumat)
            $stats['teknisi_kasbon_minggu_ini'] = Kasbon::where('status', 'approved')
                ->whereHas('user', fn($q) => $q->where('jabatan', 'Teknisi'))
                ->whereBetween('tanggal', [$startOfWeekSat->format('Y-m-d'), $endOfWeekFri->format('Y-m-d')])
                ->sum('nominal');

            // Kasbon Sales Bulan Ini
            $stats['sales_kasbon_bulan_ini'] = Kasbon::where('status', 'approved')
                ->whereHas('user', fn($q) => $q->where('jabatan', 'Sales'))
                ->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year)
                ->sum('nominal');

        } elseif ($isTeknisi) {
            // Teknisi Metrics
            $myKasbonMingguIni = Kasbon::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereBetween('tanggal', [$startOfWeekSat->format('Y-m-d'), $endOfWeekFri->format('Y-m-d')])
                ->sum('nominal');

            $myPendapatanMingguIni = $this->calculateTeknisiPendapatan($user->id, $startOfWeekSat, $endOfWeekFri, $baseFee);

            $stats['teknisi_kasbon_minggu_ini'] = $myKasbonMingguIni;
            $stats['teknisi_pendapatan_minggu_ini'] = $myPendapatanMingguIni;
            $stats['teknisi_sisa_estimasi'] = max(0, $myPendapatanMingguIni - $myKasbonMingguIni);
            $stats['my_pending_count'] = Kasbon::where('user_id', $user->id)->where('status', 'pending')->count();
            $stats['my_pending_nominal'] = Kasbon::where('user_id', $user->id)->where('status', 'pending')->sum('nominal');

        } elseif ($isSales) {
            // Sales Metrics
            $myKasbonBulanIni = Kasbon::where('user_id', $user->id)
                ->where('status', 'approved')
                ->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year)
                ->sum('nominal');

            $myAktivasiBulanIni = $this->calculateSalesAktivasi($user->id, $startOfMonth, $endOfMonth);

            $stats['sales_kasbon_bulan_ini'] = $myKasbonBulanIni;
            $stats['sales_aktivasi_bulan_ini'] = $myAktivasiBulanIni;
            $stats['my_pending_count'] = Kasbon::where('user_id', $user->id)->where('status', 'pending')->count();
            $stats['my_pending_nominal'] = Kasbon::where('user_id', $user->id)->where('status', 'pending')->sum('nominal');
        }

        // Data User untuk Dropdown Admin
        $teknisis = User::where('jabatan', 'Teknisi')->orderBy('nama_lengkap', 'asc')->get();
        $allSales = User::where('jabatan', 'Sales')->orderBy('nama_lengkap', 'asc')->get();

        return view('finance.kasbon', compact(
            'kasbons',
            'isAdmin',
            'isTeknisi',
            'isSales',
            'user',
            'stats',
            'teknisis',
            'allSales',
            'userId',
            'roleFilter',
            'statusFilter',
            'start',
            'end',
            'isFiltered',
            'startOfWeekSat',
            'endOfWeekFri',
            'startOfMonth',
            'endOfMonth'
        ));
    }

    /**
     * Simpan Kasbon Baru / Pengajuan Kasbon
     */
    public function store(Request $request)
    {
        $currentUser = auth()->user();
        $isAdmin = $currentUser->isAdmin();
        $isTeknisi = $currentUser->jabatan === 'Teknisi';
        $isSales = $currentUser->jabatan === 'Sales';

        if (!$isAdmin && !$isTeknisi && !$isSales) {
            abort(403, "Akses ditolak");
        }

        $rules = [
            'nominal'    => 'required|numeric|min:1000',
            'tanggal'    => 'required|date',
            'bukti'      => 'nullable|mimes:jpeg,png,jpg,pdf|max:5120',
            'keterangan' => 'nullable|string|max:500'
        ];

        if ($isAdmin) {
            $rules['user_id'] = 'required|exists:users,id';
        }

        $request->validate($rules);

        $targetUserId = $isAdmin ? (int)$request->input('user_id') : $currentUser->id;
        $targetUser = User::findOrFail($targetUserId);

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('kasbons');
        }

        // Jika dibuat oleh Admin, status langsung 'approved'
        // Jika diajukan oleh Teknisi / Sales, status 'pending' (menunggu ACC)
        $status = $isAdmin ? 'approved' : 'pending';
        $approvedBy = $isAdmin ? $currentUser->id : null;
        $approvedAt = $isAdmin ? now() : null;

        Kasbon::create([
            'user_id'       => $targetUser->id,
            'teknisi_id'    => $targetUser->id,
            'nominal'       => $request->input('nominal'),
            'tanggal'       => $request->input('tanggal'),
            'bukti'         => $buktiPath,
            'keterangan'    => $request->input('keterangan'),
            'status'        => $status,
            'approved_by'   => $approvedBy,
            'approved_at'   => $approvedAt,
        ]);

        $message = $isAdmin
            ? "Kasbon {$targetUser->nama_lengkap} (Rp " . number_format($request->nominal, 0, ',', '.') . ") berhasil dicatat dan disetujui."
            : "Pengajuan kasbon sebesar Rp " . number_format($request->nominal, 0, ',', '.') . " berhasil dikirim! Menunggu persetujuan (ACC) dari Admin.";

        return redirect()->back()->with('success', $message);
    }

    /**
     * ACC / Setujui Pengajuan Kasbon (Khusus Admin / Super Admin)
     */
    public function approve($id, Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->isAdmin()) {
            abort(403, "Akses ditolak. Hanya Admin atau Super Admin yang dapat melakukan ACC.");
        }

        $kasbon = Kasbon::with('user')->findOrFail($id);

        $kasbon->update([
            'status'        => 'approved',
            'approved_by'   => $currentUser->id,
            'approved_at'   => now(),
            'catatan_admin' => $request->input('catatan_admin', 'Disetujui oleh ' . $currentUser->nama_lengkap)
        ]);

        $namaPemohon = $kasbon->user->nama_lengkap ?? 'Pemohon';
        $nominal = number_format($kasbon->nominal, 0, ',', '.');

        return redirect()->back()->with('success', "Pengajuan kasbon {$namaPemohon} sebesar Rp {$nominal} berhasil disetujui (ACC).");
    }

    /**
     * Tolak Pengajuan Kasbon (Khusus Admin / Super Admin)
     */
    public function reject($id, Request $request)
    {
        $currentUser = auth()->user();
        if (!$currentUser->isAdmin()) {
            abort(403, "Akses ditolak. Hanya Admin atau Super Admin yang dapat menolak pengajuan kasbon.");
        }

        $kasbon = Kasbon::with('user')->findOrFail($id);

        $kasbon->update([
            'status'        => 'rejected',
            'approved_by'   => $currentUser->id,
            'approved_at'   => now(),
            'catatan_admin' => $request->input('catatan_admin', 'Ditolak oleh ' . $currentUser->nama_lengkap)
        ]);

        $namaPemohon = $kasbon->user->nama_lengkap ?? 'Pemohon';

        return redirect()->back()->with('success', "Pengajuan kasbon {$namaPemohon} telah ditolak.");
    }

    /**
     * Hapus Kasbon
     */
    public function destroy($id)
    {
        $currentUser = auth()->user();
        $kasbon = Kasbon::with('user')->findOrFail($id);

        // Hanya Admin yang bisa hapus kasbon yang sudah diapprove
        // Teknisi/Sales hanya boleh membatalkan pengajuan mereka sendiri jika statusnya masih 'pending'
        if (!$currentUser->isAdmin()) {
            if ($kasbon->user_id !== $currentUser->id || $kasbon->status !== 'pending') {
                abort(403, "Akses ditolak.");
            }
        }

        if ($kasbon->bukti) {
            Storage::delete($kasbon->bukti);
        }

        $kasbon->delete();

        return redirect()->back()->with('success', 'Data kasbon berhasil dihapus.');
    }

    /**
     * Helper: Hitung total pendapatan aktivasi teknisi pada rentang tanggal
     */
    private function calculateTeknisiPendapatan($teknisiId, Carbon $startDate, Carbon $endDate, float $baseFee): float
    {
        $customers = Customer::where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                  ->orWhereJsonContains('partner_ids', (string)$teknisiId)
                  ->orWhereJsonContains('partner_ids', (int)$teknisiId);
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalPendapatan = 0;
        foreach ($customers as $c) {
            $partners = is_array($c->partner_ids) ? $c->partner_ids : json_decode($c->partner_ids, true);
            $partnerCount = is_array($partners) ? count($partners) : 0;
            $totalTechs = 1 + $partnerCount;
            $feePerTech = $baseFee / max(1, $totalTechs);

            $isMain = (int)$c->id_teknisi === (int)$teknisiId;
            $isPartner = is_array($partners) && (in_array((string)$teknisiId, $partners) || in_array((int)$teknisiId, $partners));

            if ($isMain || $isPartner) {
                $totalPendapatan += $feePerTech;
            }
        }

        return (float)$totalPendapatan;
    }

    /**
     * Helper: Hitung total aktivasi pelanggan sales pada rentang tanggal
     */
    private function calculateSalesAktivasi($salesId, Carbon $startDate, Carbon $endDate): int
    {
        return Customer::where(function ($q) use ($salesId) {
                $q->where('id_sales', $salesId)
                  ->orWhereHas('preOrder', function ($po) use ($salesId) {
                      $po->where('id_sales', $salesId);
                  });
            })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();
    }
}

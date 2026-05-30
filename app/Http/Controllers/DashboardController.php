<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Customer;
use Illuminate\Http\Request;
use App\Models\PreOrder;
use App\Models\User;
use App\Models\Maintenance;
use App\Models\Dismantle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Data Dasar & Log Aktivitas
        $logs = collect();
        if ($user->jabatan === 'Administrator') {
            $logs = ActivityLog::latest()->take(10)->get();
        }

        // Custom Billing Cycle: 29th to 28th
        $today = Carbon::now();
        if ($today->day >= 29) {
            $startOfMonth = $today->copy()->startOfDay()->day(29);
            $endOfMonth = $today->copy()->addMonth()->startOfDay()->day(28)->endOfDay();
        } else {
            $startOfMonth = $today->copy()->subMonth()->startOfDay()->day(29);
            $endOfMonth = $today->copy()->startOfDay()->day(28)->endOfDay();
        }

        $startOfPreviousMonth = $startOfMonth->copy()->subMonth();
        $endOfPreviousMonth = $endOfMonth->copy()->subMonth();

        $currentPeriodLabel = $startOfMonth->translatedFormat('d M Y') . ' - ' . $endOfMonth->translatedFormat('d M Y');
        $previousPeriodLabel = $startOfPreviousMonth->translatedFormat('d M Y') . ' - ' . $endOfPreviousMonth->translatedFormat('d M Y');

        // 2. Query PO Umum
        $poQueryThisMonth = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $poQueryPreviousMonth = PreOrder::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth]);

        if ($user->jabatan === 'Sales') {
            $poQueryThisMonth->where('id_sales', $user->id);
            $poQueryPreviousMonth->where('id_sales', $user->id);
        }

        $totalPoThisMonth = $poQueryThisMonth->count();
        $totalPoPreviousMonth = $poQueryPreviousMonth->count();

        // 3. Query Aktivasi & Pending Umum
        $activatedPOIds = Customer::pluck('pre_order_id');
        $totalAktivasiThisMonthQuery = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
        $totalPendingThisMonthQuery = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->whereNotIn('id', $activatedPOIds);

        if ($user->jabatan === 'Sales') {
            $totalAktivasiThisMonthQuery->whereHas('preorder', function ($query) use ($user) {
                $query->where('id_sales', $user->id);
            });
            $totalPendingThisMonthQuery->where('id_sales', $user->id);
        }

        $totalAktivasiThisMonth = $totalAktivasiThisMonthQuery->count();
        $totalPendingThisMonth = $totalPendingThisMonthQuery->count();

        // 4. Perhitungan Persentase
        $percentageChange = 0;
        if ($totalPoPreviousMonth > 0) {
            $percentageChange = (($totalPoThisMonth - $totalPoPreviousMonth) / $totalPoPreviousMonth) * 100;
        } elseif ($totalPoThisMonth > 0) {
            $percentageChange = 100;
        }

        // 4.1 Maintenance Selesai Global (Untuk Admin)
        $totalMaintenanceSelesaiBulanIni = Maintenance::where('status', 'Selesai')
            ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
            ->count();

        // 5. Inisialisasi Variabel Chart
        $mChartLabels = []; $mChartValues = []; $mDonutLabels = []; $mDonutValues = [];
        $dChartLabels = []; $dChartValues = []; $dDonutLabels = []; $dDonutValues = [];
        $aChartLabels = []; $aChartValues = []; $aDonutLabels = []; $aDonutValues = [];
        $sChartLabels = []; $sChartActivated = []; $sChartPending = [];

        // 6. Logika Admin: Fetch Stats
        if ($user->jabatan === 'Administrator') {
            $thisMonth = Carbon::now()->month;
            $thisYear = Carbon::now()->year;

            // Tren Aktivasi Mingguan (Sabtu - Jumat)
            $mostRecentFriday = Carbon::now()->isFriday() ? Carbon::now() : Carbon::now()->previous(Carbon::FRIDAY);
            for ($i = 4; $i >= 0; $i--) {
                $endOfPeriod = $mostRecentFriday->copy()->subWeeks($i);
                $startOfPeriod = $endOfPeriod->copy()->subDays(6);
                
                $label = $startOfPeriod->translatedFormat('d M') . ' - ' . $endOfPeriod->translatedFormat('d M');
                $aChartLabels[] = $label;
                $aChartValues[] = Customer::withTrashed()->whereBetween('created_at', [
                    $startOfPeriod->startOfDay(),
                    $endOfPeriod->endOfDay()
                ])->count();
            }

            // Top Teknisi Aktivasi (Bulan Ini)
            $activations = Customer::withTrashed()->whereMonth('created_at', $thisMonth)
                ->whereYear('created_at', $thisYear)
                ->get();

            $techPoints = [];
            foreach ($activations as $act) {
                if ($act->id_teknisi) {
                    $techPoints[$act->id_teknisi] = ($techPoints[$act->id_teknisi] ?? 0) + 1;
                }
                if (!empty($act->partner_ids)) {
                    $pIds = is_array($act->partner_ids) ? $act->partner_ids : json_decode($act->partner_ids, true);
                    if (is_array($pIds)) {
                        foreach ($pIds as $pid) {
                            $techPoints[$pid] = ($techPoints[$pid] ?? 0) + 1;
                        }
                    }
                }
            }

            arsort($techPoints);
            $topActIds = array_slice(array_keys($techPoints), 0, 5);
            $topActUsers = User::whereIn('id', $topActIds)->get()->keyBy('id');

            foreach ($topActIds as $tid) {
                $aDonutLabels[] = $topActUsers[$tid]->nama_lengkap ?? 'Unknown';
                $aDonutValues[] = $techPoints[$tid];
            }

            // Top Sales
            $salesPerform = PreOrder::whereMonth('created_at', $thisMonth)
                ->whereYear('created_at', $thisYear)
                ->with('sales')
                ->get()
                ->groupBy('id_sales')
                ->map(function($group) use ($activatedPOIds) {
                    return [
                        'name' => $group->first()->sales->nama_lengkap ?? 'Unknown',
                        'activated' => $group->filter(fn($po) => $activatedPOIds->contains($po->id))->count(),
                        'pending' => $group->filter(fn($po) => !$activatedPOIds->contains($po->id))->count(),
                        'total' => $group->count()
                    ];
                })
                ->sortByDesc('total')
                ->take(5);

            foreach ($salesPerform as $sp) {
                $sChartLabels[] = $sp['name'];
                $sChartActivated[] = $sp['activated'];
                $sChartPending[] = $sp['pending'];
            }

            // Maintenance & Dismantle Trends
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = Carbon::now()->day($i);
                $mChartLabels[] = $date->translatedFormat('d M');
                $mChartValues[] = Maintenance::whereDate('created_at', $date->toDateString())->count();
                $dChartLabels[] = $date->translatedFormat('d M');
                $dChartValues[] = Dismantle::whereDate('created_at', $date->toDateString())->count();
            }

            // Maintenance Top Teknisi
            $mTop = Maintenance::where('status', 'Selesai')
                ->whereMonth('completed_at', $thisMonth)
                ->whereYear('completed_at', $thisYear)
                ->with('teknisiEksekutor')
                ->get()
                ->groupBy('id_teknisi_eksekutor')
                ->map(fn($g) => [
                    'name' => $g->first()->teknisiEksekutor->nama_lengkap ?? 'Unknown',
                    'count' => $g->count()
                ])
                ->sortByDesc('count')
                ->take(5);
            $mDonutLabels = $mTop->pluck('name')->toArray();
            $mDonutValues = $mTop->pluck('count')->toArray();

            // Dismantle Top Teknisi
            $dTop = Dismantle::where('status', 'Selesai')
                ->whereMonth('completed_at', $thisMonth)
                ->whereYear('completed_at', $thisYear)
                ->with('teknisiEksekutor')
                ->get()
                ->groupBy('id_teknisi_eksekutor')
                ->map(fn($g) => [
                    'name' => $g->first()->teknisiEksekutor->nama_lengkap ?? 'Unknown',
                    'count' => $g->count()
                ])
                ->sortByDesc('count')
                ->take(5);
            $dDonutLabels = $dTop->pluck('name')->toArray();
            $dDonutValues = $dTop->pluck('count')->toArray();
        }

        // 7. Inisialisasi Variabel Teknisi
        $delegatedTasks = collect();
        $aktivasiMingguIni = 0;
        $aktivasiBulanIni = 0;
        $pendingTugas = 0;
        $totalExpired = 0;
        $personalMaintenanceSelesaiBulanIni = 0;
        $statsMaintenance = ['alone' => 0, 'pair' => 0, 'trio' => 0, 'total' => 0];
        $statsAktivasi = ['alone' => 0, 'pair' => 0, 'trio' => 0, 'total' => 0];

        // --- STATS KHUSUS SALES ---
        $totalPelangganSaya = 0;
        $totalIsolirSaya = 0;

        if ($user->jabatan === 'Sales' || $user->jabatan === 'Administrator') {
            $myCustomersQuery = Customer::query();
            if ($user->jabatan === 'Sales') {
                $myCustomersQuery->where('id_sales', $user->id);
            }
            $totalPelangganSaya = $myCustomersQuery->count();

            // Hitung Isolir
            $pppoeData = Cache::get('mikrotik_pppoe_active');
            if (!$pppoeData) {
                $pppoeData = $this->mikroTikService->getPppoeActive();
            }

            if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
                $isolirUsernames = collect($pppoeData['data'])
                    ->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
                    ->pluck('name')
                    ->toArray();

                if (!empty($isolirUsernames)) {
                    $isolirSayaQuery = Customer::whereIn('pppoe_username', $isolirUsernames);
                    if ($user->jabatan === 'Sales') {
                        $isolirSayaQuery->where('id_sales', $user->id);
                    }
                    $totalIsolirSaya = $isolirSayaQuery->count();
                }
            }
        }

        // 8. Logika Khusus Teknisi
        if ($user->jabatan === 'Teknisi') {
            // Periode Sabtu - Jumat (Untuk Aktivasi)
            $startOfWeekSat = Carbon::now()->isSaturday() ? Carbon::now()->startOfDay() : Carbon::now()->previous(Carbon::SATURDAY)->startOfDay();
            $endOfWeekFri = $startOfWeekSat->copy()->addDays(6)->endOfDay();

            // 1. Maintenance Selesai SAYA (Bulan Berjalan: 29 - 28)
            $maintenancesMonthly = Maintenance::where('status', 'Selesai')
                ->where('id_teknisi_eksekutor', $user->id)
                ->whereBetween('completed_at', [$startOfMonth, $endOfMonth])
                ->get();

            $statsMaintenance = [
                'alone' => 0,
                'pair' => 0,
                'trio' => 0,
                'total' => $maintenancesMonthly->count()
            ];

            foreach ($maintenancesMonthly as $m) {
                $partnerCount = 0;
                if (!empty($m->partner_ids)) {
                    $pIds = is_array($m->partner_ids) ? $m->partner_ids : json_decode($m->partner_ids, true);
                    $partnerCount = is_array($pIds) ? count($pIds) : 0;
                }
                if ($partnerCount === 0) $statsMaintenance['alone']++;
                elseif ($partnerCount === 1) $statsMaintenance['pair']++;
                elseif ($partnerCount >= 2) $statsMaintenance['trio']++;
            }

            // 2. Aktivasi PO Selesai SAYA (Sabtu - Jumat)
            $activationsWeekly = Customer::whereBetween('created_at', [$startOfWeekSat, $endOfWeekFri])
                ->where(function ($query) use ($user) {
                    $query->where('id_teknisi', $user->id)
                        ->orWhereJsonContains('partner_ids', (string)$user->id);
                })->get();

            $statsAktivasi = [
                'alone' => 0,
                'pair' => 0,
                'trio' => 0,
                'total' => $activationsWeekly->count()
            ];

            foreach ($activationsWeekly as $act) {
                $partnerCount = 0;
                if (!empty($act->partner_ids)) {
                    $pIds = is_array($act->partner_ids) ? $act->partner_ids : json_decode($act->partner_ids, true);
                    $partnerCount = is_array($pIds) ? count($pIds) : 0;
                }
                if ($partnerCount === 0) $statsAktivasi['alone']++;
                elseif ($partnerCount === 1) $statsAktivasi['pair']++;
                elseif ($partnerCount >= 2) $statsAktivasi['trio']++;
            }

            // Keep monthly total for top card
            $personalMaintenanceSelesaiBulanIni = $statsMaintenance['total'];
            $aktivasiMingguIni = $statsAktivasi['total'];

            // Aktivasi Bulan Ini (Total Count)
            $aktivasiBulanIni = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($query) use ($user) {
                    $query->where('id_teknisi', $user->id)
                        ->orWhereJsonContains('partner_ids', (string)$user->id);
                })->count();

            // Pending Tugas
            $pendingTugas = PreOrder::whereNotIn('id', $activatedPOIds)
                ->where('id_teknisi_delegasi', $user->id)
                ->count();

            // Total Expired
            $totalExpired = PreOrder::where('id_teknisi_delegasi', $user->id)
                ->whereNotIn('id', $activatedPOIds)
                ->whereNotNull('delegated_at')
                ->get()
                ->filter(function ($po) {
                    return Carbon::now()->greaterThan(Carbon::parse($po->delegated_at)->addDays(3));
                })->count();

            // Tugas Delegasi List
            $delegatedTasks = PreOrder::whereNotIn('id', $activatedPOIds)
                ->where('id_teknisi_delegasi', $user->id)
                ->whereNotNull('delegated_at')
                ->orderBy('delegated_at', 'asc')
                ->get()
                ->map(function ($po) {
                    $deadline = Carbon::parse($po->delegated_at)->addDays(2);
                    $po->deadline_date = $deadline->format('d-m-Y H:i');
                    $po->status_display = 'AKTIF';
                    $po->bg_color = 'bg-info';

                    if (Carbon::now()->greaterThan($deadline)) {
                        $po->status_display = 'EXPIRED';
                        $po->bg_color = 'bg-danger';
                    } elseif (Carbon::now()->diffInHours($deadline) <= 24) {
                        $po->status_display = 'URGENT';
                        $po->bg_color = 'bg-warning text-dark';
                    }
                    return $po;
                });
        }

        return view('dashboard', compact(
            'user',
            'logs',
            'totalPoThisMonth',
            'totalPoPreviousMonth',
            'percentageChange',
            'totalAktivasiThisMonth',
            'totalPendingThisMonth',
            'currentPeriodLabel',
            'previousPeriodLabel',
            'totalPelangganSaya',
            'totalIsolirSaya',
            'delegatedTasks',
            'aktivasiMingguIni',
            'aktivasiBulanIni',
            'pendingTugas',
            'totalExpired',
            'totalMaintenanceSelesaiBulanIni',
            'personalMaintenanceSelesaiBulanIni',
            'statsMaintenance',
            'statsAktivasi',
            'aChartLabels',
            'aChartValues',
            'aDonutLabels',
            'aDonutValues',
            'sChartLabels',
            'sChartActivated',
            'sChartPending',
            'mChartLabels',
            'mChartValues',
            'mDonutLabels',
            'mDonutValues',
            'dChartLabels',
            'dChartValues',
            'dDonutLabels',
            'dDonutValues'
        ));
    }
}

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
use Carbon\Carbon;

class DashboardController extends Controller
{
    // public function index()
    // {
    //     $user = Auth::user();

    //     $logs = collect();
    //     if ($user->jabatan === 'Administrator') {
    //         $logs = ActivityLog::latest()->take(10)->get();
    //     }

    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();
    //     $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();
    //     $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

    //     $poQueryThisMonth = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    //     $poQueryPreviousMonth = PreOrder::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth]);

    //     if ($user->jabatan === 'Sales') {
    //         $poQueryThisMonth->where('id_sales', $user->id);
    //         $poQueryPreviousMonth->where('id_sales', $user->id);
    //     }

    //     $totalPoThisMonth = $poQueryThisMonth->count();
    //     $totalPoPreviousMonth = $poQueryPreviousMonth->count();

    //     $activatedPOIds = Customer::pluck('pre_order_id');
    //     $totalAktivasiThisMonth = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    //     $totalPendingThisMonthQuery = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //         ->whereNotIn('id', $activatedPOIds);

    //     if ($user->jabatan === 'Sales') {
    //         $totalAktivasiThisMonth->whereHas('preorder', function ($query) use ($user) {
    //             $query->where('id_sales', $user->id);
    //         });
    //         $totalPendingThisMonthQuery->where('id_sales', $user->id);
    //     }

    //     $totalAktivasiThisMonth = $totalAktivasiThisMonth->count();
    //     $totalPendingThisMonth = $totalPendingThisMonthQuery->count();
    //     $percentageChange = 0;
    //     if ($totalPoPreviousMonth > 0) {
    //         $percentageChange = (($totalPoThisMonth - $totalPoPreviousMonth) / $totalPoPreviousMonth) * 100;
    //     } elseif ($totalPoThisMonth > 0) {
    //         $percentageChange = 100;
    //     }

    //     $delegatedTasks = collect();

    //     $aktivasiMingguIni = 0;
    //     $aktivasiBulanIni = 0;
    //     $pendingTugas = 0;
    //     $totalExpired = 0;


    //     if ($user->jabatan === 'Teknisi') {
    //         // Aktivasi minggu ini oleh teknisi
    //         $startOfWeek = Carbon::now()->startOfWeek();
    //         $endOfWeek = Carbon::now()->endOfWeek();

    //         $aktivasiMingguIni = Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])
    //             ->whereHas('preorder', function ($query) use ($user) {
    //                 $query->where('id_teknisi_delegasi', $user->id);
    //             })
    //             ->count();

    //         // Aktivasi bulan ini oleh teknisi
    //         $aktivasiBulanIni = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //             ->whereHas('preorder', function ($query) use ($user) {
    //                 $query->where('id_teknisi_delegasi', $user->id);
    //             })
    //             ->count();

    //         // Total pending tugas
    //         $pendingTugas = PreOrder::whereNotIn('id', $activatedPOIds)
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->count();

    //         // Total expired delegasi
    //         $expiredTugas = PreOrder::where('id_teknisi_delegasi', $user->id)
    //             ->whereNotNull('delegated_at')
    //             ->get()
    //             ->filter(function ($po) {
    //                 $delegationDate = Carbon::parse($po->delegated_at);
    //                 return Carbon::now()->greaterThan($delegationDate->copy()->addDays(3));
    //             });

    //         $totalExpired = $expiredTugas->count();

    //         // Delegated tasks log + status display
    //         $delegatedTasks = PreOrder::whereNotIn('id', $activatedPOIds)
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->whereNotNull('delegated_at')
    //             ->orderBy('delegated_at', 'asc')
    //             ->get()
    //             ->map(function ($po) {
    //                 $po->deadline_date = null;
    //                 $po->status_display = 'AKTIF';
    //                 $po->bg_color = 'bg-info';

    //                 if ($po->delegated_at) {
    //                     $delegationDate = Carbon::parse($po->delegated_at);
    //                     $deadline = $delegationDate->copy()->addDays(3);
    //                     $now = Carbon::now();

    //                     $po->deadline_date = $deadline->format('d-m-Y H:i');

    //                     if ($now->greaterThan($deadline)) {
    //                         $po->status_display = 'EXPIRED';
    //                         $po->bg_color = 'bg-danger';
    //                     } elseif ($now->diffInHours($deadline) <= 24) {
    //                         $po->status_display = 'SEGERA EXPIRED';
    //                         $po->bg_color = 'bg-warning text-dark';
    //                     }
    //                 }
    //                 return $po;
    //             });
    //     }

    //     return view('dashboard', compact(
    //         'totalPoThisMonth',
    //         'totalPoPreviousMonth',
    //         'percentageChange',
    //         'totalAktivasiThisMonth',
    //         'totalPendingThisMonth',
    //         'delegatedTasks',
    //         'user',
    //         'logs',
    //         'aktivasiMingguIni',
    //         'aktivasiBulanIni',
    //         'pendingTugas',
    //         'totalExpired'
    //     ));
    // }


    // public function index()
    // {
    //     $user = Auth::user();

    //     // 1. Data Dasar & Log Aktivitas
    //     $logs = collect();
    //     if ($user->jabatan === 'Administrator') {
    //         $logs = ActivityLog::latest()->take(10)->get();
    //     }

    //     $startOfMonth = Carbon::now()->startOfMonth();
    //     $endOfMonth = Carbon::now()->endOfMonth();
    //     $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();
    //     $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

    //     // 2. Query PO Umum (Untuk Statistik Atas)
    //     $poQueryThisMonth = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    //     $poQueryPreviousMonth = PreOrder::whereBetween('created_at', [$startOfPreviousMonth, $endOfPreviousMonth]);

    //     if ($user->jabatan === 'Sales') {
    //         $poQueryThisMonth->where('id_sales', $user->id);
    //         $poQueryPreviousMonth->where('id_sales', $user->id);
    //     }

    //     $totalPoThisMonth = $poQueryThisMonth->count();
    //     $totalPoPreviousMonth = $poQueryPreviousMonth->count();

    //     // 3. Query Aktivasi & Pending Umum
    //     $activatedPOIds = Customer::pluck('pre_order_id');
    //     $totalAktivasiThisMonthQuery = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth]);
    //     $totalPendingThisMonthQuery = PreOrder::whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //         ->whereNotIn('id', $activatedPOIds);

    //     if ($user->jabatan === 'Sales') {
    //         $totalAktivasiThisMonthQuery->whereHas('preorder', function ($query) use ($user) {
    //             $query->where('id_sales', $user->id);
    //         });
    //         $totalPendingThisMonthQuery->where('id_sales', $user->id);
    //     }

    //     $totalAktivasiThisMonth = $totalAktivasiThisMonthQuery->count();
    //     $totalPendingThisMonth = $totalPendingThisMonthQuery->count();

    //     // 4. Perhitungan Persentase
    //     $percentageChange = 0;
    //     if ($totalPoPreviousMonth > 0) {
    //         $percentageChange = (($totalPoThisMonth - $totalPoPreviousMonth) / $totalPoPreviousMonth) * 100;
    //     } elseif ($totalPoThisMonth > 0) {
    //         $percentageChange = 100;
    //     }

    //     // 5. Inisialisasi Variabel Teknisi agar tidak Error di View
    //     $delegatedTasks = collect();
    //     $aktivasiMingguIni = 0;
    //     $aktivasiBulanIni = 0;
    //     $pendingTugas = 0;
    //     $totalExpired = 0;

    //     // 6. Logika Khusus Teknisi
    //     if ($user->jabatan === 'Teknisi') {
    //         $startOfWeek = Carbon::now()->startOfWeek();
    //         $endOfWeek = Carbon::now()->endOfWeek();

    //         // AKTIVASI MINGGU INI: Langsung cek kolom id_teknisi di tabel customers
    //         $aktivasiMingguIni = Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])
    //             ->where('id_teknisi', $user->id) // Menggunakan ID teknisi yang login
    //             ->count();

    //         // AKTIVASI BULAN INI: Langsung cek kolom id_teknisi di tabel customers
    //         $aktivasiBulanIni = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])
    //             ->where('id_teknisi', $user->id)
    //             ->count();

    //         // PENDING TUGAS: Mengacu pada PO yang didelegasikan ke dia tapi belum ada di tabel customers
    //         $pendingTugas = PreOrder::whereNotIn('id', $activatedPOIds)
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->count();

    //         // TOTAL EXPIRED: PO yang belum aktivasi dan sudah lewat 3 hari sejak didelegasikan
    //         $totalExpired = PreOrder::where('id_teknisi_delegasi', $user->id)
    //             ->whereNotIn('id', $activatedPOIds)
    //             ->whereNotNull('delegated_at')
    //             ->get()
    //             ->filter(function ($po) {
    //                 return Carbon::now()->greaterThan(Carbon::parse($po->delegated_at)->addDays(3));
    //             })->count();

    //         // TUGAS DELEGASI: Untuk tampilan list card
    //         $delegatedTasks = PreOrder::whereNotIn('id', $activatedPOIds)
    //             ->where('id_teknisi_delegasi', $user->id)
    //             ->whereNotNull('delegated_at')
    //             ->orderBy('delegated_at', 'asc')
    //             ->get()
    //             ->map(function ($po) {
    //                 $deadline = Carbon::parse($po->delegated_at)->addDays(3);
    //                 $po->deadline_date = $deadline->format('d-m-Y H:i');
    //                 $po->status_display = 'AKTIF';
    //                 $po->bg_color = 'bg-info';

    //                 if (Carbon::now()->greaterThan($deadline)) {
    //                     $po->status_display = 'EXPIRED';
    //                     $po->bg_color = 'bg-danger';
    //                 } elseif (Carbon::now()->diffInHours($deadline) <= 24) {
    //                     $po->status_display = 'URGENT';
    //                     $po->bg_color = 'bg-warning text-dark';
    //                 }
    //                 return $po;
    //             });
    //     }

    //     return view('dashboard', compact(
    //         'user',
    //         'logs',
    //         'totalPoThisMonth',
    //         'totalPoPreviousMonth',
    //         'percentageChange',
    //         'totalAktivasiThisMonth',
    //         'totalPendingThisMonth',
    //         'delegatedTasks',
    //         'aktivasiMingguIni',
    //         'aktivasiBulanIni',
    //         'pendingTugas',
    //         'totalExpired'
    //     ));
    // }

    public function index()
    {
        $user = Auth::user();

        // 1. Data Dasar & Log Aktivitas
        $logs = collect();
        if ($user->jabatan === 'Administrator') {
            $logs = ActivityLog::latest()->take(10)->get();
        }

        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $startOfPreviousMonth = Carbon::now()->subMonth()->startOfMonth();
        $endOfPreviousMonth = Carbon::now()->subMonth()->endOfMonth();

        // 2. Query PO Umum (Untuk Statistik Atas)
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

        // 5. Inisialisasi Variabel Statistik Activation, Maintenance, Dismantle & Sales
        $mChartLabels = []; $mChartValues = []; $mDonutLabels = []; $mDonutValues = [];
        $dChartLabels = []; $dChartValues = []; $dDonutLabels = []; $dDonutValues = [];
        $aChartLabels = []; $aChartValues = []; $aDonutLabels = []; $aDonutValues = [];
        $sChartLabels = []; $sChartActivated = []; $sChartPending = [];

        // 6. Logika Admin: Fetch Stats
        if ($user->jabatan === 'Administrator') {
            $thisMonth = Carbon::now()->month;
            $thisYear = Carbon::now()->year;

            // --- PERFORMA AKTIVASI ---
            
            // Tren Aktivasi Mingguan (Sabtu - Jumat) - 5 Minggu Terakhir
            // Menggunakan withTrashed() karena aktivasi yang sudah non-aktif tetap dihitung sebagai hasil kerja
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

            // Top Teknisi Aktivasi (Bulan Ini, termasuk Partner)
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

            // --- PERFORMA SALES ---
            
            // Top Sales (Bulan Ini: Sudah Aktivasi vs Pending)
            $activatedPOIds = Customer::withTrashed()->pluck('pre_order_id');
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

            // --- MAINTENANCE & DISMANTLE ---
            $daysInMonth = Carbon::now()->daysInMonth;
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = Carbon::now()->day($i);
                $mChartLabels[] = $date->translatedFormat('d M');
                $mChartValues[] = Maintenance::whereDate('created_at', $date->toDateString())->count();
            }

            // Maintenance Top Teknisi (Bulan Ini)
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

            // Dismantle Trends (Bulan Ini)
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $date = Carbon::now()->day($i);
                $dChartLabels[] = $date->translatedFormat('d M');
                $dChartValues[] = Dismantle::whereDate('created_at', $date->toDateString())->count();
            }

            // Dismantle Top Teknisi (Bulan Ini)
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

        // 7. Inisialisasi Variabel Teknisi agar tidak Error di View
        $delegatedTasks = collect();
        $aktivasiMingguIni = 0;
        $aktivasiBulanIni = 0;
        $pendingTugas = 0;
        $totalExpired = 0;

        // 8. Logika Khusus Teknisi (Update: Termasuk Partner)
        if ($user->jabatan === 'Teknisi') {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();

            // AKTIVASI MINGGU INI: Cek id_teknisi UTAMA atau dalam array partner_ids
            $aktivasiMingguIni = Customer::whereBetween('created_at', [$startOfWeek, $endOfWeek])
                ->where(function ($query) use ($user) {
                    $query->where('id_teknisi', $user->id)
                        ->orWhereJsonContains('partner_ids', (string)$user->id);
                })->count();

            // AKTIVASI BULAN INI: Cek id_teknisi UTAMA atau dalam array partner_ids
            $aktivasiBulanIni = Customer::whereBetween('created_at', [$startOfMonth, $endOfMonth])
                ->where(function ($query) use ($user) {
                    $query->where('id_teknisi', $user->id)
                        ->orWhereJsonContains('partner_ids', (string)$user->id);
                })->count();

            // PENDING TUGAS: Tugas yang didelegasikan ke dia tapi belum ada di tabel customers
            $pendingTugas = PreOrder::whereNotIn('id', $activatedPOIds)
                ->where('id_teknisi_delegasi', $user->id)
                ->count();

            // TOTAL EXPIRED: PO yang belum aktivasi dan sudah lewat 3 hari sejak didelegasikan
            $totalExpired = PreOrder::where('id_teknisi_delegasi', $user->id)
                ->whereNotIn('id', $activatedPOIds)
                ->whereNotNull('delegated_at')
                ->get()
                ->filter(function ($po) {
                    return Carbon::now()->greaterThan(Carbon::parse($po->delegated_at)->addDays(3));
                })->count();

            // TUGAS DELEGASI: Untuk tampilan list card
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
            'delegatedTasks',
            'aktivasiMingguIni',
            'aktivasiBulanIni',
            'pendingTugas',
            'totalExpired',
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

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
use App\Models\Dismantle;
use App\Models\Maintenance;
use App\Models\Customer;
use App\Models\PreOrder;
use App\Models\User;
use PDF;
use Carbon\Carbon;

class ActivityLogController extends Controller
{
    public function index()
    {
        // Hanya Administrator yang bisa mengakses
        if (auth()->user()->jabatan !== 'Administrator') {
            abort(403, 'Akses ditolak.');
        }

        $logs = ActivityLog::with('user')
            ->orderBy('logged_at', 'desc')
            ->get();

        // --- STATISTIK ---
        $totalLogs = ActivityLog::count();
        $logsToday = ActivityLog::whereDate('logged_at', Carbon::today())->count();
        $logsPreOrder = ActivityLog::where('activity_type', 'LIKE', '%preorder%')->count();
        $logsAktivasi = ActivityLog::where('activity_type', 'LIKE', '%aktivasi%')->count();

        return view('activitylog.activity-log', compact(
            'logs',
            'totalLogs',
            'logsToday',
            'logsPreOrder',
            'logsAktivasi'
        ));
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $logs = ActivityLog::with('user')
            ->whereBetween('logged_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ])
            ->orderByDesc('logged_at')
            ->get();

        $pdf = PDF::loadView('activitylog.export-pdf', [
            'logs' => $logs,
            'start' => $request->start_date,
            'end' => $request->end_date,
        ]);

        return $pdf->download("activity-log-{$request->start_date}_to_{$request->end_date}.pdf");
    }

    // public function laporanExpired(Request $request)
    // {
    //     $teknisiId = $request->input('teknisi_id');
    //     $start = $request->input('start_date');
    //     $end = $request->input('end_date');

    //     $query = ActivityLog::with('user')
    //         ->where('activity_type', 'delegasi_expired')
    //         ->orderByDesc('logged_at');

    //     if ($teknisiId) {
    //         $query->where('user_id', $teknisiId);
    //     }

    //     if ($start) {
    //         $query->whereDate('logged_at', '>=', $start);
    //     }

    //     if ($end) {
    //         $query->whereDate('logged_at', '<=', $end);
    //     }

    //     $logs = $query->get();

    //     $teknisis = User::where('jabatan', 'Teknisi')->get();

    //     return view('activitylog.expired-log', compact('logs', 'teknisis', 'teknisiId', 'start', 'end'));
    // }

    // public function exportExpired(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $query = ActivityLog::with('user')
    //         ->where('activity_type', 'delegasi_expired')
    //         ->whereBetween('logged_at', [
    //             Carbon::parse($request->start_date)->startOfDay(),
    //             Carbon::parse($request->end_date)->endOfDay(),
    //         ])
    //         ->orderBy('logged_at', 'desc');

    //     if ($request->has('teknisi_id') && $request->teknisi_id) {
    //         $query->where('user_id', $request->teknisi_id);
    //     }

    //     $logs = $query->get();

    //     $pdf = PDF::loadView('activitylog.export-expired', [
    //         'logs' => $logs,
    //         'start' => $request->start_date,
    //         'end' => $request->end_date,
    //         'teknisi' => User::find($request->teknisi_id),
    //     ]);

    //     return $pdf->download("laporan-expired-po-{$request->start_date}_to_{$request->end_date}.pdf");
    // }

    public function laporanExpired(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = ActivityLog::with('user')
            ->where('activity_type', 'delegasi_expired')
            ->orderByDesc('logged_at');

        // Filter berdasarkan Teknisi
        if ($teknisiId) {
            $query->where('user_id', $teknisiId);
        }

        // Filter Rentang Tanggal
        if ($start) $query->whereDate('logged_at', '>=', $start);
        if ($end) $query->whereDate('logged_at', '<=', $end);

        $logs = $query->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        return view('activitylog.po-expired-log', compact('logs', 'teknisis', 'teknisiId', 'start', 'end'));
    }

    /**
     * Export PDF Laporan PO Expired
     */
    public function exportExpired(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $query = ActivityLog::with('user')
            ->where('activity_type', 'delegasi_expired')
            ->whereBetween('logged_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->teknisi_id) {
            $query->where('user_id', $request->teknisi_id);
        }

        $logs = $query->orderBy('logged_at', 'desc')->get();

        $pdf = PDF::loadView('activitylog.export-po-expired', [
            'logs' => $logs,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id)
        ]);

        return $pdf->download("laporan-po-expired-{$request->start_date}.pdf");
    }

    public function laporanAktivasi(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Gunakan withTrashed() agar pelanggan yang sudah di-dismantle tetap muncul di laporan aktivasi
        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales']);

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                    ->orWhereJsonContains('partner_ids', (string)$teknisiId);
            });
        }

        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        $customers = $query->orderBy('created_at', 'desc')->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // --- STATISTIK ---
        $stats = [
            'total_filtered' => $customers->count(),
            'total_all' => Customer::withTrashed()->count(),
            'this_month' => Customer::withTrashed()->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'today' => Customer::withTrashed()->whereDate('created_at', Carbon::today())->count(),
        ];

        return view('activitylog.aktivasi-log', compact('customers', 'teknisis', 'teknisiId', 'start', 'end', 'stats'));
    }

    public function exportAktivasi(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        // Gunakan withTrashed() juga saat melakukan ekspor PDF
        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales'])
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->teknisi_id) {
            $query->where(function ($q) use ($request) {
                $q->where('id_teknisi', $request->teknisi_id)
                    ->orWhereJsonContains('partner_ids', (string)$request->teknisi_id);
            });
        }

        $customers = $query->orderBy('created_at', 'asc')->get();

        $pdf = PDF::loadView('activitylog.export-aktivasi', [
            'customers' => $customers,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $customers->count()
        ]);

        return $pdf->download("laporan-aktivasi-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    public function laporanMaintenance(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = Maintenance::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai');

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi_eksekutor', $teknisiId)
                    ->orWhereJsonContains('partner_ids', (string)$teknisiId);
            });
        }

        if ($start) $query->whereDate('completed_at', '>=', $start);
        if ($end) $query->whereDate('completed_at', '<=', $end);

        $maintenances = $query->orderBy('completed_at', 'desc')->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // --- STATISTIK ---
        $stats = [
            'total_filtered' => $maintenances->count(),
            'total_all' => Maintenance::where('status', 'Selesai')->count(),
            'this_month' => Maintenance::where('status', 'Selesai')->whereMonth('completed_at', Carbon::now()->month)->whereYear('completed_at', Carbon::now()->year)->count(),
            'today' => Maintenance::where('status', 'Selesai')->whereDate('completed_at', Carbon::today())->count(),
        ];

        return view('activitylog.maintenance-log', compact('maintenances', 'teknisis', 'teknisiId', 'start', 'end', 'stats'));
    }

    public function exportMaintenance(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $query = Maintenance::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai')
            ->whereBetween('completed_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->teknisi_id) {
            $query->where(function ($q) use ($request) {
                $q->where('id_teknisi_eksekutor', $request->teknisi_id)
                    ->orWhereJsonContains('partner_ids', (string)$request->teknisi_id);
            });
        }

        $maintenances = $query->orderBy('completed_at', 'asc')->get();
        $pdf = PDF::loadView('activitylog.export-maintenance', [
            'maintenances' => $maintenances,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $maintenances->count()
        ]);

        return $pdf->download("laporan-maintenance-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    // public function laporanPreOrder(Request $request)
    // {
    //     $salesId = $request->input('sales_id');
    //     $start = $request->input('start_date');
    //     $end = $request->input('end_date');

    //     $query = PreOrder::with('sales')->orderByDesc('created_at');

    //     // Filter berdasarkan Sales
    //     if ($salesId) {
    //         $query->where('id_sales', $salesId);
    //     }

    //     // Filter Rentang Tanggal
    //     if ($start) $query->whereDate('created_at', '>=', $start);
    //     if ($end) $query->whereDate('created_at', '<=', $end);

    //     $preorders = $query->get();

    //     // Ambil daftar user dengan jabatan Sales untuk dropdown filter
    //     $allSales = User::where('jabatan', 'Sales')->get();

    //     // Menghitung total data yang difilter
    //     $total = $preorders->count();

    //     return view('activitylog.preorder-log', compact('preorders', 'allSales', 'salesId', 'start', 'end', 'total'));
    // }

    // /**
    //  * Export PDF Laporan Pre-Order
    //  */
    // public function exportPreOrder(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $query = PreOrder::with('sales')
    //         ->whereBetween('created_at', [
    //             Carbon::parse($request->start_date)->startOfDay(),
    //             Carbon::parse($request->end_date)->endOfDay()
    //         ]);

    //     if ($request->sales_id) {
    //         $query->where('id_sales', $request->sales_id);
    //     }

    //     $preorders = $query->orderBy('created_at', 'asc')->get();

    //     // Pastikan setiap key diakhiri dengan koma agar tidak terjadi Parse Error
    //     $pdf = PDF::loadView('activitylog.export-preorder', [
    //         'preorders' => $preorders,
    //         'start' => $request->start_date,
    //         'end' => $request->end_date,
    //         'sales' => User::find($request->sales_id), // Ditambahkan koma di sini
    //         'total' => $preorders->count()
    //     ]);

    //     return $pdf->download("laporan-preorder-{$request->start_date}.pdf");
    // }

    private function processPreOrderStatuses($preorders)
    {
        $customerData = Customer::withTrashed()->get(['pre_order_id', 'deleted_at']);
        $activeIds = $customerData->whereNull('deleted_at')->pluck('pre_order_id')->toArray();
        $stoppedIds = $customerData->whereNotNull('deleted_at')->pluck('pre_order_id')->toArray();

        return $preorders->map(function ($po) use ($activeIds, $stoppedIds) {
            if (in_array($po->id, $activeIds)) {
                $po->po_status = 'Aktif';
            } elseif (in_array($po->id, $stoppedIds)) {
                $po->po_status = 'Berhenti';
            } else {
                $po->po_status = 'Pending';
            }
            return $po;
        });
    }

    public function laporanPreOrder(Request $request)
    {
        $salesId = $request->input('sales_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = PreOrder::with('sales')->orderByDesc('created_at');
        if ($salesId) $query->where('id_sales', $salesId);
        if ($start) $query->whereDate('created_at', '>=', $start);
        if ($end) $query->whereDate('created_at', '<=', $end);

        $preorders = $this->processPreOrderStatuses($query->get());

        $allSales = User::where('jabatan', 'Sales')->get();

        // Hitung Statistik
        $stats = [
            'total' => $preorders->count(),
            'aktif' => $preorders->where('po_status', 'Aktif')->count(),
            'berhenti' => $preorders->where('po_status', 'Berhenti')->count(),
            'pending' => $preorders->where('po_status', 'Pending')->count(),
        ];

        return view('activitylog.preorder-log', compact('preorders', 'allSales', 'salesId', 'start', 'end', 'stats'));
    }

    public function exportPreOrder(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $query = PreOrder::with('sales')
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->sales_id) $query->where('id_sales', $request->sales_id);

        $preorders = $this->processPreOrderStatuses($query->orderBy('created_at', 'asc')->get());

        // Hitung Statistik untuk PDF
        $stats = [
            'total' => $preorders->count(),
            'aktif' => $preorders->where('po_status', 'Aktif')->count(),
            'berhenti' => $preorders->where('po_status', 'Berhenti')->count(),
            'pending' => $preorders->where('po_status', 'Pending')->count(),
        ];

        $pdf = PDF::loadView('activitylog.export-preorder', [
            'preorders' => $preorders,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'sales' => User::find($request->sales_id),
            'stats' => $stats
        ]);

        return $pdf->download("laporan-preorder-{$request->start_date}.pdf");
    }

    // public function laporanPreOrder(Request $request)
    // {
    //     $salesId = $request->input('sales_id');
    //     $start = $request->input('start_date');
    //     $end = $request->input('end_date');

    //     $query = PreOrder::with('sales')->orderByDesc('created_at');

    //     if ($salesId) $query->where('id_sales', $salesId);
    //     if ($start) $query->whereDate('created_at', '>=', $start);
    //     if ($end) $query->whereDate('created_at', '<=', $end);

    //     $preorders = $query->get();

    //     // Ambil data customer untuk pengecekan status (Aktif/Berhenti)
    //     $customerData = Customer::withTrashed()->get(['pre_order_id', 'deleted_at']);
    //     $activeIds = $customerData->whereNull('deleted_at')->pluck('pre_order_id')->toArray();
    //     $stoppedIds = $customerData->whereNotNull('deleted_at')->pluck('pre_order_id')->toArray();

    //     // Map status ke koleksi preorder
    //     $preorders = $preorders->map(function ($po) use ($activeIds, $stoppedIds) {
    //         if (in_array($po->id, $activeIds)) {
    //             $po->po_status = 'Aktif';
    //         } elseif (in_array($po->id, $stoppedIds)) {
    //             $po->po_status = 'Berhenti';
    //         } else {
    //             $po->po_status = 'Pending';
    //         }
    //         return $po;
    //     });

    //     $allSales = User::where('jabatan', 'Sales')->get();
    //     $total = $preorders->count();

    //     return view('activitylog.preorder-log', compact('preorders', 'allSales', 'salesId', 'start', 'end', 'total'));
    // }

    // /**
    //  * Export PDF Laporan Pre-Order dengan Status Dinamis
    //  */
    // public function exportPreOrder(Request $request)
    // {
    //     $request->validate([
    //         'start_date' => 'required|date',
    //         'end_date' => 'required|date|after_or_equal:start_date',
    //     ]);

    //     $query = PreOrder::with('sales')
    //         ->whereBetween('created_at', [
    //             Carbon::parse($request->start_date)->startOfDay(),
    //             Carbon::parse($request->end_date)->endOfDay()
    //         ]);

    //     if ($request->sales_id) $query->where('id_sales', $request->sales_id);

    //     $preorders = $query->orderBy('created_at', 'asc')->get();

    //     // Ambil data customer untuk pengecekan status
    //     $customerData = Customer::withTrashed()->get(['pre_order_id', 'deleted_at']);
    //     $activeIds = $customerData->whereNull('deleted_at')->pluck('pre_order_id')->toArray();
    //     $stoppedIds = $customerData->whereNotNull('deleted_at')->pluck('pre_order_id')->toArray();

    //     $preorders = $preorders->map(function ($po) use ($activeIds, $stoppedIds) {
    //         if (in_array($po->id, $activeIds)) {
    //             $po->po_status = 'Aktif';
    //         } elseif (in_array($po->id, $stoppedIds)) {
    //             $po->po_status = 'Berhenti';
    //         } else {
    //             $po->po_status = 'Pending';
    //         }
    //         return $po;
    //     });

    //     $pdf = PDF::loadView('activitylog.export-preorder', [
    //         'preorders' => $preorders,
    //         'start' => $request->start_date,
    //         'end' => $request->end_date,
    //         'sales' => User::find($request->sales_id),
    //         'total' => $preorders->count()
    //     ]);

    //     return $pdf->download("laporan-preorder-{$request->start_date}.pdf");
    // }

    public function laporanMaintenanceExpired(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = ActivityLog::with('user')
            ->where('activity_type', 'maintenance_expired')
            ->orderByDesc('logged_at');

        if ($teknisiId) $query->where('user_id', $teknisiId);
        if ($start) $query->whereDate('logged_at', '>=', $start);
        if ($end) $query->whereDate('logged_at', '<=', $end);

        // Ambil data dan pastikan kolom 'data' diproses sebagai array
        $logs = $query->get()->map(function ($log) {
            if (is_string($log->data)) {
                $log->data = json_decode($log->data, true) ?: [];
            }
            return $log;
        });

        $teknisis = User::where('jabatan', 'Teknisi')->get();

        return view('activitylog.maintenance-expired-log', compact('logs', 'teknisis', 'teknisiId', 'start', 'end'));
    }

    public function exportMaintenanceExpired(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $query = ActivityLog::with('user')
            ->where('activity_type', 'maintenance_expired')
            ->whereBetween('logged_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->teknisi_id) $query->where('user_id', $request->teknisi_id);

        $logs = $query->get()->map(function ($log) {
            if (is_string($log->data)) {
                $log->data = json_decode($log->data, true) ?: [];
            }
            return $log;
        });

        $pdf = PDF::loadView('activitylog.export-maintenance-expired', [
            'logs' => $logs,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id)
        ]);

        return $pdf->download("laporan-maintenance-expired-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    public function laporanDismantle(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = Dismantle::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai');

        // Filter berdasarkan Teknisi Eksekutor
        if ($teknisiId) {
            $query->where('id_teknisi_eksekutor', $teknisiId);
        }

        // Filter berdasarkan Periode Selesai
        if ($start) $query->whereDate('completed_at', '>=', $start);
        if ($end) $query->whereDate('completed_at', '<=', $end);

        $dismantles = $query->orderBy('completed_at', 'desc')->get();
        $teknisis = User::where('jabatan', 'Teknisi')->get();

        // --- STATISTIK ---
        $stats = [
            'total_filtered' => $dismantles->count(),
            'total_all' => Dismantle::where('status', 'Selesai')->count(),
            'this_month' => Dismantle::where('status', 'Selesai')->whereMonth('completed_at', Carbon::now()->month)->whereYear('completed_at', Carbon::now()->year)->count(),
            'today' => Dismantle::where('status', 'Selesai')->whereDate('completed_at', Carbon::today())->count(),
        ];

        return view('activitylog.dismantle-log', compact('dismantles', 'teknisis', 'teknisiId', 'start', 'end', 'stats'));
    }

    /**
     * Export PDF Laporan Dismantle
     */
    public function exportDismantle(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date'
        ]);

        $query = Dismantle::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai')
            ->whereBetween('completed_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->teknisi_id) {
            $query->where('id_teknisi_eksekutor', $request->teknisi_id);
        }

        $dismantles = $query->orderBy('completed_at', 'asc')->get();

        $pdf = PDF::loadView('activitylog.export-dismantle', [
            'dismantles' => $dismantles,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $dismantles->count()
        ]);

        return $pdf->download("laporan-dismantle-{$request->start_date}-ke-{$request->end_date}.pdf");
    }
}

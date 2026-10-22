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
    public function index(Request $request)
    {
        // Hanya Administrator yang bisa mengakses
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if ($request->ajax()) {
            $query = ActivityLog::with('user')->orderBy('logged_at', 'desc');
            
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->editColumn('logged_at', function ($log) {
                    $date = \Carbon\Carbon::parse($log->logged_at)->format('d/m/Y');
                    $time = \Carbon\Carbon::parse($log->logged_at)->format('H:i:s');
                    return '<div class="text-slate-900 font-bold">' . $date . '</div>
                            <div class="text-[10px] text-slate-400 uppercase tracking-wider">' . $time . '</div>';
                })
                ->addColumn('user', function ($log) {
                    $nama = $log->user->nama_lengkap ?? 'Sistem';
                    $jabatan = $log->user->jabatan ?? 'ROOT';
                    return '<div style="font-weight: 700; color: #0f172a;">' . htmlspecialchars($nama) . '</div>
                            <div style="font-size: 10px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">' . htmlspecialchars($jabatan) . '</div>';
                })
                ->editColumn('activity_type', function ($log) {
                    $bg = '#f1f5f9'; $color = '#64748b';
                    $type = strtolower($log->activity_type);
                    if(str_contains($type, 'create')) { $bg = '#f0fdf4'; $color = '#16a34a'; }
                    elseif(str_contains($type, 'update')) { $bg = '#fffbeb'; $color = '#d97706'; }
                    elseif(str_contains($type, 'delete')) { $bg = '#fef2f2'; $color = '#dc2626'; }
                    elseif(str_contains($type, 'aktivasi')) { $bg = '#f0f9ff'; $color = '#0ea5e9'; }
                    
                    $label = str_replace('_', ' ', $log->activity_type);
                    return '<span class="badge" style="background: ' . $bg . '; color: ' . $color . '; padding: 4px 10px; border-radius: 6px; font-size: 9px; font-weight: 800; text-transform: uppercase;">' . htmlspecialchars($label) . '</span>';
                })
                ->editColumn('description', function ($log) {
                    return '<div style="font-size: 12px; color: #475569; font-weight: 500; line-height: 1.5;">' . htmlspecialchars($log->description) . '</div>';
                })
                ->rawColumns(['logged_at', 'user', 'activity_type', 'description'])
                ->make(true);
        }

        $totalLogs = ActivityLog::count();
        $logsToday = ActivityLog::whereDate('logged_at', Carbon::today())->count();
        $logsPreOrder = ActivityLog::where('activity_type', 'LIKE', '%preorder%')->count();
        $logsAktivasi = ActivityLog::where('activity_type', 'LIKE', '%aktivasi%')->count();

        return view('activitylog.activity-log', compact(
            'totalLogs',
            'logsToday',
            'logsPreOrder',
            'logsAktivasi'
        ));
    }

    public function getNotifications()
    {
        $types = ['preorder_create', 'aktivasi_pelanggan', 'maintenance_complete', 'eksekusi_dismantle', 'tambah_perbantuan'];

        $notifications = ActivityLog::with('user')
            ->whereIn('activity_type', $types)
            ->orderByDesc('logged_at')
            ->limit(10)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'user' => $log->user->nama_lengkap ?? 'System',
                    'type' => $log->activity_type,
                    'description' => $log->description,
                    'time' => Carbon::parse($log->logged_at)->diffForHumans(),
                    'icon' => $this->getIconForType($log->activity_type),
                    'color' => $this->getColorForType($log->activity_type)
                ];
            });

        return response()->json($notifications);
    }

    private function getIconForType($type)
    {
        return match ($type) {
            'preorder_create' => 'fa-cart-plus',
            'aktivasi_pelanggan' => 'fa-user-check',
            'maintenance_complete' => 'fa-screwdriver-wrench',
            'eksekusi_dismantle' => 'fa-house-circle-xmark',
            'tambah_perbantuan' => 'fa-handshake-angle',
            default => 'fa-info-circle'
        };
    }

    private function getColorForType($type)
    {
        return match ($type) {
            'preorder_create' => 'text-blue-500',
            'aktivasi_pelanggan' => 'text-emerald-500',
            'maintenance_complete' => 'text-indigo-500',
            'eksekusi_dismantle' => 'text-rose-500',
            'tambah_perbantuan' => 'text-amber-500',
            default => 'text-slate-500'
        };
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

    public function laporanAktivasi(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Check if user manually applied a filter
        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales']);

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi', $teknisiId)
                    ->orWhereJsonContains('partner_ids', (string)$teknisiId);
            });
        }

        if ($isFiltered) {
            if ($start) $query->whereDate('created_at', '>=', $start);
            if ($end) $query->whereDate('created_at', '<=', $end);
            $customers = $query->orderBy('created_at', 'desc')->get();
        } else {
            $customers = collect();
        }

        $teknisis = User::where('jabatan', 'Teknisi')->get();

        $stats = [
            'total_filtered' => $customers->count(),
            'total_all' => Customer::withTrashed()->count(),
            'this_month' => Customer::withTrashed()->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'today' => Customer::withTrashed()->whereDate('created_at', Carbon::today())->count(),
        ];

        return view('activitylog.aktivasi-log', compact('customers', 'teknisis', 'teknisiId', 'start', 'end', 'stats', 'isFiltered'));
    }

    public function exportAktivasi(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

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

        $pdf = PDF::loadView('customers.export', [
            'customers' => $customers,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $customers->count()
        ]);

        return $pdf->download("laporan-aktivasi-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    public function laporanAktivasiSales(Request $request)
    {
        $salesId = $request->input('sales_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Check if user manually applied a filter
        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales']);

        if ($salesId) {
            $query->where('id_sales', $salesId);
        }

        if ($isFiltered) {
            if ($start) $query->whereDate('created_at', '>=', $start);
            if ($end) $query->whereDate('created_at', '<=', $end);
            $customers = $query->orderBy('created_at', 'desc')->get();
        } else {
            $customers = collect();
        }

        $allSales = User::where('jabatan', 'Sales')->get();

        $stats = [
            'total_filtered' => $customers->count(),
            'total_all' => Customer::withTrashed()->count(),
            'this_month' => Customer::withTrashed()->whereMonth('created_at', Carbon::now()->month)->whereYear('created_at', Carbon::now()->year)->count(),
            'today' => Customer::withTrashed()->whereDate('created_at', Carbon::today())->count(),
        ];

        return view('activitylog.aktivasi-sales-log', compact('customers', 'allSales', 'salesId', 'start', 'end', 'stats', 'isFiltered'));
    }

    public function exportAktivasiSales(Request $request)
    {
        $request->validate(['start_date' => 'required|date', 'end_date' => 'required|date']);

        $query = Customer::withTrashed()->with(['teknisiUtama', 'sales'])
            ->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay()
            ]);

        if ($request->sales_id) {
            $query->where('id_sales', $request->sales_id);
        }

        $customers = $query->orderBy('created_at', 'asc')->get();

        $pdf = PDF::loadView('customers.export-sales', [
            'customers' => $customers,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'sales' => User::find($request->sales_id),
            'total' => $customers->count()
        ]);

        return $pdf->download("laporan-aktivasi-sales-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

    public function laporanMaintenance(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Check if user manually applied a filter
        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        $query = Maintenance::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai');

        if ($teknisiId) {
            $query->where(function ($q) use ($teknisiId) {
                $q->where('id_teknisi_eksekutor', $teknisiId)
                    ->orWhereJsonContains('partner_ids', (string)$teknisiId);
            });
        }

        if ($isFiltered) {
            if ($start) $query->whereDate('completed_at', '>=', $start);
            if ($end) $query->whereDate('completed_at', '<=', $end);
            $maintenances = $query->orderBy('completed_at', 'desc')->get();
        } else {
            $maintenances = collect();
        }

        $teknisis = User::where('jabatan', 'Teknisi')->get();

        $stats = [
            'total_filtered' => $maintenances->count(),
            'total_all' => Maintenance::where('status', 'Selesai')->count(),
            'this_month' => Maintenance::where('status', 'Selesai')->whereMonth('completed_at', Carbon::now()->month)->whereYear('completed_at', Carbon::now()->year)->count(),
            'today' => Maintenance::where('status', 'Selesai')->whereDate('completed_at', Carbon::today())->count(),
        ];

        return view('activitylog.maintenance-log', compact('maintenances', 'teknisis', 'teknisiId', 'start', 'end', 'stats', 'isFiltered'));
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
        $pdf = PDF::loadView('maintenances.export', [
            'maintenances' => $maintenances,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $maintenances->count()
        ]);

        return $pdf->download("laporan-pemeliharaan-{$request->start_date}-ke-{$request->end_date}.pdf");
    }

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

        // Check if user manually applied a filter
        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        if ($isFiltered) {
            $query = PreOrder::with('sales')->orderByDesc('created_at');
            if ($salesId) $query->where('id_sales', $salesId);
            if ($start) $query->whereDate('created_at', '>=', $start);
            if ($end) $query->whereDate('created_at', '<=', $end);
            $preorders = $this->processPreOrderStatuses($query->get());
        } else {
            $preorders = collect();
        }

        $allSales = User::where('jabatan', 'Sales')->get();

        $stats = [
            'total' => $preorders->count(),
            'aktif' => $preorders->where('po_status', 'Aktif')->count(),
            'berhenti' => $preorders->where('po_status', 'Berhenti')->count(),
            'pending' => $preorders->where('po_status', 'Pending')->count(),
        ];

        return view('activitylog.preorder-log', compact('preorders', 'allSales', 'salesId', 'start', 'end', 'stats', 'isFiltered'));
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

        $stats = [
            'total' => $preorders->count(),
            'aktif' => $preorders->where('po_status', 'Aktif')->count(),
            'berhenti' => $preorders->where('po_status', 'Berhenti')->count(),
            'pending' => $preorders->where('po_status', 'Pending')->count(),
        ];

        $pdf = PDF::loadView('preorders.export', [
            'preorders' => $preorders,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'sales' => User::find($request->sales_id),
            'stats' => $stats
        ]);

        return $pdf->download("laporan-preorder-{$request->start_date}.pdf");
    }

    public function laporanDismantle(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        // Check if user manually applied a filter
        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        $query = Dismantle::with(['customer', 'teknisiEksekutor'])
            ->where('status', 'Selesai');

        if ($teknisiId) {
            $query->where('id_teknisi_eksekutor', $teknisiId);
        }

        if ($isFiltered) {
            if ($start) $query->whereDate('completed_at', '>=', $start);
            if ($end) $query->whereDate('completed_at', '<=', $end);
            $dismantles = $query->orderBy('completed_at', 'desc')->get();
        } else {
            $dismantles = collect();
        }

        $teknisis = User::where('jabatan', 'Teknisi')->get();

        $stats = [
            'total_filtered' => $dismantles->count(),
            'total_all' => Dismantle::where('status', 'Selesai')->count(),
            'this_month' => Dismantle::where('status', 'Selesai')->whereMonth('completed_at', Carbon::now()->month)->whereYear('completed_at', Carbon::now()->year)->count(),
            'today' => Dismantle::where('status', 'Selesai')->whereDate('completed_at', Carbon::today())->count(),
        ];

        return view('activitylog.dismantle-log', compact('dismantles', 'teknisis', 'teknisiId', 'start', 'end', 'stats', 'isFiltered'));
    }

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

        $pdf = PDF::loadView('dismantles.export', [
            'dismantles' => $dismantles,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
            'total' => $dismantles->count()
        ]);

        return $pdf->download("laporan-pencabutan-{$request->start_date}-ke-{$request->end_date}.pdf");
    }
}

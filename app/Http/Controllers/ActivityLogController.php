<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ActivityLog;
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
            ->take(20)
            ->get();

        return view('activitylog.activity-log', compact('logs'));
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

    public function laporanExpired(Request $request)
    {
        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $query = ActivityLog::with('user')
            ->where('activity_type', 'delegasi_expired')
            ->orderByDesc('logged_at');

        if ($teknisiId) {
            $query->where('user_id', $teknisiId);
        }

        if ($start) {
            $query->whereDate('logged_at', '>=', $start);
        }

        if ($end) {
            $query->whereDate('logged_at', '<=', $end);
        }

        $logs = $query->get();

        $teknisis = User::where('jabatan', 'Teknisi')->get();

        return view('activitylog.expired-log', compact('logs', 'teknisis', 'teknisiId', 'start', 'end'));
    }

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
                Carbon::parse($request->end_date)->endOfDay(),
            ])
            ->orderBy('logged_at', 'desc');

        if ($request->has('teknisi_id') && $request->teknisi_id) {
            $query->where('user_id', $request->teknisi_id);
        }

        $logs = $query->get();

        $pdf = PDF::loadView('activitylog.export-expired', [
            'logs' => $logs,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'teknisi' => User::find($request->teknisi_id),
        ]);

        return $pdf->download("laporan-expired-po-{$request->start_date}_to_{$request->end_date}.pdf");
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SupportingActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PDF;

class SupportingActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = SupportingActivity::with(['mainTechnician', 'createdBy']);

        // Filters
        if ($request->filled('start_date')) {
            $query->whereDate('activity_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('activity_date', '<=', $request->end_date);
        }
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $activities = $query->orderBy('activity_date', 'desc')->paginate(15);
        $technicians = User::where('jabatan', 'Teknisi')->get();

        // Calculate Stats
        $stats = $this->calculateStats($request);

        return view('supporting.index', compact('activities', 'technicians', 'stats'));
    }

    private function calculateStats(Request $request)
    {
        $all = SupportingActivity::all();
        $scoreboard = [];

        foreach ($all as $act) {
            // Main Tech
            $mid = $act->main_technician_id;
            $scoreboard[$mid] = ($scoreboard[$mid] ?? 0) + 1;

            // Partners
            if ($act->partner_ids) {
                foreach ($act->partner_ids as $pid) {
                    $scoreboard[$pid] = ($scoreboard[$pid] ?? 0) + 1;
                }
            }
        }

        // Map to names
        $techs = User::whereIn('id', array_keys($scoreboard))->get();
        $mappedScoreboard = $techs->map(function($t) use ($scoreboard) {
            return [
                'name' => $t->nama_lengkap,
                'count' => $scoreboard[$t->id]
            ];
        })->sortByDesc('count')->values();

        return [
            'total' => $all->count(),
            'scoreboard' => $mappedScoreboard
        ];
    }
}

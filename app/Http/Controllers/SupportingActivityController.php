<?php

namespace App\Http\Controllers;

use App\Models\SupportingActivity;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PDF;

class SupportingActivityController extends Controller
{
    private function checkAccess()
    {
        if (auth()->check() && auth()->user()->jabatan === 'Sales') {
            abort(403, 'Akses ditolak.');
        }
    }

    public function index(Request $request)
    {
        $this->checkAccess();
        $user = auth()->user();
        $query = SupportingActivity::with(['mainTechnician', 'createdBy']);

        if ($user->jabatan === 'Teknisi') {
            $query->where(function($q) use ($user) {
                $q->where('main_technician_id', $user->id)
                  ->orWhereJsonContains('partner_ids', (string)$user->id);
            });
        }

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
        if ($request->filled('teknisi_id')) {
            $query->where(function($q) use ($request) {
                $q->where('main_technician_id', $request->teknisi_id)
                  ->orWhereJsonContains('partner_ids', (string)$request->teknisi_id);
            });
        }

        $activities = $query->orderBy('activity_date', 'desc')->get();
        $technicians = User::where('jabatan', 'Teknisi')->get();


        // Calculate Stats
        $stats = $this->calculateStats($query);

        return view('supporting.index', compact('activities', 'technicians', 'stats'));
    }

    public function store(Request $request)
    {
        $this->checkAccess();
        $validated = $request->validate([
            'activity_type' => 'required|in:PO,Pemeliharaan,Buat ODP,Aktivasi Jalur',
            'activity_date' => 'required|date',
            'main_technician_id' => 'required|exists:users,id',
            'partner_ids' => 'nullable|array',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('supporting_activities');
        }

        $validated['created_by'] = auth()->id();
        $supporting = SupportingActivity::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'tambah_perbantuan',
            'description' => "Menambahkan catatan perbantuan baru: {$supporting->activity_type}",
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Data perbantuan berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $this->checkAccess();
        $activity = SupportingActivity::findOrFail($id);

        $validated = $request->validate([
            'activity_type' => 'required|in:PO,Pemeliharaan,Buat ODP,Aktivasi Jalur',
            'activity_date' => 'required|date',
            'main_technician_id' => 'required|exists:users,id',
            'partner_ids' => 'nullable|array',
            'description' => 'nullable|string',
            'photo' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($activity->photo) {
                Storage::delete($activity->photo);
            }
            $validated['photo'] = $request->file('photo')->store('supporting_activities');
        }

        $activity->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'update_perbantuan',
            'description' => "Memperbarui catatan perbantuan: {$activity->activity_type}",
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Data perbantuan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $this->checkAccess();
        $activity = SupportingActivity::findOrFail($id);
        $type = $activity->activity_type;

        // Delete photo from storage if exists
        if ($activity->photo) {
            Storage::delete($activity->photo);
        }

        $activity->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'hapus_perbantuan',
            'description' => "Menghapus catatan perbantuan: $type",
            'logged_at' => now(),
        ]);

        return back()->with('success', 'Data perbantuan berhasil dihapus.');
    }

    public function export(Request $request)
    {
        $this->checkAccess();
        $user = auth()->user();
        
        $query = SupportingActivity::with(['mainTechnician', 'createdBy']);

        if ($user->jabatan === 'Teknisi') {
            $query->where(function($q) use ($user) {
                $q->where('main_technician_id', $user->id)
                  ->orWhereJsonContains('partner_ids', (string)$user->id);
            });
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('activity_date', [$request->start_date, $request->end_date]);
        }
        if ($request->filled('activity_type')) {
            $query->where('activity_type', $request->activity_type);
        }
        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }
        if ($request->filled('teknisi_id')) {
            $query->where(function($q) use ($request) {
                $q->where('main_technician_id', $request->teknisi_id)
                  ->orWhereJsonContains('partner_ids', (string)$request->teknisi_id);
            });
        }
        
        $activities = $query->orderBy('activity_date', 'asc')->get();

        $pdf = PDF::loadView('supporting.export', compact('activities'));
        
        $fileName = 'laporan-perbantuan';
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $fileName .= "-{$request->start_date}-ke-{$request->end_date}";
        }
        $fileName .= '.pdf';

        return $pdf->download($fileName);
    }

    private function calculateStats($baseQuery)
    {
        // Clone the base query to prevent modifying the original instance used for listing
        $all = (clone $baseQuery)->get();

        $now = Carbon::now();
        $thisMonth = (clone $baseQuery)->whereMonth('activity_date', $now->month)
            ->whereYear('activity_date', $now->year)->count();
        $lastMonth = (clone $baseQuery)->whereMonth('activity_date', $now->subMonth()->month)
            ->whereYear('activity_date', $now->year)->count();

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
        $mappedScoreboard = $techs->map(function ($t) use ($scoreboard) {
            return [
                'name' => $t->nama_lengkap,
                'count' => $scoreboard[$t->id]
            ];
        })->sortByDesc('count')->values();

        return [
            'total' => $all->count(),
            'this_month' => $thisMonth,
            'last_month' => $lastMonth,
            'scoreboard' => $mappedScoreboard
        ];
    }
}

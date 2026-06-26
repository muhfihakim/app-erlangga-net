<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportingActivity;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        $query = SupportingActivity::with(['mainTechnician:id,nama_lengkap', 'createdBy:id,nama_lengkap']);

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

        $activities = $query->orderBy('activity_date', 'desc')->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $activities
        ]);
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
            $validated['photo'] = $request->file('photo')->store('supporting_activities', 'public');
        }

        $validated['created_by'] = auth()->id();
        $supporting = SupportingActivity::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'tambah_perbantuan',
            'description' => "Menambahkan catatan perbantuan baru: {$supporting->activity_type} via Mobile App",
            'logged_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data perbantuan berhasil disimpan.',
            'data' => $supporting
        ]);
    }

    public function show($id)
    {
        $this->checkAccess();
        $activity = SupportingActivity::with(['mainTechnician:id,nama_lengkap', 'createdBy:id,nama_lengkap'])->findOrFail($id);
        
        return response()->json([
            'status' => 'success',
            'data' => $activity
        ]);
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
            if ($activity->photo) {
                Storage::disk('public')->delete($activity->photo);
            }
            $validated['photo'] = $request->file('photo')->store('supporting_activities', 'public');
        }

        $activity->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'update_perbantuan',
            'description' => "Memperbarui catatan perbantuan: {$activity->activity_type} via Mobile App",
            'logged_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data perbantuan berhasil diperbarui.',
            'data' => $activity
        ]);
    }

    public function destroy($id)
    {
        $this->checkAccess();
        $activity = SupportingActivity::findOrFail($id);
        $type = $activity->activity_type;

        if ($activity->photo) {
            Storage::disk('public')->delete($activity->photo);
        }

        $activity->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'hapus_perbantuan',
            'description' => "Menghapus catatan perbantuan: $type via Mobile App",
            'logged_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Data perbantuan berhasil dihapus.'
        ]);
    }
}

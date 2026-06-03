<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Odp;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OdpController extends Controller
{
    public function index()
    {
        $odps = Odp::latest()->get();
        $stats = [
            'total' => $odps->count(),
            'gpon' => $odps->where('jenis_odp', 'GPON')->count(),
            'epon' => $odps->where('jenis_odp', 'EPON')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'data' => $odps
        ]);
    }

    public function store(Request $request)
    {
        if (auth()->user()->jabatan === 'Sales') return response()->json(['message' => 'Forbidden'], 403);

        $request->validate([
            'jenis_odp' => 'required|in:GPON,EPON',
            'lokasi_odp' => 'required|string',
            'titik_koordinat' => 'required|string',
            'foto_odp' => 'nullable|image|max:20480'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_odp')) {
            $fotoPath = $request->file('foto_odp')->store('odp-images');
        }

        $odp = Odp::create([
            'jenis_odp' => $request->jenis_odp,
            'lokasi_odp' => $request->lokasi_odp,
            'titik_koordinat' => $request->titik_koordinat,
            'keterangan' => $request->keterangan,
            'foto_odp' => $fotoPath,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'tambah_odp',
            'description' => "Menambahkan ODP baru: {$odp->lokasi_odp}",
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'ODP berhasil ditambahkan', 'data' => $odp], 201);
    }

    public function update(Request $request, $id)
    {
        if (auth()->user()->jabatan === 'Sales') return response()->json(['message' => 'Forbidden'], 403);
        $odp = Odp::findOrFail($id);

        $request->validate([
            'jenis_odp' => 'required|in:GPON,EPON',
            'lokasi_odp' => 'required|string',
            'titik_koordinat' => 'required|string',
            'foto_odp' => 'nullable|image|max:20480'
        ]);

        if ($request->hasFile('foto_odp')) {
            if ($odp->foto_odp) Storage::delete($odp->foto_odp);
            $odp->foto_odp = $request->file('foto_odp')->store('odp-images');
        }

        $odp->update($request->except(['foto_odp']) + ['foto_odp' => $odp->foto_odp]);

        return response()->json(['message' => 'ODP berhasil diperbarui', 'data' => $odp]);
    }

    public function destroy($id)
    {
        if (auth()->user()->jabatan === 'Sales') return response()->json(['message' => 'Forbidden'], 403);
        $odp = Odp::findOrFail($id);
        if ($odp->foto_odp) Storage::delete($odp->foto_odp);
        $odp->delete();

        return response()->json(['message' => 'ODP berhasil dihapus']);
    }
}

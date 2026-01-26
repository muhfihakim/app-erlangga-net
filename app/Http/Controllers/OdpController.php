<?php

namespace App\Http\Controllers;

use App\Models\Odp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OdpController extends Controller
{
    public function index()
    {
        $odps = Odp::latest()->get();

        // Hitung Statistik Sederhana
        $stats = [
            'total' => $odps->count(),
            'gpon' => $odps->where('jenis_odp', 'GPON')->count(),
            'epon' => $odps->where('jenis_odp', 'EPON')->count(),
        ];

        return view('odp.index', compact('odps', 'stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            // Validasi nama_odp dihapus
            'jenis_odp' => 'required|in:GPON,EPON',
            'lokasi_odp' => 'required|string',
            'titik_koordinat' => 'required|string',
            // Validasi kapasitas & terisi dihapus
            'foto_odp' => 'nullable|image|max:20480'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto_odp')) {
            $fotoPath = $request->file('foto_odp')->store('odp-images', 'public');
        }

        Odp::create([
            'jenis_odp' => $request->jenis_odp,
            'lokasi_odp' => $request->lokasi_odp,
            'titik_koordinat' => $request->titik_koordinat,
            'keterangan' => $request->keterangan,
            'foto_odp' => $fotoPath,
        ]);

        return redirect()->route('odp.index')->with('success', 'Data ODP berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $odp = Odp::findOrFail($id);

        $request->validate([
            // Validasi nama_odp dihapus
            'jenis_odp' => 'required|in:GPON,EPON',
            'lokasi_odp' => 'required|string',
            'titik_koordinat' => 'required|string',
            // Validasi kapasitas & terisi dihapus
            'foto_odp' => 'nullable|image|max:20480'
        ]);

        if ($request->hasFile('foto_odp')) {
            if ($odp->foto_odp && Storage::disk('public')->exists($odp->foto_odp)) {
                Storage::disk('public')->delete($odp->foto_odp);
            }
            $odp->foto_odp = $request->file('foto_odp')->store('odp-images', 'public');
        }

        $odp->update($request->except(['foto_odp']) + ['foto_odp' => $odp->foto_odp]);

        return redirect()->route('odp.index')->with('success', 'Data ODP berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $odp = Odp::findOrFail($id);

        if ($odp->foto_odp && Storage::disk('public')->exists($odp->foto_odp)) {
            Storage::disk('public')->delete($odp->foto_odp);
        }

        $odp->delete();

        return redirect()->route('odp.index')->with('success', 'Data ODP berhasil dihapus.');
    }
}

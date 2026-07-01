<?php

namespace App\Http\Controllers;

use App\Models\Kasbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KasbonController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403, "Akses ditolak");

        $teknisiId = $request->input('teknisi_id');
        $start = $request->input('start_date');
        $end = $request->input('end_date');

        $isFiltered = $request->filled('start_date') && $request->filled('end_date');

        if (!$start && !$end) {
            $endOfCurrentCycle = \Carbon\Carbon::now()->isFriday() ? \Carbon\Carbon::now() : \Carbon\Carbon::now()->next(\Carbon\Carbon::FRIDAY);
            $startOfPreviousCycle = $endOfCurrentCycle->copy()->subDays(13); 
            
            $start = $startOfPreviousCycle->format('Y-m-d');
            $end = $endOfCurrentCycle->format('Y-m-d');
        }

        $kasbonsQuery = Kasbon::with('teknisi');
        if ($teknisiId) {
            $kasbonsQuery->where('teknisi_id', $teknisiId);
        }
        if ($start) $kasbonsQuery->whereDate('tanggal', '>=', $start);
        if ($end) $kasbonsQuery->whereDate('tanggal', '<=', $end);
        
        $kasbons = $kasbonsQuery->orderBy('tanggal', 'desc')->get();
        $totalKasbon = $kasbons->sum('nominal');
        $teknisis = \App\Models\User::where('jabatan', 'Teknisi')->get();

        return view('finance.kasbon', compact('kasbons', 'totalKasbon', 'teknisis', 'teknisiId', 'start', 'end', 'isFiltered'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403, "Akses ditolak");

        $request->validate([
            'teknisi_id' => 'required|exists:users,id',
            'nominal' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
            'bukti' => 'nullable|image|mimes:jpeg,png,jpg|max:5120',
            'keterangan' => 'nullable|string'
        ]);

        $data = $request->except('bukti');

        if ($request->hasFile('bukti')) {
            $data['bukti'] = $request->file('bukti')->store('kasbons');
        }

        Kasbon::create($data);

        return redirect()->back()->with('success', 'Kasbon teknisi berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) abort(403, "Akses ditolak");

        $kasbon = Kasbon::findOrFail($id);
        
        if ($kasbon->bukti) {
            Storage::delete($kasbon->bukti);
        }
        
        $kasbon->delete();

        return redirect()->back()->with('success', 'Kasbon teknisi berhasil dihapus.');
    }
}

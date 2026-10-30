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

        // Total Kasbon Previous Cycle
        $prevStart = \Carbon\Carbon::parse($start)->subDays(14)->format('Y-m-d');
        $prevEnd = \Carbon\Carbon::parse($start)->subDays(1)->format('Y-m-d');
        $kasbonPrevQuery = \App\Models\Kasbon::query();
        if ($teknisiId) $kasbonPrevQuery->where('teknisi_id', $teknisiId);
        $totalKasbonPrevious = $kasbonPrevQuery->whereDate('tanggal', '>=', $prevStart)->whereDate('tanggal', '<=', $prevEnd)->sum('nominal');

        // Total Kasbon This Month
        $kasbonMonthQuery = \App\Models\Kasbon::query();
        if ($teknisiId) $kasbonMonthQuery->where('teknisi_id', $teknisiId);
        $totalKasbonThisMonth = $kasbonMonthQuery->whereMonth('tanggal', \Carbon\Carbon::now()->month)->whereYear('tanggal', \Carbon\Carbon::now()->year)->sum('nominal');

        $teknisis = \App\Models\User::where('jabatan', 'Teknisi')->orderBy('nama_lengkap', 'asc')->get();

        return view('finance.kasbon', compact('kasbons', 'totalKasbon', 'totalKasbonPrevious', 'totalKasbonThisMonth', 'teknisis', 'teknisiId', 'start', 'end', 'isFiltered'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403, "Akses ditolak");

        $request->validate([
            'teknisi_id' => 'required|exists:users,id',
            'nominal' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
            'bukti' => 'nullable|mimes:jpeg,png,jpg,pdf|max:5120',
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

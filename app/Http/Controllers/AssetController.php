<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;

class AssetController extends Controller
{
    public function index(Request $request)
    {
        $assets = Asset::orderBy('name')->get();

        // Statistics
        $totalItems = $assets->count();
        $totalStock = $assets->sum('stock');
        $lowStockCount = $assets->filter(fn($a) => $a->stock <= 5)->count();

        $users = User::whereIn('jabatan', ['Administrator', 'Teknisi'])->orderBy('nama_lengkap')->get();

        return view('assets.index', compact('assets', 'totalItems', 'totalStock', 'lowStockCount', 'users'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        Asset::create($validated);

        return redirect()->back()->with('success', 'Asset berhasil ditambahkan.');
    }

    public function update(Request $request, Asset $asset)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->back()->with('success', 'Asset berhasil diperbarui.');
    }

    public function destroy(Asset $asset)
    {
        if (!auth()->user()->isAdmin()) abort(403);

        if ($asset->transactions()->exists()) {
            return redirect()->back()->with('error', 'Asset tidak bisa dihapus karena memiliki riwayat transaksi.');
        }

        $asset->delete();

        return redirect()->back()->with('success', 'Asset berhasil dihapus.');
    }

    public function transactions(Request $request)
    {
        $query = AssetTransaction::with(['asset', 'user']);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->latest()->get();
        $assets = Asset::orderBy('name')->get();
        $users = User::whereIn('jabatan', ['Administrator', 'Teknisi'])->orderBy('nama_lengkap')->get();

        return view('assets.transactions', compact('transactions', 'assets', 'users'));
    }

    public function storeTransaction(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'type' => 'required|in:Masuk,Keluar',
            'quantity' => 'required|integer|min:1',
            'date' => 'required|date',
            'user_id' => 'nullable|exists:users,id',
            'external_person_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $asset = Asset::findOrFail($request->asset_id);

        // Validation for Outgoing
        if ($request->type === 'Keluar' && $asset->stock < $request->quantity) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi untuk transaksi ini.');
        }

        DB::transaction(function () use ($validated, $asset) {
            AssetTransaction::create($validated);

            if ($validated['type'] === 'Masuk') {
                $asset->increment('stock', $validated['quantity']);
            } else {
                $asset->decrement('stock', $validated['quantity']);
            }

            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity_type' => 'asset_transaction',
                'description' => "Mencatat transaksi " . strtolower($validated['type']) . " aset: " . $asset->name . " sebanyak " . $validated['quantity'] . " " . $asset->unit,
                'logged_at' => now()
            ]);
        });

        return redirect()->back()->with('success', 'Transaksi aset berhasil dicatat.');
    }

    public function export(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
        ]);

        $query = AssetTransaction::with(['asset', 'user']);

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $query->whereBetween('date', [$request->start_date, $request->end_date]);

        $transactions = $query->orderBy('date', 'asc')->get();

        $pdf = PDF::loadView('assets.export', [
            'transactions' => $transactions,
            'start' => $request->start_date,
            'end' => $request->end_date,
            'type' => $request->type
        ]);

        return $pdf->download("laporan-mutasi-aset-{$request->start_date}-sd-{$request->end_date}.pdf");
    }
}

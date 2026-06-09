<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetTransaction;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function index()
    {
        $assets = Asset::orderBy('name')->get();
        return response()->json($assets);
    }

    public function transactions(Request $request)
    {
        $user = $request->user();
        $query = AssetTransaction::with(['asset', 'user']);

        if ($user->jabatan === 'Teknisi') {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        $asset = Asset::with('transactions.user')->findOrFail($id);
        return response()->json($asset);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $asset = Asset::create($validated);

        return response()->json(['message' => 'Asset berhasil ditambahkan', 'data' => $asset], 201);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $asset = Asset::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:255',
            'unit' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $asset->update($validated);

        return response()->json(['message' => 'Asset berhasil diperbarui', 'data' => $asset]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $asset = Asset::findOrFail($id);

        if ($asset->transactions()->exists()) {
            return response()->json(['message' => 'Asset tidak bisa dihapus karena memiliki riwayat transaksi'], 422);
        }

        $asset->delete();

        return response()->json(['message' => 'Asset berhasil dihapus']);
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

        if ($request->type === 'Keluar' && $asset->stock < $request->quantity) {
            return response()->json(['message' => 'Stok tidak mencukupi untuk transaksi ini'], 422);
        }

        $transaction = DB::transaction(function () use ($validated, $asset) {
            $tx = AssetTransaction::create($validated);

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

            return $tx;
        });

        return response()->json(['message' => 'Transaksi aset berhasil dicatat', 'data' => $transaction], 201);
    }
}

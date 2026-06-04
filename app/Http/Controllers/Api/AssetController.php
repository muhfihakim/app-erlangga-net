<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\AssetTransaction;
use Illuminate\Http\Request;

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

        return response()->json($query->latest()->paginate(20));
    }

    public function show($id)
    {
        $asset = Asset::with('transactions.user')->findOrFail($id);
        return response()->json($asset);
    }
}

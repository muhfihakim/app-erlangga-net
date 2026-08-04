<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::latest()->get();
        return view('paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket',
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        Paket::create($validated);
        return redirect()->back()->with('success', 'Paket berhasil ditambahkan.');
    }

    public function update(Request $request, Paket $paket)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket,' . $paket->id,
            'harga' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        $paket->update($validated);
        return redirect()->back()->with('success', 'Paket berhasil diperbarui.');
    }

    public function destroy(Paket $paket)
    {
        $paket->delete();
        return redirect()->back()->with('success', 'Paket berhasil dihapus.');
    }
}

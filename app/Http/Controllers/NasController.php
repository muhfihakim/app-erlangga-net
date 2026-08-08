<?php

namespace App\Http\Controllers;

use App\Models\Nas;
use Illuminate\Http\Request;

class NasController extends Controller
{
    public function index()
    {
        $routers = Nas::latest('id')->get();
        return view('nas.index', compact('routers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'shortname' => 'required|string|max:32',
            'nasname' => 'required|string|max:128',
            'secret' => 'required|string|max:60',
            'description' => 'nullable|string|max:200',
        ]);

        $validated['type'] = 'mikrotik'; // default type for router

        Nas::create($validated);
        return redirect()->back()->with('success', 'Router/NAS berhasil ditambahkan.');
    }

    public function update(Request $request, Nas $na) // typical laravel route binding for 'nas' might resolve to $na
    {
        $validated = $request->validate([
            'shortname' => 'required|string|max:32',
            'nasname' => 'required|string|max:128',
            'secret' => 'required|string|max:60',
            'description' => 'nullable|string|max:200',
        ]);

        $na->update($validated);
        return redirect()->back()->with('success', 'Router/NAS berhasil diperbarui.');
    }

    public function destroy(Nas $na)
    {
        $na->delete();
        return redirect()->back()->with('success', 'Router/NAS berhasil dihapus.');
    }
}

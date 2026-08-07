<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;

class PaketController extends Controller
{
    public function index()
    {
        $pakets = Paket::withCount('customers')->latest()->get();
        return view('paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket',
            'harga' => 'required|numeric|min:0',
            'rate_limit' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $paket = Paket::create($validated);

        if (!empty($paket->rate_limit)) {
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op' => '=',
                'value' => $paket->rate_limit,
            ]);
            
            // Dummy untuk memunculkan profile di daloradius
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);
        }

        return redirect()->back()->with('success', 'Paket berhasil ditambahkan & tersinkronisasi ke Radius.');
    }

    public function update(Request $request, Paket $paket)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket,' . $paket->id,
            'harga' => 'required|numeric|min:0',
            'rate_limit' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $oldName = $paket->nama_paket;
        $paket->update($validated);

        // Update Radius
        if ($oldName !== $paket->nama_paket) {
            \App\Models\Radgroupreply::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
            \App\Models\Radgroupcheck::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
            // update usergroup too to keep them attached to the renamed profile
            \App\Models\Radusergroup::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
        }

        \App\Models\Radgroupreply::where('groupname', $paket->nama_paket)
            ->where('attribute', 'Mikrotik-Rate-Limit')
            ->delete();
        \App\Models\Radgroupcheck::where('groupname', $paket->nama_paket)
            ->where('attribute', 'Auth-Type')
            ->delete();

        if (!empty($paket->rate_limit)) {
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op' => '=',
                'value' => $paket->rate_limit,
            ]);
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);
        }

        return redirect()->back()->with('success', 'Paket berhasil diperbarui & tersinkronisasi ke Radius.');
    }

    public function destroy(Paket $paket)
    {
        $groupname = $paket->nama_paket;
        
        \App\Models\Radgroupreply::where('groupname', $groupname)->delete();
        \App\Models\Radgroupcheck::where('groupname', $groupname)->delete();
        // optionally radusergroup? No, let the users stay but they lose the rate limit

        $paket->delete();
        return redirect()->back()->with('success', 'Paket berhasil dihapus dari Sistem dan Radius.');
    }
}

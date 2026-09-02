<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use Illuminate\Http\Request;
use App\Services\MikroTikService;

class PaketController extends Controller
{
    protected $mikrotikService;

    public function __construct(MikroTikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

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
                'op' => ':=',
                'value' => $paket->rate_limit,
            ]);
            
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Group',
                'op' => ':=',
                'value' => $paket->nama_paket,
            ]);
            
            // Dummy untuk memunculkan profile di daloradius
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);

            // Auto-create PPP Profile in Mikrotik via API (Graceful fallback)
            try {
                $this->mikrotikService->createOrUpdatePppProfile($paket->nama_paket, '192.168.100.1');
            } catch (\Throwable $e) {
                \Log::warning('MikroTik PPP Profile creation failed (FreeRADIUS still synced): ' . $e->getMessage());
            }
        }

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil ditambahkan & tersinkronisasi ke Radius.');
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
            
            // Remove old profile from Mikrotik
            try {
                $this->mikrotikService->removePppProfile($oldName);
            } catch (\Throwable $e) {}
        }

        \App\Models\Radgroupreply::where('groupname', $paket->nama_paket)
            ->whereIn('attribute', ['Mikrotik-Rate-Limit', 'Mikrotik-Group'])
            ->delete();
        \App\Models\Radgroupcheck::where('groupname', $paket->nama_paket)
            ->where('attribute', 'Auth-Type')
            ->delete();

        if (!empty($paket->rate_limit)) {
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Rate-Limit',
                'op' => ':=',
                'value' => $paket->rate_limit,
            ]);
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Group',
                'op' => ':=',
                'value' => $paket->nama_paket,
            ]);
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);
        }

        // Auto-update PPP Profile in Mikrotik via API
        try {
            $this->mikrotikService->createOrUpdatePppProfile($paket->nama_paket, '192.168.100.1');
        } catch (\Throwable $e) {
            \Log::warning('MikroTik PPP Profile update failed (FreeRADIUS still synced): ' . $e->getMessage());
        }

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil diperbarui & tersinkronisasi ke Radius.');
    }

    public function destroy(Paket $paket)
    {
        $oldName = $paket->nama_paket;
        
        \App\Models\Radgroupreply::where('groupname', $oldName)->delete();
        \App\Models\Radgroupcheck::where('groupname', $oldName)->delete();

        $paket->delete();
        
        // Remove PPP Profile from Mikrotik via API
        try {
            $this->mikrotikService->removePppProfile($oldName);
        } catch (\Throwable $e) {}

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil dihapus dari Sistem dan Radius.');
    }

    public function syncFromMikrotik()
    {
        try {
            $profiles = $this->mikrotikService->runRawQuery('/ppp/profile/print');
            $count = 0;

            foreach ($profiles as $profile) {
                $name = $profile['name'] ?? null;
                // Lewati profil default atau bawaan
                if (!$name || in_array($name, ['default', 'default-encryption'])) continue;

                $rateLimit = $profile['rate-limit'] ?? null;
                
                // Cari atau buat paket di database lokal
                $paket = Paket::firstOrCreate(
                    ['nama_paket' => $name],
                    [
                        'harga' => 0, // Default 0, bisa diedit nanti
                        'rate_limit' => $rateLimit,
                        'keterangan' => 'Hasil sinkronisasi dari MikroTik',
                    ]
                );

                // Sinkronisasikan ke tabel Radius jika rate limit ada
                if ($rateLimit) {
                    \App\Models\Radgroupreply::updateOrCreate(
                        ['groupname' => $name, 'attribute' => 'Mikrotik-Rate-Limit'],
                        ['op' => ':=', 'value' => $rateLimit]
                    );
                    \App\Models\Radgroupreply::updateOrCreate(
                        ['groupname' => $name, 'attribute' => 'Mikrotik-Group'],
                        ['op' => ':=', 'value' => $name]
                    );
                    \App\Models\Radgroupcheck::updateOrCreate(
                        ['groupname' => $name, 'attribute' => 'Auth-Type'],
                        ['op' => ':=', 'value' => 'Accept']
                    );
                }

                $count++;
            }

            return redirect()->route('pakets.index')->with('success', "Berhasil menarik $count profil dari MikroTik!");
        } catch (\Exception $e) {
            return redirect()->route('pakets.index')->with('error', 'Gagal menarik data dari MikroTik: ' . $e->getMessage());
        }
    }
}

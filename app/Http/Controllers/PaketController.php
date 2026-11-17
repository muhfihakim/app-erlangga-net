<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Customer;
use App\Models\Pppoe;
use App\Models\PreOrder;
use App\Models\Invoice;
use App\Models\Radusergroup;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        $pakets = Paket::withCount(['customers', 'pppoes'])->latest()->get();
        return view('paket.index', compact('pakets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket',
            'harga' => 'required|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'rate_limit' => 'nullable|string|max:255',
            'local_address' => 'nullable|string|max:255',
            'ip_pool' => 'nullable|string|max:255',
            'pool_range' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $validated['pajak'] = $validated['pajak'] ?? 0;
        $validated['diskon'] = $validated['diskon'] ?? 0;

        $paket = Paket::create($validated);

        try {
            if (!empty($paket->rate_limit)) {
                \App\Models\Radgroupreply::create([
                    'groupname' => $paket->nama_paket,
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => ':=',
                    'value' => $paket->rate_limit,
                ]);
            }
            
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Group',
                'op' => ':=',
                'value' => $paket->nama_paket,
            ]);

            if (!empty($paket->ip_pool)) {
                \App\Models\Radgroupreply::create([
                    'groupname' => $paket->nama_paket,
                    'attribute' => 'Framed-Pool',
                    'op' => ':=',
                    'value' => $paket->ip_pool,
                ]);
            }
            
            // Dummy untuk memunculkan profile di daloradius
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);
        } catch (\Throwable $e) {
            \Log::warning('FreeRADIUS group creation warning: ' . $e->getMessage());
        }

        // Auto-create IP Pool in MikroTik if configured
        if (!empty($paket->ip_pool) && !empty($paket->pool_range)) {
            try {
                $this->mikrotikService->createOrUpdateIpPool($paket->ip_pool, $paket->pool_range);
            } catch (\Throwable $e) {
                \Log::warning('MikroTik IP Pool creation failed: ' . $e->getMessage());
            }
        }

        // Auto-create PPP Profile in Mikrotik via API (Graceful fallback)
        try {
            $poolNormalGateway = \App\Models\Setting::get('pool_normal_gateway', '10.10.0.1');
            $poolNormalName = \App\Models\Setting::get('pool_normal_name', 'pool-normal');
            $dnsServers = \App\Models\Setting::get('mikrotik_dns_servers', '8.8.8.8,1.1.1.1');

            $localAddress = !empty($paket->local_address) ? $paket->local_address : $poolNormalGateway;
            $remoteAddress = !empty($paket->ip_pool) ? $paket->ip_pool : $poolNormalName;

            $profileParams = [
                'local-address' => $localAddress,
                'remote-address' => $remoteAddress,
                'dns-server' => $dnsServers,
                'comment' => 'Paket: ' . $paket->nama_paket . ' (Rp ' . number_format($paket->harga, 0, ',', '.') . ')'
            ];

            if (!empty($paket->rate_limit)) {
                $profileParams['rate-limit'] = $paket->rate_limit;
            }

            $this->mikrotikService->createOrUpdatePppProfile($paket->nama_paket, $profileParams);
        } catch (\Throwable $e) {
            \Log::warning('MikroTik PPP Profile creation failed: ' . $e->getMessage());
        }

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil ditambahkan.');
    }

    public function update(Request $request, Paket $paket)
    {
        $validated = $request->validate([
            'nama_paket' => 'required|string|max:255|unique:pakets,nama_paket,' . $paket->id,
            'harga' => 'required|numeric|min:0',
            'pajak' => 'nullable|numeric|min:0',
            'diskon' => 'nullable|numeric|min:0',
            'rate_limit' => 'nullable|string|max:255',
            'local_address' => 'nullable|string|max:255',
            'ip_pool' => 'nullable|string|max:255',
            'pool_range' => 'nullable|string|max:255',
            'keterangan' => 'nullable|string',
        ]);

        $validated['pajak'] = $validated['pajak'] ?? 0;
        $validated['diskon'] = $validated['diskon'] ?? 0;

        $oldName = $paket->nama_paket;
        $paket->update($validated);

        // Update Radius
        try {
            if ($oldName !== $paket->nama_paket) {
                \App\Models\Radgroupreply::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
                \App\Models\Radgroupcheck::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
                \App\Models\Radusergroup::where('groupname', $oldName)->update(['groupname' => $paket->nama_paket]);
                
                try {
                    $this->mikrotikService->removePppProfile($oldName);
                } catch (\Throwable $e) {}
            }

            \App\Models\Radgroupreply::where('groupname', $paket->nama_paket)
                ->whereIn('attribute', ['Mikrotik-Rate-Limit', 'Mikrotik-Group', 'Framed-Pool'])
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
            }
            \App\Models\Radgroupreply::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Mikrotik-Group',
                'op' => ':=',
                'value' => $paket->nama_paket,
            ]);
            if (!empty($paket->ip_pool)) {
                \App\Models\Radgroupreply::create([
                    'groupname' => $paket->nama_paket,
                    'attribute' => 'Framed-Pool',
                    'op' => ':=',
                    'value' => $paket->ip_pool,
                ]);
            }
            \App\Models\Radgroupcheck::create([
                'groupname' => $paket->nama_paket,
                'attribute' => 'Auth-Type',
                'op' => ':=',
                'value' => 'Accept',
            ]);
        } catch (\Throwable $e) {
            \Log::warning('FreeRADIUS update warning on Paket update: ' . $e->getMessage());
        }

        // Update IP Pool in MikroTik if configured
        if (!empty($paket->ip_pool) && !empty($paket->pool_range)) {
            try {
                $this->mikrotikService->createOrUpdateIpPool($paket->ip_pool, $paket->pool_range);
            } catch (\Throwable $e) {
                \Log::warning('MikroTik IP Pool update failed: ' . $e->getMessage());
            }
        }

        // Update PPP Profile in MikroTik
        try {
            $poolNormalGateway = \App\Models\Setting::get('pool_normal_gateway', '10.10.0.1');
            $poolNormalName = \App\Models\Setting::get('pool_normal_name', 'pool-normal');
            $dnsServers = \App\Models\Setting::get('mikrotik_dns_servers', '8.8.8.8,1.1.1.1');

            $localAddress = !empty($paket->local_address) ? $paket->local_address : $poolNormalGateway;
            $remoteAddress = !empty($paket->ip_pool) ? $paket->ip_pool : $poolNormalName;

            $profileParams = [
                'local-address' => $localAddress,
                'remote-address' => $remoteAddress,
                'dns-server' => $dnsServers,
                'comment' => 'Paket: ' . $paket->nama_paket . ' (Rp ' . number_format($paket->harga, 0, ',', '.') . ')'
            ];

            if (!empty($paket->rate_limit)) {
                $profileParams['rate-limit'] = $paket->rate_limit;
            }

            $this->mikrotikService->createOrUpdatePppProfile($paket->nama_paket, $profileParams);
        } catch (\Throwable $e) {
            \Log::warning('MikroTik PPP Profile update failed: ' . $e->getMessage());
        }

        // Auto-sync all unpaid invoices of PPPoE accounts on this package
        $pppoeIds = \App\Models\Pppoe::where('paket_id', $paket->id)->pluck('id');
        if ($pppoeIds->isNotEmpty()) {
            $unpaidInvoices = \App\Models\Invoice::whereIn('pppoe_id', $pppoeIds)
                ->where('status', 'unpaid')
                ->with('pppoe')
                ->get();

            foreach ($unpaidInvoices as $inv) {
                $tax = ($inv->pppoe && $inv->pppoe->pajak !== null && (float)$inv->pppoe->pajak > 0) ? (float)$inv->pppoe->pajak : (float)($paket->pajak ?? 0);
                $discount = ($inv->pppoe && $inv->pppoe->diskon !== null && (float)$inv->pppoe->diskon > 0) ? (float)$inv->pppoe->diskon : (float)($paket->diskon ?? 0);
                $amount = (float) $paket->harga;
                $total = max(0, $amount + $tax - $discount);

                $inv->update([
                    'amount' => $amount,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                ]);
            }
        }

        return redirect()->route('pakets.index')->with('success', 'Paket berhasil diperbarui, tagihan belum lunas disinkronkan, & data tersinkronisasi ke Radius.');
    }

    /**
     * Migrasi / Pindahkan seluruh pelanggan dan akun PPPoE secara massal dari satu paket ke paket lain.
     */
    public function migrateCustomers(Request $request)
    {
        $validated = $request->validate([
            'source_paket_id' => 'required|exists:pakets,id',
            'target_paket_id' => 'required|exists:pakets,id|different:source_paket_id',
            'sync_unpaid_invoices' => 'nullable',
            'kick_sessions' => 'nullable',
        ], [
            'source_paket_id.required' => 'Pilih paket asal yang ingin dialihkan.',
            'source_paket_id.exists' => 'Paket asal tidak ditemukan.',
            'target_paket_id.required' => 'Pilih paket tujuan baru.',
            'target_paket_id.exists' => 'Paket tujuan tidak ditemukan.',
            'target_paket_id.different' => 'Paket tujuan tidak boleh sama dengan paket asal.',
        ]);

        $sourcePaket = Paket::findOrFail($validated['source_paket_id']);
        $targetPaket = Paket::findOrFail($validated['target_paket_id']);
        $syncUnpaid = $request->boolean('sync_unpaid_invoices', true);
        $kickSessions = $request->boolean('kick_sessions', false);

        DB::beginTransaction();
        try {
            // Ambil semua customer dan pppoe dari paket asal
            $customers = Customer::where('paket_id', $sourcePaket->id)->get();
            $pppoes = Pppoe::where('paket_id', $sourcePaket->id)->get();

            $affectedCustomerCount = $customers->count();
            $affectedPppoeCount = $pppoes->count();

            if ($affectedCustomerCount === 0 && $affectedPppoeCount === 0) {
                DB::rollBack();
                return redirect()->route('pakets.index')->with('warning', "Tidak ada pelanggan atau akun PPPoE yang terdaftar pada paket '{$sourcePaket->nama_paket}'.");
            }

            // Kumpulkan daftar username PPPoE yang terdampak
            $pppoeUsernames = $pppoes->pluck('username')->filter()->unique()->values()->all();
            $customerUsernames = $customers->pluck('pppoe_username')->filter()->unique()->values()->all();
            $allUsernames = array_values(array_unique(array_filter(array_merge($pppoeUsernames, $customerUsernames))));

            // 1. Update Customers
            Customer::where('paket_id', $sourcePaket->id)->update([
                'paket_id' => $targetPaket->id
            ]);

            // 2. Update PPPoE
            Pppoe::where('paket_id', $sourcePaket->id)->update([
                'paket_id' => $targetPaket->id
            ]);

            // Jika ada PPPoE yang terkait dengan username customer terdampak tapi belum terupdate
            if (!empty($allUsernames)) {
                Pppoe::whereIn('username', $allUsernames)
                    ->where('paket_id', $sourcePaket->id)
                    ->update(['paket_id' => $targetPaket->id]);
            }

            // 3. Update PreOrders jika ada yang masih berstatus paket asal
            PreOrder::where('paket_id', $sourcePaket->id)->update([
                'paket_id' => $targetPaket->id
            ]);

            // 4. Update FreeRADIUS radusergroup (kecuali user yang saat ini sedang ISOLIR)
            if (!empty($allUsernames)) {
                Radusergroup::whereIn('username', $allUsernames)
                    ->where('groupname', '!=', 'ISOLIR')
                    ->update(['groupname' => $targetPaket->nama_paket]);
            }

            // 5. Sinkronisasi tagihan invoice unpaid jika dicentang
            $updatedInvoicesCount = 0;
            if ($syncUnpaid) {
                $pppoeIds = Pppoe::where('paket_id', $targetPaket->id)
                    ->whereIn('username', $allUsernames)
                    ->pluck('id');

                if ($pppoeIds->isNotEmpty()) {
                    $unpaidInvoices = Invoice::whereIn('pppoe_id', $pppoeIds)
                        ->where('status', 'unpaid')
                        ->with('pppoe')
                        ->get();

                    foreach ($unpaidInvoices as $inv) {
                        $tax = ($inv->pppoe && $inv->pppoe->pajak !== null && (float)$inv->pppoe->pajak > 0)
                            ? (float)$inv->pppoe->pajak
                            : (float)($targetPaket->pajak ?? 0);
                        $discount = ($inv->pppoe && $inv->pppoe->diskon !== null && (float)$inv->pppoe->diskon > 0)
                            ? (float)$inv->pppoe->diskon
                            : (float)($targetPaket->diskon ?? 0);
                        $amount = (float) $targetPaket->harga;
                        $total = max(0, $amount + $tax - $discount);

                        $inv->update([
                            'amount' => $amount,
                            'tax' => $tax,
                            'discount' => $discount,
                            'total' => $total,
                        ]);
                        $updatedInvoicesCount++;
                    }
                }
            }

            // 6. Catat log aktivitas
            ActivityLog::create([
                'user_id' => auth()->id(),
                'activity_type' => 'paket_migration',
                'description' => "Migrasi massal pelanggan dari '{$sourcePaket->nama_paket}' ke '{$targetPaket->nama_paket}'. Berhasil mengalihkan {$affectedCustomerCount} pelanggan, {$affectedPppoeCount} akun PPPoE, dan menyinkronkan {$updatedInvoicesCount} invoice unpaid.",
                'logged_at' => now(),
            ]);

            DB::commit();

            // 7. Kick active sessions di MikroTik jika dicentang (dilakukan setelah DB commit)
            $kickedCount = 0;
            if ($kickSessions && !empty($allUsernames)) {
                foreach ($allUsernames as $usr) {
                    try {
                        if ($this->mikrotikService->kickPppoeUser($usr)) {
                            $kickedCount++;
                        }
                    } catch (\Throwable $e) {
                        // ignore router kick failure
                    }
                }
            }

            $feedback = "Berhasil memindahkan {$affectedCustomerCount} pelanggan dan {$affectedPppoeCount} akun PPPoE dari '{$sourcePaket->nama_paket}' ke '{$targetPaket->nama_paket}'.";
            if ($updatedInvoicesCount > 0) {
                $feedback .= " Sebanyak {$updatedInvoicesCount} invoice unpaid telah disesuaikan dengan tarif baru.";
            }
            if ($kickSessions) {
                $feedback .= " {$kickedCount} sesi aktif MikroTik diputus untuk pembaruan profil rate limit.";
            }

            return redirect()->route('pakets.index')->with('success', $feedback);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Paket Migration Failed: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect()->route('pakets.index')->with('error', 'Gagal memindahkan paket: ' . $e->getMessage());
        }
    }

    public function destroy(Paket $paket)
    {
        $customerCount = \App\Models\Customer::where('paket_id', $paket->id)->count();
        $pppoeCount = \App\Models\Pppoe::where('paket_id', $paket->id)->count();

        if ($customerCount > 0 || $pppoeCount > 0) {
            return redirect()->route('pakets.index')->with('error', "Paket '{$paket->nama_paket}' tidak dapat dihapus karena masih digunakan oleh {$customerCount} Pelanggan dan {$pppoeCount} Akun PPPoE aktif.");
        }

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
                $localAddress = $profile['local-address'] ?? null;
                $remoteAddress = $profile['remote-address'] ?? null;
                
                // Cari atau buat paket di database lokal
                $paket = Paket::firstOrCreate(
                    ['nama_paket' => $name],
                    [
                        'harga' => 0, // Default 0, bisa diedit nanti
                        'rate_limit' => $rateLimit,
                        'local_address' => $localAddress,
                        'ip_pool' => $remoteAddress,
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
                    if (!empty($remoteAddress)) {
                        \App\Models\Radgroupreply::updateOrCreate(
                            ['groupname' => $name, 'attribute' => 'Framed-Pool'],
                            ['op' => ':=', 'value' => $remoteAddress]
                        );
                    }
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

    public function syncToMikrotik()
    {
        try {
            $pakets = Paket::all();
            if ($pakets->isEmpty()) {
                return redirect()->route('pakets.index')->with('warning', 'Tidak ada data paket di database untuk disinkronkan.');
            }

            // Ambil parameter IP Pool & Gateway dari pengaturan (sebagai default fallback)
            $poolNormalName = \App\Models\Setting::get('pool_normal_name', 'pool-normal');
            $poolNormalRange = \App\Models\Setting::get('pool_normal_range', '10.10.0.2-10.10.15.254');
            $poolNormalGateway = \App\Models\Setting::get('pool_normal_gateway', '10.10.0.1');
            $dnsServers = \App\Models\Setting::get('mikrotik_dns_servers', '8.8.8.8,1.1.1.1');

            // 1. Pastikan IP Pool normal default ada di MikroTik
            $this->mikrotikService->createOrUpdateIpPool($poolNormalName, $poolNormalRange);

            // 2. Pastikan IP Pool isolir & profile isolir ada di MikroTik
            $poolIsolirName = \App\Models\Setting::get('pool_isolir_name', 'pool-isolir');
            $poolIsolirRange = \App\Models\Setting::get('pool_isolir_range', '172.30.0.2-172.30.255.254');
            $poolIsolirGateway = \App\Models\Setting::get('pool_isolir_gateway', '172.30.0.1');
            $this->mikrotikService->createOrUpdateIpPool($poolIsolirName, $poolIsolirRange);

            $this->mikrotikService->createOrUpdatePppProfile('profile-isolir', [
                'local-address' => $poolIsolirGateway,
                'remote-address' => $poolIsolirName,
                'rate-limit' => '128k/128k',
                'dns-server' => $dnsServers,
                'comment' => 'Profile Pelanggan Isolir - Auto Created'
            ]);

            // 3. Sinkronkan seluruh paket layanan ke MikroTik & FreeRADIUS
            $syncedCount = 0;
            $errors = [];

            foreach ($pakets as $paket) {
                // Buat IP Pool paket jika diisi
                $poolName = $poolNormalName;
                if (!empty($paket->ip_pool) && !empty($paket->pool_range)) {
                    $this->mikrotikService->createOrUpdateIpPool($paket->ip_pool, $paket->pool_range);
                    $poolName = $paket->ip_pool;
                } elseif (!empty($paket->ip_pool)) {
                    $poolName = $paket->ip_pool;
                }

                $localGateway = !empty($paket->local_address) ? $paket->local_address : $poolNormalGateway;

                $params = [
                    'local-address' => $localGateway,
                    'remote-address' => $poolName,
                    'dns-server' => $dnsServers,
                    'comment' => 'Paket: ' . $paket->nama_paket . ' (Rp ' . number_format($paket->harga, 0, ',', '.') . ')'
                ];

                if (!empty($paket->rate_limit)) {
                    $params['rate-limit'] = $paket->rate_limit;
                }

                $res = $this->mikrotikService->createOrUpdatePppProfile($paket->nama_paket, $params);

                if ($res) {
                    $syncedCount++;

                    // Pastikan juga sinkron ke FreeRADIUS (radgroupreply & radgroupcheck)
                    try {
                        if (!empty($paket->rate_limit)) {
                            \App\Models\Radgroupreply::updateOrCreate(
                                ['groupname' => $paket->nama_paket, 'attribute' => 'Mikrotik-Rate-Limit'],
                                ['op' => ':=', 'value' => $paket->rate_limit]
                            );
                        }
                        \App\Models\Radgroupreply::updateOrCreate(
                            ['groupname' => $paket->nama_paket, 'attribute' => 'Mikrotik-Group'],
                            ['op' => ':=', 'value' => $paket->nama_paket]
                        );
                        if (!empty($paket->ip_pool)) {
                            \App\Models\Radgroupreply::updateOrCreate(
                                ['groupname' => $paket->nama_paket, 'attribute' => 'Framed-Pool'],
                                ['op' => ':=', 'value' => $paket->ip_pool]
                            );
                        }
                        \App\Models\Radgroupcheck::updateOrCreate(
                            ['groupname' => $paket->nama_paket, 'attribute' => 'Auth-Type'],
                            ['op' => ':=', 'value' => 'Accept']
                        );
                    } catch (\Throwable $e) {
                        \Log::warning('FreeRADIUS sync warning for ' . $paket->nama_paket . ': ' . $e->getMessage());
                    }
                } else {
                    $errors[] = $paket->nama_paket;
                }
            }

            $routerHost = \App\Models\Setting::get('mikrotik_host', config('mikrotik.host'));

            if (empty($errors)) {
                return redirect()->route('pakets.index')->with('success', "Berhasil mengirim & menyinkronkan {$syncedCount} paket ke MikroTik ($routerHost) & RADIUS (PPP Profile & IP Pool)!");
            } else {
                $fail = implode(', ', $errors);
                return redirect()->route('pakets.index')->with('warning', "Tersinkron {$syncedCount} paket, gagal pada: {$fail}");
            }
        } catch (\Exception $e) {
            return redirect()->route('pakets.index')->with('error', 'Gagal menyinkronkan paket ke MikroTik: ' . $e->getMessage());
        }
    }
}

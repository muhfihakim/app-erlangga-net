<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\Radgroupreply;
use App\Models\Radreply;
use App\Models\Radusergroup;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class IsolirService
{
    protected MikroTikService $mikrotikService;

    public function __construct(MikroTikService $mikrotikService)
    {
        $this->mikrotikService = $mikrotikService;
    }

    /**
     * Memastikan konfigurasi attribute group ISOLIR di radgroupreply FreeRADIUS lengkap
     */
    public function ensureIsolirGroupReply(): void
    {
        try {
            $poolName = Setting::get('pool_isolir_name', 'pool-isolir');

            // 1. Framed-Pool: mengarahkan MikroTik agar memberi IP dari pool isolir (172.30.x.x)
            Radgroupreply::updateOrCreate(
                ['groupname' => 'ISOLIR', 'attribute' => 'Framed-Pool'],
                ['op' => ':=', 'value' => $poolName]
            );

            // 2. Mikrotik-Group: profile isolir MikroTik
            Radgroupreply::updateOrCreate(
                ['groupname' => 'ISOLIR', 'attribute' => 'Mikrotik-Group'],
                ['op' => ':=', 'value' => 'profile-isolir']
            );

            // 3. Mikrotik-Rate-Limit: batasi kecepatan saat isolir
            Radgroupreply::updateOrCreate(
                ['groupname' => 'ISOLIR', 'attribute' => 'Mikrotik-Rate-Limit'],
                ['op' => ':=', 'value' => '128k/128k']
            );

            // 4. Mikrotik-Address-List: masukkan IP otomatis ke firewall address list 'ISOLIR'
            Radgroupreply::updateOrCreate(
                ['groupname' => 'ISOLIR', 'attribute' => 'Mikrotik-Address-List'],
                ['op' => ':=', 'value' => 'ISOLIR']
            );
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal ensureIsolirGroupReply: " . $e->getMessage());
        }
    }

    /**
     * Isolir akun pelanggan / PPPoE secara manual atau otomatis
     *
     * @param string $username Username PPPoE
     * @param string|null $reason Alasan pengisolasian
     * @param bool $forceKick Paksa putus sesi MikroTik meski sudah berstatus isolir
     * @return array{success: bool, message: string, kicked: bool}
     */
    public function isolate(string $username, ?string $reason = null, bool $forceKick = false): array
    {
        $reason = $reason ?: 'Isolir manual oleh Admin';

        $pppoe = Pppoe::where('username', $username)->first();
        $wasAlreadyIsolated = $pppoe && $pppoe->is_isolir;

        // 1. Backup IP statis asli pelanggan ke database Laravel sebelum dihapus dari radreply
        if ($pppoe) {
            if (empty($pppoe->ip_address)) {
                try {
                    $existingIp = Radreply::where('username', $username)
                        ->where('attribute', 'Framed-IP-Address')
                        ->value('value');
                    if ($existingIp) {
                        $pppoe->ip_address = $existingIp;
                    }
                } catch (\Throwable $e) {
                    Log::warning("IsolirService: Gagal membaca IP dari radreply ({$username}): " . $e->getMessage());
                }
            }

            $pppoe->is_isolir = true;
            $pppoe->isolir_reason = $reason;
            $pppoe->isolir_at = $pppoe->isolir_at ?? now();
            $pppoe->save();
        }

        // 2. Hapus Framed-IP-Address dari radreply agar FreeRADIUS memakai Framed-Pool (pool-isolir)
        // Jika Framed-IP-Address tetap ada di radreply, FreeRADIUS akan selalu memberi IP normal tersebut
        try {
            Radreply::where('username', $username)
                ->where('attribute', 'Framed-IP-Address')
                ->delete();
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal menghapus Framed-IP-Address dari radreply ({$username}): " . $e->getMessage());
        }

        // 3. Pindahkan usergroup ke 'ISOLIR' di FreeRADIUS
        try {
            Radusergroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => 'ISOLIR', 'priority' => 1]
            );
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal update Radusergroup ke ISOLIR ({$username}): " . $e->getMessage());
        }

        // 4. Pastikan radgroupreply memiliki attribute Framed-Pool dsb
        $this->ensureIsolirGroupReply();

        // 5. Kick sesi MikroTik agar user reconnect dan dial masuk ke pool-isolir (172.30.x.x)
        $kicked = false;
        if (!$wasAlreadyIsolated || $forceKick) {
            try {
                $kicked = $this->mikrotikService->kickPppoeUser($username);
            } catch (\Throwable $e) {
                Log::warning("IsolirService: Gagal kick session MikroTik ({$username}): " . $e->getMessage());
            }
        }

        Cache::forget('mikrotik_pppoe_active');

        Log::info("IsolirService: Akun PPPoE {$username} berhasil diisolir. Alasan: {$reason}. Sesi terputus: " . ($kicked ? 'Ya' : 'Tidak'));

        return [
            'success' => true,
            'message' => "Akun {$username} berhasil diisolir (" . $reason . ")." . ($kicked ? " Sesi MikroTik telah diputus & dialihkan ke profil isolir." : ""),
            'kicked' => $kicked,
        ];
    }

    /**
     * Buka isolir (aktifkan kembali) layanan pelanggan / PPPoE
     *
     * @param string $username Username PPPoE
     * @return array{success: bool, message: string, kicked: bool}
     */
    public function unIsolate(string $username): array
    {
        $pppoe = Pppoe::with('paket')->where('username', $username)->first();
        $targetGroup = $pppoe?->paket?->nama_paket;

        if (!$targetGroup) {
            $customer = Customer::with('paket')->where('pppoe_username', $username)->first();
            $targetGroup = $customer?->paket?->nama_paket;
        }

        if (!$targetGroup) {
            $firstPaket = Paket::first();
            $targetGroup = $firstPaket?->nama_paket ?? 'default';
        }

        // 1. Kembalikan Framed-IP-Address ke radreply jika pppoe memiliki ip_address statis
        if ($pppoe && !empty($pppoe->ip_address)) {
            try {
                Radreply::updateOrCreate(
                    ['username' => $username, 'attribute' => 'Framed-IP-Address'],
                    ['op' => ':=', 'value' => $pppoe->ip_address]
                );
            } catch (\Throwable $e) {
                Log::warning("IsolirService: Gagal restore Framed-IP-Address ({$username}): " . $e->getMessage());
            }
        }

        // 2. Kembalikan radusergroup ke paket normal
        try {
            Radusergroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => $targetGroup, 'priority' => 1]
            );
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal restore Radusergroup ({$username}): " . $e->getMessage());
        }

        // 3. Update status pppoe
        if ($pppoe) {
            $pppoe->update([
                'is_isolir' => false,
                'isolir_reason' => null,
                'isolir_at' => null,
            ]);
        }

        // 4. Kick sesi MikroTik agar reconnect dan memperoleh IP normal
        $kicked = false;
        try {
            $kicked = $this->mikrotikService->kickPppoeUser($username);
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal kick session MikroTik ({$username}): " . $e->getMessage());
        }

        Cache::forget('mikrotik_pppoe_active');

        Log::info("IsolirService: Isolir akun {$username} dibuka kembali. Paket: {$targetGroup}. Sesi terputus: " . ($kicked ? 'Ya' : 'Tidak'));

        return [
            'success' => true,
            'message' => "Isolir akun {$username} berhasil dibuka. Layanan paket '{$targetGroup}' telah aktif kembali." . ($kicked ? " Sesi MikroTik telah di-refresh." : ""),
            'kicked' => $kicked,
        ];
    }
}

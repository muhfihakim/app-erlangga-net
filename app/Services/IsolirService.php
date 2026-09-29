<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Paket;
use App\Models\Pppoe;
use App\Models\Radusergroup;
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
     * Isolir akun pelanggan / PPPoE secara manual atau otomatis
     *
     * @param string $username Username PPPoE
     * @param string|null $reason Alasan pengisolasian
     * @return array{success: bool, message: string, kicked: bool}
     */
    public function isolate(string $username, ?string $reason = null): array
    {
        $reason = $reason ?: 'Isolir manual oleh Admin';

        // 1. Update FreeRADIUS radusergroup to ISOLIR
        try {
            Radusergroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => 'ISOLIR', 'priority' => 1]
            );
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal update Radusergroup ke ISOLIR ({$username}): " . $e->getMessage());
        }

        // 2. Update status di tabel pppoes
        $pppoe = Pppoe::where('username', $username)->first();
        if ($pppoe) {
            $pppoe->update([
                'is_isolir' => true,
                'isolir_reason' => $reason,
                'isolir_at' => now(),
            ]);
        }

        // 3. Kick active PPPoE session di MikroTik agar dial ulang dan masuk ke profile-isolir (172.30.x.x)
        $kicked = false;
        try {
            $kicked = $this->mikrotikService->kickPppoeUser($username);
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal kick session MikroTik ({$username}): " . $e->getMessage());
        }

        // 4. Invalidate cache PPPoE active
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
        // 1. Cari groupname target (nama paket pelanggan)
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

        // 2. Restore FreeRADIUS radusergroup ke paket pelanggan
        try {
            Radusergroup::updateOrCreate(
                ['username' => $username],
                ['groupname' => $targetGroup, 'priority' => 1]
            );
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal restore Radusergroup ({$username}): " . $e->getMessage());
        }

        // 3. Update status di tabel pppoes
        if ($pppoe) {
            $pppoe->update([
                'is_isolir' => false,
                'isolir_reason' => null,
                'isolir_at' => null,
            ]);
        }

        // 4. Kick active PPPoE session di MikroTik agar dial ulang dan masuk ke profil paket normal
        $kicked = false;
        try {
            $kicked = $this->mikrotikService->kickPppoeUser($username);
        } catch (\Throwable $e) {
            Log::warning("IsolirService: Gagal kick session MikroTik ({$username}): " . $e->getMessage());
        }

        // 5. Invalidate cache PPPoE active
        Cache::forget('mikrotik_pppoe_active');

        Log::info("IsolirService: Isolir akun {$username} dibuka kembali. Paket: {$targetGroup}. Sesi terputus: " . ($kicked ? 'Ya' : 'Tidak'));

        return [
            'success' => true,
            'message' => "Isolir akun {$username} berhasil dibuka. Layanan paket '{$targetGroup}' telah aktif kembali." . ($kicked ? " Sesi MikroTik telah di-refresh." : ""),
            'kicked' => $kicked,
        ];
    }
}

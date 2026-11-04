<?php

namespace App\Services;

use App\Models\Paket;
use App\Models\Pppoe;

class PppoeIpService
{
    /**
     * Dapatkan IP address berikutnya untuk paket tertentu.
     * Aturan:
     * - Host octet ke-4 berkisar antara .2 sampai .253 saja (.0 network, .1 gateway, .254/.255 reserved).
     * - Melanjutkan dari IP tertinggi paket tersebut.
     * - Jika mencapai .253, subnet octet ke-3 bertambah (X + 1) dan octet ke-4 mulai dari .2.
     * - Menghindari duplikasi dengan IP yang sudah terdaftar.
     */
    public function getNextIp(?int $paketId): ?string
    {
        if (!$paketId) {
            return null;
        }

        $paket = Paket::find($paketId);
        if (!$paket) {
            return null;
        }

        $prefix = $this->resolvePrefix($paket);

        // Kumpulkan semua IP yang sudah terdaftar di database dengan prefix ini agar tidak bentrok
        $existingIps = Pppoe::where('ip_address', 'LIKE', $prefix . '.%')
            ->pluck('ip_address')
            ->filter(fn($ip) => filter_var($ip, FILTER_VALIDATE_IP))
            ->unique()
            ->values()
            ->toArray();

        $existingSet = array_flip($existingIps);

        // Ambil semua IP yang digunakan khusus untuk paket ini
        $paketIps = Pppoe::where('paket_id', $paketId)
            ->where('ip_address', 'LIKE', $prefix . '.%')
            ->pluck('ip_address')
            ->filter(fn($ip) => filter_var($ip, FILTER_VALIDATE_IP));

        if ($paketIps->isEmpty()) {
            $startX = 0;
            $startY = 2;
        } else {
            // Sortir numerik berdasarkan octet ke-3 (X) dan ke-4 (Y)
            $parsed = $paketIps->map(function ($ip) {
                $p = explode('.', $ip);
                return [
                    'ip' => $ip,
                    'x' => (int) ($p[2] ?? 0),
                    'y' => (int) ($p[3] ?? 0),
                ];
            })->sort(function ($a, $b) {
                if ($a['x'] === $b['x']) {
                    return $a['y'] <=> $b['y'];
                }
                return $a['x'] <=> $b['x'];
            })->values();

            $highest = $parsed->last();
            $startX = $highest['x'];
            $startY = $highest['y'] + 1;
        }

        // Terapkan batas: octet ke-4 harus antara 2 s/d 253
        if ($startY > 253) {
            $startX++;
            $startY = 2;
        } elseif ($startY < 2) {
            $startY = 2;
        }

        // Cari IP pertama yang belum terpakai
        $currX = $startX;
        $currY = $startY;
        $maxIterations = 50000;

        while ($maxIterations-- > 0) {
            $candidate = "{$prefix}.{$currX}.{$currY}";
            if (!isset($existingSet[$candidate])) {
                return $candidate;
            }

            $currY++;
            if ($currY > 253) {
                $currX++;
                $currY = 2;
            }
        }

        return "{$prefix}.{$currX}.{$currY}";
    }

    /**
     * Dapatkan peta IP berikutnya untuk seluruh paket yang tersedia.
     * @return array<int, string> [paket_id => next_ip]
     */
    public function getAllNextIps(): array
    {
        $result = [];
        $pakets = Paket::all();
        foreach ($pakets as $paket) {
            $result[$paket->id] = $this->getNextIp($paket->id);
        }
        return $result;
    }

    /**
     * Tentukan prefix network A.B dari konfigurasi paket.
     */
    public function resolvePrefix(Paket $paket): string
    {
        if (!empty($paket->local_address)) {
            $parts = explode('.', $paket->local_address);
            if (count($parts) >= 2) {
                return $parts[0] . '.' . $parts[1];
            }
        }

        if (!empty($paket->pool_range)) {
            $first = explode('-', $paket->pool_range)[0] ?? '';
            $parts = explode('.', $first);
            if (count($parts) >= 2) {
                return $parts[0] . '.' . $parts[1];
            }
        }

        // Fallback berdasarkan nama paket atau default 182.10
        if (str_contains(strtolower($paket->nama_paket), 'silver') || str_contains($paket->nama_paket, '20')) {
            return '182.20';
        }
        if (str_contains(strtolower($paket->nama_paket), 'gold') || str_contains($paket->nama_paket, '30')) {
            return '182.30';
        }
        if (str_contains(strtolower($paket->nama_paket), 'platinum') || str_contains($paket->nama_paket, '50')) {
            return '182.50';
        }

        return '182.10';
    }
}

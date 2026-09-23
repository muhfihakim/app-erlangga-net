<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pppoe;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * 1. INFORMASI AKUN (Profile, Paket, Teknis & Layanan)
     */
    public function info(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $customer->load(['paket', 'preOrder']);
        $pppoe = $customer->pppoe;

        $statusLayanan = 'Belum Aktif';
        if ($pppoe) {
            $statusLayanan = ($pppoe->status_invoice === 'terbayar') ? 'Aktif' : 'Menunggu Pembayaran';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'profil' => [
                    'id'             => $customer->id,
                    'id_pelanggan'   => $customer->id_pelanggan,
                    'nama_lengkap'   => $customer->nama_lengkap,
                    'no_whatsapp'    => $customer->no_whatsapp,
                    'alamat_lengkap' => $customer->alamat_lengkap,
                    'email'          => $customer->preOrder->alamat_email ?? null,
                ],
                'paket' => $customer->paket ? [
                    'id'          => $customer->paket->id,
                    'nama_paket'  => $customer->paket->nama_paket,
                    'kecepatan'   => $customer->paket->kecepatan ?? '-',
                    'harga'       => (float) $customer->paket->harga,
                    'pajak'       => (float) ($customer->paket->pajak ?? 0),
                    'diskon'      => (float) ($customer->paket->diskon ?? 0),
                    'total_harga' => (float) $customer->paket->total_harga,
                    'deskripsi'   => $customer->paket->deskripsi ?? 'Akses internet unlimited 24 jam tanpa FUP',
                ] : null,
                'teknis' => [
                    'pppoe_username' => $customer->pppoe_username,
                    'jenis_modem'    => $customer->jenis_modem,
                    'sn_perangkat'   => $customer->sn_perangkat,
                    'ip_address'     => $pppoe?->ip_address ?? null,
                    'router'         => $pppoe?->router ?? 'MikroTik Gateway',
                ],
                'layanan' => [
                    'status_layanan' => $statusLayanan,
                    'status_invoice' => $pppoe?->status_invoice ?? 'terbayar',
                    'tanggal_aktif'  => ($pppoe && $pppoe->tanggal_aktif) ? ($pppoe->tanggal_aktif instanceof \DateTimeInterface ? $pppoe->tanggal_aktif->format('Y-m-d') : substr((string)$pppoe->tanggal_aktif, 0, 10)) : ($customer->created_at ? $customer->created_at->format('Y-m-d') : null),
                    'tipe_pembayaran'=> $pppoe?->tipe_pembayaran ?? 'prepaid',
                    'periode_billing'=> $pppoe?->periode_billing ?? 'fixed',
                ]
            ]
        ]);
    }

    /**
     * 2. INFORMASI PENGGUNAAN (Traffic, Kuota, Status Koneksi Real-Time)
     */
    public function usage(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $username = $customer->pppoe_username;

        $onlineSession = null;
        $monthlyDownloadBytes = 0;
        $monthlyUploadBytes = 0;
        $totalSessions = 0;
        $recentSessions = [];

        // Query FreeRADIUS radacct jika database radius tersedia
        if (!empty($username)) {
            try {
                // Sesi aktif saat ini (Online jika acctstoptime IS NULL)
                $onlineSession = DB::connection('radius')->table('radacct')
                    ->where('username', $username)
                    ->whereNull('acctstoptime')
                    ->orderBy('radacctid', 'desc')
                    ->first();

                // Statistik pemakaian bulan berjalan
                $startOfMonth = now()->startOfMonth()->toDateTimeString();
                $endOfMonth = now()->endOfMonth()->toDateTimeString();

                $usageQuery = DB::connection('radius')->table('radacct')
                    ->where('username', $username)
                    ->where('acctstarttime', '>=', $startOfMonth)
                    ->where('acctstarttime', '<=', $endOfMonth);

                $monthlyUploadBytes = (float) $usageQuery->sum('acctinputoctets');
                $monthlyDownloadBytes = (float) $usageQuery->sum('acctoutputoctets');
                $totalSessions = $usageQuery->count();

                // Tambahkan traffic dari sesi yang sedang aktif berjalan
                if ($onlineSession) {
                    $monthlyUploadBytes += (float) ($onlineSession->acctinputoctets ?? 0);
                    $monthlyDownloadBytes += (float) ($onlineSession->acctoutputoctets ?? 0);
                }

                // 5 Riwayat sesi koneksi terakhir
                $recentRecords = DB::connection('radius')->table('radacct')
                    ->where('username', $username)
                    ->orderBy('radacctid', 'desc')
                    ->limit(5)
                    ->get();

                foreach ($recentRecords as $rec) {
                    $recentSessions[] = [
                        'ip_address'      => $rec->framedipaddress ?? '-',
                        'waktu_konek'     => $rec->acctstarttime ? Carbon::parse($rec->acctstarttime)->format('d/m/Y H:i') : '-',
                        'waktu_putus'     => $rec->acctstoptime ? Carbon::parse($rec->acctstoptime)->format('d/m/Y H:i') : 'Aktif Sekarang',
                        'durasi'          => $this->formatDuration((int) ($rec->acctsessiontime ?? 0)),
                        'download'        => $this->formatBytes((float) ($rec->acctoutputoctets ?? 0)),
                        'upload'          => $this->formatBytes((float) ($rec->acctinputoctets ?? 0)),
                        'total'           => $this->formatBytes((float) (($rec->acctoutputoctets ?? 0) + ($rec->acctinputoctets ?? 0))),
                        'status'          => $rec->acctstoptime ? 'selesai' : 'online',
                    ];
                }
            } catch (\Throwable $e) {
                // FreeRADIUS database fallback (misal saat offline atau testing)
            }
        }

        $isOnline = $onlineSession !== null;
        $totalBytes = $monthlyDownloadBytes + $monthlyUploadBytes;

        $uptimeSeconds = (int) ($onlineSession->acctsessiontime ?? 0);
        $uptimeFormatted = $isOnline ? $this->formatDuration($uptimeSeconds) : 'Offline';

        return response()->json([
            'success' => true,
            'data' => [
                'koneksi' => [
                    'status'         => $isOnline ? 'online' : 'offline',
                    'ip_address'     => $onlineSession ? ($onlineSession->framedipaddress ?? '-') : ($customer->pppoe->ip_address ?? '-'),
                    'mac_address'    => $onlineSession ? ($onlineSession->callingstationid ?? '-') : '-',
                    'online_since'   => $onlineSession && $onlineSession->acctstarttime ? Carbon::parse($onlineSession->acctstarttime)->format('Y-m-d H:i:s') : null,
                    'uptime'         => $uptimeFormatted,
                    'uptime_seconds' => $uptimeSeconds,
                ],
                'penggunaan_kuota' => [
                    'periode'        => now()->translatedFormat('F Y'),
                    'total_download' => $this->formatBytes($monthlyDownloadBytes),
                    'total_upload'   => $this->formatBytes($monthlyUploadBytes),
                    'total_traffic'  => $this->formatBytes($totalBytes),
                    'download_bytes' => $monthlyDownloadBytes,
                    'upload_bytes'   => $monthlyUploadBytes,
                    'total_bytes'    => $totalBytes,
                    'total_sesi'     => $totalSessions,
                    'tipe_kuota'     => 'Unlimited (Tanpa FUP)',
                ],
                'paket' => [
                    'nama_paket'     => $customer->paket->nama_paket ?? 'Internet',
                    'kecepatan'      => $customer->paket->kecepatan ?? '-',
                ],
                'riwayat_sesi' => $recentSessions,
            ]
        ]);
    }

    /**
     * Helper: Format bytes menjadi KB, MB, GB, TB
     */
    private function formatBytes(float $bytes, int $precision = 2): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Helper: Format durasi detik menjadi Hari, Jam, Menit
     */
    private function formatDuration(int $seconds): string
    {
        if ($seconds <= 0) return '0 Menit';
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $parts = [];
        if ($days > 0) $parts[] = "{$days} Hari";
        if ($hours > 0) $parts[] = "{$hours} Jam";
        if ($minutes > 0 || empty($parts)) $parts[] = "{$minutes} Menit";

        return implode(' ', $parts);
    }
}

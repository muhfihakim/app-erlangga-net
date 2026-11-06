<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Invoice;
use App\Models\Setting;
use App\Services\GenieAcsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * ScreenController (Backend-For-Frontend / Screen-Oriented API)
 *
 * Dirancang khusus untuk mempermudah integrasi Mobile Apps (Flutter BBS / Erlangga Net)
 * dengan konsep: 1 Screen / Halaman = 1 API Endpoint.
 *
 * Daftar Endpoint:
 * 1. GET /api/dashboard       -> Tab 1: Home Screen (home_screen.dart)
 * 2. GET /api/riwayat         -> Tab 2: History Screen (billing_history_screen.dart)
 * 3. GET /api/notifications   -> Tab 3: Notification Screen (notification_screen.dart)
 * 4. GET /api/profile         -> Tab 4: Profile Screen (profile_screen.dart)
 */
class ScreenController extends Controller
{
    protected ?GenieAcsService $acsService = null;

    public function __construct()
    {
        try {
            $this->acsService = app(GenieAcsService::class);
        } catch (\Throwable $e) {
            $this->acsService = null;
        }
    }

    /**
     * 1. SCREEN 1: HOME SCREEN (home_screen.dart)
     * URL: GET /api/dashboard (atau GET /api/screens/home)
     *
     * Menggabungkan 3 komponen utama halaman beranda:
     * - Status Koneksi & Telemetri PPPoE (StatusSection)
     * - Paket Internet Aktif (InfoPaketSection)
     * - Ringkasan & Tagihan Berjalan (TagihanSection)
     */
    public function dashboard(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau kadaluarsa.'
            ], 401);
        }

        Carbon::setLocale('id');

        $customer->load(['paket', 'preOrder']);
        $pppoe = $customer->pppoe;

        // 1. Data Paket
        $paket = $customer->paket;
        $namaPaket = $paket->nama_paket ?? 'Paket Internet Unlimited';
        $kecepatan = $paket->kecepatan ?? '-';
        $harga = (float) ($paket->harga ?? 0);
        $totalHargaPaket = (float) ($paket->total_harga ?? $harga);
        $deskripsiPaket = $paket->keterangan ?? ($paket->deskripsi ?? 'Akses internet unlimited 24 jam tanpa FUP.');

        // 2. Data Tagihan & Faktur Berjalan
        $unpaidInvoices = collect();
        if ($pppoe) {
            $unpaidInvoices = Invoice::where('pppoe_id', $pppoe->id)
                ->where('status', 'unpaid')
                ->orderBy('due_date', 'asc')
                ->get();
        }

        $unpaidCount = $unpaidInvoices->count();
        $hasUnpaidBill = $unpaidCount > 0;
        $totalTunggakan = (float) $unpaidInvoices->sum('total');
        $earliestUnpaid = $unpaidInvoices->first();
        $isEarliestExpired = $earliestUnpaid ? $earliestUnpaid->isExpired() : false;

        // Status invoice dihitung langsung dari ada/tidaknya tagihan yang belum terbayar di database invoices
        $statusInvoice = $hasUnpaidBill ? 'belum_bayar' : 'terbayar';

        // Sinkronkan status_invoice pada model pppoe jika tidak sesuai
        if ($pppoe && $pppoe->status_invoice !== $statusInvoice) {
            try {
                $pppoe->update(['status_invoice' => $statusInvoice]);
            } catch (\Throwable $e) {
                // Non-blocking bypass
            }
        }

        // Periode tagihan & jatuh tempo
        $dueDateStr = null;
        $periodeStr = 'Tagihan ' . now()->translatedFormat('F Y');

        if ($earliestUnpaid) {
            $dueDateStr = $earliestUnpaid->due_date ? Carbon::parse($earliestUnpaid->due_date)->format('Y-m-d') : null;
            if ($earliestUnpaid->due_date) {
                $periodeStr = 'Tagihan ' . Carbon::parse($earliestUnpaid->due_date)->translatedFormat('F Y');
            }
        } elseif ($pppoe?->tanggal_aktif) {
            $dt = Carbon::parse($pppoe->tanggal_aktif);
            $nextDue = now()->copy()->setDay(min($dt->day, now()->daysInMonth));
            if ($nextDue->isPast()) {
                $nextDue = now()->copy()->addMonth()->setDay(min($dt->day, now()->addMonth()->daysInMonth));
            }
            $dueDateStr = $nextDue->format('Y-m-d');
        }

        // 3. Status Koneksi & Telemetri
        $isOnline = false;
        $ipAddress = $pppoe?->ip_address ?? '-';
        $uptime = '-';
        $rxPower = null;

        // Cek FreeRADIUS sesi aktif
        if ($customer->pppoe_username) {
            try {
                $onlineSession = DB::connection('radius')->table('radacct')
                    ->where('username', $customer->pppoe_username)
                    ->whereNull('acctstoptime')
                    ->orderBy('radacctid', 'desc')
                    ->first();

                if ($onlineSession) {
                    $isOnline = true;
                    $ipAddress = $onlineSession->framedipaddress ?? $ipAddress;
                    $uptimeSeconds = (int) ($onlineSession->acctsessiontime ?? 0);
                    $uptime = $this->formatDuration($uptimeSeconds);
                }
            } catch (\Throwable $e) {
                // Radius DB connection bypass
            }
        }

        // Cek GenieACS untuk status modem & Redaman RX
        if ($this->acsService) {
            try {
                $device = $this->acsService->findDeviceForCustomer($customer);
                if ($device) {
                    if (!$isOnline && isset($device['is_online']) && $device['is_online']) {
                        $isOnline = true;
                    }
                    if (!empty($device['rx_power'])) {
                        $rxPower = $device['rx_power'] . ' dBm';
                    }
                }
            } catch (\Throwable $e) {
                // ACS bypass
            }
        }

        $statusKoneksi = $isOnline ? 'Online' : 'Offline';

        // 4. Status Akun Pelanggan (aktif / isolir / non_aktif)
        $isIsolir = (bool) ($pppoe && $pppoe->is_isolir);
        if ($hasUnpaidBill && $isEarliestExpired) {
            $isIsolir = true;
        }

        $statusAkun = $isIsolir ? 'isolir' : ($customer->trashed() ? 'non_aktif' : 'aktif');

        $tanggalAktifStr = ($pppoe && $pppoe->tanggal_aktif)
            ? ($pppoe->tanggal_aktif instanceof \DateTimeInterface ? $pppoe->tanggal_aktif->format('Y-m-d') : substr((string)$pppoe->tanggal_aktif, 0, 10))
            : ($customer->created_at ? $customer->created_at->format('Y-m-d') : null);

        // Siapkan struktur data ganda (Flat + Nested) agar kompatibel 100% dengan mobile model parser
        $responseData = [
            // Flat attributes sesuai kebutuhan dekompilasi APK BBS
            'id'                    => $customer->id,
            'id_pelanggan'          => (string) $customer->id_pelanggan,
            'nama_lengkap'          => (string) $customer->nama_lengkap,
            'pppoe_username'        => (string) ($customer->pppoe_username ?? '-'),
            'alamat_lengkap'        => (string) ($customer->alamat_lengkap ?? '-'),
            'sn_perangkat'          => (string) ($customer->sn_perangkat ?? '-'),
            'tanggal_aktif'         => $tanggalAktifStr,
            'nama_paket'            => $namaPaket,
            'kecepatan'             => $kecepatan,
            'harga'                 => $harga,
            'total_harga'           => $hasUnpaidBill ? $totalTunggakan : $totalHargaPaket,
            'status'                => $statusKoneksi,
            'status_akun'           => $statusAkun,
            'status_invoice'        => $statusInvoice,
            'has_unpaid_bill'       => $hasUnpaidBill,
            'unpaid_invoices_count' => $unpaidCount,

            // Nested categories untuk widget modular (StatusSection, InfoPaketSection, TagihanSection)
            'customer' => [
                'id'             => $customer->id,
                'id_pelanggan'   => $customer->id_pelanggan,
                'nama_lengkap'   => $customer->nama_lengkap,
                'no_whatsapp'    => $customer->no_whatsapp,
                'alamat_lengkap' => $customer->alamat_lengkap,
                'sn_perangkat'   => $customer->sn_perangkat,
                'jenis_modem'    => $customer->jenis_modem,
                'status_akun'    => $statusAkun,
            ],
            'koneksi' => [
                'pppoe_username' => $customer->pppoe_username,
                'status'         => $statusKoneksi,
                'is_online'      => $isOnline,
                'ip_address'     => $ipAddress,
                'uptime'         => $uptime,
                'rx_power'       => $rxPower,
            ],
            'paket' => [
                'id'          => $paket?->id,
                'nama_paket'  => $namaPaket,
                'kecepatan'   => $kecepatan,
                'harga'       => $harga,
                'total_harga' => $totalHargaPaket,
                'deskripsi'   => $deskripsiPaket,
            ],
            'tagihan' => [
                'status_akun'           => $statusAkun,
                'status_invoice'        => $statusInvoice,
                'status'                => $hasUnpaidBill ? ($isEarliestExpired ? 'expired' : ($earliestUnpaid?->status ?? 'unpaid')) : 'paid',
                'status_badge'          => $hasUnpaidBill ? ($isEarliestExpired ? 'Kedaluwarsa' : 'Belum Bayar') : 'Lunas',
                'is_expired'            => $isEarliestExpired,
                'has_unpaid_bill'       => $hasUnpaidBill,
                'unpaid_invoices_count' => $unpaidCount,
                'total_harga'           => $hasUnpaidBill ? $totalTunggakan : $totalHargaPaket,
                'external_id'           => $earliestUnpaid?->invoice_number,
                'invoice_id'            => $earliestUnpaid?->id,
                'periode'               => $periodeStr,
                'due_date'              => $dueDateStr,
                'invoice_url'           => ($earliestUnpaid && !$isEarliestExpired) ? (string) ($earliestUnpaid->xendit_invoice_url ?? '') : '',
            ],
        ];

        return response()->json([
            'success' => true,
            'message' => 'Data Home Screen / Dashboard berhasil dimuat.',
            'data'    => $responseData,
        ]);
    }

    /**
     * 2. SCREEN 2: RIWAYAT SCREEN (billing_history_screen.dart)
     * URL: GET /api/riwayat (atau GET /api/screens/history)
     *
     * Mengembalikan daftar riwayat tagihan & pembayaran pelanggan
     * untuk dipetakan ke widget BillingCard & BillingDetailScreen.
     */
    public function riwayat(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau kadaluarsa.'
            ], 401);
        }

        Carbon::setLocale('id');

        $pppoe = $customer->pppoe;
        if (!$pppoe) {
            return response()->json([
                'success' => true,
                'message' => 'Belum ada data tagihan.',
                'data'    => [],
            ]);
        }

        $invoices = Invoice::with('transactions')
            ->where('pppoe_id', $pppoe->id)
            ->latest('id')
            ->get();

        $snPerangkat = (string) ($customer->sn_perangkat ?? '-');

        $formattedRecords = $invoices->map(function ($inv) use ($snPerangkat) {
            $lastTx = $inv->transactions->first();

            // Tanggal untuk penamaan periode tagihan
            $dateForPeriod = $inv->due_date ? Carbon::parse($inv->due_date) : $inv->created_at;
            $periode = 'Tagihan ' . $dateForPeriod->translatedFormat('F Y');

            $isExpired = $inv->isExpired();
            $statusInvoice = ($inv->status === 'paid') ? 'paid' : ($isExpired ? 'expired' : 'unpaid');
            $statusBadge = ($inv->status === 'paid') ? 'Lunas' : ($isExpired ? 'Kedaluwarsa' : 'Belum Bayar');

            return [
                'id'               => $inv->id,
                'external_id'      => (string) $inv->invoice_number,
                'invoice_number'   => (string) $inv->invoice_number,
                'sn_perangkat'     => $snPerangkat,
                'periode'          => $periode,
                'status_invoice'   => $statusInvoice,
                'status'           => ($inv->status === 'paid') ? 'paid' : ($isExpired ? 'expired' : $inv->status),
                'status_badge'     => $statusBadge,
                'is_expired'       => $isExpired,
                'harga'            => (float) $inv->amount,
                'tax'              => (float) $inv->tax,
                'discount'         => (float) $inv->discount,
                'total_harga'      => (float) $inv->total,
                'total'            => (float) $inv->total,
                'invoice_url'      => $isExpired ? '' : (string) ($inv->xendit_invoice_url ?? ''),
                'invoice_duration' => 86400, // Valid 24 jam checkout link
                'due_date'         => $inv->due_date ? Carbon::parse($inv->due_date)->format('Y-m-d') : null,
                'created_at'       => $inv->created_at->format('Y-m-d H:i:s'),
                'paid_at'          => ($inv->status === 'paid' && $lastTx) ? $lastTx->payment_date : ($inv->status === 'paid' ? $inv->updated_at->format('Y-m-d H:i:s') : null),
                'payment_method'   => $inv->payment_method ?? ($lastTx ? $lastTx->payment_method : null),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar riwayat tagihan berhasil dimuat.',
            'data'    => $formattedRecords,
        ]);
    }

    /**
     * 3. SCREEN 3: NOTIFIKASI SCREEN (notification_screen.dart)
     * URL: GET /api/notifications (atau GET /api/screens/notifications)
     *
     * Feed notifikasi dinamis untuk pelanggan:
     * - Tagihan baru terbit / pengingat jatuh tempo
     * - Konfirmasi pembayaran lunas
     * - Pemberitahuan pemeliharaan jaringan / maintenance
     * - Ucapan selamat datang & pengumuman
     */
    public function notifications(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau kadaluarsa.'
            ], 401);
        }

        Carbon::setLocale('id');

        // 1. Ambil notifikasi tersimpan dari tabel customer_notifications
        $dbNotifications = CustomerNotification::where('customer_id', $customer->id)
            ->latest('id')
            ->limit(50)
            ->get();

        // 2. Jika pelanggan belum memiliki riwayat notifikasi, inisialisasi default
        if ($dbNotifications->isEmpty()) {
            $pppoe = $customer->pppoe;

            // Inisialisasi notifikasi sambutan
            $namaLengkap = $customer->nama_lengkap ?? 'Pelanggan';
            CustomerNotification::create([
                'customer_id' => $customer->id,
                'title'       => "Selamat Datang di BBS Mobile, {$namaLengkap}!",
                'body'        => 'Kelola tagihan, cek status perangkat ONT, dan nikmati koneksi internet super cepat tanpa hambatan bersama Erlangga Network.',
                'type'        => 'general',
                'data'        => ['action_url' => '/dashboard'],
                'is_read'     => true,
                'read_at'     => now(),
            ]);

            // Inisialisasi tagihan belum bayar jika ada
            if ($pppoe) {
                $unpaid = Invoice::where('pppoe_id', $pppoe->id)
                    ->where('status', 'unpaid')
                    ->orderBy('due_date', 'asc')
                    ->get();

                foreach ($unpaid as $inv) {
                    $dueDate = $inv->due_date ? Carbon::parse($inv->due_date)->translatedFormat('d F Y') : '-';
                    $isOverdue = $inv->due_date && Carbon::parse($inv->due_date)->isPast();
                    CustomerNotification::create([
                        'customer_id' => $customer->id,
                        'title'       => $isOverdue ? 'Peringatan: Tagihan Jatuh Tempo' : 'Tagihan Internet Baru',
                        'body'        => "Tagihan #{$inv->invoice_number} sebesar Rp " . number_format($inv->total, 0, ',', '.') . " jatuh tempo pada {$dueDate}. Segera lakukan pembayaran.",
                        'type'        => 'billing',
                        'data'        => [
                            'invoice_id' => $inv->id,
                            'action_url' => '/riwayat',
                        ],
                        'is_read'     => false,
                    ]);
                }
            }

            // Ambil ulang setelah diinisialisasi
            $dbNotifications = CustomerNotification::where('customer_id', $customer->id)
                ->latest('id')
                ->limit(50)
                ->get();
        }

        $formatted = $dbNotifications->map(function ($notif) {
            $data = is_array($notif->data) ? $notif->data : [];
            $actionUrl = $data['action_url'] ?? ($notif->type === 'billing' || $notif->type === 'payment' ? '/riwayat' : '/dashboard');
            $invoiceId = $data['invoice_id'] ?? null;

            return [
                'id'         => $notif->id,
                'type'       => $notif->type ?? 'general',
                'title'      => $notif->title,
                'message'    => $notif->body,
                'body'       => $notif->body,
                'data'       => $data,
                'is_read'    => (bool) $notif->is_read,
                'created_at' => $notif->created_at ? $notif->created_at->format('Y-m-d H:i:s') : now()->format('Y-m-d H:i:s'),
                'action_url' => $actionUrl,
                'invoice_id' => $invoiceId,
            ];
        });

        $unreadCount = $dbNotifications->where('is_read', false)->count();

        return response()->json([
            'success'      => true,
            'message'      => 'Daftar notifikasi berhasil dimuat.',
            'unread_count' => $unreadCount,
            'data'         => $formatted,
        ]);
    }

    /**
     * Tandai notifikasi tertentu sebagai telah dibaca
     */
    public function markAsRead(Request $request, $id)
    {
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $notif = CustomerNotification::where('customer_id', $customer->id)
            ->where('id', $id)
            ->first();

        if (!$notif) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan.',
            ], 404);
        }

        $notif->markAsRead();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi ditandai telah dibaca.',
            'id'      => $notif->id,
        ]);
    }

    /**
     * Tandai seluruh notifikasi pelanggan sebagai telah dibaca
     */
    public function markAllAsRead(Request $request)
    {
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        CustomerNotification::where('customer_id', $customer->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Seluruh notifikasi berhasil ditandai telah dibaca.',
        ]);
    }

    /**
     * 4. SCREEN 4: PROFIL SCREEN (profile_screen.dart)
     * URL: GET /api/profile (atau GET /api/screens/profile)
     *
     * Mengembalikan data teknis pelanggan, akun langganan,
     * serta tombol intent bantuan WhatsApp dan Email CS.
     */
    public function profile(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau kadaluarsa.'
            ], 401);
        }

        Carbon::setLocale('id');

        $customer->load(['paket', 'preOrder']);
        $pppoe = $customer->pppoe;

        $paket = $customer->paket;
        $tanggalAktifStr = ($pppoe && $pppoe->tanggal_aktif)
            ? ($pppoe->tanggal_aktif instanceof \DateTimeInterface ? $pppoe->tanggal_aktif->format('Y-m-d') : substr((string)$pppoe->tanggal_aktif, 0, 10))
            : ($customer->created_at ? $customer->created_at->format('Y-m-d') : null);

        $isIsolir = (bool) ($pppoe && $pppoe->is_isolir);
        $statusAkun = $isIsolir ? 'isolir' : ($customer->trashed() ? 'non_aktif' : 'aktif');
        $statusAkunBadge = $isIsolir ? 'Terisolir' : ($customer->trashed() ? 'Non-Aktif' : 'Aktif');

        // Kontak Customer Service & Support
        $waAdmin = Setting::get('support_whatsapp', env('SUPPORT_WHATSAPP', '6282191285271'));
        $emailAdmin = Setting::get('support_email', env('SUPPORT_EMAIL', 'Hersapms@erlangga.net'));
        $waTemplate = "Halo Admin, saya mengalami kendala lupa kata sandi/akun Aplikasi Erlangga Network (" . ($customer->id_pelanggan ?? $customer->nama_lengkap) . ").";

        return response()->json([
            'success' => true,
            'message' => 'Data profil pelanggan berhasil dimuat.',
            'data'    => [
                // Field identitas pelanggan
                'id'             => $customer->id,
                'id_pelanggan'   => (string) $customer->id_pelanggan,
                'nama_lengkap'   => (string) $customer->nama_lengkap,
                'no_whatsapp'    => (string) ($customer->no_whatsapp ?? '-'),
                'email'          => (string) ($customer->preOrder?->alamat_email ?? '-'),
                'alamat_lengkap' => (string) ($customer->alamat_lengkap ?? '-'),

                // Field teknis perangkat & status akun
                'pppoe_username' => (string) ($customer->pppoe_username ?? '-'),
                'sn_perangkat'   => (string) ($customer->sn_perangkat ?? '-'),
                'jenis_modem'    => (string) ($customer->jenis_modem ?? '-'),
                'tanggal_aktif'  => $tanggalAktifStr,
                'status_akun'    => $statusAkun,

                // Field paket langganan
                'nama_paket'     => (string) ($paket->nama_paket ?? 'Paket Internet Unlimited'),
                'kecepatan'      => (string) ($paket->kecepatan ?? '-'),
                'harga'          => (float) ($paket->harga ?? 0),
                'total_harga'    => (float) ($paket->total_harga ?? $paket->harga ?? 0),

                // Kontak Bantuan (Intent WA & Mailto)
                'support'        => [
                    'whatsapp'         => $waAdmin,
                    'whatsapp_message' => $waTemplate,
                    'whatsapp_url'     => 'https://wa.me/' . preg_replace('/[^0-9]/', '', $waAdmin) . '?text=' . rawurlencode($waTemplate),
                    'email'            => $emailAdmin,
                    'email_subject'    => 'Bantuan Layanan Internet - ' . ($customer->id_pelanggan ?? $customer->nama_lengkap),
                ],
            ],
        ]);
    }

    /**
     * Format detik menjadi durasi yang mudah dibaca (e.g. 5j 12m)
     */
    protected function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds . 'd';
        }
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        $out = [];
        if ($days > 0) $out[] = $days . 'h';
        if ($hours > 0) $out[] = $hours . 'j';
        if ($minutes > 0) $out[] = $minutes . 'm';

        return implode(' ', $out) ?: '1m';
    }
}

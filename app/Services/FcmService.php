<?php

namespace App\Services;

use App\Models\BroadcastNotification;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    /**
     * Cek apakah fitur Firebase FCM diaktifkan.
     */
    public function isEnabled(): bool
    {
        $val = Setting::get('firebase_enabled', config('services.firebase.enabled', '1'));
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Ambil Firebase Project ID.
     */
    public function getProjectId(): ?string
    {
        $projectId = Setting::get('firebase_project_id', config('services.firebase.project_id', env('FIREBASE_PROJECT_ID')));
        if (!empty($projectId)) {
            return trim($projectId);
        }

        $sa = $this->getServiceAccount();
        return $sa['project_id'] ?? null;
    }

    /**
     * Ambil array data Google Service Account (credentials).
     */
    public function getServiceAccount(): ?array
    {
        $raw = Setting::get('firebase_service_account', config('services.firebase.credentials_json'));

        // Jika tersimpan sebagai path file atau path default di storage
        if (empty($raw)) {
            $defaultFile = config('services.firebase.credentials_file', storage_path('app/firebase_credentials.json'));
            if (file_exists($defaultFile)) {
                $raw = file_get_contents($defaultFile);
            }
        } elseif (is_string($raw) && file_exists($raw)) {
            $raw = file_get_contents($raw);
        }

        if (empty($raw)) {
            return null;
        }

        if (is_array($raw)) {
            return $raw;
        }

        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    /**
     * Ambil Google OAuth2 Access Token untuk FCM HTTP v1 API.
     */
    public function getOAuth2AccessToken(?array $customSa = null): ?string
    {
        if ($customSa === null) {
            $cachedToken = Cache::get('fcm_oauth2_access_token');
            if (!empty($cachedToken)) {
                return $cachedToken;
            }
        }

        $sa = $customSa ?? $this->getServiceAccount();
        if (!$sa || empty($sa['client_email']) || empty($sa['private_key'])) {
            return null;
        }

        try {
            $now = time();
            $header = ['alg' => 'RS256', 'typ' => 'JWT'];
            $payload = [
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ];

            $base64UrlHeader = $this->base64UrlEncode(json_encode($header));
            $base64UrlPayload = $this->base64UrlEncode(json_encode($payload));
            $signatureInput = "{$base64UrlHeader}.{$base64UrlPayload}";

            $signature = '';
            $privateKey = openssl_pkey_get_private($sa['private_key']);
            if (!$privateKey) {
                Log::error('FCM: Private key service account tidak valid.');
                return null;
            }

            openssl_sign($signatureInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $base64UrlSignature = $this->base64UrlEncode($signature);

            $jwt = "{$signatureInput}.{$base64UrlSignature}";

            $response = Http::asForm()->timeout(10)->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if ($response->successful()) {
                $tokenData = $response->json();
                $accessToken = $tokenData['access_token'] ?? null;
                $expiresIn = (int) ($tokenData['expires_in'] ?? 3600);

                if ($accessToken) {
                    Cache::put('fcm_oauth2_access_token', $accessToken, max(300, $expiresIn - 300));
                    return $accessToken;
                }
            } else {
                Log::error('FCM OAuth2 Token Error: ' . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error('FCM OAuth2 Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Kirim pesan notifikasi push ke satu token FCM.
     */
    public function sendToToken(string $token, string $title, string $body, array $data = []): array
    {
        if (!$this->isEnabled()) {
            return ['success' => false, 'message' => 'Notifikasi Firebase FCM dinonaktifkan di pengaturan.'];
        }

        $cleanToken = trim($token);
        if (empty($cleanToken)) {
            return ['success' => false, 'message' => 'Token FCM kosong.'];
        }

        // 1. Coba gunakan Google FCM HTTP v1 API
        $accessToken = $this->getOAuth2AccessToken();
        $projectId = $this->getProjectId();

        if ($accessToken && $projectId) {
            try {
                $url = "https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send";

                // Format string data values untuk FCM HTTP v1 spec
                $stringData = [];
                foreach ($data as $k => $v) {
                    $stringData[(string) $k] = is_scalar($v) ? (string) $v : json_encode($v);
                }

                $payload = [
                    'message' => [
                        'token' => $cleanToken,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'data' => array_merge([
                            'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        ], $stringData),
                        'android' => [
                            'priority' => 'high',
                            'notification' => [
                                'sound' => 'default',
                                'channel_id' => 'erlangga_net_channel',
                                'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                            ],
                        ],
                        'apns' => [
                            'payload' => [
                                'aps' => [
                                    'sound' => 'default',
                                    'badge' => 1,
                                ],
                            ],
                        ],
                    ],
                ];

                $response = Http::withHeaders([
                    'Authorization' => "Bearer {$accessToken}",
                    'Content-Type'  => 'application/json',
                ])->timeout(10)->post($url, $payload);

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'message' => 'Notifikasi FCM berhasil terkirim via HTTP v1.',
                        'result'  => $response->json(),
                    ];
                }

                Log::warning("FCM HTTP v1 Error ({$response->status()}): " . $response->body());
            } catch (\Throwable $e) {
                Log::error('FCM HTTP v1 Exception: ' . $e->getMessage());
            }
        }

        // 2. Fallback: Coba gunakan FCM Legacy API jika Server Key tersedia
        $serverKey = Setting::get('firebase_server_key', config('services.firebase.server_key', env('FCM_SERVER_KEY')));
        if (!empty($serverKey)) {
            try {
                $legacyResponse = Http::withHeaders([
                    'Authorization' => 'key=' . trim($serverKey),
                    'Content-Type'  => 'application/json',
                ])->timeout(10)->post('https://fcm.googleapis.com/fcm/send', [
                    'to'           => $cleanToken,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                    ],
                    'data'         => $data,
                    'priority'     => 'high',
                ]);

                if ($legacyResponse->successful()) {
                    return [
                        'success' => true,
                        'message' => 'Notifikasi FCM berhasil terkirim via Legacy API.',
                        'result'  => $legacyResponse->json(),
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Gagal mengirim via FCM Legacy API: ' . $legacyResponse->body(),
                ];
            } catch (\Throwable $e) {
                return ['success' => false, 'message' => 'Exception FCM Legacy: ' . $e->getMessage()];
            }
        }

        return [
            'success' => false,
            'message' => 'Kredensial Firebase (Service Account JSON atau Server Key) belum terkonfigurasi di Pengaturan Sistem.',
        ];
    }

    /**
     * Kirim notifikasi ke pelanggan (simpan ke database dan kirim push FCM jika ada token).
     */
    public function sendToCustomer(Customer $customer, string $title, string $body, array $data = [], string $type = 'general'): array
    {
        // 1. Simpan selalu ke tabel customer_notifications (agar muncul di in-app notification list mobile)
        $notif = CustomerNotification::create([
            'customer_id' => $customer->id,
            'title'       => $title,
            'body'        => $body,
            'type'        => $type,
            'data'        => $data,
            'is_read'     => false,
        ]);

        $pushResult = ['success' => false, 'message' => 'Pelanggan belum menghubungkan perangkat/token FCM.'];

        // 2. Kirim push notification ke perangkat mobile pelanggan jika token_fcm tersedia
        if (!empty($customer->fcm_token)) {
            $pushResult = $this->sendToToken($customer->fcm_token, $title, $body, array_merge($data, [
                'notification_id' => $notif->id,
                'type'            => $type,
            ]));
        }

        return [
            'notification_id' => $notif->id,
            'push_sent'       => $pushResult['success'],
            'push_result'     => $pushResult,
        ];
    }

    /**
     * Kirim notifikasi pengingat faktur jatuh tempo.
     */
    public function sendInvoiceDueReminder(Invoice $invoice): array
    {
        $invoice->load(['pppoe.customer', 'pppoe.paket']);
        $customer = $invoice->pppoe?->customer;

        if (!$customer) {
            return ['success' => false, 'message' => 'Pelanggan tidak ditemukan untuk faktur ini.'];
        }

        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->translatedFormat('d F Y') : 'segera';
        $totalFormatted = 'Rp ' . number_format($invoice->total, 0, ',', '.');

        $title = 'Tagihan Internet Jatuh Tempo';
        $body = "Tagihan #{$invoice->invoice_number} sebesar {$totalFormatted} jatuh tempo pada {$dueDate}. Mohon lakukan pembayaran untuk kelancaran akses internet Anda.";

        return $this->sendToCustomer($customer, $title, $body, [
            'type'        => 'billing',
            'invoice_id'  => $invoice->id,
            'due_date'    => $invoice->due_date ? Carbon::parse($invoice->due_date)->format('Y-m-d') : null,
            'total'       => $invoice->total,
            'action_url'  => '/riwayat',
        ], 'billing');
    }

    /**
     * Kirim notifikasi konfirmasi pembayaran berhasil / lunas.
     */
    public function sendInvoicePaidNotification(Invoice $invoice): array
    {
        $invoice->load(['pppoe.customer']);
        $customer = $invoice->pppoe?->customer;

        if (!$customer) {
            return ['success' => false, 'message' => 'Pelanggan tidak ditemukan untuk faktur ini.'];
        }

        $totalFormatted = 'Rp ' . number_format($invoice->total, 0, ',', '.');
        $title = 'Pembayaran Berhasil Diterima';
        $body = "Terima kasih! Pembayaran tagihan #{$invoice->invoice_number} sebesar {$totalFormatted} telah berhasil diverifikasi. Layanan internet Anda aktif.";

        return $this->sendToCustomer($customer, $title, $body, [
            'type'        => 'payment',
            'invoice_id'  => $invoice->id,
            'total'       => $invoice->total,
            'action_url'  => '/riwayat',
        ], 'payment');
    }

    /**
     * Kirim broadcast pemberitahuan dari Admin (Pemeliharaan/Maintenance, Pengumuman, Promo).
     */
    public function broadcast(
        string $title,
        string $body,
        string $type = 'maintenance',
        string $targetType = 'all',
        ?int $targetId = null,
        ?int $createdBy = null
    ): array {
        @set_time_limit(180);

        $customersQuery = Customer::query()->whereNull('deleted_at');

        if ($targetType === 'package' && $targetId) {
            $customersQuery->where('paket_id', $targetId);
        } elseif ($targetType === 'customer' && $targetId) {
            $customersQuery->where('id', $targetId);
        }

        $customers = $customersQuery->get();
        $totalTargeted = $customers->count();
        $totalSent = 0;
        $totalFailed = 0;

        $broadcast = BroadcastNotification::create([
            'title'          => $title,
            'body'           => $body,
            'type'           => $type,
            'target_type'    => $targetType,
            'target_id'      => $targetId,
            'total_targeted' => $totalTargeted,
            'created_by'     => $createdBy,
        ]);

        // 1. Bulk insert notifikasi in-app dalam chunk (mencegah bottleneck ratusan query SQL)
        $now = now();
        $notificationData = json_encode([
            'broadcast_id' => $broadcast->id,
            'type'         => $type,
        ]);

        $inAppChunks = [];
        foreach ($customers as $cust) {
            $inAppChunks[] = [
                'customer_id' => $cust->id,
                'title'       => $title,
                'body'        => $body,
                'type'        => $type,
                'data'        => $notificationData,
                'is_read'     => 0,
                'created_at'  => $now,
                'updated_at'  => $now,
            ];

            if (count($inAppChunks) >= 200) {
                CustomerNotification::insert($inAppChunks);
                $inAppChunks = [];
            }
        }
        if (!empty($inAppChunks)) {
            CustomerNotification::insert($inAppChunks);
        }

        // 2. Kirim push notification FCM hanya untuk pelanggan yang memiliki token aktif
        $customersWithToken = $customers->filter(fn($c) => !empty(trim((string)$c->fcm_token)));

        foreach ($customersWithToken as $cust) {
            try {
                $push = $this->sendToToken($cust->fcm_token, $title, $body, [
                    'type'         => $type,
                    'broadcast_id' => $broadcast->id,
                ]);

                if (!empty($push['success'])) {
                    $totalSent++;
                } else {
                    $totalFailed++;
                }
            } catch (\Throwable $e) {
                $totalFailed++;
                Log::warning("Gagal push FCM broadcast ke customer #{$cust->id}: " . $e->getMessage());
            }
        }

        $broadcast->update([
            'total_sent'   => $totalSent,
            'total_failed' => $totalFailed,
        ]);

        return [
            'success'        => true,
            'broadcast_id'   => $broadcast->id,
            'total_targeted' => $totalTargeted,
            'total_sent'     => $totalSent,
            'total_failed'   => $totalFailed,
        ];
    }

    /**
     * Helper URL-safe base64 encode untuk JWT
     */
    private function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}

<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Normalisasi nomor telepon ke format internasional 628xxx (hanya digit)
     */
    public static function normalizePhone(?string $phone): string
    {
        if (empty($phone)) {
            return '';
        }

        // Hapus semua karakter non-digit
        $digits = preg_replace('/[^0-9]/', '', $phone);

        // Jika diawali 08, ganti 0 dengan 62
        if (str_starts_with($digits, '08')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        return $digits;
    }

    /**
     * Masking nomor telepon untuk privasi tampilan (contoh: +62 812-****-7890)
     */
    public static function maskPhone(?string $phone): string
    {
        $normalized = static::normalizePhone($phone);
        if (strlen($normalized) < 8) {
            return $phone ?? '-';
        }

        $start = substr($normalized, 0, 5); // misal: 62812
        $end = substr($normalized, -4);     // misal: 7890

        return "+{$start}-****-{$end}";
    }

    /**
     * Kirim pesan WhatsApp melalui API provider gateway
     */
    public static function sendMessage(string $phone, string $message, array $options = []): array
    {
        $targetPhone = static::normalizePhone($phone);

        if (empty($targetPhone)) {
            return [
                'success' => false,
                'message' => 'Nomor WhatsApp tujuan tidak valid atau kosong.',
            ];
        }

        // Ambil konfigurasi dari database Setting (fallback ke env)
        $apiUrl = Setting::get('whatsapp_api_url', env('WHATSAPP_API_URL', ''));
        $apiKey = Setting::get('whatsapp_api_key', env('WHATSAPP_API_KEY', ''));
        $sender = Setting::get('whatsapp_sender', env('WHATSAPP_SENDER', ''));

        // Jika API Key belum dikonfigurasi, jalankan mode simulasi & catat di Log
        if (empty($apiKey)) {
            Log::info("WhatsAppService [SIMULATION MODE - No API Key] Target: {$targetPhone} | Message:\n{$message}");
            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Pesan diproses (Mode Simulasi / API Key belum diisi di Pengaturan Sistem).',
                'target' => $targetPhone,
            ];
        }

        try {
            // Payload standar yang kompatibel dengan berbagai webhook gateway WhatsApp
            $payload = [
                'phone' => $targetPhone,
                'to' => $targetPhone,
                'target' => $targetPhone,
                'recipient' => $targetPhone,
                'message' => $message,
                'text' => $message,
            ];

            if (!empty($sender)) {
                $payload['sender'] = $sender;
                $payload['device'] = $sender;
            }

            // Headers request
            $headers = [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
                'X-API-KEY' => $apiKey,
                'api-key' => $apiKey,
            ];

            $response = Http::timeout(10)
                ->withHeaders($headers)
                ->post($apiUrl, $payload);

            $body = $response->json();
            $statusCode = $response->status();

            if ($response->successful() || ($body['status'] ?? null) === true || ($body['success'] ?? null) === true) {
                Log::info("WhatsApp message sent successfully to {$targetPhone}");
                return [
                    'success' => true,
                    'simulated' => false,
                    'message' => 'Pesan WhatsApp berhasil dikirim.',
                    'data' => $body,
                ];
            }

            Log::warning("WhatsApp API returned error response ({$statusCode}): " . json_encode($body));
            return [
                'success' => false,
                'message' => $body['message'] ?? 'Gagal mengirim pesan WhatsApp via provider API.',
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsAppService Exception: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Terjadi kendala koneksi ke server WhatsApp API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Kirim Kode OTP Penarikan Dana Super Admin ke WhatsApp
     */
    public static function sendWithdrawalOtp(string $phone, string $otp, array $details): array
    {
        $amountFormatted = number_format($details['amount'] ?? 0, 0, ',', '.');
        $bankName = $details['bank_name'] ?? ($details['bank_code'] ?? 'Bank');
        $bankCode = $details['bank_code'] ?? '';
        $accountNumber = $details['account_number'] ?? '-';
        $accountHolder = strtoupper($details['account_holder_name'] ?? '-');
        $timeNow = date('d/m/Y H:i:s');

        $message = "🔐 *KODE OTP PENARIKAN SALDO XENDIT*\n";
        $message .= "*ERLANGGA NET*\n\n";
        $message .= "Kode OTP Verifikasi Anda:\n";
        $message .= "👉 *{$otp}*\n\n";
        $message .= "📋 *Rincian Penarikan Dana:*\n";
        $message .= "• Nominal : *Rp {$amountFormatted}*\n";
        $message .= "• Bank Tujuan : *{$bankName} ({$bankCode})*\n";
        $message .= "• No. Rekening : *{$accountNumber}*\n";
        $message .= "• Atas Nama : *{$accountHolder}*\n";
        $message .= "• Waktu Pengajuan : *{$timeNow} WIB*\n\n";
        $message .= "⚠️ *PERINGATAN KEAMANAN:*\n";
        $message .= "Kode OTP ini bersifat RAHASIA dan berlaku selama 5 menit. JANGAN berikan kode ini kepada siapapun termasuk pihak yang mengaku tim Erlangga Net atau Xendit.\n";

        return static::sendMessage($phone, $message, [
            'type' => 'otp',
            'amount' => $details['amount'] ?? 0,
        ]);
    }
}

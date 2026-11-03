<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\WhatsAppMessage;
use App\Models\Invoice;
use App\Models\BankAccount;
use Carbon\Carbon;
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

        // Ambil konfigurasi dari database Setting (fallback ke env atau default api.co.id)
        $apiUrl = trim(Setting::get('whatsapp_api_url', env('WHATSAPP_API_URL', 'https://chat.api.co.id/api/v1/public/messages/send')));
        $apiKey = trim(Setting::get('whatsapp_api_key', env('WHATSAPP_API_KEY', '')));
        $sender = trim(Setting::get('whatsapp_sender', env('WHATSAPP_SENDER', '')));

        // Normalisasi URL jika menggunakan api.co.id
        if (str_contains($apiUrl, 'api.co.id') && !str_ends_with($apiUrl, '/messages/send')) {
            $apiUrl = rtrim($apiUrl, '/') . '/messages/send';
        }

        $recordMessage = $options['record'] ?? true;
        $type = $options['type'] ?? 'text';
        $senderId = $options['sender_id'] ?? auth()->id();
        $pppoeId = $options['pppoe_id'] ?? null;
        $customerId = $options['customer_id'] ?? null;
        $customerName = $options['customer_name'] ?? null;
        $metadata = $options['metadata'] ?? [];

        // Jika API Key atau API URL belum dikonfigurasi, beri peringatan jelas dan jangan kirim
        if (empty($apiKey) || empty($apiUrl)) {
            Log::warning("WhatsAppService [API KEY EMPTY] Target: {$targetPhone} | Settings whatsapp_api_key or whatsapp_api_url is not configured!");

            $msgRecord = null;
            if ($recordMessage) {
                $msgRecord = WhatsAppMessage::create([
                    'phone' => $targetPhone,
                    'customer_name' => $customerName,
                    'customer_id' => $customerId,
                    'pppoe_id' => $pppoeId,
                    'sender_id' => $senderId,
                    'direction' => 'outbound',
                    'type' => $type,
                    'message' => $message,
                    'status' => 'failed',
                    'metadata' => array_merge($metadata, [
                        'error' => 'API Key WhatsApp belum diisi di Pengaturan Gateway',
                        'simulated' => true
                    ]),
                ]);
            }

            return [
                'success' => false,
                'simulated' => true,
                'message' => 'Gagal mengirim ke WhatsApp: API Key WhatsApp belum diisi atau disimpan di menu Pengaturan > WhatsApp Gateway.',
                'target' => $targetPhone,
                'record' => $msgRecord,
            ];
        }

        try {
            // Deteksi provider api.co.id Chat API
            $isApiCoId = str_contains($apiUrl, 'api.co.id');

            if ($isApiCoId) {
                // Format spesifikasi resmi chat.api.co.id
                // Sertakan phone_number, recipient_phone, content, dan message agar kompatibel dengan seluruh versi API
                $payload = [
                    'phone_number' => $targetPhone,
                    'recipient_phone' => $targetPhone,
                    'channel' => 'whatsapp',
                    'message_type' => 'text',
                    'content' => $message,
                    'message' => $message,
                ];

                if (!empty($sender)) {
                    $payload['phone_number_id'] = $sender;
                    $payload['sender'] = $sender;
                }
            } else {
                // Payload gateway umum / webhook lain (Fonnte, Wablas, MPWA)
                $payload = [
                    'phone_number' => $targetPhone,
                    'phone' => $targetPhone,
                    'to' => $targetPhone,
                    'target' => $targetPhone,
                    'recipient' => $targetPhone,
                    'channel' => 'whatsapp',
                    'message_type' => 'text',
                    'content' => $message,
                    'message' => $message,
                    'text' => $message,
                ];

                if (!empty($sender)) {
                    $payload['sender'] = $sender;
                    $payload['device'] = $sender;
                    $payload['phone_number_id'] = $sender;
                }
            }

            // Headers request
            $headers = [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $apiKey,
            ];

            Log::info("WhatsApp API Dispatching to {$apiUrl} for {$targetPhone} | Payload: " . json_encode($payload));

            $response = Http::timeout(15)
                ->withHeaders($headers)
                ->post($apiUrl, $payload);

            $body = $response->json() ?? [];
            $statusCode = $response->status();

            Log::info("WhatsApp API Response ({$statusCode}): " . json_encode($body));

            $isSuccess = $response->successful()
                || ($body['status'] ?? null) === 'success'
                || ($body['status'] ?? null) === true
                || ($body['success'] ?? null) === true;

            if ($isSuccess) {
                Log::info("WhatsApp message sent successfully to {$targetPhone} via {$apiUrl}");

                $msgRecord = null;
                if ($recordMessage) {
                    $msgRecord = WhatsAppMessage::create([
                        'phone' => $targetPhone,
                        'customer_name' => $customerName,
                        'customer_id' => $customerId,
                        'pppoe_id' => $pppoeId,
                        'sender_id' => $senderId,
                        'direction' => 'outbound',
                        'type' => $type,
                        'message' => $message,
                        'status' => 'sent',
                        'metadata' => array_merge($metadata, ['response' => $body]),
                    ]);
                }

                return [
                    'success' => true,
                    'simulated' => false,
                    'message' => 'Pesan WhatsApp berhasil dikirim.',
                    'data' => $body,
                    'record' => $msgRecord,
                ];
            }

            // Tangani error dari response API
            $errMsg = $body['message'] ?? ($body['error'] ?? ($body['meta']['message'] ?? null));
            if (empty($errMsg)) {
                $errMsg = "HTTP {$statusCode}: Gagal mengirim pesan ke WhatsApp.";
            }
            if (is_array($errMsg)) {
                $errMsg = json_encode($errMsg);
            }

            Log::warning("WhatsApp API returned error response ({$statusCode}): " . json_encode($body));

            if ($recordMessage) {
                WhatsAppMessage::create([
                    'phone' => $targetPhone,
                    'customer_name' => $customerName,
                    'customer_id' => $customerId,
                    'pppoe_id' => $pppoeId,
                    'sender_id' => $senderId,
                    'direction' => 'outbound',
                    'type' => $type,
                    'message' => $message,
                    'status' => 'failed',
                    'metadata' => array_merge($metadata, ['error' => $body, 'status_code' => $statusCode]),
                ]);
            }

            return [
                'success' => false,
                'message' => $errMsg,
                'response' => $body,
                'status_code' => $statusCode,
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsAppService Exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());

            if ($recordMessage) {
                WhatsAppMessage::create([
                    'phone' => $targetPhone,
                    'customer_name' => $customerName,
                    'customer_id' => $customerId,
                    'pppoe_id' => $pppoeId,
                    'sender_id' => $senderId,
                    'direction' => 'outbound',
                    'type' => $type,
                    'message' => $message,
                    'status' => 'failed',
                    'metadata' => array_merge($metadata, ['exception' => $e->getMessage()]),
                ]);
            }

            return [
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat mengirim pesan: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Format rincian tagihan invoice resmi untuk WhatsApp
     */
    public static function formatInvoiceText(Invoice $invoice): string
    {
        $invoice->loadMissing(['pppoe.preOrder', 'pppoe.paket']);

        $pppoe = $invoice->pppoe;
        $preOrder = $pppoe ? $pppoe->preOrder : null;
        $customerName = $preOrder->nama_lengkap ?? ($pppoe->username ?? 'Pelanggan');
        $namaPaket = $pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : 'Internet';
        $totalFormatted = number_format($invoice->total, 0, ',', '.');
        $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->translatedFormat('d F Y') : '-';
        $periode = Carbon::parse($invoice->created_at)->translatedFormat('F Y');

        $text = "*TAGIHAN INTERNET ERLANGGA NET*\n";
        $text .= "--------------------------------------\n";
        $text .= "Yth. Bpk/Ibu *{$customerName}*,\n\n";
        $text .= "Berikut rincian tagihan layanan internet Anda:\n";
        $text .= "• No. Invoice : `{$invoice->invoice_number}`\n";
        $text .= "• ID Pelanggan: `{$pppoe->username}`\n";
        $text .= "• Paket       : {$namaPaket}\n";
        $text .= "• Periode     : {$periode}\n";
        $text .= "• Total       : *Rp {$totalFormatted}*\n";
        $text .= "• Jatuh Tempo : *{$dueDate}*\n\n";

        // Rekening Pembayaran
        $bankAccounts = BankAccount::orderBy('is_default', 'desc')->get();
        if ($bankAccounts->isNotEmpty()) {
            $text .= "*Pembayaran Transfer Bank:*\n";
            foreach ($bankAccounts as $bank) {
                $holder = $bank->account_holder_name ?: 'Erlangga Net';
                $text .= "• {$bank->bank_name}: `{$bank->account_number}` a/n {$holder}\n";
            }
            $text .= "\n";
        }

        // Link Pembayaran Online Xendit jika tersedia
        if (!empty($invoice->xendit_invoice_url)) {
            $text .= "*Pembayaran Online (QRIS / Virtual Account):*\n";
            $text .= "{$invoice->xendit_invoice_url}\n\n";
        }

        $text .= "Mohon melakukan pembayaran sebelum tanggal jatuh tempo. Abaikan pesan ini jika Anda telah menyelesaikan pembayaran.\n\n";
        $text .= "Terima kasih,\n*Erlangga Net*";

        return $text;
    }

    /**
     * Daftar template pesan cepat siap pakai
     */
    public static function getQuickTemplates(): array
    {
        return [
            [
                'id' => 'invoice_bill',
                'title' => 'Tagihan Baru (Invoice)',
                'icon' => 'fa-file-invoice-dollar',
                'description' => 'Pemberitahuan invoice bulanan dan informasi pembayaran',
                'body' => "*TAGIHAN INTERNET ERLANGGA NET*\n\nYth. Bpk/Ibu *{nama}*,\nTagihan layanan internet Anda periode *{periode}* telah diterbitkan:\n\n• No. Invoice: *{nomor_invoice}*\n• Total: *Rp {total}*\n• Jatuh Tempo: *{jatuh_tempo}*\n\nMohon lakukan pembayaran sebelum jatuh tempo. Terima kasih.",
            ],
            [
                'id' => 'reminder_due',
                'title' => 'Pengingat Jatuh Tempo (H-1)',
                'icon' => 'fa-clock',
                'description' => 'Pemberitahuan tagihan yang akan jatuh tempo',
                'body' => "*PENGINGAT JATUH TEMPO - ERLANGGA NET*\n\nYth. Bpk/Ibu *{nama}*,\nKami mengingatkan bahwa tagihan internet Anda sebesar *Rp {total}* akan jatuh tempo pada *{jatuh_tempo}*.\n\nMohon selesaikan pembayaran untuk menjaga kelancaran koneksi internet Anda. Terima kasih.",
            ],
            [
                'id' => 'isolated_notice',
                'title' => 'Pemberitahuan Isolir',
                'icon' => 'fa-ban',
                'description' => 'Pemberitahuan penonaktifan sementara akibat lewat jatuh tempo',
                'body' => "*PEMBERITAHUAN ISOLIR LAYANAN*\n\nYth. Bpk/Ibu *{nama}*,\nLayanan internet akun *{username}* saat ini terisolir karena melewati tanggal jatuh tempo ({jatuh_tempo}).\n\nTotal Tagihan: *Rp {total}*\n\nSilakan selesaikan pembayaran untuk mengaktifkan kembali koneksi internet Anda. Terima kasih.",
            ],
            [
                'id' => 'payment_success',
                'title' => 'Konfirmasi Pembayaran Lunas',
                'icon' => 'fa-circle-check',
                'description' => 'Konfirmasi bahwa pembayaran telah diterima',
                'body' => "*PEMBAYARAN DITERIMA - ERLANGGA NET*\n\nTerima kasih Bpk/Ibu *{nama}*,\nPembayaran tagihan internet sebesar *Rp {total}* (Invoice: *{nomor_invoice}*) telah kami terima dan diverifikasi.\n\nStatus layanan: *AKTIF*. Terima kasih.",
            ],
            [
                'id' => 'maintenance_info',
                'title' => 'Informasi Pemeliharaan Jaringan',
                'icon' => 'fa-screwdriver-wrench',
                'description' => 'Pemberitahuan jadwal pemeliharaan jaringan',
                'body' => "*INFORMASI PEMELIHARAAN JARINGAN*\n\nYth. Pelanggan Erlangga Net,\nKami informasikan adanya jadwal pemeliharaan jaringan pada:\n\nWaktu: *{waktu_pemeliharaan}*\nEstimasi Durasi: *{estimasi_durasi}*\n\nSelama pemeliharaan berlangsung, akses internet mungkin mengalami penurunan kualitas sementara. Kami mohon maaf atas ketidaknyamanan ini.",
            ],
            [
                'id' => 'welcome_new',
                'title' => 'Aktivasi Pelanggan Baru',
                'icon' => 'fa-user-check',
                'description' => 'Pemberitahuan aktivasi setelah instalasi selesai',
                'body' => "*AKTIVASI LAYANAN - ERLANGGA NET*\n\nYth. Bpk/Ibu *{nama}*,\nLayanan internet Anda telah aktif dengan rincian:\n\n• ID Akun: `{username}`\n• Paket Layanan: *{paket}*\n\nJika membutuhkan bantuan layanan atau informasi tagihan, silakan hubungi kontak ini. Terima kasih.",
            ],
        ];
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

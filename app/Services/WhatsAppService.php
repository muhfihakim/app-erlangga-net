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

        // Ambil konfigurasi dari database Setting (fallback ke env)
        $apiUrl = Setting::get('whatsapp_api_url', env('WHATSAPP_API_URL', ''));
        $apiKey = Setting::get('whatsapp_api_key', env('WHATSAPP_API_KEY', ''));
        $sender = Setting::get('whatsapp_sender', env('WHATSAPP_SENDER', ''));

        $recordMessage = $options['record'] ?? true;
        $type = $options['type'] ?? 'text';
        $senderId = $options['sender_id'] ?? auth()->id();
        $pppoeId = $options['pppoe_id'] ?? null;
        $customerId = $options['customer_id'] ?? null;
        $customerName = $options['customer_name'] ?? null;
        $metadata = $options['metadata'] ?? [];

        // Jika API Key atau API URL belum dikonfigurasi, jalankan mode simulasi & catat di Log
        if (empty($apiKey) || empty($apiUrl)) {
            Log::info("WhatsAppService [SIMULATION MODE - Incomplete Settings] Target: {$targetPhone} | Message:\n{$message}");
            
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
                    'status' => 'simulated',
                    'metadata' => array_merge($metadata, ['simulated' => true]),
                ]);
            }

            return [
                'success' => true,
                'simulated' => true,
                'message' => 'Pesan diproses (Mode Simulasi / API URL atau API Key belum diisi di Pengaturan Sistem).',
                'target' => $targetPhone,
                'record' => $msgRecord,
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
                'message' => $body['message'] ?? 'Gagal mengirim pesan WhatsApp via provider API.',
                'response' => $body,
            ];
        } catch (\Throwable $e) {
            Log::error("WhatsAppService Exception: " . $e->getMessage());

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
                'message' => 'Terjadi kendala koneksi ke server WhatsApp API: ' . $e->getMessage(),
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

        $text = "📡 *TAGIHAN INTERNET ERLANGGA NET*\n";
        $text .= "--------------------------------------\n";
        $text .= "Halo Bpk/Ibu *{$customerName}*,\n\n";
        $text .= "Berikut kami sampaikan rincian tagihan layanan internet Anda:\n\n";
        $text .= "• *No. Invoice* : `{$invoice->invoice_number}`\n";
        $text .= "• *ID / Akun*   : `{$pppoe->username}`\n";
        $text .= "• *Paket Layanan*: {$namaPaket}\n";
        $text .= "• *Periode*     : {$periode}\n";
        $text .= "• *Total Tagihan*: *Rp {$totalFormatted}*\n";
        $text .= "• *Jatuh Tempo* : *{$dueDate}*\n\n";

        // Rekening Pembayaran
        $bankAccounts = BankAccount::orderBy('is_default', 'desc')->get();
        if ($bankAccounts->isNotEmpty()) {
            $text .= "💳 *Metode Pembayaran Transfer Bank:*\n";
            foreach ($bankAccounts as $bank) {
                $holder = $bank->account_holder_name ?: 'Erlangga Net';
                $text .= "• *{$bank->bank_name}*: `{$bank->account_number}` a/n {$holder}\n";
            }
            $text .= "\n";
        }

        // Link Pembayaran Online Xendit jika tersedia
        if (!empty($invoice->xendit_invoice_url)) {
            $text .= "🔗 *Link Bayar Otomatis (QRIS / VA / E-Wallet):*\n";
            $text .= "{$invoice->xendit_invoice_url}\n\n";
        }

        $text .= "Mohon melakukan pembayaran sebelum tanggal jatuh tempo agar layanan internet Anda tetap aktif tanpa kendala.\n\n";
        $text .= "Abaikan pesan ini jika Anda telah melakukan pembayaran.\n";
        $text .= "Terima kasih atas kepercayaannya bersama *Erlangga Net*! 🙏";

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
                'description' => 'Pemberitahuan invoice bulanan dan nomor rekening / link bayar',
                'body' => "📡 *TAGIHAN INTERNET ERLANGGA NET*\n\nHalo Bpk/Ibu *{nama}*,\nTagihan internet Anda untuk periode *{periode}* telah diterbitkan:\n\n• No. Invoice: *{nomor_invoice}*\n• Total: *Rp {total}*\n• Jatuh Tempo: *{jatuh_tempo}*\n\nSilakan lakukan pembayaran sebelum tanggal jatuh tempo. Terima kasih. 🙏",
            ],
            [
                'id' => 'reminder_due',
                'title' => 'Pengingat Jatuh Tempo (H-1)',
                'icon' => 'fa-clock-rotate-left',
                'description' => 'Peringatan tagihan yang akan segera jatuh tempo besok',
                'body' => "⏰ *PENGINGAT JATUH TEMPO ERLANGGA NET*\n\nYth. Bpk/Ibu *{nama}*,\nKami mengingatkan bahwa tagihan internet Anda sebesar *Rp {total}* akan jatuh tempo pada *{jatuh_tempo}*.\n\nMohon selesaikan pembayaran untuk menghindari pemutusan/isolir koneksi otomatis. Terima kasih! 🙏",
            ],
            [
                'id' => 'isolated_notice',
                'title' => 'Pemberitahuan Isolir',
                'icon' => 'fa-ban',
                'description' => 'Pemberitahuan penonaktifan sementara karena lewat jatuh tempo',
                'body' => "⚠️ *PEMBERITAHUAN ISOLIR SEMENTARA*\n\nYth. Bpk/Ibu *{nama}*,\nLayanan internet Anda untuk akun *{username}* saat ini dinonaktifkan sementara (terisolir) karena telah melewati tanggal jatuh tempo ({jatuh_tempo}).\n\nTotal Tagihan: *Rp {total}*\n\nSilakan segera lakukan pembayaran untuk membuka kembali akses internet Anda secara otomatis. Terima kasih.",
            ],
            [
                'id' => 'payment_success',
                'title' => 'Konfirmasi Pembayaran Lunas',
                'icon' => 'fa-circle-check',
                'description' => 'Kwitansi digital tanda pembayaran berhasil diterima',
                'body' => "✅ *PEMBAYARAN DITERIMA - ERLANGGA NET*\n\nTerima kasih Bpk/Ibu *{nama}*,\nPembayaran internet Anda sebesar *Rp {total}* untuk invoice *{nomor_invoice}* telah kami terima dan diverifikasi.\n\nLayanan internet Anda berstatus *AKTIF*. Selamat menikmati layanan internet kami! 🚀",
            ],
            [
                'id' => 'maintenance_info',
                'title' => 'Info Pemeliharaan Jaringan',
                'icon' => 'fa-wrench',
                'description' => 'Pengumuman jadwal pemeliharaan jaringan kabel/perangkat',
                'body' => "🛠️ *INFORMASI PEMELIHARAAN JARINGAN*\n\nYth. Pelanggan Setia Erlangga Net,\nKami informasikan bahwa akan dilakukan kegiatan pemeliharaan jaringan pada:\n\n📅 Waktu: *{waktu_pemeliharaan}*\n⏳ Estimasi: *{estimasi_durasi}*\n\nSelama proses pemeliharaan, koneksi internet Anda mungkin mengalami gangguan sementara. Kami memohon maaf atas ketidaknyamanan ini.",
            ],
            [
                'id' => 'welcome_new',
                'title' => 'Sambutan Pelanggan Baru',
                'icon' => 'fa-handshake',
                'description' => 'Pesan selamat datang setelah instalasi dan aktivasi selesai',
                'body' => "🎉 *SELAMAT BERGABUNG DI ERLANGGA NET*\n\nHalo Bpk/Ibu *{nama}*,\nSelamat! Layanan internet Anda telah aktif dengan detail:\n\n• Username: `{$username}`\n• Paket: *{paket}*\n\nJika membutuhkan bantuan teknis atau informasi layanan, silakan hubungi Customer Service kami di nomor ini. Selamat berselancar! 🌐",
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

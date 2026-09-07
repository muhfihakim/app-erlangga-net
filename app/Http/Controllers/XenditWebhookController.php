<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Pppoe;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class XenditWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        $xenditXCallbackToken = Setting::get('xendit_webhook_token', config('services.xendit.webhook_token'));
        $reqHeaders = $request->header('x-callback-token');

        if ($reqHeaders !== $xenditXCallbackToken) {
            Log::warning('Xendit Webhook: Invalid callback token attempt', [
                'ip' => $request->ip(),
                'received_token' => $reqHeaders ? substr($reqHeaders, 0, 4) . '***' : 'null'
            ]);
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $data = $request->all();
        $externalId = $data['external_id'] ?? null;
        $xenditId = $data['id'] ?? null;
        $status = strtoupper($data['status'] ?? '');

        Log::info("Xendit Webhook Received: {$externalId} | Status: {$status} | Amount: " . ($data['paid_amount'] ?? ($data['amount'] ?? 0)));

        // 1. Cari faktur berdasarkan nomor invoice atau Xendit Invoice ID
        $invoice = null;
        if ($externalId) {
            $invoice = Invoice::where('invoice_number', $externalId)->first();
        }
        if (!$invoice && $xenditId) {
            $invoice = Invoice::where('xendit_invoice_id', $xenditId)->first();
        }

        // 2. AUTO-RECOVERY: Jika faktur belum tercatat di database, buatkan otomatis on-the-fly
        if (!$invoice && $externalId) {
            try {
                $pppoe = null;

                // A. Cek pola ID PPPoE dalam external_id (contoh: INV-20260907-0042-892)
                if (preg_match('/INV-\d{8}-(\d+)-\d+/', $externalId, $matches)) {
                    $pppoeId = (int) $matches[1];
                    $pppoe = Pppoe::with('paket')->find($pppoeId);
                }

                // B. Cek berdasarkan username PPPoE
                if (!$pppoe) {
                    $pppoe = Pppoe::with('paket')->where('username', $externalId)->first();
                }

                // C. Cek berdasarkan nomor WhatsApp pelanggan
                if (!$pppoe) {
                    $pppoe = Pppoe::with('paket')
                        ->whereHas('preOrder', function ($q) use ($externalId) {
                            $q->where('no_whatsapp', $externalId);
                        })->first();
                }

                // D. Jika akun pelanggan ditemukan, buatkan faktur on-the-fly
                if ($pppoe && $pppoe->paket) {
                    $paidAmount = (float) ($data['paid_amount'] ?? ($data['amount'] ?? $pppoe->paket->total_harga));
                    $amount = (float) $pppoe->paket->harga;
                    $tax = ($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($pppoe->paket->pajak ?? 0);
                    $discount = ($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($pppoe->paket->diskon ?? 0);
                    $total = max(0, $amount + $tax - $discount);

                    if ($paidAmount > 0 && abs($paidAmount - $total) > 100) {
                        $total = $paidAmount;
                    }

                    $invoice = Invoice::create([
                        'pppoe_id' => $pppoe->id,
                        'invoice_number' => $externalId,
                        'amount' => $amount,
                        'tax' => $tax,
                        'discount' => $discount,
                        'total' => $total,
                        'due_date' => now()->addDays(7)->format('Y-m-d'),
                        'status' => 'unpaid',
                        'xendit_invoice_id' => $xenditId,
                        'xendit_invoice_url' => $data['invoice_url'] ?? null,
                    ]);

                    Log::info("Xendit Auto-Recovery: Faktur {$externalId} berhasil dibuat on-the-fly untuk {$pppoe->username}");
                }
            } catch (\Throwable $e) {
                Log::error("Xendit Auto-Recovery Creation Error: " . $e->getMessage());
            }
        }

        if (!$invoice) {
            Log::warning("Xendit Webhook: Faktur tidak ditemukan & tidak dapat dipulihkan untuk external_id: {$externalId}");
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        // 3. Proses Pembayaran Lunas (Anti-Crash & Resilient)
        if ($status === 'PAID' || $status === 'SETTLED') {
            try {
                $channel = !empty($data['payment_channel']) ? strtoupper($data['payment_channel']) : (!empty($data['payment_method']) ? strtoupper($data['payment_method']) : 'ONLINE');
                $payMethod = 'Xendit - ' . $channel;

                $invoice->markAsPaid(
                    $payMethod,
                    $data['paid_amount'] ?? $invoice->total,
                    $data['payment_id'] ?? ($data['id'] ?? null)
                );

                Log::info("Xendit Webhook Success: Invoice {$invoice->invoice_number} berhasil dilunaskan ({$payMethod}).");
            } catch (\Throwable $e) {
                Log::error("Xendit Webhook Payment Processing Error: " . $e->getMessage(), [
                    'invoice_id' => $invoice->id,
                    'trace' => $e->getTraceAsString()
                ]);
            }
        }

        return response()->json(['message' => 'Success', 'status' => $status]);
    }

    /**
     * Webhook Callback untuk Xendit Disbursement (Pencairan Dana Super Admin)
     */
    public function handleDisbursement(Request $request)
    {
        $xenditXCallbackToken = Setting::get('xendit_webhook_token', config('services.xendit.webhook_token'));
        $reqHeaders = $request->header('x-callback-token');

        if ($reqHeaders !== $xenditXCallbackToken) {
            Log::warning('Xendit Disbursement Webhook: Invalid callback token attempt', [
                'ip' => $request->ip()
            ]);
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $data = $request->all();
        $externalId = $data['external_id'] ?? null;
        $xenditId = $data['id'] ?? null;
        $status = strtoupper($data['status'] ?? '');

        Log::info("Xendit Disbursement Webhook Received: {$externalId} | Status: {$status}");

        $withdrawal = null;
        if ($externalId) {
            $withdrawal = \App\Models\Withdrawal::where('reference_id', $externalId)->first();
        }
        if (!$withdrawal && $xenditId) {
            $withdrawal = \App\Models\Withdrawal::where('xendit_disbursement_id', $xenditId)->first();
        }

        if ($withdrawal) {
            $withdrawal->update([
                'status' => in_array($status, ['COMPLETED', 'SUCCESS']) ? 'COMPLETED' : ($status === 'FAILED' ? 'FAILED' : 'PENDING'),
                'failure_code' => $data['failure_code'] ?? $withdrawal->failure_code,
            ]);
            Log::info("Withdrawal {$withdrawal->reference_id} status updated to {$withdrawal->status} via Webhook.");
        }

        return response()->json(['message' => 'Disbursement webhook processed successfully']);
    }
}


<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    /**
     * Dapatkan status pembayaran dan ringkasan tagihan pelanggan saat ini
     */
    public function status(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $pppoe = $customer->pppoe;

        if (!$pppoe) {
            return response()->json([
                'success' => true,
                'data' => [
                    'status_layanan'       => 'belum_aktif',
                    'status_invoice'       => 'belum_bayar',
                    'tanggal_jatuh_tempo'  => null,
                    'sisa_hari'            => null,
                    'is_overdue'           => false,
                    'total_tunggakan'      => 0,
                    'unpaid_count'         => 0,
                    'tagihan_aktif'        => null,
                ]
            ]);
        }

        $unpaidInvoices = Invoice::where('pppoe_id', $pppoe->id)
            ->where('status', 'unpaid')
            ->orderBy('due_date', 'asc')
            ->get();

        $totalTunggakan = $unpaidInvoices->sum('total');
        $earliestUnpaid = $unpaidInvoices->first();

        $dueDateStr = null;
        $sisaHari = null;
        $isOverdue = false;

        if ($earliestUnpaid && $earliestUnpaid->due_date) {
            $dueDate = Carbon::parse($earliestUnpaid->due_date);
            $dueDateStr = $dueDate->format('Y-m-d');
            $isOverdue = $dueDate->isPast() && !$dueDate->isToday();
            $sisaHari = (int) now()->startOfDay()->diffInDays($dueDate->copy()->startOfDay(), false);
        } elseif ($pppoe->tanggal_aktif) {
            $dt = Carbon::parse($pppoe->tanggal_aktif);
            if ($dt->isPast()) {
                $nextDue = now()->copy()->setDay(min($dt->day, now()->daysInMonth));
                if ($nextDue->isPast()) {
                    $nextDue = now()->copy()->addMonth()->setDay(min($dt->day, now()->addMonth()->daysInMonth));
                }
            } else {
                $nextDue = $dt;
            }
            $dueDateStr = $nextDue->format('Y-m-d');
            $sisaHari = (int) now()->startOfDay()->diffInDays($nextDue->copy()->startOfDay(), false);
        }

        $statusLayanan = 'aktif';
        if ($isOverdue && $unpaidInvoices->count() > 0) {
            $statusLayanan = 'isolir';
        } elseif ($unpaidInvoices->count() > 0) {
            $statusLayanan = 'menunggu_pembayaran';
        }

        return response()->json([
            'success' => true,
            'data' => [
                'status_layanan'       => $statusLayanan,
                'status_invoice'       => $pppoe->status_invoice ?? ($unpaidInvoices->count() > 0 ? 'belum_bayar' : 'terbayar'),
                'tanggal_jatuh_tempo'  => $dueDateStr,
                'sisa_hari'            => $sisaHari,
                'is_overdue'           => $isOverdue,
                'total_tunggakan'      => (float) $totalTunggakan,
                'unpaid_count'         => $unpaidInvoices->count(),
                'tagihan_aktif'        => $earliestUnpaid ? [
                    'id'                 => $earliestUnpaid->id,
                    'invoice_number'     => $earliestUnpaid->invoice_number,
                    'amount'             => (float) $earliestUnpaid->amount,
                    'tax'                => (float) $earliestUnpaid->tax,
                    'discount'           => (float) $earliestUnpaid->discount,
                    'total'              => (float) $earliestUnpaid->total,
                    'status'             => $earliestUnpaid->status,
                    'due_date'           => $earliestUnpaid->due_date,
                    'xendit_invoice_url' => $earliestUnpaid->xendit_invoice_url,
                ] : null,
            ]
        ]);
    }

    /**
     * Dapatkan daftar seluruh riwayat & tagihan pelanggan
     */
    public function invoices(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $pppoe = $customer->pppoe;

        if (!$pppoe) {
            return response()->json([
                'success' => true,
                'data'    => []
            ]);
        }

        $query = Invoice::with('transactions')
            ->where('pppoe_id', $pppoe->id)
            ->latest('id');

        if ($request->has('status') && in_array($request->status, ['unpaid', 'paid', 'expired', 'failed'])) {
            $query->where('status', $request->status);
        }

        $invoices = $query->get()->map(function ($inv) {
            $lastTx = $inv->transactions->first();
            return [
                'id'                 => $inv->id,
                'invoice_number'     => $inv->invoice_number,
                'amount'             => (float) $inv->amount,
                'tax'                => (float) $inv->tax,
                'discount'           => (float) $inv->discount,
                'total'              => (float) $inv->total,
                'status'             => $inv->status,
                'due_date'           => $inv->due_date,
                'payment_method'     => $inv->payment_method ?? ($lastTx ? $lastTx->payment_method : null),
                'xendit_invoice_url' => $inv->xendit_invoice_url,
                'paid_at'            => ($inv->status === 'paid' && $lastTx) ? $lastTx->payment_date : ($inv->status === 'paid' ? $inv->updated_at->format('Y-m-d H:i:s') : null),
                'created_at'         => $inv->created_at->format('Y-m-d H:i:s'),
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $invoices
        ]);
    }

    /**
     * Dapatkan detail faktur tertentu
     */
    public function show(Request $request, $id)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $pppoe = $customer->pppoe;

        if (!$pppoe) {
            return response()->json(['success' => false, 'message' => 'Akun PPPoE tidak ditemukan.'], 404);
        }

        $invoice = Invoice::with(['transactions', 'pppoe.paket'])
            ->where('pppoe_id', $pppoe->id)
            ->where('id', $id)
            ->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Faktur tagihan tidak ditemukan.'], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'id'                 => $invoice->id,
                'invoice_number'     => $invoice->invoice_number,
                'paket_nama'         => $invoice->pppoe->paket->nama_paket ?? 'Internet',
                'amount'             => (float) $invoice->amount,
                'tax'                => (float) $invoice->tax,
                'discount'           => (float) $invoice->discount,
                'total'              => (float) $invoice->total,
                'status'             => $invoice->status,
                'due_date'           => $invoice->due_date,
                'payment_method'     => $invoice->payment_method,
                'xendit_invoice_url' => $invoice->xendit_invoice_url,
                'created_at'         => $invoice->created_at->format('Y-m-d H:i:s'),
                'transactions'       => $invoice->transactions->map(function ($tx) {
                    return [
                        'id'                => $tx->id,
                        'payment_method'    => $tx->payment_method,
                        'amount_paid'       => (float) $tx->amount_paid,
                        'payment_date'      => $tx->payment_date,
                        'status'            => $tx->status,
                        'xendit_payment_id' => $tx->xendit_payment_id,
                    ];
                }),
            ]
        ]);
    }

    /**
     * Lakukan Pembayaran via Payment Gateway (Xendit)
     * Mengembalikan URL checkout pembayaran Xendit (Virtual Account, QRIS, E-Wallet, dll.)
     */
    public function pay(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $pppoe = $customer->pppoe;

        if (!$pppoe) {
            return response()->json(['success' => false, 'message' => 'Layanan internet pelanggan belum aktif.'], 400);
        }

        $invoiceId = $request->input('invoice_id');

        if ($invoiceId) {
            $invoice = Invoice::where('pppoe_id', $pppoe->id)->where('id', $invoiceId)->first();
        } else {
            // Pilih tagihan unpaid yang paling awal jatuh tempo
            $invoice = Invoice::where('pppoe_id', $pppoe->id)
                ->where('status', 'unpaid')
                ->orderBy('due_date', 'asc')
                ->first();
        }

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Tidak ada tagihan yang perlu dibayar.'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Tagihan ini sudah lunas terbayar.'], 400);
        }

        // Jika invoice Xendit sudah ada dan masih aktif
        if (!empty($invoice->xendit_invoice_url) && !empty($invoice->xendit_invoice_id)) {
            return response()->json([
                'success'        => true,
                'message'        => 'Checkout payment gateway siap digunakan.',
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total'          => (float) $invoice->total,
                'payment_url'    => $invoice->xendit_invoice_url,
            ]);
        }

        // Buat Invoice baru di Xendit Payment Gateway
        try {
            $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
            if (empty($secretKey)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Konfigurasi Payment Gateway belum diatur oleh administrator.'
                ], 500);
            }

            \Xendit\Configuration::setXenditKey($secretKey);
            $apiInstance = new \Xendit\Invoice\InvoiceApi();

            $customer->load('preOrder');
            $payerEmail = ($customer->preOrder && !empty($customer->preOrder->alamat_email) && filter_var($customer->preOrder->alamat_email, FILTER_VALIDATE_EMAIL))
                ? $customer->preOrder->alamat_email
                : 'billing@erlangga.net';

            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->endOfDay() : now()->addDays(7);
            $durationSeconds = max(86400, $dueDate->diffInSeconds(now()));

            $items = [
                [
                    'name'     => 'Paket Internet ' . ($pppoe->paket->nama_paket ?? ''),
                    'quantity' => 1,
                    'price'    => (float) $invoice->amount,
                    'category' => 'INTERNET_SERVICE',
                ]
            ];

            if ($invoice->tax > 0) {
                $items[] = [
                    'name'     => 'Pajak / PPN',
                    'quantity' => 1,
                    'price'    => (float) $invoice->tax,
                    'category' => 'TAX',
                ];
            }

            if ($invoice->discount > 0) {
                $items[] = [
                    'name'     => 'Diskon Promo',
                    'quantity' => 1,
                    'price'    => -1 * (float) $invoice->discount,
                    'category' => 'DISCOUNT',
                ];
            }

            $customerPayload = [
                'given_names'   => $customer->nama_lengkap ?? $customer->id_pelanggan,
                'email'         => $payerEmail,
                'mobile_number' => $customer->no_whatsapp ?: null,
            ];

            $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
                'external_id'      => $invoice->invoice_number,
                'amount'           => (float) $invoice->total,
                'payer_email'      => $payerEmail,
                'description'      => 'Tagihan Internet ' . $invoice->invoice_number . ' - ' . $customer->id_pelanggan,
                'invoice_duration' => (int) $durationSeconds,
                'customer'         => $customerPayload,
                'items'            => $items,
            ]);

            $result = $apiInstance->createInvoice($createInvoiceRequest);

            $invoice->update([
                'xendit_invoice_id'  => $result['id'],
                'xendit_invoice_url' => $result['invoice_url']
            ]);

            return response()->json([
                'success'        => true,
                'message'        => 'Checkout payment gateway berhasil dibuat.',
                'invoice_id'     => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'total'          => (float) $invoice->total,
                'payment_url'    => $result['invoice_url'],
            ]);
        } catch (\Throwable $e) {
            Log::error('API Mobile Xendit Creation Failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace'      => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menghubungkan ke Payment Gateway: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cek status pembayaran secara real-time dari Xendit
     */
    public function checkStatus(Request $request, $id)
    {
        /** @var Customer $customer */
        $customer = $request->user();
        $pppoe = $customer->pppoe;

        if (!$pppoe) {
            return response()->json(['success' => false, 'message' => 'Akun PPPoE tidak ditemukan.'], 404);
        }

        $invoice = Invoice::where('pppoe_id', $pppoe->id)->where('id', $id)->first();

        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Faktur tidak ditemukan.'], 404);
        }

        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => true,
                'status'  => 'paid',
                'message' => 'Tagihan sudah lunas terbayar.'
            ]);
        }

        if (!$invoice->xendit_invoice_id) {
            return response()->json([
                'success' => true,
                'status'  => 'unpaid',
                'message' => 'Menunggu pembayaran.'
            ]);
        }

        try {
            $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
            \Xendit\Configuration::setXenditKey($secretKey);
            $apiInstance = new \Xendit\Invoice\InvoiceApi();
            $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

            $xStatus = strtoupper($res['status'] ?? '');

            if ($xStatus === 'PAID' || $xStatus === 'SETTLED') {
                $channel = !empty($res['payment_channel']) ? strtoupper($res['payment_channel']) : (!empty($res['payment_method']) ? strtoupper($res['payment_method']) : 'ONLINE');
                $payMethod = 'Xendit - ' . $channel;

                $invoice->markAsPaid($payMethod, $invoice->total, $invoice->xendit_invoice_id);

                return response()->json([
                    'success'        => true,
                    'status'         => 'paid',
                    'payment_method' => $payMethod,
                    'message'        => 'Pembayaran lunas terverifikasi.'
                ]);
            }

            return response()->json([
                'success' => true,
                'status'  => strtolower($xStatus),
                'message' => 'Status pembayaran saat ini: ' . $xStatus
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sinkronisasi status: ' . $e->getMessage()
            ], 500);
        }
    }
}

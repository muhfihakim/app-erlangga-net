<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        $xenditXCallbackToken = config('services.xendit.webhook_token');
        $reqHeaders = $request->header('x-callback-token');

        if ($reqHeaders !== $xenditXCallbackToken) {
            return response()->json(['message' => 'Invalid token'], 403);
        }

        $data = $request->all();
        
        $invoice = \App\Models\Invoice::where('invoice_number', $data['external_id'])->first();

        if (!$invoice) {
            return response()->json(['message' => 'Invoice not found'], 404);
        }

        if ($data['status'] === 'PAID' || $data['status'] === 'SETTLED') {
            if ($invoice->status !== 'paid') {
                $payMethod = ($data['payment_method'] ?? 'XENDIT');
                if (!empty($data['payment_channel'])) {
                    $payMethod .= ' - ' . $data['payment_channel'];
                }

                $invoice->update([
                    'status' => 'paid',
                    'payment_method' => $payMethod,
                ]);

                \App\Models\Transaction::create([
                    'invoice_id' => $invoice->id,
                    'payment_method' => $payMethod,
                    'amount_paid' => $data['paid_amount'],
                    'payment_date' => now(),
                    'status' => 'success',
                    'xendit_payment_id' => $data['payment_id'] ?? ($data['id'] ?? null),
                ]);

                // Update PPPoE status
                if ($invoice->pppoe) {
                    $invoice->pppoe->update(['status_invoice' => 'terbayar']);
                }
            }
        }

        return response()->json(['message' => 'Success']);
    }
}

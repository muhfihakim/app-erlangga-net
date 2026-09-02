<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class XenditWebhookController extends Controller
{
    public function handleInvoice(Request $request)
    {
        $xenditXCallbackToken = \App\Models\Setting::get('xendit_webhook_token', config('services.xendit.webhook_token'));
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
            $payMethod = ($data['payment_method'] ?? 'XENDIT');
            if (!empty($data['payment_channel'])) {
                $payMethod .= ' - ' . $data['payment_channel'];
            }

            $invoice->markAsPaid(
                $payMethod,
                $data['paid_amount'] ?? $invoice->total,
                $data['payment_id'] ?? ($data['id'] ?? null)
            );
        }

        return response()->json(['message' => 'Success']);
    }
}

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
            $channel = !empty($data['payment_channel']) ? strtoupper($data['payment_channel']) : (!empty($data['payment_method']) ? strtoupper($data['payment_method']) : 'ONLINE');
            $payMethod = 'Xendit - ' . $channel;

            $invoice->markAsPaid(
                $payMethod,
                $data['paid_amount'] ?? $invoice->total,
                $data['payment_id'] ?? ($data['id'] ?? null)
            );
        }

        return response()->json(['message' => 'Success']);
    }
}

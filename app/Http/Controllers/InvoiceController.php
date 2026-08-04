<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Transaction;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Invoice::with('pppoe.preOrder')->latest()->get();
        return view('invoices.index', compact('invoices'));
    }

    public function payManual(Request $request, Invoice $invoice)
    {
        $request->validate([
            'payment_method' => 'required|in:cash,cashless',
        ]);

        $invoice->update([
            'status' => 'paid',
            'payment_method' => $request->payment_method,
        ]);

        Transaction::create([
            'invoice_id' => $invoice->id,
            'payment_method' => $request->payment_method,
            'amount_paid' => $invoice->total,
            'payment_date' => now(),
            'status' => 'success',
        ]);

        return redirect()->back()->with('success', 'Invoice berhasil dibayar secara manual.');
    }
}

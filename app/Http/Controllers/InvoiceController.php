<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Pppoe;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceController extends Controller
{
    public function index()
    {
        // Tampilkan daftar pelanggan (PPPoE) yang memiliki potensi tagihan
        $pppoes = Pppoe::with(['preOrder', 'paket', 'invoices'])->latest()->get();
        return view('invoices.index', compact('pppoes'));
    }

    public function show(Pppoe $pppoe)
    {
        $pppoe->load(['preOrder', 'paket', 'invoices' => function($q) {
            $q->latest();
        }]);
        return view('invoices.show', compact('pppoe'));
    }

    public function print(Invoice $invoice)
    {
        $invoice->load(['pppoe.preOrder', 'pppoe.paket', 'transactions']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        return $pdf->download($invoice->invoice_number . '.pdf');
    }

    public function generate()
    {
        $pppoes = Pppoe::with('paket')->where('generate_invoice', true)->get();
        
        $count = 0;
        foreach ($pppoes as $pppoe) {
            // Generate unique invoice number: INV-YYYYMMDD-PPPOEID
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT);
            
            // Check if already generated for this month
            $exists = Invoice::where('pppoe_id', $pppoe->id)
                ->whereMonth('created_at', date('m'))
                ->whereYear('created_at', date('Y'))
                ->exists();
                
            if (!$exists && $pppoe->paket) {
                $amount = $pppoe->paket->harga;
                $tax = $pppoe->pajak ?? 0;
                $discount = $pppoe->diskon ?? 0;
                
                // Calculate tax amount if it's percentage, assuming it's nominal for now
                $total = $amount + $tax - $discount;
                
                // Due date: usually X days from generate, or exact date of active date
                $dueDate = Carbon::now()->addDays(7); 
                
                Invoice::create([
                    'pppoe_id' => $pppoe->id,
                    'invoice_number' => $invoiceNumber,
                    'amount' => $amount,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'unpaid',
                    'due_date' => $dueDate
                ]);
                $count++;
            }
        }
        
        return redirect()->back()->with('success', "Berhasil generate $count tagihan baru bulan ini.");
    }

    public function generateSingle(Pppoe $pppoe)
    {
        $pppoe->load('paket');
        if (!$pppoe->paket) {
            return redirect()->back()->with('error', 'Gagal generate: Pelanggan belum memiliki paket.');
        }

        $amount = $pppoe->paket->harga;
        $tax = $pppoe->pajak ?? 0;
        $discount = $pppoe->diskon ?? 0;
        $total = $amount + $tax - $discount;
        $dueDate = Carbon::now()->addDays(7);
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

        Invoice::create([
            'pppoe_id' => $pppoe->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'status' => 'unpaid',
            'due_date' => $dueDate
        ]);

        return redirect()->back()->with('success', 'Berhasil generate tagihan manual untuk pelanggan ini.');
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

        if ($invoice->pppoe) {
            $invoice->pppoe->update(['status_invoice' => 'terbayar']);
        }

        return redirect()->back()->with('success', 'Invoice berhasil dibayar secara manual.');
    }

    public function payXendit(Request $request, Invoice $invoice)
    {
        if ($invoice->xendit_invoice_url && $invoice->status !== 'paid') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['url' => $invoice->xendit_invoice_url]);
            }
            return redirect($invoice->xendit_invoice_url);
        }

        if ($invoice->status === 'paid') {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Invoice sudah dibayar.']);
            }
            return redirect()->back()->with('error', 'Invoice sudah dibayar.');
        }

        \Xendit\Configuration::setXenditKey(config('services.xendit.secret_key'));
        $apiInstance = new \Xendit\Invoice\InvoiceApi();

        $invoice->load('pppoe.preOrder');
        $preOrder = $invoice->pppoe->preOrder;

        $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
            'external_id' => $invoice->invoice_number,
            'amount' => $invoice->total,
            'payer_email' => $preOrder ? $preOrder->alamat_email : 'customer@example.com',
            'description' => 'Pembayaran Tagihan ' . $invoice->invoice_number,
            'success_redirect_url' => route('invoices.show', $invoice->pppoe_id),
            'failure_redirect_url' => route('invoices.show', $invoice->pppoe_id),
        ]);

        try {
            $result = $apiInstance->createInvoice($createInvoiceRequest);
            $invoice->update([
                'xendit_invoice_id' => $result['id'],
                'xendit_invoice_url' => $result['invoice_url']
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['url' => $result['invoice_url']]);
            }
            return redirect($result['invoice_url']);
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Gagal membuat Xendit invoice: ' . $e->getMessage()]);
            }
            return redirect()->back()->with('error', 'Gagal membuat Xendit invoice: ' . $e->getMessage());
        }
    }
}

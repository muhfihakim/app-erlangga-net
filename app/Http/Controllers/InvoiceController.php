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
    public function index(Request $request)
    {
        $filterStatus = $request->input('status_tagihan', '');
        $filterPaket = $request->input('paket_id', '');

        if ($request->ajax()) {
            $query = Pppoe::with(['preOrder', 'paket', 'invoices'])
                ->latest()
                ->select('pppoes.*');

            if ($filterStatus === 'unpaid') {
                $query->whereHas('invoices', function ($q) {
                    $q->where('status', 'unpaid');
                });
            } elseif ($filterStatus === 'paid') {
                $query->whereDoesntHave('invoices', function ($q) {
                    $q->where('status', 'unpaid');
                });
            }

            if (!empty($filterPaket)) {
                $query->where('pppoes.paket_id', $filterPaket);
            }

            return \Yajra\DataTables\Facades\DataTables::eloquent($query)
                ->addColumn('no', function ($pppoe) {
                    return ''; // Handled by DataTables render
                })
                ->addColumn('pelanggan', function ($pppoe) {
                    $name = $pppoe->preOrder->nama_lengkap ?? '-';
                    $user = $pppoe->username;
                    $phone = $pppoe->preOrder->no_whatsapp ?? '';
                    return '<div><div style="font-weight: 700; color: #1e293b; font-size: 12.5px;">' . htmlspecialchars($name) . '</div><span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($user) . ($phone ? ' • ' . htmlspecialchars($phone) : '') . '</span></div>';
                })
                ->addColumn('paket', function ($pppoe) {
                    $namaPaket = $pppoe->paket->nama_paket ?? '-';
                    $harga = $pppoe->paket->harga ?? 0;
                    return '<div><span class="badge" style="background:#eef2ff; color:#4338ca; font-weight:700; font-size:11px; padding:3px 8px; border-radius:6px; border:1px solid #c7d2fe;">' . htmlspecialchars($namaPaket) . '</span><div style="font-size: 11px; color: #64748b; margin-top:2px; font-weight:600;">Rp ' . number_format($harga, 0, ',', '.') . '</div></div>';
                })
                ->addColumn('tanggal_aktif', function ($pppoe) {
                    if (!$pppoe->tanggal_aktif) return '<div class="text-center text-slate-400 font-semibold">-</div>';
                    $dt = Carbon::parse($pppoe->tanggal_aktif);
                    return '<div style="text-align: center; color: #334155; font-size: 12px; font-weight: 700;">' . $dt->format('d/m/Y') . '</div>';
                })
                ->addColumn('status_tagihan', function ($pppoe) {
                    $unpaidCount = $pppoe->invoices->where('status', 'unpaid')->count();
                    if ($unpaidCount > 0) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef2f2; color:#be123c; font-size:11px; padding:3px 8px; border:1px solid #fecdd3; border-radius:6px; font-weight:700;"><i class="fa-solid fa-clock mr-1"></i> ' . $unpaidCount . ' BELUM LUNAS</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge badge-published" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-check mr-1"></i> LUNAS</span></div>';
                })
                ->addColumn('opsi', function ($pppoe) {
                    return '<div style="text-align: center;">
                        <a href="' . route('invoices.show', $pppoe->id) . '" class="btn-primary-admin !h-8 !px-3 !text-[11px] !bg-indigo-600 hover:!bg-indigo-700 inline-flex items-center gap-1.5 shadow-sm" title="Lihat Tagihan">
                            <i class="fa-solid fa-file-invoice-dollar"></i> Detail Tagihan
                        </a>
                    </div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('pppoes.username', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.ip_address', 'LIKE', "%{$search}%")
                              ->orWhereHas('preOrder', function ($poq) use ($search) {
                                  $poq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                      ->orWhere('no_whatsapp', 'LIKE', "%{$search}%")
                                      ->orWhere('alamat_lengkap', 'LIKE', "%{$search}%");
                              })
                              ->orWhereHas('paket', function ($pq) use ($search) {
                                  $pq->where('nama_paket', 'LIKE', "%{$search}%");
                              });
                        });
                    }
                })
                ->rawColumns(['pelanggan', 'paket', 'tanggal_aktif', 'status_tagihan', 'opsi'])
                ->make(true);
        }

        // Stats calculation for the top cards
        $totalPppoeCount = Pppoe::count();
        $totalUnpaidCount = Invoice::where('status', 'unpaid')->count();
        $totalUnpaidAmount = Invoice::where('status', 'unpaid')->sum('total');
        $totalPaidCount = Invoice::where('status', 'paid')->count();
        $totalPaidAmount = Invoice::where('status', 'paid')->sum('total');

        $pakets = \App\Models\Paket::orderBy('harga', 'asc')->get();

        return view('invoices.index', compact(
            'totalPppoeCount',
            'totalUnpaidCount',
            'totalUnpaidAmount',
            'totalPaidCount',
            'totalPaidAmount',
            'pakets',
            'filterStatus',
            'filterPaket'
        ));
    }

    public function show(Pppoe $pppoe)
    {
        $pppoe->load(['preOrder', 'paket', 'invoices' => function($q) {
            $q->latest();
        }]);

        // Auto-sync pending Xendit invoices
        foreach ($pppoe->invoices as $invoice) {
            if ($invoice->status !== 'paid' && $invoice->xendit_invoice_id) {
                try {
                    \Xendit\Configuration::setXenditKey(config('services.xendit.secret_key'));
                    $apiInstance = new \Xendit\Invoice\InvoiceApi();
                    $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

                    if ($res['status'] === 'PAID' || $res['status'] === 'SETTLED') {
                        $payMethod = 'Xendit - ' . ($res['payment_method'] ?? 'Online');
                        if (!empty($res['payment_channel'])) {
                            $payMethod .= ' (' . $res['payment_channel'] . ')';
                        }

                        $invoice->update([
                            'status' => 'paid',
                            'payment_method' => $payMethod,
                        ]);

                        Transaction::firstOrCreate(
                            ['invoice_id' => $invoice->id],
                            [
                                'payment_method' => $payMethod,
                                'amount_paid' => $invoice->total,
                                'payment_date' => now(),
                                'status' => 'success',
                                'xendit_payment_id' => $invoice->xendit_invoice_id,
                            ]
                        );

                        $pppoe->update(['status_invoice' => 'terbayar']);
                    }
                } catch (\Throwable $e) {
                    // Ignore transient network errors on show view
                }
            }
        }

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

        try {
            \Xendit\Configuration::setXenditKey(config('services.xendit.secret_key'));
            $apiInstance = new \Xendit\Invoice\InvoiceApi();

            $invoice->load('pppoe.preOrder');
            $preOrder = $invoice->pppoe ? $invoice->pppoe->preOrder : null;
            $payerEmail = ($preOrder && !empty($preOrder->alamat_email) && filter_var($preOrder->alamat_email, FILTER_VALIDATE_EMAIL))
                ? $preOrder->alamat_email
                : 'customer@erlangga.net';

            $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
                'external_id' => $invoice->invoice_number,
                'amount' => (float) $invoice->total,
                'payer_email' => $payerEmail,
                'description' => 'Pembayaran Tagihan ' . $invoice->invoice_number,
                'success_redirect_url' => route('invoices.show', $invoice->pppoe_id),
                'failure_redirect_url' => route('invoices.show', $invoice->pppoe_id),
            ]);

            $result = $apiInstance->createInvoice($createInvoiceRequest);
            $invoice->update([
                'xendit_invoice_id' => $result['id'],
                'xendit_invoice_url' => $result['invoice_url']
            ]);
            
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['url' => $result['invoice_url']]);
            }
            return redirect($result['invoice_url']);
        } catch (\Throwable $e) {
            \Log::error('Xendit Invoice Creation Failed: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Gagal membuat Xendit invoice: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal membuat Xendit invoice: ' . $e->getMessage());
        }
    }

    public function syncXendit(Request $request, Invoice $invoice)
    {
        if (!$invoice->xendit_invoice_id) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Invoice ini belum memiliki ID Xendit.'], 400);
            }
            return redirect()->back()->with('error', 'Invoice ini belum memiliki ID Xendit.');
        }

        try {
            \Xendit\Configuration::setXenditKey(config('services.xendit.secret_key'));
            $apiInstance = new \Xendit\Invoice\InvoiceApi();
            $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

            if ($res['status'] === 'PAID' || $res['status'] === 'SETTLED') {
                $payMethod = 'Xendit - ' . ($res['payment_method'] ?? 'Online');
                if (!empty($res['payment_channel'])) {
                    $payMethod .= ' (' . $res['payment_channel'] . ')';
                }

                $invoice->update([
                    'status' => 'paid',
                    'payment_method' => $payMethod,
                ]);

                Transaction::firstOrCreate(
                    ['invoice_id' => $invoice->id],
                    [
                        'payment_method' => $payMethod,
                        'amount_paid' => $invoice->total,
                        'payment_date' => now(),
                        'status' => 'success',
                        'xendit_payment_id' => $invoice->xendit_invoice_id,
                    ]
                );

                if ($invoice->pppoe) {
                    $invoice->pppoe->update(['status_invoice' => 'terbayar']);
                }

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'paid', 'message' => 'Status pembayaran berhasil disinkronkan (LUNAS).']);
                }
                return redirect()->back()->with('success', 'Status pembayaran berhasil disinkronkan (LUNAS).');
            }

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['status' => $res['status'], 'message' => 'Status Xendit saat ini: ' . $res['status']]);
            }
            return redirect()->back()->with('info', 'Status Xendit saat ini: ' . $res['status']);
        } catch (\Throwable $e) {
            \Log::error('Xendit Sync Error: ' . $e->getMessage(), [
                'invoice_id' => $invoice->id,
                'trace' => $e->getTraceAsString()
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['error' => 'Gagal sinkronisasi Xendit: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Gagal sinkronisasi Xendit: ' . $e->getMessage());
        }
    }
}

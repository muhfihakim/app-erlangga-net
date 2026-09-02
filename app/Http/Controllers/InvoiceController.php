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
                    $totalHarga = $pppoe->paket ? $pppoe->paket->total_harga : 0;
                    return '<div><span class="badge" style="background:#eef2ff; color:#4338ca; font-weight:700; font-size:11px; padding:3px 8px; border-radius:6px; border:1px solid #c7d2fe;">' . htmlspecialchars($namaPaket) . '</span><div style="font-size: 11px; color: #64748b; margin-top:2px; font-weight:600;">Rp ' . number_format($totalHarga, 0, ',', '.') . '</div></div>';
                })
                ->addColumn('tagihan_selanjutnya', function ($pppoe) {
                    $unpaidInv = $pppoe->invoices->where('status', 'unpaid')->sortBy('due_date')->first();

                    if ($unpaidInv && $unpaidInv->due_date) {
                        $due = Carbon::parse($unpaidInv->due_date);
                        $isOverdue = $due->isPast() && !$due->isToday();

                        if ($isOverdue) {
                            $diff = (int) $due->copy()->startOfDay()->diffInDays(now()->startOfDay());
                            return '<div style="text-align: center;">
                                <div style="color: #e11d48; font-weight: 800; font-size: 12.5px;">' . $due->format('d/m/Y') . '</div>
                                <span class="badge" style="background:#fff1f2; color:#e11d48; font-size:10px; padding:1px 6px; border-radius:4px; font-weight:700; border:1px solid #fecdd3;">Lewat ' . $diff . ' hari</span>
                            </div>';
                        }

                        return '<div style="text-align: center;">
                            <div style="color: #d97706; font-weight: 800; font-size: 12.5px;">' . $due->format('d/m/Y') . '</div>
                            <span class="badge" style="background:#fffbeb; color:#b45309; font-size:10px; padding:1px 6px; border-radius:4px; font-weight:700; border:1px solid #fef3c7;">Jatuh Tempo</span>
                        </div>';
                    }

                    // Jika semua lunas, tampilkan siklus tagihan berikutnya
                    if ($pppoe->tanggal_aktif) {
                        $dt = Carbon::parse($pppoe->tanggal_aktif);
                        if ($dt->isPast()) {
                            $nextDue = now()->copy()->setDay(min($dt->day, now()->daysInMonth));
                            if ($nextDue->isPast()) {
                                $nextDue = now()->copy()->addMonth()->setDay(min($dt->day, now()->addMonth()->daysInMonth));
                            }
                        } else {
                            $nextDue = $dt;
                        }

                        return '<div style="text-align: center;">
                            <div style="color: #059669; font-weight: 800; font-size: 12.5px;">' . $nextDue->format('d/m/Y') . '</div>
                            <span class="badge" style="background:#ecfdf5; color:#059669; font-size:10px; padding:1px 6px; border-radius:4px; font-weight:700; border:1px solid #a7f3d0;">Siklus Berikutnya</span>
                        </div>';
                    }

                    return '<div class="text-center text-slate-400 font-semibold">-</div>';
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
                ->rawColumns(['pelanggan', 'paket', 'tagihan_selanjutnya', 'status_tagihan', 'opsi'])
                ->make(true);
        }

        // Stats calculation for the top cards
        $totalPppoeCount = Pppoe::count();
        $totalUnpaidCount = Invoice::where('status', 'unpaid')->count();
        $totalUnpaidAmount = Invoice::where('status', 'unpaid')->sum('total');
        $totalOverdueCount = Invoice::where('status', 'unpaid')->where('due_date', '<', now()->toDateString())->count();
        $totalOverdueAmount = Invoice::where('status', 'unpaid')->where('due_date', '<', now()->toDateString())->sum('total');
        $totalPaidCount = Invoice::where('status', 'paid')->count();
        $totalPaidAmount = Invoice::where('status', 'paid')->sum('total');

        $pakets = \App\Models\Paket::orderBy('harga', 'asc')->get();

        return view('invoices.index', compact(
            'totalPppoeCount',
            'totalUnpaidCount',
            'totalUnpaidAmount',
            'totalOverdueCount',
            'totalOverdueAmount',
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
                    \Xendit\Configuration::setXenditKey(\App\Models\Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
                    $apiInstance = new \Xendit\Invoice\InvoiceApi();
                    $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

                    if ($res['status'] === 'PAID' || $res['status'] === 'SETTLED') {
                        $channel = !empty($res['payment_channel']) ? strtoupper($res['payment_channel']) : (!empty($res['payment_method']) ? strtoupper($res['payment_method']) : 'ONLINE');
                        $payMethod = 'Xendit - ' . $channel;

                        $invoice->markAsPaid($payMethod, $invoice->total, $invoice->xendit_invoice_id);
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
                $amount = (float) $pppoe->paket->harga;
                $tax = ($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($pppoe->paket->pajak ?? 0);
                $discount = ($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($pppoe->paket->diskon ?? 0);
                $total = max(0, $amount + $tax - $discount);
                
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

        $amount = (float) $pppoe->paket->harga;
        $tax = ($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($pppoe->paket->pajak ?? 0);
        $discount = ($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($pppoe->paket->diskon ?? 0);
        $total = max(0, $amount + $tax - $discount);
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

        $invoice->markAsPaid($request->payment_method);

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
            \Xendit\Configuration::setXenditKey(\App\Models\Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
            $apiInstance = new \Xendit\Invoice\InvoiceApi();

            $invoice->load(['pppoe.preOrder', 'pppoe.paket']);
            $pppoe = $invoice->pppoe;
            $preOrder = $pppoe ? $pppoe->preOrder : null;
            $payerEmail = ($preOrder && !empty($preOrder->alamat_email) && filter_var($preOrder->alamat_email, FILTER_VALIDATE_EMAIL))
                ? $preOrder->alamat_email
                : 'billing@erlangga.net';

            // Calculate invoice duration until due date (minimum 1 day = 86400 seconds)
            $dueDate = $invoice->due_date ? Carbon::parse($invoice->due_date)->endOfDay() : now()->addDays(7);
            $durationSeconds = max(86400, $dueDate->diffInSeconds(now()));

            // Build detailed items breakdown for checkout screen
            $items = [];
            $items[] = [
                'name' => 'Paket Internet ' . ($pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : ''),
                'quantity' => 1,
                'price' => (float) $invoice->amount,
                'category' => 'INTERNET_SERVICE',
            ];

            if ($invoice->tax > 0) {
                $items[] = [
                    'name' => 'Pajak / PPN',
                    'quantity' => 1,
                    'price' => (float) $invoice->tax,
                    'category' => 'TAX',
                ];
            }

            if ($invoice->discount > 0) {
                $items[] = [
                    'name' => 'Diskon Promo',
                    'quantity' => 1,
                    'price' => -1 * (float) $invoice->discount,
                    'category' => 'DISCOUNT',
                ];
            }

            $customer = [
                'given_names' => $preOrder ? ($preOrder->nama_lengkap ?? $pppoe->username) : ($pppoe->username ?? 'Pelanggan'),
                'email' => $payerEmail,
                'mobile_number' => $preOrder && !empty($preOrder->no_whatsapp) ? $preOrder->no_whatsapp : null,
            ];

            $baseUrl = $request->getSchemeAndHttpHost();
            $redirectUrl = $baseUrl . '/invoices/' . $invoice->pppoe_id;

            $createInvoiceRequest = new \Xendit\Invoice\CreateInvoiceRequest([
                'external_id' => $invoice->invoice_number,
                'amount' => (float) $invoice->total,
                'payer_email' => $payerEmail,
                'description' => 'Tagihan Internet ' . $invoice->invoice_number . ' - ' . ($pppoe ? $pppoe->username : ''),
                'invoice_duration' => (int) $durationSeconds,
                'customer' => $customer,
                'items' => $items,
                'success_redirect_url' => $redirectUrl,
                'failure_redirect_url' => $redirectUrl,
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
            \Xendit\Configuration::setXenditKey(\App\Models\Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
            $apiInstance = new \Xendit\Invoice\InvoiceApi();
            $res = $apiInstance->getInvoiceById($invoice->xendit_invoice_id);

            if ($res['status'] === 'PAID' || $res['status'] === 'SETTLED') {
                $channel = !empty($res['payment_channel']) ? strtoupper($res['payment_channel']) : (!empty($res['payment_method']) ? strtoupper($res['payment_method']) : 'ONLINE');
                $payMethod = 'Xendit - ' . $channel;

                $invoice->markAsPaid($payMethod, $invoice->total, $invoice->xendit_invoice_id);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['status' => 'paid', 'message' => 'Pembayaran lunas terverifikasi via Xendit!']);
                }
                return redirect()->back()->with('success', 'Pembayaran lunas terverifikasi via Xendit!');
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

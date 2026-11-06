<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Transaction;
use App\Models\Pppoe;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

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
                    $name = $pppoe->preOrder->nama_lengkap ?? ($pppoe->customer->nama_lengkap ?? ($pppoe->keterangan ?: '-'));
                    $user = $pppoe->username;
                    $phone = $pppoe->preOrder->no_whatsapp ?? ($pppoe->customer->no_whatsapp ?? '');
                    return '<div><div style="font-weight: 700; color: #1e293b; font-size: 12.5px;">' . htmlspecialchars($name) . '</div><span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($user) . ($phone ? ' • ' . htmlspecialchars($phone) : '') . '</span></div>';
                })
                ->addColumn('paket', function ($pppoe) {
                    $namaPaket = $pppoe->paket->nama_paket ?? '-';
                    $totalHarga = $pppoe->paket ? $pppoe->paket->total_harga : 0;
                    $adjBadge = '';
                    if ((float)($pppoe->penyesuaian_tagihan ?? 0) != 0) {
                        $adjPrefix = $pppoe->penyesuaian_tagihan > 0 ? '+' : '';
                        $adjBadge = '<div class="mt-1"><span class="badge" style="background:#e0f2fe; color:#0369a1; font-size:9.5px; padding:1px 5px; border-radius:4px; font-weight:700; border:1px solid #bae6fd;" title="' . htmlspecialchars($pppoe->keterangan_penyesuaian ?: 'Penyesuaian Siklus Berikutnya') . '"><i class="fa-solid fa-sliders text-[9px] mr-1"></i> ' . $adjPrefix . number_format($pppoe->penyesuaian_tagihan, 0, ',', '.') . '</span></div>';
                    }
                    return '<div><span class="badge" style="background:#eef2ff; color:#4338ca; font-weight:700; font-size:11px; padding:3px 8px; border-radius:6px; border:1px solid #c7d2fe;">' . htmlspecialchars($namaPaket) . '</span><div style="font-size: 11px; color: #64748b; margin-top:2px; font-weight:600;">Rp ' . number_format($totalHarga, 0, ',', '.') . '</div>' . $adjBadge . '</div>';
                })
                ->addColumn('tagihan_selanjutnya', function ($pppoe) {
                    if (!$pppoe->generate_invoice || $pppoe->status_invoice === 'non_billing') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#64748b; font-size:10px; padding:2px 7px; border-radius:4px; font-weight:700; border:1px solid #e2e8f0;">Non-Billing</span></div>';
                    }

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
                    if (!$pppoe->generate_invoice || $pppoe->status_invoice === 'non_billing') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#475569; font-size:10.5px; padding:2.5px 8px; border:1px solid #cbd5e1; border-radius:6px; font-weight:700;"><i class="fa-solid fa-shield-halved mr-1 text-slate-400"></i> NON-BILLING</span></div>';
                    }

                    $unpaidCount = $pppoe->invoices->where('status', 'unpaid')->count();
                    if ($unpaidCount > 0) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef2f2; color:#be123c; font-size:10.5px; padding:2.5px 8px; border:1px solid #fecdd3; border-radius:6px; font-weight:700;"><i class="fa-solid fa-clock mr-1"></i> ' . $unpaidCount . ' BELUM LUNAS</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge badge-published" style="font-size:10.5px; padding:2.5px 8px;"><i class="fa-solid fa-check mr-1"></i> LUNAS</span></div>';
                })
                ->addColumn('opsi', function ($pppoe) {
                    $phone = $pppoe->preOrder->no_whatsapp ?? '';
                    $waBtn = '';
                    if (!empty($phone)) {
                        $waBtn = '<a href="' . route('whatsapp.index', ['pppoe_id' => $pppoe->id]) . '" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-emerald-300 hover:!bg-emerald-50 ml-1" title="Kirim Tagihan via WhatsApp Center">
                            <i class="fa-brands fa-whatsapp text-emerald-600"></i>
                        </a>';
                    }
                    return '<div class="flex items-center justify-center">
                        <a href="' . route('invoices.show', $pppoe->id) . '" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Detail Tagihan & Riwayat Invoice">
                            <i class="fa-solid fa-file-invoice-dollar text-slate-500"></i>
                        </a>' . $waBtn . '
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

    /**
     * Pastikan kolom penyesuaian sudah ada pada database schema (auto-migrate fallback)
     */
    protected function ensurePenyesuaianColumnsExist(): void
    {
        if (!Schema::hasColumn('invoices', 'penyesuaian') || !Schema::hasColumn('pppoes', 'penyesuaian_tagihan')) {
            try {
                Artisan::call('migrate', ['--force' => true]);
            } catch (\Throwable $e) {
                Log::warning('Auto-migration error on InvoiceController: ' . $e->getMessage());
            }
        }
    }

    public function generateSingle(Request $request, Pppoe $pppoe)
    {
        $this->ensurePenyesuaianColumnsExist();

        $pppoe->load('paket');
        if (!$pppoe->paket) {
            return redirect()->back()->with('error', 'Gagal generate: Pelanggan belum memiliki paket.');
        }

        $amount = $request->filled('amount') ? (float)$request->amount : (float)$pppoe->paket->harga;
        $penyesuaian = $request->filled('penyesuaian') ? (float)$request->penyesuaian : (float)($pppoe->penyesuaian_tagihan ?? 0);
        $catatanPenyesuaian = $request->filled('catatan_penyesuaian') ? $request->catatan_penyesuaian : ($pppoe->keterangan_penyesuaian ?? null);

        $tax = $request->filled('tax') ? (float)$request->tax : (($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($pppoe->paket->pajak ?? 0));
        $discount = $request->filled('discount') ? (float)$request->discount : (($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($pppoe->paket->diskon ?? 0));
        $total = max(0, $amount + $penyesuaian + $tax - $discount);

        $dueDate = $request->filled('due_date') ? Carbon::parse($request->due_date) : Carbon::now()->addDays(7);
        $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999);

        $invoiceData = [
            'pppoe_id' => $pppoe->id,
            'invoice_number' => $invoiceNumber,
            'amount' => $amount,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $total,
            'status' => 'unpaid',
            'due_date' => $dueDate->format('Y-m-d'),
        ];

        if (Schema::hasColumn('invoices', 'penyesuaian')) {
            $invoiceData['penyesuaian'] = $penyesuaian;
            $invoiceData['catatan_penyesuaian'] = $catatanPenyesuaian;
        }

        Invoice::create($invoiceData);

        $pppoeUpdates = ['status_invoice' => 'belum_bayar'];
        if (Schema::hasColumn('pppoes', 'penyesuaian_tipe') && ($pppoe->penyesuaian_tipe ?? 'sekali') === 'sekali' && $penyesuaian != 0) {
            $pppoeUpdates['penyesuaian_tagihan'] = 0;
            $pppoeUpdates['keterangan_penyesuaian'] = null;
        }
        $pppoe->update($pppoeUpdates);

        return redirect()->back()->with('success', 'Berhasil generate tagihan faktur untuk pelanggan ini.');
    }

    /**
     * Penyesuaian tagihan berjalan (Custom Invoice: Penyesuaian, Biaya Tambahan, dsb.)
     */
    public function adjustInvoice(Request $request, Invoice $invoice)
    {
        $this->ensurePenyesuaianColumnsExist();

        if ($invoice->status === 'paid') {
            return redirect()->back()->with('error', 'Faktur yang sudah lunas tidak dapat disesuaikan lagi.');
        }

        $request->validate([
            'amount' => 'nullable|numeric|min:0',
            'penyesuaian' => 'nullable|numeric',
            'catatan_penyesuaian' => 'nullable|string|max:255',
            'tax' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
        ]);

        $amount = $request->filled('amount') ? (float)$request->amount : (float)$invoice->amount;
        $penyesuaian = $request->filled('penyesuaian') ? (float)$request->penyesuaian : (float)($invoice->penyesuaian ?? 0);
        $catatanPenyesuaian = $request->catatan_penyesuaian;
        $tax = $request->filled('tax') ? (float)$request->tax : (float)$invoice->tax;
        $discount = $request->filled('discount') ? (float)$request->discount : (float)$invoice->discount;
        $dueDate = $request->filled('due_date') ? $request->due_date : $invoice->due_date;

        $newTotal = max(0, $amount + $penyesuaian + $tax - $discount);

        $updates = [
            'amount' => $amount,
            'tax' => $tax,
            'discount' => $discount,
            'total' => $newTotal,
            'due_date' => $dueDate,
        ];

        if (Schema::hasColumn('invoices', 'penyesuaian')) {
            $updates['penyesuaian'] = $penyesuaian;
            $updates['catatan_penyesuaian'] = $catatanPenyesuaian;
        }

        // Jika total tagihan berubah, kedaluwarsa & reset link Xendit agar pelanggan tidak membayar nominal lama
        if (abs($newTotal - (float)$invoice->total) > 0.01) {
            if (!empty($invoice->xendit_invoice_id)) {
                try {
                    \Xendit\Configuration::setXenditKey(\App\Models\Setting::get('xendit_secret_key', config('services.xendit.secret_key')));
                    $apiInstance = new \Xendit\Invoice\InvoiceApi();
                    $apiInstance->expireInvoice($invoice->xendit_invoice_id);
                } catch (\Throwable $e) {
                    Log::info("Xendit Expire Previous Invoice ({$invoice->xendit_invoice_id}): " . $e->getMessage());
                }
            }
            $updates['xendit_invoice_id'] = null;
            $updates['xendit_invoice_url'] = null;
        }

        $invoice->update($updates);

        return redirect()->back()->with('success', "Penyesuaian faktur #{$invoice->invoice_number} berhasil disimpan. Total tagihan baru: Rp " . number_format($newTotal, 0, ',', '.'));
    }

    /**
     * Atur penyesuaian untuk tagihan berikutnya (upcoming billing)
     */
    public function setNextAdjustment(Request $request, Pppoe $pppoe)
    {
        $this->ensurePenyesuaianColumnsExist();

        $request->validate([
            'penyesuaian_tagihan' => 'required|numeric',
            'keterangan_penyesuaian' => 'nullable|string|max:255',
            'penyesuaian_tipe' => 'required|in:sekali,berulang',
        ]);

        $updates = [];
        if (Schema::hasColumn('pppoes', 'penyesuaian_tagihan')) {
            $updates['penyesuaian_tagihan'] = (float)$request->penyesuaian_tagihan;
            $updates['keterangan_penyesuaian'] = $request->keterangan_penyesuaian;
            $updates['penyesuaian_tipe'] = $request->penyesuaian_tipe;
            $pppoe->update($updates);
        } else {
            return redirect()->back()->with('error', 'Kolom penyesuaian tagihan belum tersedia di database. Jalankan migrasi database terlebih dahulu.');
        }

        $tipeStr = $request->penyesuaian_tipe === 'sekali' ? '1x (Tagihan Berikutnya)' : 'Berulang Setiap Bulan';
        return redirect()->back()->with('success', "Pengaturan penyesuaian tagihan berikutnya berhasil disimpan (Nominal: Rp " . number_format($request->penyesuaian_tagihan, 0, ',', '.') . " - {$tipeStr}).");
    }

    /**
     * Hapus penyesuaian tagihan berikutnya
     */
    public function clearNextAdjustment(Pppoe $pppoe)
    {
        $pppoe->update([
            'penyesuaian_tagihan' => 0,
            'keterangan_penyesuaian' => null,
            'penyesuaian_tipe' => 'sekali',
        ]);

        return redirect()->back()->with('success', 'Penyesuaian tagihan berikutnya berhasil dinonaktifkan.');
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

            $isRevised = (!empty($invoice->xendit_invoice_id) || $invoice->isExpired() || (float)($invoice->penyesuaian ?? 0) != 0);
            $externalId = $isRevised ? ($invoice->invoice_number . '-R' . time()) : $invoice->invoice_number;

            // Build detailed items breakdown for checkout screen
            $items = [];
            $items[] = [
                'name' => 'Paket Internet ' . ($pppoe && $pppoe->paket ? $pppoe->paket->nama_paket : ''),
                'quantity' => 1,
                'price' => (float) $invoice->amount,
                'category' => 'INTERNET_SERVICE',
            ];

            if (!empty($invoice->penyesuaian) && (float)$invoice->penyesuaian != 0) {
                $penyesuaianVal = (float)$invoice->penyesuaian;
                $items[] = [
                    'name' => !empty($invoice->catatan_penyesuaian) ? ('Penyesuaian: ' . $invoice->catatan_penyesuaian) : 'Penyesuaian Tagihan',
                    'quantity' => 1,
                    'price' => $penyesuaianVal,
                    'category' => $penyesuaianVal < 0 ? 'DISCOUNT' : 'OTHER',
                ];
            }

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

            // Pastikan akumulasi items sama persis dengan total tagihan agar Xendit tidak menolak
            $itemsSum = array_sum(array_map(fn($it) => $it['price'] * $it['quantity'], $items));
            if (abs($itemsSum - (float)$invoice->total) > 0.01) {
                $items = [
                    [
                        'name' => 'Tagihan Internet ' . $invoice->invoice_number,
                        'quantity' => 1,
                        'price' => (float) $invoice->total,
                        'category' => 'INTERNET_SERVICE',
                    ]
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
                'external_id' => $externalId,
                'amount' => (float) $invoice->total,
                'payer_email' => $payerEmail,
                'description' => 'Tagihan Internet ' . $invoice->invoice_number . ' - ' . ($pppoe ? $pppoe->username : ''),
                'invoice_duration' => (int) $durationSeconds,
                'customer' => $customer,
                'items' => $items,
                'success_redirect_url' => $redirectUrl . '?status=success',
                'failure_redirect_url' => $redirectUrl . '?status=failed',
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

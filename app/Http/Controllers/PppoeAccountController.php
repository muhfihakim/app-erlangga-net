<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\PppoeIpService;

class PppoeAccountController extends Controller
{
    public function index(Request $request, PppoeIpService $ipService)
    {
        $filterPaket = $request->input('paket_id', '');
        $filterStatus = $request->input('status_invoice', '');
        $filterTipe = $request->input('tipe_pembayaran', '');
        $filterRouter = $request->input('router', '');

        if ($request->ajax()) {
            $query = \App\Models\Pppoe::with(['preOrder', 'paket', 'customer'])
                ->latest()
                ->select('pppoes.*');

            if (!empty($filterPaket)) {
                $query->where('pppoes.paket_id', $filterPaket);
            }

            if (!empty($filterStatus)) {
                if ($filterStatus === 'isolir') {
                    $query->where('pppoes.is_isolir', true);
                } elseif ($filterStatus === 'non_billing') {
                    $query->where(function($q) {
                        $q->where('pppoes.generate_invoice', false)
                          ->orWhere('pppoes.status_invoice', 'non_billing');
                    });
                } elseif ($filterStatus === 'terbayar') {
                    $query->where('pppoes.status_invoice', 'terbayar')
                          ->where('pppoes.generate_invoice', true);
                } elseif ($filterStatus === 'belum_bayar') {
                    $query->where(function($q) {
                        $q->where('pppoes.status_invoice', 'belum_bayar')
                          ->orWhereNull('pppoes.status_invoice');
                    })->where('pppoes.generate_invoice', true);
                } else {
                    $query->where('pppoes.status_invoice', $filterStatus);
                }
            }

            if (!empty($filterTipe)) {
                $query->where('pppoes.tipe_pembayaran', $filterTipe);
            }

            if (!empty($filterRouter)) {
                if ($filterRouter === 'All') {
                    $query->where(function($q) {
                        $q->where('pppoes.router', 'All')->orWhereNull('pppoes.router')->orWhere('pppoes.router', '');
                    });
                } else {
                    $query->where('pppoes.router', $filterRouter);
                }
            }

            return \Yajra\DataTables\Facades\DataTables::eloquent($query)
                ->addColumn('no', function ($pppoe) {
                    return ''; // Handled by DataTables render
                })
                ->addColumn('pelanggan', function ($pppoe) {
                    $name = $pppoe->preOrder->nama_lengkap ?? ($pppoe->customer->nama_lengkap ?? null);
                    $phone = $pppoe->preOrder->no_whatsapp ?? ($pppoe->customer->no_whatsapp ?? '');
                    
                    if ($name) {
                        return '<div><div style="font-weight: 600; color: #1e293b; font-size: 12.5px; text-transform: none !important;">' . htmlspecialchars($name) . '</div>' . ($phone ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($phone) . '</span>' : '') . '</div>';
                    }

                    if (!empty($pppoe->keterangan)) {
                        return '<div><div style="font-weight: 600; color: #1e293b; font-size: 12.5px; text-transform: none !important;">' . htmlspecialchars($pppoe->keterangan) . '</div><span style="font-size: 10px; color: #64748b; background: #f1f5f9; padding: 1px 6px; border-radius: 4px; font-weight: 600;">PPPoE Mandiri</span></div>';
                    }

                    return '<div><span style="font-size: 11px; color: #94a3b8; font-style: italic; font-weight: 500;">Tanpa Pre-Order</span></div>';
                })
                ->addColumn('username_col', function ($pppoe) {
                    return '<div class="pppoe-username"><div class="pppoe-username-val" style="font-weight: 600; color: #0f172a; font-family: monospace; font-size: 12px; text-transform: none !important;">' . htmlspecialchars($pppoe->username) . '</div><span style="font-size: 11px; color: #94a3b8; font-family: monospace; text-transform: none !important;">Pass: ' . htmlspecialchars($pppoe->password) . '</span></div>';
                })
                ->addColumn('paket', function ($pppoe) {
                    $namaPaket = $pppoe->paket->nama_paket ?? '-';
                    $harga = $pppoe->paket->harga ?? 0;
                    return '<div><span class="badge" style="background:#e0f2fe;color:#0369a1; font-weight:700; font-size:11px; padding:3px 8px; border-radius:6px;">' . htmlspecialchars($namaPaket) . '</span><div style="font-size: 11px; color: #64748b; margin-top:2px; font-weight:600;">Rp ' . number_format($harga, 0, ',', '.') . '</div></div>';
                })
                ->addColumn('router', function ($pppoe) {
                    return '<div style="text-align: center; color: #475569; font-size: 12px; font-weight: 600;">' . htmlspecialchars($pppoe->router ?: 'All') . '</div>';
                })
                ->addColumn('ip_address', function ($pppoe) {
                    return '<div style="text-align: center; font-family: monospace; font-size: 11.5px; color: #334155; font-weight: 600;">' . htmlspecialchars($pppoe->ip_address ?: '-') . '</div>';
                })
                ->addColumn('tipe_pembayaran_badge', function ($pppoe) {
                    if (!$pppoe->generate_invoice || $pppoe->status_invoice === 'non_billing') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f8fafc; color:#94a3b8; border:1px solid #e2e8f0; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:600;">-</span></div>';
                    }
                    $tipe = strtolower($pppoe->tipe_pembayaran ?: 'prepaid');
                    if ($tipe === 'prepaid') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:700;">PREPAID</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:700;">POSTPAID</span></div>';
                })
                ->addColumn('status_invoice_badge', function ($pppoe) {
                    if ($pppoe->is_isolir) {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fee2e2; color:#be123c; border:1px solid #fecdd3; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:800;" title="' . htmlspecialchars($pppoe->isolir_reason ?? 'Isolir manual', ENT_QUOTES) . '"><i class="fa-solid fa-ban mr-1"></i> TERISOLIR</span></div>';
                    }
                    if (!$pppoe->generate_invoice || $pppoe->status_invoice === 'non_billing') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f1f5f9; color:#475569; border:1px solid #cbd5e1; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:700;" title="Tidak ada penagihan atau invoice otomatis"><i class="fa-solid fa-shield-halved mr-1 text-slate-400"></i> NON-BILLING</span></div>';
                    }
                    if ($pppoe->status_invoice === 'terbayar') {
                        return '<div style="text-align: center;"><span class="badge badge-published" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-check mr-1"></i> TERBAYAR</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="background:#fffbeb; color:#b45309; border:1px solid #fde68a; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-clock mr-1"></i> BELUM BAYAR</span></div>';
                })
                ->addColumn('opsi', function ($pppoe) {
                    $tgl = $pppoe->tanggal_aktif ? (is_string($pppoe->tanggal_aktif) ? \Carbon\Carbon::parse($pppoe->tanggal_aktif)->format('Y-m-d') : $pppoe->tanggal_aktif->format('Y-m-d')) : '';
                    $gen = $pppoe->generate_invoice ? '1' : '0';
                    $invoiceRoute = route('invoices.show', $pppoe->id);
                    $btnInvoice = '<a href="' . $invoiceRoute . '" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Kelola Tagihan & Invoice"><i class="fa-solid fa-file-invoice-dollar text-slate-500"></i></a>';
                    
                    $btnIsolir = '';
                    if ($pppoe->is_isolir) {
                        $btnIsolir = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="openBukaIsolirPppoeModal(\'' . route('pppoe_accounts.buka_isolir', $pppoe->id) . '\', \'' . addslashes($pppoe->username) . '\')" title="Buka Isolir / Aktifkan Kembali"><i class="fa-solid fa-unlock-keyhole text-emerald-600"></i></button>';
                    } else {
                        $btnIsolir = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="openIsolirPppoeModal(\'' . route('pppoe_accounts.isolir', $pppoe->id) . '\', \'' . addslashes($pppoe->username) . '\')" title="Isolir Akun PPPoE"><i class="fa-solid fa-ban text-rose-500"></i></button>';
                    }

                    $btnEdit = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="openEditModal(' . $pppoe->id . ', \'' . addslashes($pppoe->username) . '\', \'' . addslashes($pppoe->password) . '\', \'' . $pppoe->paket_id . '\', \'' . addslashes($pppoe->router ?? 'All') . '\', \'' . addslashes($pppoe->ip_address ?? '') . '\', \'' . addslashes($pppoe->tipe_pembayaran ?? 'prepaid') . '\', \'' . $tgl . '\', \'' . addslashes($pppoe->periode_billing ?? 'fixed') . '\', \'' . ($pppoe->pajak ?? 0) . '\', \'' . ($pppoe->diskon ?? 0) . '\', \'' . addslashes($pppoe->status_invoice ?? 'belum_bayar') . '\', \'' . $gen . '\', \'' . addslashes($pppoe->keterangan ?? '') . '\')" title="Edit"><i class="fa-solid fa-pen-to-square text-slate-500"></i></button>';
                    $btnDelete = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg hover:!border-rose-300 hover:!bg-rose-50" onclick="confirmGlobalDelete(\'' . route('pppoe_accounts.destroy', $pppoe->id) . '\', \'PPPoE ' . addslashes($pppoe->username) . '\')" title="Hapus"><i class="fa-solid fa-trash text-slate-500 hover:text-rose-600"></i></button>';
                    return '<div class="flex items-center justify-center gap-1.5 whitespace-nowrap flex-nowrap">' . $btnInvoice . $btnIsolir . $btnEdit . $btnDelete . '</div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('pppoes.username', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.ip_address', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.router', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.keterangan', 'LIKE', "%{$search}%")
                              ->orWhereHas('preOrder', function ($poq) use ($search) {
                                  $poq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                      ->orWhere('no_whatsapp', 'LIKE', "%{$search}%");
                              })
                              ->orWhereHas('customer', function ($cq) use ($search) {
                                  $cq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                      ->orWhere('no_whatsapp', 'LIKE', "%{$search}%");
                              })
                              ->orWhereHas('paket', function ($pq) use ($search) {
                                  $pq->where('nama_paket', 'LIKE', "%{$search}%");
                              });
                        });
                    }
                })
                ->rawColumns(['pelanggan', 'username_col', 'paket', 'router', 'ip_address', 'tipe_pembayaran_badge', 'status_invoice_badge', 'opsi'])
                ->make(true);
        }

        // Stats calculation for the top cards
        $totalPppoeCount = \App\Models\Pppoe::count();
        $totalPaidCount = \App\Models\Pppoe::where('status_invoice', 'terbayar')->where('is_isolir', false)->where('generate_invoice', true)->count();
        $totalUnpaidCount = \App\Models\Pppoe::where(function($q) {
            $q->where('status_invoice', '!=', 'terbayar')->orWhereNull('status_invoice');
        })->where('generate_invoice', true)->count();
        $totalNonBillingCount = \App\Models\Pppoe::where(function($q) {
            $q->where('generate_invoice', false)->orWhere('status_invoice', 'non_billing');
        })->count();
        $totalIsolirCount = \App\Models\Pppoe::where('is_isolir', true)->count();

        $pakets = \App\Models\Paket::orderBy('harga', 'asc')->get();

        try {
            $routers = \App\Models\Nas::orderBy('shortname')->get();
        } catch (\Throwable $e) {
            $routers = collect();
        }

        $existingPoIds = \App\Models\Pppoe::whereNotNull('pre_order_id')->pluck('pre_order_id')->toArray();
        $unlinkedPreOrders = \App\Models\PreOrder::whereNotIn('id', $existingPoIds)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'no_whatsapp', 'alamat_lengkap', 'paket_id']);

        $nextIps = $ipService->getAllNextIps();

        return view('pppoe.index', compact(
            'totalPppoeCount',
            'totalPaidCount',
            'totalUnpaidCount',
            'totalNonBillingCount',
            'totalIsolirCount',
            'pakets',
            'routers',
            'unlinkedPreOrders',
            'nextIps',
            'filterPaket',
            'filterStatus',
            'filterTipe',
            'filterRouter'
        ));
    }

    public function getNextIp(Request $request, PppoeIpService $ipService)
    {
        $paketId = (int) $request->input('paket_id');
        $nextIp = $ipService->getNextIp($paketId);
        return response()->json([
            'success' => !empty($nextIp),
            'ip' => $nextIp,
        ]);
    }

    public function syncRadius()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('radius:sync');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return redirect()->route('pppoe_accounts.index')->with('success', 'Sinkronisasi dengan RADIUS berhasil diselesaikan!');
        } catch (\Throwable $e) {
            return redirect()->route('pppoe_accounts.index')->with('error', 'Gagal sinkronisasi RADIUS: ' . $e->getMessage());
        }
    }

    public function store(Request $request, PppoeIpService $ipService)
    {
        $validated = $request->validate([
            'pre_order_id' => 'nullable|exists:pre_orders,id',
            'keterangan' => 'nullable|string|max:255',
            'username' => 'required|string|max:255|unique:pppoes,username',
            'password' => 'required|string|max:255',
            'paket_id' => 'required|exists:pakets,id',
            'router' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'is_billing_active' => 'nullable',
            'tipe_pembayaran' => 'nullable|string',
            'tanggal_aktif' => 'nullable|date',
            'periode_billing' => 'nullable|string',
            'pajak' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'status_invoice' => 'nullable|string',
            'generate_invoice' => 'nullable',
        ], [
            'username.unique' => 'Username PPPoE ini sudah digunakan oleh akun lain.',
        ]);

        if (empty($validated['ip_address'])) {
            $validated['ip_address'] = $ipService->getNextIp($validated['paket_id']);
        }

        $isBillingActive = $request->boolean('is_billing_active', false);
        if ($isBillingActive) {
            $validated['generate_invoice'] = $request->has('generate_invoice');
            $validated['status_invoice'] = $request->input('status_invoice', 'belum_bayar');
            $validated['tipe_pembayaran'] = $request->input('tipe_pembayaran', 'prepaid');
        } else {
            $validated['generate_invoice'] = false;
            $validated['status_invoice'] = 'non_billing';
            $validated['tipe_pembayaran'] = null;
            $validated['pajak'] = 0;
            $validated['diskon'] = 0;
        }
        unset($validated['is_billing_active']);

        $pppoe = \App\Models\Pppoe::create($validated);

        // Sync ke Radius (radcheck & radreply)
        $radiusSynced = true;
        try {
            \App\Models\Radcheck::create([
                'username' => $pppoe->username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $pppoe->password,
            ]);

            if (!empty($pppoe->ip_address)) {
                \App\Models\Radreply::create([
                    'username' => $pppoe->username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => $pppoe->ip_address,
                ]);
            }

            // 2. Insert ke daloRADIUS tables
            \App\Models\Radusergroup::create([
                'username' => $pppoe->username,
                'groupname' => $pppoe->paket->nama_paket ?? 'default',
                'priority' => 1,
            ]);

            $ownerName = $pppoe->preOrder->nama_lengkap 
                ?? ($request->input('keterangan') ?: $pppoe->username);
            $email = $pppoe->preOrder->alamat_email ?? ($pppoe->username . '@erlangga.net');
            $address = $pppoe->preOrder->alamat_lengkap ?? ($request->input('keterangan') ?: '');
            $phone = $pppoe->preOrder->no_whatsapp ?? '';

            \App\Models\Userinfo::create([
                'username' => $pppoe->username,
                'firstname' => $ownerName,
                'lastname' => '',
                'email' => $email,
                'address' => $address,
                'mobilephone' => $phone,
                'creationdate' => date('Y-m-d H:i:s'),
                'updatedate' => date('Y-m-d H:i:s'),
                'creationby' => 'administrator',
                'updateby' => 'administrator',
            ]);
        } catch (\Throwable $e) {
            $radiusSynced = false;
            \Illuminate\Support\Facades\Log::warning("Radius sync error on PPPoE store ({$pppoe->username}): " . $e->getMessage());
        }

        if ($pppoe->generate_invoice) {
            $paket = \App\Models\Paket::find($pppoe->paket_id);
            if ($paket) {
                $amount = (float) $paket->harga;
                $tax = ($pppoe->pajak !== null && (float)$pppoe->pajak > 0) ? (float)$pppoe->pajak : (float)($paket->pajak ?? 0);
                $discount = ($pppoe->diskon !== null && (float)$pppoe->diskon > 0) ? (float)$pppoe->diskon : (float)($paket->diskon ?? 0);
                $total = max(0, $amount + $tax - $discount);
                $isPaid = ($pppoe->status_invoice === 'terbayar');
                $status = $isPaid ? 'paid' : 'unpaid';
                $dueDate = ($pppoe->tanggal_aktif && \Carbon\Carbon::parse($pppoe->tanggal_aktif)->isFuture()) ? \Carbon\Carbon::parse($pppoe->tanggal_aktif) : \Carbon\Carbon::now()->addDays(7);
                
                $invoice = \App\Models\Invoice::create([
                    'pppoe_id' => $pppoe->id,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                    'amount' => $amount,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'due_date' => $dueDate->format('Y-m-d'),
                    'billing_period' => $dueDate->format('Y-m'),
                    'status' => $status,
                    'payment_method' => $isPaid ? 'cash' : null,
                ]);

                if ($isPaid) {
                    \App\Models\Transaction::create([
                        'invoice_id' => $invoice->id,
                        'payment_method' => 'cash',
                        'amount_paid' => $total,
                        'payment_date' => now(),
                        'status' => 'success',
                    ]);
                }
            }
        }

        $successMessage = $radiusSynced 
            ? 'Akun PPPoE berhasil dibuat & Tersinkronisasi dengan Radius.' 
            : 'Akun PPPoE berhasil disimpan. (Catatan: Database RADIUS belum terhubung/dikonfigurasi).';

        return redirect()->back()->with('success', $successMessage);
    }

    public function update(Request $request, \App\Models\Pppoe $pppoe)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255|unique:pppoes,username,' . $pppoe->id,
            'password' => 'required|string|max:255',
            'paket_id' => 'required|exists:pakets,id',
            'keterangan' => 'nullable|string|max:255',
            'router' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'is_billing_active' => 'nullable',
            'tipe_pembayaran' => 'nullable|string',
            'tanggal_aktif' => 'nullable|date',
            'periode_billing' => 'nullable|string',
            'pajak' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'status_invoice' => 'nullable|string',
            'generate_invoice' => 'nullable',
        ], [
            'username.unique' => 'Username PPPoE ini sudah digunakan oleh akun lain.',
        ]);

        $isBillingActive = $request->boolean('is_billing_active', false);
        if ($isBillingActive) {
            $validated['generate_invoice'] = $request->has('generate_invoice');
            $validated['status_invoice'] = $request->input('status_invoice', 'belum_bayar');
        } else {
            $validated['generate_invoice'] = false;
            $validated['status_invoice'] = 'non_billing';
            $validated['tipe_pembayaran'] = null;
            $validated['pajak'] = 0;
            $validated['diskon'] = 0;
        }
        unset($validated['is_billing_active']);

        $oldStatusInvoice = $pppoe->status_invoice;
        $oldUsername = $pppoe->username;
        $oldPaketId = $pppoe->paket_id;

        $pppoe->update($validated);

        // Sync Invoice Status if status_invoice changed
        if ($oldStatusInvoice !== $pppoe->status_invoice && $isBillingActive) {
            $latestInvoice = \App\Models\Invoice::where('pppoe_id', $pppoe->id)->latest()->first();
            if ($latestInvoice) {
                if ($pppoe->status_invoice === 'terbayar') {
                    $latestInvoice->update([
                        'status' => 'paid',
                        'payment_method' => $latestInvoice->payment_method ?? 'cash',
                    ]);

                    \App\Models\Transaction::firstOrCreate(
                        ['invoice_id' => $latestInvoice->id],
                        [
                            'payment_method' => $latestInvoice->payment_method ?? 'cash',
                            'amount_paid' => $latestInvoice->total,
                            'payment_date' => now(),
                            'status' => 'success',
                        ]
                    );
                } else {
                    $latestInvoice->update([
                        'status' => 'unpaid',
                        'payment_method' => null,
                    ]);
                }
            }
        }

        try {
            \App\Models\Radcheck::where('username', $oldUsername)->delete();
            \App\Models\Radreply::where('username', $oldUsername)->delete();

            \App\Models\Radcheck::create([
                'username' => $pppoe->username,
                'attribute' => 'Cleartext-Password',
                'op' => ':=',
                'value' => $pppoe->password,
            ]);

            if (!empty($pppoe->ip_address)) {
                \App\Models\Radreply::create([
                    'username' => $pppoe->username,
                    'attribute' => 'Framed-IP-Address',
                    'op' => ':=',
                    'value' => $pppoe->ip_address,
                ]);
            }

            \App\Models\Radusergroup::where('username', $oldUsername)->delete();
            \App\Models\Userinfo::where('username', $oldUsername)->delete();

            $pppoe->load(['preOrder', 'paket', 'customer']);

            \App\Models\Radusergroup::create([
                'username' => $pppoe->username,
                'groupname' => $pppoe->paket->nama_paket ?? 'default',
                'priority' => 1,
            ]);

            $ownerName = $pppoe->preOrder->nama_lengkap 
                ?? ($pppoe->customer->nama_lengkap ?? ($pppoe->keterangan ?: $pppoe->username));
            $email = $pppoe->preOrder->alamat_email 
                ?? ($pppoe->customer->alamat_email ?? ($pppoe->username . '@erlangga.net'));
            $address = $pppoe->preOrder->alamat_lengkap 
                ?? ($pppoe->customer->alamat_lengkap ?? ($pppoe->keterangan ?: ''));
            $phone = $pppoe->preOrder->no_whatsapp 
                ?? ($pppoe->customer->no_whatsapp ?? '');

            \App\Models\Userinfo::create([
                'username' => $pppoe->username,
                'firstname' => $ownerName,
                'lastname' => '',
                'email' => $email,
                'address' => $address,
                'mobilephone' => $phone,
                'creationdate' => date('Y-m-d H:i:s'),
                'updatedate' => date('Y-m-d H:i:s'),
                'creationby' => 'administrator',
                'updateby' => 'administrator',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Radius sync warning on PPPoE update ({$pppoe->username}): " . $e->getMessage());
        }

        // Reconnect sesi aktif MikroTik jika paket atau username diubah agar profil baru langsung berlaku
        if ($oldUsername !== $pppoe->username || $oldPaketId !== (int) $pppoe->paket_id) {
            try {
                $mikrotik = app(\App\Services\MikroTikService::class);
                if ($oldUsername && $oldUsername !== $pppoe->username) {
                    $mikrotik->kickPppoeUser($oldUsername);
                }
                $mikrotik->kickPppoeUser($pppoe->username);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Kick user after PPPoE update warning ({$pppoe->username}): " . $e->getMessage());
            }
        }

        return redirect()->back()->with('success', 'Data PPPoE berhasil diperbarui & Tersinkronisasi.');
    }

    public function destroy(\App\Models\Pppoe $pppoe)
    {
        $username = $pppoe->username;
        
        try {
            \App\Models\Radcheck::where('username', $username)->delete();
            \App\Models\Radreply::where('username', $username)->delete();
            \App\Models\Radusergroup::where('username', $username)->delete();
            \App\Models\Userinfo::where('username', $username)->delete();
            app(\App\Services\MikroTikService::class)->kickPppoeUser($username);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Radius delete warning on PPPoE destroy ({$username}): " . $e->getMessage());
        }

        $pppoe->delete();
        return redirect()->back()->with('success', 'Akun PPPoE berhasil dihapus dari Sistem dan Radius.');
    }

    public function isolir(Request $request, \App\Models\Pppoe $pppoe, \App\Services\IsolirService $isolirService)
    {
        $request->validate([
            'reason' => 'nullable|string|max:255',
        ]);

        $reason = $request->input('reason', 'Isolir manual oleh Admin');
        $result = $isolirService->isolate($pppoe->username, $reason, true);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }

    public function bukaIsolir(Request $request, \App\Models\Pppoe $pppoe, \App\Services\IsolirService $isolirService)
    {
        $result = $isolirService->unIsolate($pppoe->username);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json($result, $result['success'] ? 200 : 400);
        }

        if ($result['success']) {
            return redirect()->back()->with('success', $result['message']);
        }
        return redirect()->back()->with('error', $result['message']);
    }
}

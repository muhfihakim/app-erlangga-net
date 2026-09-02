<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PppoeAccountController extends Controller
{
    public function index(Request $request)
    {
        $filterPaket = $request->input('paket_id', '');
        $filterStatus = $request->input('status_invoice', '');
        $filterTipe = $request->input('tipe_pembayaran', '');
        $filterRouter = $request->input('router', '');

        if ($request->ajax()) {
            $query = \App\Models\Pppoe::with(['preOrder', 'paket'])
                ->latest()
                ->select('pppoes.*');

            if (!empty($filterPaket)) {
                $query->where('pppoes.paket_id', $filterPaket);
            }

            if (!empty($filterStatus)) {
                $query->where('pppoes.status_invoice', $filterStatus);
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
                    $name = $pppoe->preOrder->nama_lengkap ?? '-';
                    $phone = $pppoe->preOrder->no_whatsapp ?? '';
                    return '<div><div style="font-weight: 700; color: #1e293b; font-size: 12.5px;">' . htmlspecialchars($name) . '</div>' . ($phone ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($phone) . '</span>' : '') . '</div>';
                })
                ->addColumn('username_col', function ($pppoe) {
                    return '<div><div style="font-weight: 700; color: #0f172a; font-family: monospace; font-size: 12.5px;">' . htmlspecialchars($pppoe->username) . '</div><span style="font-size: 11px; color: #94a3b8; font-family: monospace;">Pass: ' . htmlspecialchars($pppoe->password) . '</span></div>';
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
                    $tipe = strtolower($pppoe->tipe_pembayaran ?: 'prepaid');
                    if ($tipe === 'prepaid') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:700;">PREPAID</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="background:#eff6ff; color:#1e40af; border:1px solid #bfdbfe; font-size:11px; padding:2px 7px; border-radius:6px; font-weight:700;">POSTPAID</span></div>';
                })
                ->addColumn('status_invoice_badge', function ($pppoe) {
                    if ($pppoe->status_invoice === 'terbayar') {
                        return '<div style="text-align: center;"><span class="badge badge-published" style="font-size:11px; padding:3px 8px;"><i class="fa-solid fa-check mr-1"></i> TERBAYAR</span></div>';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="background:#fef2f2; color:#be123c; border:1px solid #fecdd3; font-size:11px; padding:3px 8px; border-radius:6px; font-weight:700;"><i class="fa-solid fa-clock mr-1"></i> BELUM BAYAR</span></div>';
                })
                ->addColumn('opsi', function ($pppoe) {
                    $tgl = $pppoe->tanggal_aktif ? (is_string($pppoe->tanggal_aktif) ? \Carbon\Carbon::parse($pppoe->tanggal_aktif)->format('Y-m-d') : $pppoe->tanggal_aktif->format('Y-m-d')) : '';
                    $gen = $pppoe->generate_invoice ? '1' : '0';
                    $invoiceRoute = route('invoices.show', $pppoe->id);
                    $btnInvoice = '<a href="' . $invoiceRoute . '" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" title="Kelola Tagihan & Xendit"><i class="fa-solid fa-file-invoice-dollar text-emerald-500"></i></a>';
                    $btnEdit = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="openEditModal(' . $pppoe->id . ', \'' . addslashes($pppoe->username) . '\', \'' . addslashes($pppoe->password) . '\', \'' . $pppoe->paket_id . '\', \'' . addslashes($pppoe->router ?? 'All') . '\', \'' . addslashes($pppoe->ip_address ?? '') . '\', \'' . addslashes($pppoe->tipe_pembayaran ?? 'prepaid') . '\', \'' . $tgl . '\', \'' . addslashes($pppoe->periode_billing ?? 'fixed') . '\', \'' . ($pppoe->pajak ?? 0) . '\', \'' . ($pppoe->diskon ?? 0) . '\', \'' . addslashes($pppoe->status_invoice ?? 'belum_bayar') . '\', \'' . $gen . '\')" title="Edit PPPoE"><i class="fa-solid fa-pen-to-square text-amber-500"></i></button>';
                    $btnDelete = '<button type="button" class="topbar-icon-btn !w-8 !h-8 !rounded-lg" onclick="confirmGlobalDelete(\'' . route('pppoe_accounts.destroy', $pppoe->id) . '\', \'PPPoE ' . addslashes($pppoe->username) . '\')" title="Hapus"><i class="fa-solid fa-trash text-rose-500"></i></button>';
                    return '<div class="flex items-center justify-center gap-1">' . $btnInvoice . $btnEdit . $btnDelete . '</div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('pppoes.username', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.ip_address', 'LIKE', "%{$search}%")
                              ->orWhere('pppoes.router', 'LIKE', "%{$search}%")
                              ->orWhereHas('preOrder', function ($poq) use ($search) {
                                  $poq->where('nama_lengkap', 'LIKE', "%{$search}%")
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
        $totalPaidCount = \App\Models\Pppoe::where('status_invoice', 'terbayar')->count();
        $totalUnpaidCount = \App\Models\Pppoe::where('status_invoice', '!=', 'terbayar')->orWhereNull('status_invoice')->count();

        $pakets = \App\Models\Paket::orderBy('harga', 'asc')->get();

        try {
            $routers = \App\Models\Nas::orderBy('shortname')->get();
        } catch (\Throwable $e) {
            $routers = collect();
        }

        return view('pppoe.index', compact(
            'totalPppoeCount',
            'totalPaidCount',
            'totalUnpaidCount',
            'pakets',
            'routers',
            'filterPaket',
            'filterStatus',
            'filterTipe',
            'filterRouter'
        ));
    }

    public function syncRadius()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('radius:sync');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return redirect()->route('pppoe_accounts.index')->with('success', 'Sinkronisasi dengan FreeRADIUS / daloRADIUS berhasil diselesaikan!');
        } catch (\Throwable $e) {
            return redirect()->route('pppoe_accounts.index')->with('error', 'Gagal sinkronisasi FreeRADIUS: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pre_order_id' => 'required|exists:pre_orders,id',
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'paket_id' => 'required|exists:pakets,id',
            'router' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'tipe_pembayaran' => 'nullable|string',
            'tanggal_aktif' => 'nullable|date',
            'periode_billing' => 'nullable|string',
            'pajak' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'status_invoice' => 'nullable|string',
        ]);

        $validated['generate_invoice'] = $request->has('generate_invoice');

        $pppoe = \App\Models\Pppoe::create($validated);

        // Sync ke Radius (radcheck & radreply)
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

        \App\Models\Userinfo::create([
            'username' => $pppoe->username,
            'firstname' => $pppoe->preOrder->nama_lengkap ?? 'Pelanggan',
            'lastname' => '',
            'email' => $pppoe->preOrder->alamat_email ?? '',
            'address' => $pppoe->preOrder->alamat ?? '',
            'mobilephone' => $pppoe->preOrder->no_whatsapp ?? '',
            'creationdate' => date('Y-m-d H:i:s'),
            'updatedate' => date('Y-m-d H:i:s'),
            'creationby' => 'administrator',
            'updateby' => 'administrator',
        ]);

        if ($pppoe->generate_invoice) {
            $paket = \App\Models\Paket::find($pppoe->paket_id);
            if ($paket) {
                $amount = $paket->harga;
                $tax = $pppoe->pajak ?? 0;
                $discount = $pppoe->diskon ?? 0;
                $total = $amount + $tax - $discount;
                $isPaid = ($pppoe->status_invoice === 'terbayar');
                $status = $isPaid ? 'paid' : 'unpaid';
                $dueDate = $pppoe->tanggal_aktif ? \Carbon\Carbon::parse($pppoe->tanggal_aktif)->addDays(7) : \Carbon\Carbon::now()->addDays(7);
                
                $invoice = \App\Models\Invoice::create([
                    'pppoe_id' => $pppoe->id,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT) . '-' . rand(100, 999),
                    'amount' => $amount,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => $status,
                    'payment_method' => $isPaid ? 'cash' : null,
                    'due_date' => $dueDate
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

        return redirect()->back()->with('success', 'PPPoE berhasil dibuat & Tersinkronisasi dengan Radius.');
    }

    public function update(Request $request, \App\Models\Pppoe $pppoe)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string|max:255',
            'paket_id' => 'required|exists:pakets,id',
            'router' => 'nullable|string|max:255',
            'ip_address' => 'nullable|string|max:255',
            'tipe_pembayaran' => 'nullable|string',
            'tanggal_aktif' => 'nullable|date',
            'periode_billing' => 'nullable|string',
            'pajak' => 'nullable|numeric',
            'diskon' => 'nullable|numeric',
            'status_invoice' => 'nullable|string',
        ]);

        $oldStatusInvoice = $pppoe->status_invoice;
        $validated['generate_invoice'] = $request->has('generate_invoice');

        $oldUsername = $pppoe->username;

        $pppoe->update($validated);

        // Sync Invoice Status if status_invoice changed
        if ($oldStatusInvoice !== $pppoe->status_invoice) {
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

        // Update Radius
        try {
            // 1. Hapus record radius lama berdasarkan username lama
            \App\Models\Radcheck::where('username', $oldUsername)->delete();
            \App\Models\Radreply::where('username', $oldUsername)->delete();

            // 2. Insert record baru
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
                    'op' => '=',
                    'value' => $pppoe->ip_address,
                ]);
            }

            // Hapus & Update daloRADIUS tables
            \App\Models\Radusergroup::where('username', $oldUsername)->delete();
            \App\Models\Userinfo::where('username', $oldUsername)->delete();

            $pppoe->load(['preOrder', 'paket']);

            \App\Models\Radusergroup::create([
                'username' => $pppoe->username,
                'groupname' => $pppoe->paket->nama_paket ?? 'default',
                'priority' => 1,
            ]);

            \App\Models\Userinfo::create([
                'username' => $pppoe->username,
                'firstname' => $pppoe->preOrder->nama_lengkap ?? 'Pelanggan',
                'lastname' => '',
                'email' => $pppoe->preOrder->alamat_email ?? '',
                'address' => $pppoe->preOrder->alamat_lengkap ?? '',
                'mobilephone' => $pppoe->preOrder->no_whatsapp ?? '',
                'creationdate' => date('Y-m-d H:i:s'),
                'updatedate' => date('Y-m-d H:i:s'),
                'creationby' => 'administrator',
                'updateby' => 'administrator',
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Radius sync warning on PPPoE update ({$pppoe->username}): " . $e->getMessage());
        }

        return redirect()->back()->with('success', 'Data PPPoE berhasil diperbarui & Tersinkronisasi.');
    }

    public function destroy(\App\Models\Pppoe $pppoe)
    {
        $username = $pppoe->username;
        
        // Hapus dari Radius
        try {
            \App\Models\Radcheck::where('username', $username)->delete();
            \App\Models\Radreply::where('username', $username)->delete();
            \App\Models\Radusergroup::where('username', $username)->delete();
            \App\Models\Userinfo::where('username', $username)->delete();
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Radius delete warning on PPPoE destroy ({$username}): " . $e->getMessage());
        }

        $pppoe->delete();
        return redirect()->back()->with('success', 'Akun PPPoE berhasil dihapus dari Sistem dan Radius.');
    }
}

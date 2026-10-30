<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $filterMethod = $request->input('payment_method', '');
        $filterStatus = $request->input('status', '');
        $filterDateFrom = $request->input('date_from', '');
        $filterDateTo = $request->input('date_to', '');

        if ($request->ajax()) {
            $query = Transaction::with(['invoice.pppoe.preOrder', 'invoice.pppoe.paket'])
                ->latest('payment_date')
                ->select('transactions.*');

            if (!empty($filterMethod)) {
                $query->where('transactions.payment_method', 'LIKE', '%' . $filterMethod . '%');
            }

            if (!empty($filterStatus)) {
                $query->where('transactions.status', $filterStatus);
            }

            if (!empty($filterDateFrom)) {
                $query->whereDate('transactions.payment_date', '>=', $filterDateFrom);
            }

            if (!empty($filterDateTo)) {
                $query->whereDate('transactions.payment_date', '<=', $filterDateTo);
            }

            return DataTables::eloquent($query)
                ->addColumn('no', function ($tx) {
                    return ''; // Handled by DataTables render
                })
                ->addColumn('payment_date_formatted', function ($tx) {
                    if (!$tx->payment_date) return '<div class="text-center text-slate-400">-</div>';
                    $dt = Carbon::parse($tx->payment_date);
                    return '<div style="text-align: center; color: #1e293b; font-size: 12px; font-weight: 600;">' . $dt->format('d/m/Y') . '<br><span style="color:#64748b; font-size: 11px; font-weight: 500;">' . $dt->format('H:i') . ' WIB</span></div>';
                })
                ->addColumn('pelanggan', function ($tx) {
                    $name = $tx->invoice->pppoe->preOrder->nama_lengkap ?? '-';
                    $user = $tx->invoice->pppoe->username ?? '';
                    return '<div><div style="font-weight: 700; color: #0f172a; font-size: 13px;">' . htmlspecialchars($name) . '</div>' . ($user ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($user) . '</span>' : '') . '</div>';
                })
                ->addColumn('invoice_number', function ($tx) {
                    $invNum = $tx->invoice->invoice_number ?? '-';
                    $pppoeId = $tx->invoice->pppoe_id ?? null;
                    if ($pppoeId) {
                        return '<div style="text-align: center;"><a href="' . route('invoices.show', $pppoeId) . '" style="color: #0284c7; font-weight: 700; font-family: monospace; font-size: 12px; text-decoration: none;" class="hover:underline" title="Lihat Invoice">' . htmlspecialchars($invNum) . '</a></div>';
                    }
                    return '<div style="text-align: center; font-weight: 700; font-family: monospace; color: #334155; font-size: 12px;">' . htmlspecialchars($invNum) . '</div>';
                })
                ->addColumn('nominal', function ($tx) {
                    return '<div style="text-align: right; font-weight: 700; color: #0f172a; font-size: 13px; font-family: monospace;">Rp ' . number_format($tx->amount_paid, 0, ',', '.') . '</div>';
                })
                ->addColumn('metode', function ($tx) {
                    $method = strtoupper($tx->payment_method ?? 'CASH');
                    $badgeStyle = 'background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;';
                    $icon = 'fa-money-bill-wave';

                    if (str_contains($method, 'BCA') || str_contains($method, 'BRI') || str_contains($method, 'BNI') || str_contains($method, 'MANDIRI') || str_contains($method, 'PERMATA') || str_contains($method, 'CIMB') || str_contains($method, 'BSI')) {
                        $badgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                        $icon = 'fa-building-columns';
                    } elseif (str_contains($method, 'QRIS')) {
                        $badgeStyle = 'background: #fdf2f8; color: #be185d; border: 1px solid #fbcfe8;';
                        $icon = 'fa-qrcode';
                    } elseif (str_contains($method, 'OVO') || str_contains($method, 'DANA') || str_contains($method, 'SHOPEEPAY') || str_contains($method, 'GOPAY') || str_contains($method, 'EWALLET')) {
                        $badgeStyle = 'background: #faf5ff; color: #7e22ce; border: 1px solid #e9d5ff;';
                        $icon = 'fa-wallet';
                    } elseif (str_contains($method, 'XENDIT') || str_contains($method, 'VIRTUAL_ACCOUNT')) {
                        $badgeStyle = 'background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;';
                        $icon = 'fa-credit-card';
                    } elseif (str_contains($method, 'CASHLESS')) {
                        $badgeStyle = 'background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;';
                        $icon = 'fa-receipt';
                    }

                    return '<div style="text-align: center;"><span class="badge" style="' . $badgeStyle . ' font-size: 11px; padding: 2.5px 8px; border-radius: 6px; font-weight: 700;"><i class="fa-solid ' . $icon . ' mr-1"></i> ' . htmlspecialchars($method) . '</span></div>';
                })
                ->addColumn('status_badge', function ($tx) {
                    $st = strtolower($tx->status ?? 'success');
                    if ($st === 'success') {
                        return '<div style="text-align: center;"><span class="badge badge-published" style="font-size: 11px; padding: 2.5px 8px; font-weight: 700;"><i class="fa-solid fa-circle-check mr-1"></i> BERHASIL</span></div>';
                    } elseif ($st === 'pending') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef3c7; color:#b45309; border:1px solid #fde68a; font-size: 11px; padding: 2.5px 8px; font-weight: 700; border-radius: 6px;"><i class="fa-solid fa-clock mr-1"></i> PENDING</span></div>';
                    } else {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef2f2; color:#be123c; border:1px solid #fecdd3; font-size: 11px; padding: 2.5px 8px; font-weight: 700; border-radius: 6px;"><i class="fa-solid fa-circle-xmark mr-1"></i> ' . strtoupper($st) . '</span></div>';
                    }
                })
                ->addColumn('action', function ($tx) {
                    $invId = $tx->invoice_id;
                    $pppoeId = $tx->invoice->pppoe_id ?? null;
                    $invUrl = $pppoeId ? route('invoices.show', $pppoeId) : '';
                    $printUrl = $invId ? route('invoices.print', $invId) : '';
                    $name = $tx->invoice->pppoe->preOrder->nama_lengkap ?? '-';
                    $user = $tx->invoice->pppoe->username ?? '-';
                    $paket = $tx->invoice->pppoe->paket->nama_paket ?? '-';
                    $wa = $tx->invoice->pppoe->preOrder->no_whatsapp ?? '-';
                    $alamat = $tx->invoice->pppoe->preOrder->alamat_pemasangan ?? '-';
                    $invNum = $tx->invoice->invoice_number ?? '-';
                    $dt = $tx->payment_date ? Carbon::parse($tx->payment_date)->format('d/m/Y, H:i') . ' WIB' : '-';
                    $nominal = 'Rp ' . number_format($tx->amount_paid, 0, ',', '.');
                    $method = strtoupper($tx->payment_method ?? 'CASH');
                    $status = strtoupper($tx->status ?? 'SUCCESS');
                    $xenditId = $tx->xendit_payment_id ?? '-';

                    $payload = htmlspecialchars(json_encode([
                        'id' => $tx->id,
                        'invoice_id' => $invId,
                        'invoice_number' => $invNum,
                        'invoice_url' => $invUrl,
                        'print_url' => $printUrl,
                        'nama' => $name,
                        'username' => $user,
                        'paket' => $paket,
                        'whatsapp' => $wa,
                        'alamat' => $alamat,
                        'tanggal_bayar' => $dt,
                        'nominal' => $nominal,
                        'metode' => $method,
                        'status' => $status,
                        'xendit_id' => $xenditId,
                    ]), ENT_QUOTES, 'UTF-8');

                    return '<div class="flex items-center justify-center gap-1.5 flex-nowrap">
                        <button type="button" class="btn-primary-admin !h-8 !px-2.5 !text-xs !rounded-lg !bg-sky-600 hover:!bg-sky-700 inline-flex items-center justify-center gap-1.5 font-semibold shrink-0 shadow-sm"
                            onclick="showTransactionDetail(' . $payload . ')" title="Lihat Detail Transaksi">
                            <i class="fa-solid fa-eye"></i> Detail
                        </button>
                    </div>';
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('transactions.payment_method', 'LIKE', "%{$search}%")
                              ->orWhere('transactions.amount_paid', 'LIKE', "%{$search}%")
                              ->orWhere('transactions.xendit_payment_id', 'LIKE', "%{$search}%")
                              ->orWhereHas('invoice', function ($iq) use ($search) {
                                  $iq->where('invoice_number', 'LIKE', "%{$search}%")
                                     ->orWhereHas('pppoe', function ($pq) use ($search) {
                                         $pq->where('username', 'LIKE', "%{$search}%")
                                            ->orWhereHas('preOrder', function ($poq) use ($search) {
                                                $poq->where('nama_lengkap', 'LIKE', "%{$search}%")
                                                    ->orWhere('no_whatsapp', 'LIKE', "%{$search}%");
                                            });
                                     });
                              });
                        });
                    }
                })
                ->rawColumns(['payment_date_formatted', 'pelanggan', 'invoice_number', 'nominal', 'metode', 'status_badge', 'action'])
                ->make(true);
        }

        // Stats calculation for the top cards (matching Radboox metrics)
        $now = Carbon::now();
        $startThisMonth = $now->copy()->startOfMonth();
        $endThisMonth = $now->copy()->endOfMonth();
        $startLastMonth = $now->copy()->subMonth()->startOfMonth();
        $endLastMonth = $now->copy()->subMonth()->endOfMonth();

        // 1. Monthly Earning (This Month vs Last Month) based on payment_date
        $monthlyEarning = Transaction::where('status', 'success')
            ->where(function ($q) use ($startThisMonth, $endThisMonth) {
                $q->whereBetween('payment_date', [$startThisMonth, $endThisMonth])
                  ->orWhere(function ($sq) use ($startThisMonth, $endThisMonth) {
                      $sq->whereNull('payment_date')
                         ->whereBetween('created_at', [$startThisMonth, $endThisMonth]);
                  });
            })
            ->sum('amount_paid');

        $lastMonthEarning = Transaction::where('status', 'success')
            ->where(function ($q) use ($startLastMonth, $endLastMonth) {
                $q->whereBetween('payment_date', [$startLastMonth, $endLastMonth])
                  ->orWhere(function ($sq) use ($startLastMonth, $endLastMonth) {
                      $sq->whereNull('payment_date')
                         ->whereBetween('created_at', [$startLastMonth, $endLastMonth]);
                  });
            })
            ->sum('amount_paid');

        // 2. Monthly Expenses (Kasbon / Operasional)
        $monthlyExpenses = \App\Models\Kasbon::whereBetween('tanggal', [$startThisMonth->toDateString(), $endThisMonth->toDateString()])->sum('nominal');
        $lastMonthExpenses = \App\Models\Kasbon::whereBetween('tanggal', [$startLastMonth->toDateString(), $endLastMonth->toDateString()])->sum('nominal');

        // 3. Monthly Profit (Earning - Expenses)
        $monthlyProfit = max(0, $monthlyEarning - $monthlyExpenses);

        // 4. Monthly Transaction Count (This Month vs Last Month)
        $monthlyTxCount = Transaction::where(function ($q) use ($startThisMonth, $endThisMonth) {
            $q->whereBetween('payment_date', [$startThisMonth, $endThisMonth])
              ->orWhere(function ($sq) use ($startThisMonth, $endThisMonth) {
                  $sq->whereNull('payment_date')
                     ->whereBetween('created_at', [$startThisMonth, $endThisMonth]);
              });
        })->count();

        $lastMonthTxCount = Transaction::where(function ($q) use ($startLastMonth, $endLastMonth) {
            $q->whereBetween('payment_date', [$startLastMonth, $endLastMonth])
              ->orWhere(function ($sq) use ($startLastMonth, $endLastMonth) {
                  $sq->whereNull('payment_date')
                     ->whereBetween('created_at', [$startLastMonth, $endLastMonth]);
              });
        })->count();

        return view('transactions.index', compact(
            'monthlyEarning',
            'lastMonthEarning',
            'monthlyExpenses',
            'lastMonthExpenses',
            'monthlyProfit',
            'monthlyTxCount',
            'lastMonthTxCount',
            'filterMethod',
            'filterStatus',
            'filterDateFrom',
            'filterDateTo'
        ));
    }
}

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
                    return '<div style="text-align: center; color: #475569; font-size: 12px; font-weight: 600;">' . $dt->format('d/m/Y') . '<br><span style="color:#94a3b8; font-size: 11px;">' . $dt->format('H:i') . ' WIB</span></div>';
                })
                ->addColumn('pelanggan', function ($tx) {
                    $name = $tx->invoice->pppoe->preOrder->nama_lengkap ?? '-';
                    $user = $tx->invoice->pppoe->username ?? '';
                    return '<div><div style="font-weight: 700; color: #1e293b; font-size: 12.5px;">' . htmlspecialchars($name) . '</div>' . ($user ? '<span style="font-size: 11px; color: #64748b; font-weight: 500;">' . htmlspecialchars($user) . '</span>' : '') . '</div>';
                })
                ->addColumn('invoice_number', function ($tx) {
                    $invNum = $tx->invoice->invoice_number ?? '-';
                    $pppoeId = $tx->invoice->pppoe_id ?? null;
                    if ($pppoeId) {
                        return '<div style="text-align: center;"><a href="' . route('invoices.show', $pppoeId) . '" style="color: #4f46e5; font-weight: 700; font-family: monospace; font-size: 12px; text-decoration: none;" title="Lihat Invoice">' . htmlspecialchars($invNum) . '</a></div>';
                    }
                    return '<div style="text-align: center; font-weight: 700; font-family: monospace; color: #334155; font-size: 12px;">' . htmlspecialchars($invNum) . '</div>';
                })
                ->addColumn('nominal', function ($tx) {
                    return '<div style="text-align: right; font-weight: 500; color: #334155; font-size: 12.5px;">Rp ' . number_format($tx->amount_paid, 0, ',', '.') . '</div>';
                })
                ->addColumn('metode', function ($tx) {
                    $method = strtoupper($tx->payment_method ?? 'CASH');
                    if (str_contains($method, 'XENDIT') || str_contains($method, 'QRIS') || str_contains($method, 'CASHLESS')) {
                        $badgeStyle = 'background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe;';
                    } else {
                        $badgeStyle = 'background: #ecfdf5; color: #047857; border: 1px solid #a7f3d0;';
                    }
                    return '<div style="text-align: center;"><span class="badge" style="' . $badgeStyle . ' font-size: 11px; padding: 3px 8px; border-radius: 6px; font-weight: 700;">' . htmlspecialchars($method) . '</span></div>';
                })
                ->addColumn('status_badge', function ($tx) {
                    $st = strtolower($tx->status ?? 'success');
                    if ($st === 'success') {
                        return '<div style="text-align: center;"><span class="badge badge-published" style="font-size: 11px; padding: 3px 8px;"><i class="fa-solid fa-check-circle mr-1"></i> SUCCESS</span></div>';
                    } elseif ($st === 'pending') {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef3c7;color:#b45309; font-size: 11px; padding: 3px 8px;"><i class="fa-solid fa-clock mr-1"></i> PENDING</span></div>';
                    } else {
                        return '<div style="text-align: center;"><span class="badge" style="background:#fef2f2;color:#be123c; font-size: 11px; padding: 3px 8px;">' . strtoupper($st) . '</span></div>';
                    }
                })
                ->filter(function ($query) use ($request) {
                    if ($search = $request->input('search.value')) {
                        $query->where(function ($q) use ($search) {
                            $q->where('transactions.payment_method', 'LIKE', "%{$search}%")
                              ->orWhere('transactions.amount_paid', 'LIKE', "%{$search}%")
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
                ->rawColumns(['payment_date_formatted', 'pelanggan', 'invoice_number', 'nominal', 'metode', 'status_badge'])
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

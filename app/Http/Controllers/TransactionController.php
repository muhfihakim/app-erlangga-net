<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index()
    {
        $transactions = Transaction::with('invoice.pppoe.preOrder')->latest()->get();
        return view('transactions.index', compact('transactions'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PppoeAccountController extends Controller
{
    public function index()
    {
        $pppoes = \App\Models\Pppoe::with('preOrder')->latest()->get();
        return view('pppoe.index', compact('pppoes'));
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

        \App\Models\Pppoe::create($validated);

        return redirect()->back()->with('success', 'PPPoE berhasil dibuat.');
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

        $validated['generate_invoice'] = $request->has('generate_invoice');

        $pppoe->update($validated);

        return redirect()->back()->with('success', 'Data PPPoE berhasil diperbarui.');
    }

    public function destroy(\App\Models\Pppoe $pppoe)
    {
        $pppoe->delete();
        return redirect()->back()->with('success', 'Akun PPPoE berhasil dihapus.');
    }
}

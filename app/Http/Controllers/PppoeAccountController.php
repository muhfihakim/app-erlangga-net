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

        if ($pppoe->generate_invoice && $pppoe->tanggal_aktif) {
            $paket = \App\Models\Paket::find($pppoe->paket_id);
            if ($paket) {
                $amount = $paket->harga;
                $tax = $pppoe->pajak ?? 0;
                $discount = $pppoe->diskon ?? 0;
                $total = $amount + $tax - $discount;
                
                \App\Models\Invoice::create([
                    'pppoe_id' => $pppoe->id,
                    'invoice_number' => 'INV-' . date('Ymd') . '-' . str_pad($pppoe->id, 4, '0', STR_PAD_LEFT),
                    'amount' => $amount,
                    'tax' => $tax,
                    'discount' => $discount,
                    'total' => $total,
                    'status' => 'unpaid',
                    'due_date' => \Carbon\Carbon::parse($pppoe->tanggal_aktif)->addDays(7)
                ]);
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

        $validated['generate_invoice'] = $request->has('generate_invoice');

        $oldUsername = $pppoe->username;

        $pppoe->update($validated);

        // Update Radius
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
            'address' => $pppoe->preOrder->alamat ?? '',
            'mobilephone' => $pppoe->preOrder->no_whatsapp ?? '',
            'creationdate' => date('Y-m-d H:i:s'),
            'updatedate' => date('Y-m-d H:i:s'),
            'creationby' => 'administrator',
            'updateby' => 'administrator',
        ]);

        return redirect()->back()->with('success', 'Data PPPoE berhasil diperbarui & Tersinkronisasi dengan Radius.');
    }

    public function destroy(\App\Models\Pppoe $pppoe)
    {
        $username = $pppoe->username;
        
        // Hapus dari Radius
        \App\Models\Radcheck::where('username', $username)->delete();
        \App\Models\Radreply::where('username', $username)->delete();
        \App\Models\Radusergroup::where('username', $username)->delete();
        \App\Models\Userinfo::where('username', $username)->delete();

        $pppoe->delete();
        return redirect()->back()->with('success', 'Akun PPPoE berhasil dihapus dari Sistem dan Radius.');
    }
}

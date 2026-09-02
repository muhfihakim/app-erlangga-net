<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'pppoe_id',
        'invoice_number',
        'amount',
        'tax',
        'discount',
        'total',
        'status',
        'due_date',
        'payment_method',
        'xendit_invoice_id',
        'xendit_invoice_url',
    ];

    public function pppoe()
    {
        return $this->belongsTo(Pppoe::class, 'pppoe_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }

    /**
     * Mark invoice as paid, update transaction, extend PPPoE active date, and restore FreeRADIUS package profile
     */
    public function markAsPaid(string $paymentMethod, $amountPaid = null, ?string $xenditPaymentId = null): bool
    {
        if ($this->status === 'paid') {
            return true;
        }

        $amountPaid = $amountPaid ?? $this->total;

        $this->update([
            'status' => 'paid',
            'payment_method' => $paymentMethod,
        ]);

        Transaction::create([
            'invoice_id' => $this->id,
            'payment_method' => $paymentMethod,
            'amount_paid' => $amountPaid,
            'payment_date' => now(),
            'status' => 'success',
            'xendit_payment_id' => $xenditPaymentId,
        ]);

        $this->load('pppoe.paket');
        $pppoe = $this->pppoe;

        if ($pppoe) {
            // 1. Extend active date (+1 month)
            $currentAktif = $pppoe->tanggal_aktif ? \Carbon\Carbon::parse($pppoe->tanggal_aktif) : now();
            $newAktif = $currentAktif->isPast() ? now()->addMonth() : $currentAktif->addMonth();

            $pppoe->update([
                'status_invoice' => 'terbayar',
                'tanggal_aktif' => $newAktif,
            ]);

            // 2. Restore FreeRADIUS radusergroup to package name (un-isolir)
            if ($pppoe->paket && $pppoe->paket->nama_paket) {
                try {
                    Radusergroup::where('username', $pppoe->username)
                        ->update(['groupname' => $pppoe->paket->nama_paket]);
                } catch (\Throwable $e) {
                    \Illuminate\Support\Facades\Log::warning("Gagal restore FreeRADIUS group untuk {$pppoe->username}: " . $e->getMessage());
                }
            }

            // 3. Kick active PPPoE session on MikroTik so speed immediately normalizes
            try {
                app(\App\Services\MikroTikService::class)->kickPppoeUser($pppoe->username);
            } catch (\Throwable $e) {
                // Ignore if router is not reachable
            }
        }

        return true;
    }
}

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
            $currentAktif = $pppoe->tanggal_aktif ? \Carbon\Carbon::parse($pppoe->tanggal_aktif) : now();
            $periode = strtolower($pppoe->periode_billing ?? 'fixed');

            if ($periode === 'renewal') {
                $newAktif = $currentAktif->isPast() ? now()->addMonth() : $currentAktif->copy()->addMonth();
            } else {
                $newAktif = $currentAktif->copy()->addMonth();
                if ($newAktif->isPast()) {
                    $day = $currentAktif->day;
                    $newAktif = now()->copy()->addMonth()->setDay(min($day, now()->addMonth()->daysInMonth));
                }
            }

            $hasRemainingUnpaid = static::where('pppoe_id', $pppoe->id)
                ->where('id', '!=', $this->id)
                ->where('status', 'unpaid')
                ->exists();

            $pppoe->update([
                'status_invoice' => $hasRemainingUnpaid ? 'belum_bayar' : 'terbayar',
                'tanggal_aktif' => $newAktif,
            ]);

            // Buka isolir di FreeRADIUS & MikroTik dan pulihkan Framed-IP-Address statis
            try {
                app(\App\Services\IsolirService::class)->unIsolate($pppoe->username);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal unIsolate di Invoice ({$pppoe->username}): " . $e->getMessage());
            }

            // Kirim Notifikasi Push FCM & In-App Pembayaran Berhasil ke Pelanggan
            try {
                app(\App\Services\FcmService::class)->sendInvoicePaidNotification($this);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning("Gagal kirim FCM Invoice Paid (#{$this->invoice_number}): " . $e->getMessage());
            }
        }

        return true;
    }
}

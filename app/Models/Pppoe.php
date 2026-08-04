<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pppoe extends Model
{
    protected $fillable = [
        'pre_order_id',
        'username',
        'password',
        'paket_id',
        'router',
        'ip_address',
        'tipe_pembayaran',
        'tanggal_aktif',
        'periode_billing',
        'pajak',
        'diskon',
        'status_invoice',
        'generate_invoice',
    ];

    protected $casts = [
        'tanggal_aktif' => 'date',
        'generate_invoice' => 'boolean',
    ];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }
}

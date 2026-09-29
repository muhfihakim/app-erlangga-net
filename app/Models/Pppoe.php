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
        'is_isolir',
        'isolir_reason',
        'isolir_at',
    ];

    protected $casts = [
        'tanggal_aktif' => 'date',
        'generate_invoice' => 'boolean',
        'is_isolir' => 'boolean',
        'isolir_at' => 'datetime',
    ];

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class, 'pppoe_id');
    }
}

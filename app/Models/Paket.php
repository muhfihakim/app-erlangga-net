<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $fillable = [
        'nama_paket',
        'harga',
        'pajak',
        'diskon',
        'rate_limit',
        'local_address',
        'ip_pool',
        'pool_range',
        'keterangan',
    ];

    public function getTotalHargaAttribute(): float
    {
        return max(0, (float)$this->harga + (float)($this->pajak ?? 0) - (float)($this->diskon ?? 0));
    }

    public function customers()
    {
        return $this->hasMany(Customer::class, 'paket_id');
    }
}

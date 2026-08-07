<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paket extends Model
{
    protected $fillable = [
        'nama_paket',
        'harga',
        'rate_limit',
        'keterangan',
    ];

    public function customers()
    {
        return $this->hasMany(Customer::class, 'paket_id');
    }
}

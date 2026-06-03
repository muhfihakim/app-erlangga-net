<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PreOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_sales',
        'nama_sales',
        'paket_internet',
        'nama_lengkap',
        'no_identitas',
        'no_whatsapp',
        'alamat_email',
        'alamat_lengkap',
        'titik_koordinat',
        'foto_ktp',
        'foto_rumah',
        'is_unspek',
        'keterangan_unspek',
        'is_unspek_approved',
    ];

    public function sales()
    {
        return $this->belongsTo(User::class, 'id_sales');
    }
}

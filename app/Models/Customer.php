<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'pre_order_id',
        'id_sales',
        'id_teknisi',
        'nama_lengkap',
        'alamat_lengkap',
        'no_whatsapp',
        'paket_internet',
        'id_pelanggan',
        'pppoe_username',
        'pppoe_password',
        'jenis_modem',
        'sn_perangkat',
        'pembayaran',
        'partner',
        'partner_ids',
        'keterangan',
        'foto_petugas',
        'foto_terpasang',
        'foto_speedtest',
    ];

    // public function preOrder()
    // {
    //     return $this->belongsTo(PreOrder::class);
    // }

    // Sangat penting agar data ID tersimpan sebagai JSON di DB dan Array di PHP
    protected $casts = [
        'partner_ids' => 'array',
    ];

    public function preorder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }

    public function teknisiUtama()
    {
        return $this->belongsTo(User::class, 'id_teknisi');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'pre_order_id',
        'id_sales',
        'id_teknisi',
        'nama_lengkap',
        'alamat_lengkap',
        'no_whatsapp',
        'paket_id',
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

    protected $casts = [
        'partner_ids' => 'array',
        'deleted_at'  => 'datetime',
    ];

    // public function preorder()
    // {
    //     return $this->belongsTo(PreOrder::class, 'pre_order_id');
    // }

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }


    public function sales()
    {
        return $this->belongsTo(User::class, 'id_sales');
    }

    public function teknisiUtama()
    {
        return $this->belongsTo(User::class, 'id_teknisi');
    }

    public function maintenances()
    {
        return $this->hasMany(Maintenance::class, 'customer_id');
    }

    public function dismantles()
    {
        return $this->hasMany(Dismantle::class, 'customer_id');
    }

    public function paket()
    {
        return $this->belongsTo(Paket::class, 'paket_id');
    }
}

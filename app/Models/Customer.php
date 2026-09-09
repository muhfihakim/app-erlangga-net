<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Sanctum\HasApiTokens;

class Customer extends Model
{
    use HasApiTokens, HasFactory, SoftDeletes;

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

    public function pppoeAccount()
    {
        return $this->hasOne(Pppoe::class, 'pre_order_id', 'pre_order_id');
    }

    /**
     * Dapatkan instance PPPoE terkait pelanggan
     */
    public function getPppoeAttribute()
    {
        return Pppoe::where('username', $this->pppoe_username)
            ->orWhere(function ($q) {
                if ($this->pre_order_id) {
                    $q->where('pre_order_id', $this->pre_order_id);
                }
            })->first();
    }

    /**
     * Generate ID Pelanggan berikutnya secara otomatis melanjutkan ID numerik tertinggi.
     */
    public static function generateNextIdPelanggan(): string
    {
        $latestCustomer = static::withTrashed()
            ->whereNotNull('id_pelanggan')
            ->whereRaw("id_pelanggan REGEXP '^[0-9]+$'")
            ->orderByRaw('CAST(id_pelanggan AS UNSIGNED) DESC')
            ->first();

        if ($latestCustomer && is_numeric($latestCustomer->id_pelanggan)) {
            return bcadd((string)$latestCustomer->id_pelanggan, '1', 0);
        }

        return '231140000001';
    }

    /**
     * Generate rekomendasi username PPPoE otomatis berdasarkan nama pelanggan.
     */
    public static function generateSuggestedPppoeUsername(?string $namaLengkap): string
    {
        $nama = trim($namaLengkap ?? 'user');
        $words = preg_split('/\s+/', $nama);
        $cleanFirst = ucfirst(strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $words[0] ?? 'user')));
        
        $base = $cleanFirst;
        if (isset($words[1]) && !empty($words[1])) {
            $base .= strtolower(substr(preg_replace('/[^a-zA-Z0-9]/', '', $words[1]), 0, 3));
        }

        $domain = '@erlangga.net';
        $suggested = $base . $domain;
        $counter = 1;

        while (static::where('pppoe_username', $suggested)->exists() || \App\Models\Pppoe::where('username', $suggested)->exists()) {
            $suggested = $base . $counter . $domain;
            $counter++;
        }

        return $suggested;
    }
}

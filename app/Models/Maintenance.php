<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Maintenance extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'id_admin',
        'keterangan_kendala',
        'foto_kendala_awal',
        'id_teknisi_eksekutor',
        'partner_ids',
        'partner',
        'kategori_kendala',
        'sn_baru',
        'sn_lama',
        'solusi_kendala',
        'foto_sebelum_perbaikan',
        'foto_setelah_perbaikan',
        'foto_speedtest',
        'foto_selfie_teknisi',
        'completed_at',
        'status'
    ];

    protected $casts = [
        'partner_ids' => 'array',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        // Menggunakan withTrashed() agar data pelanggan yang dihapus tetap muncul di laporan
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    public function teknisiEksekutor()
    {
        return $this->belongsTo(User::class, 'id_teknisi_eksekutor');
    }
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}

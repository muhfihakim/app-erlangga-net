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
        'id_teknisi_delegasi',
        'keterangan_kendala',
        'foto_kendala_awal',
        'delegated_at',
        'id_teknisi_eksekutor',
        'partner_ids',
        'partner',
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
        'delegated_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
    public function teknisiDelegasi()
    {
        return $this->belongsTo(User::class, 'id_teknisi_delegasi');
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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dismantle extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'id_admin',
        'id_teknisi_eksekutor',
        'keterangan_admin',
        'foto_pencabutan',
        'status',
        'completed_at'
    ];

    /**
     * Casting tipe data otomatis untuk tanggal agar dapat digunakan oleh Carbon.
     */
    protected $casts = [
        'completed_at' => 'datetime',
    ];

    /**
     * Relasi ke Customer.
     * withTrashed() memungkinkan data pelanggan yang sudah berhenti berlangganan (soft deleted)
     * tetap muncul di laporan dismantle teknisi.
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id')->withTrashed();
    }

    /**
     * Relasi ke Teknisi yang melakukan pencabutan perangkat (Eksekutor).
     */
    public function teknisiEksekutor()
    {
        return $this->belongsTo(User::class, 'id_teknisi_eksekutor');
    }

    /**
     * Relasi ke Admin yang membuat penugasan dismantle.
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'id_admin');
    }
}

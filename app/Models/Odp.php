<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Odp extends Model
{
    use HasFactory;

    protected $table = 'odps';

    protected $fillable = [
        'jenis_odp',
        'lokasi_odp',
        'titik_koordinat',
        'keterangan',
        'foto_odp',
    ];

    /**
     * Hitung sisa port yang tersedia.
     */
    public function getSisaPortAttribute()
    {
        return $this->kapasitas - $this->terisi;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    use HasFactory;

    protected $fillable = [
        'teknisi_id',
        'nominal',
        'keterangan',
        'bukti',
        'tanggal',
    ];

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kasbon extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'teknisi_id',
        'nominal',
        'keterangan',
        'bukti',
        'tanggal',
        'status', // 'pending', 'approved', 'rejected'
        'approved_by',
        'approved_at',
        'catatan_admin',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'approved_at' => 'datetime',
        'nominal' => 'float',
    ];

    protected static function booted()
    {
        static::saving(function ($kasbon) {
            if ($kasbon->user_id && !$kasbon->teknisi_id) {
                $kasbon->teknisi_id = $kasbon->user_id;
            } elseif ($kasbon->teknisi_id && !$kasbon->user_id) {
                $kasbon->user_id = $kasbon->teknisi_id;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function teknisi()
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}

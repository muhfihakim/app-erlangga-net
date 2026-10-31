<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackupLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'user_name',
        'backup_type',
        'backup_type_label',
        'file_name',
        'file_path',
        'file_size',
        'total_rows',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        if ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2, ',', '.') . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2, ',', '.') . ' KB';
        }
        return $bytes . ' B';
    }

    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->locale('id')->translatedFormat('d F Y, H:i') . ' WIB' : '-';
    }

    public function getDiffForHumansIdAttribute(): string
    {
        return $this->created_at ? $this->created_at->locale('id')->diffForHumans() : '-';
    }
}

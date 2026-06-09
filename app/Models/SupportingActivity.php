<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportingActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'activity_type',
        'description',
        'main_technician_id',
        'partner_ids',
        'photo',
        'activity_date',
        'created_by'
    ];

    protected $casts = [
        'partner_ids' => 'array',
        'activity_date' => 'date',
    ];

    public function mainTechnician()
    {
        return $this->belongsTo(User::class, 'main_technician_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

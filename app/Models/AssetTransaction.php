<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'type',
        'quantity',
        'date',
        'user_id',
        'external_person_name',
        'description',
    ];

    /**
     * Get the asset that owns the transaction.
     */
    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    /**
     * Get the user (staff) associated with the transaction.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

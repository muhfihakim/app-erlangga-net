<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsAppMessage extends Model
{
    use HasFactory;

    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'phone',
        'customer_name',
        'customer_id',
        'pppoe_id',
        'pre_order_id',
        'sender_id',
        'direction',
        'type',
        'message',
        'media_url',
        'status',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function pppoe()
    {
        return $this->belongsTo(Pppoe::class, 'pppoe_id');
    }

    public function preOrder()
    {
        return $this->belongsTo(PreOrder::class, 'pre_order_id');
    }

    public function scopeForPhone($query, string $phone)
    {
        $digits = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($digits, '08')) {
            $digits = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $digits = '62' . $digits;
        }

        // Match normalized or original
        return $query->where(function ($q) use ($phone, $digits) {
            $q->where('phone', $digits)
              ->orWhere('phone', $phone);
        });
    }
}

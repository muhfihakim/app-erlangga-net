<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'pppoe_id',
        'invoice_number',
        'amount',
        'tax',
        'discount',
        'total',
        'status',
        'due_date',
        'payment_method',
        'xendit_invoice_id',
        'xendit_invoice_url',
    ];

    public function pppoe()
    {
        return $this->belongsTo(Pppoe::class, 'pppoe_id');
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'invoice_id');
    }
}

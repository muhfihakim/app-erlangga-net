<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinanceSetting extends Model
{
    use HasFactory;

    protected $fillable = ['activation_fee'];
}

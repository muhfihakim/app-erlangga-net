<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Radgroupcheck extends Model
{
    protected $connection = 'radius';
    protected $table = 'radgroupcheck';
    public $timestamps = false;
    protected $fillable = ['groupname', 'attribute', 'op', 'value'];
}

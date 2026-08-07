<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Userinfo extends Model
{
    protected $connection = 'radius';
    protected $table = 'userinfo';
    public $timestamps = false;
    protected $fillable = [
        'username', 'firstname', 'lastname', 'email', 'department', 'company',
        'workphone', 'homephone', 'mobilephone', 'address', 'city', 'state',
        'country', 'zip', 'notes', 'changeuserinfo', 'portalloginpassword',
        'creationdate', 'creationby', 'updatedate', 'updateby'
    ];
}

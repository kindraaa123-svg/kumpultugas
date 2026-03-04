<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'role';
    protected $primaryKey = 'roleid';
    public $timestamps = false;
    protected $fillable = ['rolename'];
}

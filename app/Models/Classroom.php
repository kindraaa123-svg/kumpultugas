<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    protected $table = 'class';
    protected $primaryKey = 'classid';
    public $timestamps = false;
    protected $fillable = ['classname'];
}

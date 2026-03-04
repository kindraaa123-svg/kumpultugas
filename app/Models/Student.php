<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    protected $table = 'student';
    protected $primaryKey = 'studentid';
    public $timestamps = false;
    protected $fillable = ['name', 'email', 'phonenumber', 'classid', 'userid'];
}

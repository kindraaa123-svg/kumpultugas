<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employer extends Model
{
    protected $table = 'employer';
    protected $primaryKey = 'employerid';
    public $timestamps = false;
    protected $fillable = ['name', 'email', 'phonenumber', 'roleid', 'userid'];
}

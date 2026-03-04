<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $primaryKey = 'jadwal_id';
    public $timestamps = false;
    protected $fillable = ['academic_year_id', 'block_id', 'classid'];
}

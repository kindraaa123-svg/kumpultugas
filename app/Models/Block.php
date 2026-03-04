<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    protected $table = 'block';
    protected $primaryKey = 'block_id';
    protected $fillable = ['academic_year_id', 'name', 'order_no', 'day_of_week', 'date_start', 'date_end'];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $table = 'academic_year';
    protected $primaryKey = 'academic_year_id';
    protected $fillable = ['name', 'start_date', 'end_date', 'is_active'];
}

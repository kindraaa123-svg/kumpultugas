<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $table = 'schedule';
    protected $primaryKey = 'scheduleid';
    protected $fillable = ['academic_year_id', 'block_id', 'courseid', 'classid', 'teacherid', 'session'];

    public function course() {
        return $this->belongsTo(Course::class, 'courseid', 'courseid');
    }

    public function teacher() {
        return $this->belongsTo(Teacher::class, 'teacherid', 'teacherid');
    }

    public function classroom() {
        return $this->belongsTo(Classroom::class, 'classid', 'classid');
    }
}

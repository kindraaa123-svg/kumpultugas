<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    protected $table = 'assignment';
    protected $primaryKey = 'assignmentid';
    public $timestamps = false;
    protected $fillable = ['scheduleid', 'name', 'description', 'grading_mode', 'auto_score', 'time_start', 'time_end', 'created_at'];

    public function schedule() {
        return $this->belongsTo(Schedule::class, 'scheduleid', 'scheduleid');
    }
}

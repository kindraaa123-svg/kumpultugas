<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quest extends Model
{
    protected $table = 'quest';
    protected $primaryKey = 'questid';
    public $timestamps = true;
    protected $fillable = ['assignmentid', 'file', 'description', 'studentid', 'score', 'feedback'];
}

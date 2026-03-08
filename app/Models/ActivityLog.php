<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $table = 'activity_logs';

    protected $fillable = [
        'username',
        'role',
        'ip_address',
        'latitude',
        'longitude',
        'action',
        'user_agent',
    ];
}

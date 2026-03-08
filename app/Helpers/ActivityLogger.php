<?php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;

class ActivityLogger
{
    public static function log($action, $ip = null)
    {
        $username = Session::get('username') ?? 'Guest';
        $role = Session::get('role') ?? 'Guest';
        
        // If not in session (e.g. login attempt), try to get from request or passed data
        // But for this helper, we assume session is populated for logged in users
        // or we pass 'Guest' for pre-login.

        ActivityLog::create([
            'username' => $username,
            'role' => $role,
            'ip_address' => $ip ?? Request::ip(),
            'latitude' => Session::get('latitude'),
            'longitude' => Session::get('longitude'),
            'action' => $action,
            'user_agent' => Request::header('User-Agent'),
        ]);
    }
}

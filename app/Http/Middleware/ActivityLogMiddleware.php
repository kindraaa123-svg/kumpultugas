<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\ActivityLogger;
use Illuminate\Support\Facades\Session;

class ActivityLogMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Only log if user is logged in and method is not GET (or log specific GET routes if needed)
        // For this request, user wants to log "mulai dari login hingga logout", implying navigation.
        // So we log GET requests too, but maybe filter out assets or ajax calls if too noisy.
        // Let's log everything for now as requested.

        if (Session::has('userid')) {
             $action = $request->method() . ' ' . $request->path();
             
             // Ignore some paths to avoid spam
             if (!str_starts_with($request->path(), 'debugbar') && !str_starts_with($request->path(), '_')) {
                 ActivityLogger::log($action, $request->ip());
             }
        }

        return $response;
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsTeacher
{
    /**
     * Handle incoming request.
     * Memastikan user adalah Guru di setidaknya satu sekolah.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) abort(401);
        
        // Cek keberadaan role 'teacher' pada pivot table school_user
        if (!$user->schools()->wherePivot('role', 'teacher')->exists()) {
            abort(403);
        }
        return $next($request);
    }
}

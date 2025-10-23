<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsSupervisor
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) abort(401);
        // Check pivot role 'supervisor' existence
        if (!$user->schools()->wherePivot('role', 'supervisor')->exists()) {
            abort(403);
        }
        return $next($request);
    }
}

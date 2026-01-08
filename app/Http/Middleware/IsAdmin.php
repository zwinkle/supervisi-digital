<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle incoming request.
     * Memastikan user yang login memiliki role Admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        // Cek flag is_admin pada tabel users
        if (!$user || !$user->is_admin) {
            abort(403);
        }
        return $next($request);
    }
}

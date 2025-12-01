<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresGoogleLinked
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) abort(401);
        
        if (empty($user->google_access_token)) {
            return redirect('/profile')
                ->with('error', 'Menu ini memerlukan akses Google Drive. Silakan tautkan akun Google Anda di halaman Profil, lalu coba lagi.');
        }
        
        // Check if token is expired
        if ($user->google_token_expires_at && $user->google_token_expires_at->isPast()) {
            return redirect('/profile')
                ->with('error', 'Token Google Anda telah expired. Silakan klik "Perbarui Izin" untuk memperbarui token Anda.');
        }
        
        return $next($request);
    }
}

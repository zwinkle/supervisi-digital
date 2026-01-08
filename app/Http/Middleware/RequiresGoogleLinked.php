<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequiresGoogleLinked
{
    /**
     * Handle incoming request.
     * Memastikan user telah menautkan akun Google dan token masih valid.
     * Digunakan pada rute yang mengakses Google Drive API.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (!$user) abort(401);
        
        // Cek apakah access token ada
        if (empty($user->google_access_token)) {
            return redirect('/profile')
                ->with('error', 'Menu ini memerlukan akses Google Drive. Silakan tautkan akun Google Anda di halaman Profil, lalu coba lagi.');
        }
        
        // Cek apakah token sudah expired
        if ($user->google_token_expires_at && $user->google_token_expires_at->isPast()) {
            return redirect('/profile')
                ->with('error', 'Token Google Anda telah expired. Silakan klik "Perbarui Izin" untuk memperbarui token Anda.');
        }
        
        return $next($request);
    }
}

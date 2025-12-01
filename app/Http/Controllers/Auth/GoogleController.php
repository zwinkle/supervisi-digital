<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect(): RedirectResponse
    {
        $scopes = Config::get('services.google.scopes', []);
        $redirectUrl = Config::get('services.google.redirect');
        Log::info('Google OAuth redirect URL', ['redirect' => $redirectUrl, 'scopes' => $scopes]);
        return Socialite::driver('google')
            ->redirectUrl($redirectUrl)
            ->scopes($scopes)
            ->with(['prompt' => 'consent', 'access_type' => 'offline', 'include_granted_scopes' => 'true'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            $expiresIn = $googleUser->expiresIn ?? null;
            $expiresAt = $expiresIn ? now()->addSeconds($expiresIn) : null;

            // Jika user sedang login (tautkan Google ke akun ini, wajib email sama)
            if (Auth::check()) {
                $current = Auth::user();
                if (strcasecmp($googleUser->getEmail(), $current->email) !== 0) {
                    return redirect()->route('profile.index')
                        ->with('error', 'Tautkan akun Google yang sama dengan email terdaftar ('.$current->email.').');
                }
                // Update token ke akun saat ini (jangan override nama jika sudah ada)
                if (empty($current->name)) {
                    $current->name = $googleUser->getName() ?: $current->name;
                }
                $current->avatar = $googleUser->getAvatar() ?: $current->avatar;
                $current->google_id = $googleUser->getId();
                $current->google_access_token = $googleUser->token;
                // Always update refresh token if provided (Google only provides it on first consent)
                if ($googleUser->refreshToken) {
                    $current->google_refresh_token = $googleUser->refreshToken;
                }
                $current->google_token_expires_at = $expiresAt;
                $current->google_email = $googleUser->getEmail() ?: $current->google_email;
                $current->save();
                return redirect()->route('profile.index')->with('success', 'Akun Google berhasil ditautkan.');
            }

            // Jika tidak login, fallback: cari user berdasarkan google_id atau email
            $user = User::where('google_id', $googleUser->getId())
                ->orWhere('email', $googleUser->getEmail())
                ->first();

            if (!$user) {
                $user = new User();
                $user->email = $googleUser->getEmail();
            }

            if (empty($user->name)) {
                $user->name = $googleUser->getName() ?: $user->name;
            }
            $user->avatar = $googleUser->getAvatar() ?: $user->avatar;
            $user->google_id = $googleUser->getId();
            $user->google_access_token = $googleUser->token;
            // Always update refresh token if provided (Google only provides it on first consent)
            if ($googleUser->refreshToken) {
                $user->google_refresh_token = $googleUser->refreshToken;
            }
            $user->google_token_expires_at = $expiresAt;
            $user->google_email = $googleUser->getEmail() ?: $user->google_email;
            if (empty($user->password)) {
                $user->password = Hash::make(Str::random(40));
            }
            $user->save();

            Auth::login($user, true);

            $hasTeacher = $user->schools()->wherePivot('role', 'teacher')->exists();
            $hasSupervisor = $user->schools()->wherePivot('role', 'supervisor')->exists();
            $requiresTeacherMeta = !$hasSupervisor || $hasTeacher;
            $resolvedTeacherType = $user->resolved_teacher_type;
            $missingTeacherMeta = $requiresTeacherMeta && (
                empty($resolvedTeacherType) ||
                ($resolvedTeacherType === 'subject' && empty($user->subject)) ||
                ($resolvedTeacherType === 'class' && empty($user->class_name))
            );

            $needsProfile = empty($user->nip) || $missingTeacherMeta;
            if ($needsProfile) {
                return redirect()->route('profile.complete.show')
                    ->with('info', 'Lengkapi profil Anda terlebih dahulu.');
            }

            return redirect()->intended('/');
        } catch (\Throwable $e) {
            Log::error('Google OAuth callback error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return redirect('/')->with('error', 'Login Google gagal. Coba lagi.');
        }
    }
}

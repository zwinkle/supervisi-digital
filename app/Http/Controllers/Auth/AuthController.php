<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Menampilkan halaman login jika user belum login.
     * Jika sudah login, langsung redirect ke dashboard yang sesuai peran.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->dashboardFor(Auth::user()));
        }
        return view('auth.login');
    }

    /**
     * Memproses permintaan login dari form.
     * Menggunakan 'Auth::attempt' untuk verifikasi email & password.
     */
    public function login(Request $request)
    {
        // Validasi format input: email wajib email, password wajib string
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);
        
        $remember = $request->boolean('remember'); // Opsi "Ingat Saya"
        
        // Coba login dengan kredensial + pastikan user berstatus aktif ('is_active' => true)
        if (Auth::attempt([...$credentials, 'is_active' => true], $remember)) {
            // Regenerasi session ID untuk mencegah session fixation attack
            $request->session()->regenerate();
            
            // Redirect ke halaman yang diinginkan sebelumnya, atau ke dashboard default
            return redirect()->intended($this->dashboardFor(Auth::user()));
        }
        
        // Jika login gagal, kembali ke halaman login dengan pesan error
        return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
    }





    /**
     * Logout user dari aplikasi.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        
        // Invalidasi session user saat ini
        $request->session()->invalidate();
        
        // Regenerasi CSRF token untuk keamanan form berikutnya
        $request->session()->regenerateToken();
        
        return redirect()->route('login');
    }

    /**
     * Helper untuk menentukan URL redirect berdasarkan Role user.
     * Admin -> Dashboard Admin
     * Supervisor -> Dashboard Supervisor
     * Teacher -> Dashboard Guru
     * Lainnya -> Profil
     */
    private function dashboardFor($user): string
    {
        if ($user->is_admin) return route('admin.dashboard');
        
        // Cek apakah user memiliki peran Supervisor di sekolah manapun
        if ($user->schools()->wherePivot('role','supervisor')->exists()) return route('supervisor.dashboard');
        
        // Cek apakah user memiliki peran Teacher di sekolah manapun
        if ($user->schools()->wherePivot('role','teacher')->exists()) return route('guru.dashboard');
        
        return route('profile.index');
    }
}

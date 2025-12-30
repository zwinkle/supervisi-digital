<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect($this->dashboardFor(Auth::user()));
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);
        $remember = $request->boolean('remember');
        if (Auth::attempt([...$credentials, 'is_active' => true], $remember)) {
            $request->session()->regenerate();
            return redirect()->intended($this->dashboardFor(Auth::user()));
        }
        return back()->withErrors(['email' => 'Email atau kata sandi salah.'])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->save();
        Auth::login($user);
        return redirect($this->dashboardFor($user));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    private function dashboardFor($user): string
    {
        if ($user->is_admin) return route('admin.dashboard');
        if ($user->schools()->wherePivot('role','supervisor')->exists()) return route('supervisor.dashboard');
        if ($user->schools()->wherePivot('role','teacher')->exists()) return route('guru.dashboard');
        return route('profile.index');
    }
}

<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class InviteController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('token');
        $inv = Invitation::where('token', $token)->first();
        if (!$inv) return abort(404);
        if ($inv->used_at) return redirect()->route('login')->with('warning', 'Undangan sudah digunakan.');
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) return redirect()->route('login')->with('warning', 'Undangan kedaluwarsa.');
        return view('auth.accept-invite', ['invitation' => $inv]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'token' => ['required','string'],
            'name' => ['required','string','max:255'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        $inv = Invitation::where('token', $data['token'])->first();
        if (!$inv) return redirect()->route('login')->with('error', 'Undangan tidak ditemukan.');
        if ($inv->used_at) return redirect()->route('login')->with('warning', 'Undangan sudah digunakan.');
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) return redirect()->route('login')->with('warning', 'Undangan kedaluwarsa.');

        // Create or fetch user by email
        $user = User::firstOrNew(['email' => $inv->email]);
        // Enforce invitation name if provided; otherwise use submitted name
        $user->name = $inv->name ?: $data['name'];
        if (!$user->exists) {
            $user->password = Hash::make($data['password']);
        } else {
            // If already exists but password is empty or to be reset
            $user->password = Hash::make($data['password']);
        }
        $user->is_admin = $inv->role === 'admin';
        $user->save();

        // Clear existing pivot roles before applying new
        $user->schools()->wherePivotIn('role', ['supervisor','teacher'])->detach();
        if ($inv->role === 'supervisor') {
            foreach ((array)$inv->school_ids as $sid) {
                $user->schools()->syncWithoutDetaching([$sid => ['role' => 'supervisor']]);
            }
        } elseif ($inv->role === 'teacher') {
            if (!empty($inv->school_ids)) {
                $sid = (int) $inv->school_ids[0];
                $user->schools()->attach($sid, ['role' => 'teacher']);
            }
        }

        $inv->used_at = now();
        $inv->save();

        Auth::login($user);
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard')->with('success', 'Akun admin siap digunakan.');
        }
        return redirect()->route('profile.complete.show')->with('success', 'Pendaftaran berhasil, silakan lengkapi profil.');
    }
}

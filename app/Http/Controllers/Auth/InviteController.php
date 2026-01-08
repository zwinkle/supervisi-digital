<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;

class InviteController extends Controller
{
    /**
     * Menampilkan halaman penerimaan undangan.
     * Sebelum menampilkan form, sistem memvalidasi validitas token (ada, belum dipakai, belum kedaluwarsa).
     */
    public function show(Request $request)
    {
        $token = $request->query('token');
        $inv = Invitation::where('token', $token)->first();
        
        // Jika token tidak valid atau tidak ditemukan
        if (!$inv) return abort(404);
        
        // Jika undangan sudah pernah digunakan sebelumnya
        if ($inv->used_at) return redirect()->route('login')->with('warning', 'Undangan sudah digunakan.');
        
        // Jika undangan sudah melewati batas waktu kedaluwarsa
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) return redirect()->route('login')->with('warning', 'Undangan kedaluwarsa.');
        
        return view('auth.accept-invite', ['invitation' => $inv]);
    }

    /**
     * Memproses penerimaan undangan.
     * Membuat akun user baru, mengatur password, dan menetapkan hak akses (sekolah & role) sesuai isi undangan.
     */
    public function store(Request $request)
    {
        // Validasi input form: token, nama, password
        $data = $request->validate([
            'token' => ['required','string'],
            'name' => ['required','string','max:255'],
            'password' => ['required','string','min:8','confirmed'],
        ]);
        
        // Validasi Ulang Token di sisi server saat submit
        // Penting untuk mencegah kondisi balapan (race condition) atau jika token expired tepat saat user mengisi form.
        $inv = Invitation::where('token', $data['token'])->first();
        if (!$inv) return redirect()->route('login')->with('error', 'Token undangan tidak valid atau tidak ditemukan.');
        if ($inv->used_at) return redirect()->route('login')->with('warning', 'Undangan ini sudah pernah digunakan.');
        if ($inv->expires_at && now()->greaterThan($inv->expires_at)) return redirect()->route('login')->with('warning', 'Masa berlaku undangan ini telah habis.');

        // Buat instance User baru (atau ambil jika emailnya sudah terdaftar tapi belum lengkap datanya)
        $user = User::firstOrNew(['email' => $inv->email]);
        
        // Nama bisa dipaksa dari undangan (jika diset oleh admin) atau inputan user
        $user->name = $inv->name ?: $data['name'];
        if (!$user->exists) {
            $user->password = Hash::make($data['password']);
        } else {
            // Jika user sudah ada, update password-nya
            $user->password = Hash::make($data['password']);
        }
        
        // Set Role Admin jika undangan menyatakan role admin
        $user->is_admin = $inv->role === 'admin';
        
        // Set Atribut Guru (Mata Pelajaran atau Kelas) jika role adalah teacher
        if ($inv->role === 'teacher') {
            // Cek apakah kolom 'teacher_type' ada di tabel users (untuk kompatibilitas)
            $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');
            if ($hasTeacherTypeColumn) {
                $user->teacher_type = $inv->teacher_type;
            }
            if ($inv->teacher_type === 'subject') {
                $user->subject = $inv->teacher_subject;
                $user->class_name = null; // Reset class_name jika guru mapel
            } elseif ($inv->teacher_type === 'class') {
                $user->class_name = $inv->teacher_class;
                $user->subject = null; // Reset subject jika guru kelas
            }
        }
        $user->save();

        // Konfigurasi Relasi ke Sekolah (Pivot Table users_schools)
        // Reset relasi sekolah lama untuk memastikan data konsisten dengan undangan terbaru.
        $user->schools()->detach();
        
        if ($inv->role === 'supervisor') {
            // Supervisor dapat mengelola banyak sekolah
            foreach ((array)$inv->school_ids as $sid) {
                $user->schools()->syncWithoutDetaching([$sid => ['role' => 'supervisor']]);
            }
        } elseif ($inv->role === 'teacher') {
            // Guru hanya terikat pada satu sekolah induk
            if (!empty($inv->school_ids)) {
                $sid = (int) $inv->school_ids[0];
                $user->schools()->attach($sid, ['role' => 'teacher']);
            }
        }

        // Tandai undangan sudah digunakan
        $inv->used_at = now();
        $inv->save();

        // Login otomatis user baru
        Auth::login($user);
        
        if ($user->is_admin) {
            return redirect()->route('admin.dashboard')->with('success', 'Akun admin siap digunakan.');
        }
        
        // Untuk user non-admin, arahkan untuk melengkapi profil (NIP dll)
        return redirect()->route('profile.complete.show')->with('success', 'Pendaftaran berhasil, silakan lengkapi profil.');
    }
}

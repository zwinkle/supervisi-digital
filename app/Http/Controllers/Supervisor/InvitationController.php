<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Mail\InviteMail;
use App\Models\Invitation;
use App\Models\School;
use App\Support\TeacherOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class InvitationController extends Controller
{
    /**
     * Menampilkan daftar undangan khusus untuk sekolah supervised.
     * Hanya menampilkan undangan guru karena supervisor hanya mengelola guru.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $q = $request->input('q');
        $status = $request->input('status', 'all');

        // Ambil daftar ID sekolah yang dikelola supervisor
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id');
        
        // Filter: Hanya undangan guru ('role' = 'teacher') dan sekolahnya cocok
        $query = Invitation::where('role','teacher')
            ->whereJsonLength('school_ids', '>=', 1)
            ->where(function($q) use ($schoolIds){
                // Cek apakah school_id pertama di JSON array ada di list sekolah supervisor
                $q->whereIn('school_ids->0', $schoolIds);
            });

        // Terapkan filter pencarian email
        if ($q && trim($q) !== '') {
            $query->where('email', 'LIKE', '%' . $q . '%');
        }

        // Terapkan filter status (active, used, expired)
        if ($status === 'active') {
            $query->whereNull('used_at')
                  ->where(function ($query) {
                      $query->whereNull('expires_at')
                            ->orWhere('expires_at', '>', now());
                  });
        } elseif ($status === 'used') {
            $query->whereNotNull('used_at');
        } elseif ($status === 'expired') {
            $query->whereNull('used_at')
                  ->whereNotNull('expires_at')
                  ->where('expires_at', '<=', now());
        }

        $perPage = (int) $request->input('per_page', 20);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 20;
        }

        $invitations = $query->orderByDesc('created_at')->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('supervisor.invitations.partials.results', compact('invitations'))->render(),
            ]);
        }

        return view('supervisor.invitations.index', compact('invitations'));
    }

    /**
     * Menampilkan form pembuatan undangan guru baru.
     * Dropdown sekolah otomatis difilter hanya untuk sekolah yang Anda kelola.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        $schools = $user->schools()->wherePivot('role','supervisor')->orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        return view('supervisor.invitations.create', compact('schools','teacherTypes','subjects','classes'));
    }

    /**
     * Menyimpan undangan guru.
     * Memastikan sekolah yang dipilih valid (hak akses supervisor) dan data guru sesuai.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id')->toArray();
        
        $data = $request->validate([
            'email' => ['required','email','max:255'],
            'name' => ['required','string','max:255'],
            'school_id' => ['required','integer','exists:schools,id'],
            'expires_in_days' => ['nullable','integer','min:1','max:30'],
            'teacher_type' => ['required','in:subject,class'],
            'teacher_subject' => ['nullable','string','max:255'],
            'teacher_class' => ['nullable','string','max:50'],
        ]);
        
        // Authorization Check: Pastikan sekolah milik supervisor
        if (!in_array($data['school_id'], $schoolIds)) {
            return back()->withErrors(['school_id' => 'Sekolah tidak berada dalam pengelolaan Anda.'])->withInput();
        }

        $teacherType = $data['teacher_type'];
        if ($teacherType === 'subject') {
            $subjects = TeacherOptions::subjects();
            if (!in_array($data['teacher_subject'] ?? '', $subjects, true)) {
                return back()->withErrors(['teacher_subject' => 'Pilih mata pelajaran yang tersedia.'])->withInput();
            }
            $teacherSubject = $data['teacher_subject'];
            $teacherClass = null;
        } elseif ($teacherType === 'class') {
            $classes = TeacherOptions::classes();
            if (!in_array($data['teacher_class'] ?? '', $classes, true)) {
                return back()->withErrors(['teacher_class' => 'Pilih kelas yang tersedia.'])->withInput();
            }
            $teacherSubject = null;
            $teacherClass = $data['teacher_class'];
        }

        $token = Str::random(40);
        $expiresAt = Carbon::now()->addDays((int)($data['expires_in_days'] ?? 7));

        Invitation::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => 'teacher',
            'school_ids' => [(int)$data['school_id']],
            'token' => $token,
            'invited_by' => $user->id,
            'expires_at' => $expiresAt,
            'teacher_type' => $teacherType,
            'teacher_subject' => $teacherSubject ?? null,
            'teacher_class' => $teacherClass ?? null,
        ]);

        // Generate signed url (untuk referensi jika perlu, meski tidak dikirim email)
        $signedUrl = URL::temporarySignedRoute('invites.accept.show', $expiresAt, ['token' => $token]);

        return redirect()->route('supervisor.invitations.index')
            ->with('success', 'Undangan berhasil dibuat untuk '.$data['email'])
            ->with('info', 'Link undangan tersedia pada daftar undangan.');
    }

    /**
     * Memperbarui masa aktif undangan guru.
     * Berguna jika guru penerima lupa atau telat mendaftar sebelum link kedaluwarsa.
     */
    public function resend(Invitation $invitation)
    {
        if ($invitation->role !== 'teacher') {
            return back()->with('error', 'Hanya undangan guru yang dapat diperbarui.');
        }
        if ($invitation->used_at) {
            return back()->with('warning', 'Undangan sudah digunakan, tidak dapat diperbarui.');
        }
        $expiresAt = $invitation->expires_at ?? now()->addDays(7);
        if (now()->greaterThan($expiresAt)) {
            $expiresAt = now()->addDays(7);
        }
        $invitation->expires_at = $expiresAt;
        $invitation->save();
        return back()->with('success', 'Kedaluwarsa undangan diperbarui.');
    }

    /**
     * Mencabut undangan guru yang belum dipakai.
     */
    public function revoke(Invitation $invitation)
    {
        if ($invitation->role !== 'teacher') {
            return back()->with('error', 'Hanya undangan guru yang dapat dicabut.');
        }
        if ($invitation->used_at) {
            return back()->with('warning', 'Undangan sudah digunakan, tidak dapat dicabut.');
        }
        $invitation->delete();
        return back()->with('success', 'Undangan telah dicabut.');
    }
}

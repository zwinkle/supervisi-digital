<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\School;
use App\Support\TeacherOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteMail;

class InvitationController extends Controller
{
    public function index(Request $request)
    {
        $q = $request->input('q');
        $status = $request->input('status', 'all');

        $query = Invitation::query();

        // Only apply filters when there's a search query
        if ($q && trim($q) !== '') {
            $query->where('email', 'LIKE', '%' . $q . '%');
        }

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
                'html' => view('admin.invitations.partials.results', compact('invitations'))->render(),
            ]);
        }

        return view('admin.invitations.index', compact('invitations'));
    }
    public function create()
    {
        $schools = School::orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        return view('admin.invitations.create', compact('schools', 'teacherTypes', 'subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => ['required','email','max:255'],
            'name' => ['required','string','max:255'],
            'role' => ['required','in:admin,supervisor,teacher'],
            'supervisor_school_id' => ['nullable','integer','exists:schools,id'],
            'teacher_school_id' => ['nullable','integer','exists:schools,id'],
            'expires_in_days' => ['nullable','integer','min:1','max:30'],
            'teacher_type' => ['nullable','in:subject,class'],
            'teacher_subject' => ['nullable','string','max:255'],
            'teacher_class' => ['nullable','string','max:255'],
        ]);

        $schools = [];
        $teacherType = null;
        $teacherSubject = null;
        $teacherClass = null;
        if ($data['role'] === 'supervisor') {
            $sid = $data['supervisor_school_id'] ?? null;
            if (empty($sid)) {
                return back()->withErrors(['supervisor_school_id' => 'Pilih satu sekolah untuk Supervisor.'])->withInput();
            }
            $schools = [(int)$sid];
        } elseif ($data['role'] === 'teacher') {
            if (empty($data['teacher_school_id'])) {
                return back()->withErrors(['teacher_school_id' => 'Pilih satu sekolah untuk Guru.'])->withInput();
            }
            $schools = [(int)$data['teacher_school_id']];

            $teacherType = $data['teacher_type'] ?? null;
            if (!$teacherType) {
                return back()->withErrors(['teacher_type' => 'Pilih jenis guru.'])->withInput();
            }

            if ($teacherType === 'subject') {
                $validSubjects = TeacherOptions::subjects();
                if (!in_array($data['teacher_subject'] ?? '', $validSubjects, true)) {
                    return back()->withErrors(['teacher_subject' => 'Pilih mata pelajaran yang tersedia.'])->withInput();
                }
                $teacherSubject = $data['teacher_subject'];
            } elseif ($teacherType === 'class') {
                $validClasses = TeacherOptions::classes();
                if (!in_array($data['teacher_class'] ?? '', $validClasses, true)) {
                    return back()->withErrors(['teacher_class' => 'Pilih kelas yang tersedia.'])->withInput();
                }
                $teacherClass = $data['teacher_class'];
            } else {
                return back()->withErrors(['teacher_type' => 'Jenis guru tidak valid.'])->withInput();
            }
        }

        $token = Str::random(40);
        $expiresAt = Carbon::now()->addDays((int)($data['expires_in_days'] ?? 7));

        $inv = Invitation::create([
            'email' => $data['email'],
            'name' => $data['name'],
            'role' => $data['role'],
            'school_ids' => $schools,
            'token' => $token,
            'invited_by' => $request->user()->id,
            'expires_at' => $expiresAt,
            'teacher_type' => $teacherType,
            'teacher_subject' => $teacherSubject,
            'teacher_class' => $teacherClass,
        ]);

        $signedUrl = URL::temporarySignedRoute('invites.accept.show', $expiresAt, ['token' => $token]);

        // Tidak mengirim email: tampilkan link undangan bertanda tangan (signed URL)
        return redirect()->route('admin.users.index')
            ->with('success', 'Undangan berhasil dibuat untuk '.$data['email']);
    }

    public function resend(Invitation $invitation)
    {
        if ($invitation->used_at) {
            return back()->with('warning', 'Undangan sudah digunakan, tidak dapat dikirim ulang.');
        }
        // extend or keep expiry
        $expiresAt = $invitation->expires_at ?? \Carbon\Carbon::now()->addDays(7);
        if (now()->greaterThan($expiresAt)) {
            $expiresAt = now()->addDays(7);
        }
        $invitation->expires_at = $expiresAt;
        $invitation->save();

        // Tidak mengirim email. Link dapat dilihat/di-copy dari halaman daftar undangan.
        return back()->with('success', 'Kedaluwarsa undangan diperbarui untuk '.$invitation->email);
    }

    public function revoke(Invitation $invitation)
    {
        if ($invitation->used_at) {
            return back()->with('warning', 'Undangan sudah digunakan, tidak dapat dicabut.');
        }
        $email = $invitation->email;
        $invitation->delete();
        return back()->with('success', 'Undangan untuk '.$email.' telah dicabut.');
    }
}

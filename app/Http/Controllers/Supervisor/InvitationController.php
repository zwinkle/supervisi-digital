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
    public function index(Request $request)
    {
        $user = $request->user();
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id');
        $invitations = Invitation::where('role','teacher')
            ->whereJsonLength('school_ids', '>=', 1)
            ->where(function($q) use ($schoolIds){
                $q->whereIn('school_ids->0', $schoolIds); // simple contains first id
            })
            ->orderByDesc('created_at')
            ->paginate(20);
        return view('supervisor.invitations.index', compact('invitations'));
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $schools = $user->schools()->wherePivot('role','supervisor')->orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        return view('supervisor.invitations.create', compact('schools','teacherTypes','subjects','classes'));
    }

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

        // generate signed url (not sent via email)
        $signedUrl = URL::temporarySignedRoute('invites.accept.show', $expiresAt, ['token' => $token]);

        return redirect()->route('supervisor.invitations.index')
            ->with('success', 'Undangan berhasil dibuat untuk '.$data['email'])
            ->with('info', 'Link undangan tersedia pada daftar undangan.');
    }

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

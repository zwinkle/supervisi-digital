<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\TeacherOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UserController extends Controller
{
    public function create()
    {
        $schools = School::orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        return view('admin.users.create', compact('schools', 'teacherTypes', 'subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role' => ['required','in:admin,supervisor,teacher'],
            'nip' => ['nullable','regex:/^\d+$/','min:8','max:18'],
            'supervisor_school_ids' => ['array'],
            'supervisor_school_ids.*' => ['integer','exists:schools,id'],
            'teacher_school_id' => ['nullable','integer','exists:schools,id'],
            'teacher_type' => ['nullable','in:subject,class'],
            'teacher_subject' => ['nullable','string','max:255'],
            'teacher_class' => ['nullable','string','max:50'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->is_admin = $data['role'] === 'admin';
        $user->nip = $data['nip'] ?? null;

        $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');

        if ($data['role'] === 'supervisor') {
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            if ($supIds->isEmpty()) {
                return back()->withErrors(['supervisor_school_ids' => 'Pilih minimal satu sekolah untuk Supervisor.'])->withInput();
            }
        }

        if ($data['role'] === 'teacher') {
            $teacherSchoolId = $data['teacher_school_id'] ?? null;
            if (empty($teacherSchoolId)) {
                return back()->withErrors(['teacher_school_id' => 'Pilih satu sekolah untuk Guru.'])->withInput();
            }
            $type = $data['teacher_type'] ?? null;
            if (!$type) {
                return back()->withErrors(['teacher_type' => 'Pilih jenis guru.'])->withInput();
            }
            if ($type === 'subject') {
                $subjects = TeacherOptions::subjects();
                if (!in_array($data['teacher_subject'] ?? '', $subjects, true)) {
                    return back()->withErrors(['teacher_subject' => 'Pilih mata pelajaran yang tersedia.'])->withInput();
                }
                if ($hasTeacherTypeColumn) {
                    $user->teacher_type = 'subject';
                }
                $user->subject = $data['teacher_subject'];
                $user->class_name = null;
            } elseif ($type === 'class') {
                $classes = TeacherOptions::classes();
                if (!in_array($data['teacher_class'] ?? '', $classes, true)) {
                    return back()->withErrors(['teacher_class' => 'Pilih kelas yang tersedia.'])->withInput();
                }
                if ($hasTeacherTypeColumn) {
                    $user->teacher_type = 'class';
                }
                $user->class_name = $data['teacher_class'];
                $user->subject = null;
            }
        }
        $user->save();

        if ($data['role'] === 'supervisor') {
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            foreach ($supIds as $sid) {
                $user->schools()->attach($sid, ['role' => 'supervisor']);
            }
        } elseif ($data['role'] === 'teacher') {
            $teacherSchoolId = $data['teacher_school_id'] ?? null;
            if (!empty($teacherSchoolId)) {
                $user->schools()->attach($teacherSchoolId, ['role' => 'teacher']);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat');
    }
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $users = User::query()->with('schools')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        return view('admin.users.index', compact('users', 'q'));
    }

    public function edit(User $user)
    {
        $schools = School::orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        $resolvedTeacherType = $user->resolved_teacher_type;
        $resolvedTeacherSubject = $user->resolved_teacher_subject;
        $resolvedTeacherClass = $user->resolved_teacher_class;

        return view('admin.users.edit', compact(
            'user',
            'schools',
            'teacherTypes',
            'subjects',
            'classes',
            'resolvedTeacherType',
            'resolvedTeacherSubject',
            'resolvedTeacherClass'
        ));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:8'],
            'role' => ['required','in:admin,supervisor,teacher'],
            // roles management
            'supervisor_school_ids' => ['array'],
            'supervisor_school_ids.*' => ['integer','exists:schools,id'],
            'teacher_school_id' => ['nullable','integer','exists:schools,id'],
            'nip' => ['nullable','regex:/^\d+$/','min:8','max:18'],
            'teacher_type' => ['nullable','in:subject,class'],
            'teacher_subject' => ['nullable','string','max:255'],
            'teacher_class' => ['nullable','string','max:50'],
        ]);

        // Additional conditional checks
        if ($data['role'] === 'supervisor') {
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            if ($supIds->isEmpty()) {
                return back()->withErrors(['supervisor_school_ids' => 'Pilih minimal satu sekolah untuk Supervisor.'])->withInput();
            }
        }
        if ($data['role'] === 'teacher') {
            if (empty($data['teacher_school_id'])) {
                return back()->withErrors(['teacher_school_id' => 'Pilih satu sekolah untuk Guru.'])->withInput();
            }
            $type = $data['teacher_type'] ?? null;
            if (!$type) {
                return back()->withErrors(['teacher_type' => 'Pilih jenis guru.'])->withInput();
            }
            if ($type === 'subject') {
                $subjects = TeacherOptions::subjects();
                if (!in_array($data['teacher_subject'] ?? '', $subjects, true)) {
                    return back()->withErrors(['teacher_subject' => 'Pilih mata pelajaran yang tersedia.'])->withInput();
                }
            } elseif ($type === 'class') {
                $classes = TeacherOptions::classes();
                if (!in_array($data['teacher_class'] ?? '', $classes, true)) {
                    return back()->withErrors(['teacher_class' => 'Pilih kelas yang tersedia.'])->withInput();
                }
            } else {
                return back()->withErrors(['teacher_type' => 'Jenis guru tidak valid.'])->withInput();
            }
        }
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->nip = $data['nip'] ?? null;
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->is_admin = $data['role'] === 'admin';
        $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');

        if ($data['role'] !== 'teacher') {
            if ($hasTeacherTypeColumn) {
                $user->teacher_type = null;
            }
            $user->subject = null;
            $user->class_name = null;
        } else {
            $type = $data['teacher_type'];
            if ($hasTeacherTypeColumn) {
                $user->teacher_type = $type;
            }
            if ($type === 'subject') {
                $user->subject = $data['teacher_subject'];
                $user->class_name = null;
            } elseif ($type === 'class') {
                $user->class_name = $data['teacher_class'];
                $user->subject = null;
            }
        }
        $user->save();

        // Reset previous role assignments in pivot
        $user->schools()->detach();

        if ($data['role'] === 'supervisor') {
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            foreach ($supIds as $sid) {
                $user->schools()->attach($sid, ['role' => 'supervisor']);
            }
        } elseif ($data['role'] === 'teacher') {
            $teacherSchoolId = $data['teacher_school_id'] ?? null;
            if (!empty($teacherSchoolId)) {
                $user->schools()->attach($teacherSchoolId, ['role' => 'teacher']);
            }
        }
        return redirect()->route('admin.users.index')->with('success', 'Pengguna diperbarui');
    }

    public function deactivate(User $user)
    {
        // Soft deactivation: set password to random and mark email with suffix (optional)
        $user->password = Hash::make(bin2hex(random_bytes(16)));
        $user->save();
        return back()->with('success', 'Pengguna dinonaktifkan');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }
        // Detach relations then hard delete
        try {
            $user->schools()->detach();
        } catch (\Throwable $e) {
            // ignore
        }
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Pengguna dihapus.');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function create()
    {
        // Optionally pass schools list if needed to attach roles later
        $schools = \App\Models\School::orderBy('name')->get();
        return view('admin.users.create', compact('schools'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role' => ['required','in:admin,supervisor,teacher'],
            'school_id' => ['nullable','integer','exists:schools,id'],
        ]);

        $user = new User();
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->password = Hash::make($data['password']);
        $user->is_admin = $data['role'] === 'admin';
        $user->save();

        if (in_array($data['role'], ['supervisor','teacher'], true) && !empty($data['school_id'])) {
            $user->schools()->attach($data['school_id'], ['role' => $data['role']]);
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
        return view('admin.users.edit', compact('user','schools'));
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
        }
        $user->name = $data['name'];
        $user->email = $data['email'];
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->is_admin = $data['role'] === 'admin';
        $user->save();

        // Reset previous role assignments in pivot
        $user->schools()->wherePivotIn('role', ['supervisor','teacher'])->detach();

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

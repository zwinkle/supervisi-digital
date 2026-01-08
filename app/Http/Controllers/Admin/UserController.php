<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\User;
use App\Support\TeacherOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Menampilkan form tambah pengguna baru.
     * Mengirimkan data master (Sekolah, Mata Pelajaran, Kelas) untuk opsi dropdown.
     */
    public function create()
    {
        $schools = School::orderBy('name')->get();
        // Opsi ini diambil dari helper TeacherOptions agar konsisten di seluruh aplikasi
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        return view('admin.users.create', compact('schools', 'teacherTypes', 'subjects', 'classes'));
    }

    /**
     * Menyimpan pengguna baru ke basis data.
     * Mengatur peran (Admin/Supervisor/Guru) serta atribut spesifik seperti sekolah binaan atau mata pelajaran.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['required','email','max:255','unique:users,email'],
            'password' => ['required','string','min:8'],
            'role' => ['required','in:admin,supervisor,teacher'],
            'nip' => ['nullable','regex:/^\d+$/','min:8','max:18'],
            // Validasi khusus Supervisor (multipel sekolah)
            'supervisor_school_ids' => ['array'],
            'supervisor_school_ids.*' => ['integer','exists:schools,id'],
            // Validasi khusus Teacher (satu sekolah + detail mapel/kelas)
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

        // Validasi tambahan berdasarkan Peran yang dipilih
        if ($data['role'] === 'supervisor') {
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            if ($supIds->isEmpty()) {
                return back()->withErrors(['supervisor_school_ids' => 'Pilih minimal satu sekolah untuk Supervisor.'])->withInput();
            }
        }

        if ($data['role'] === 'teacher') {
            // Guru wajib punya 1 sekolah induk
            $teacherSchoolId = $data['teacher_school_id'] ?? null;
            if (empty($teacherSchoolId)) {
                return back()->withErrors(['teacher_school_id' => 'Pilih satu sekolah untuk Guru.'])->withInput();
            }
            
            // Guru wajib punya tipe: Guru Mapel atau Guru Kelas
            $type = $data['teacher_type'] ?? null;
            if (!$type) {
                return back()->withErrors(['teacher_type' => 'Pilih jenis guru.'])->withInput();
            }
            
            // Set detail berdasarkan tipe guru
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

        $user->save();

        // Hubungkan pengguna dengan sekolah sesuai perannya (Pivot Table)
        if ($data['role'] === 'supervisor') {
            // Supervisor bisa diassign ke banyak sekolah
            $supIds = collect($request->input('supervisor_school_ids', []))->filter()->unique();
            foreach ($supIds as $sid) {
                $user->schools()->attach($sid, ['role' => 'supervisor']);
            }
        } elseif ($data['role'] === 'teacher') {
            // Teacher hanya diassign ke satu sekolah
            $teacherSchoolId = $data['teacher_school_id'] ?? null;
            if (!empty($teacherSchoolId)) {
                $user->schools()->attach($teacherSchoolId, ['role' => 'teacher']);
            }
        }

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dibuat');
    }

    /**
     * Menampilkan daftar pengguna.
     * Mendukung fitur pencarian dan filter, serta menggunakan AJAX untuk update tabel secara dinamis.
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $search = $q !== '' ? Str::lower($q) : null;
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 10;
        }

        $usersQuery = User::query()
            ->with(['schools' => function ($relation) {
                $relation->orderBy('name');
            }]);

        // Logika Pencarian: Cocokkan kata kunci dengan Nama, Email, NIP, atau Nama Sekolah
        if ($search) {
            $usersQuery->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                    ->orWhere('nip', 'LIKE', "%{$search}%")
                    ->orWhereHas('schools', function ($schoolQuery) use ($search) {
                        $schoolQuery->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                    });
            });
        }

        $users = $usersQuery->orderBy('name')->paginate($perPage)->withQueryString();

        // Return partial view untuk AJAX response
        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.users.partials.results', [
                    'users' => $users,
                ])->render(),
            ]);
        }

        return view('admin.users.index', [
            'users' => $users,
            'q' => $q,
        ]);
    }

    /**
     * Menampilkan form edit user.
     * Mengirim data user beserta opsi-opsi master data untuk dropdown.
     */
    public function edit(User $user)
    {
        $schools = School::orderBy('name')->get();
        $teacherTypes = TeacherOptions::teacherTypes();
        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        
        // Atribut virtual/helper untuk mempermudah binding ke form
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

    /**
     * Memperbarui data input user.
     * Menangani perubahan role, assignment sekolah, dan reset password (jika diisi).
     */
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

        // Validasi Kondisional Tambahan (sama seperti store)
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

        // Update data dasar
        $user->name = $data['name'];
        $user->email = $data['email'];
        $user->nip = $data['nip'] ?? null;
        
        // Update password hanya jika diisi
        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->is_admin = $data['role'] === 'admin';
        
        $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');

        // Reset/Set data spesifik role
        if ($data['role'] !== 'teacher') {
            // Jika bukan guru, null-kan atribut guru
            if ($hasTeacherTypeColumn) {
                $user->teacher_type = null;
            }
            $user->subject = null;
            $user->class_name = null;
        } else {
            // Jika guru, set atribut sesuai tipe
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

        // Reset assignment role sekolah yang lama sebelum set yang baru
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

    /**
     * Menonaktifkan user (Soft Deactivate).
     * User tidak akan bisa login, tapi data tidak dihapus.
     */
    public function deactivate(User $user)
    {
        $user->is_active = false;
        $user->save();
        return back()->with('success', 'Pengguna dinonaktifkan');
    }

    /**
     * Mengaktifkan kembali user yang dinonaktifkan.
     */
    public function activate(User $user)
    {
        $user->is_active = true;
        $user->save();
        return back()->with('success', 'Pengguna diaktifkan kembali');
    }

    /**
     * Menghapus user secara permanen beserta semua data terkaitnya.
     * Menggunakan Database Transaction untuk memastikan integritas data (All or Nothing).
     */
    public function destroy(Request $request, User $user)
    {
        // Mencegah admin menghapus diri sendiri saat sedang login
        if ($request->user()->id === $user->id) {
            return back()->with('error', 'Tidak dapat menghapus akun Anda sendiri.');
        }

        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($user) {
                // 1. Hapus Invitations yang dibuat oleh user ini
                \App\Models\Invitation::where('invited_by', $user->id)->delete();

                // 2. Hapus Files yang diupload user ini (Mencegah file yatim piatu di DB)
                // Sebaiknya file fisik di Google Drive juga dihapus secara background job di real production,
                // tapi di sini kita hapus record DB-nya saja agar konsisten.
                \App\Models\File::where('owner_user_id', $user->id)->delete();

                // 3. Detach dari sekolah (Hapus relasi pivot)
                $user->schools()->detach();

                // 4. Hapus Jadwal yang terkait (sebagai Supervisor atau Guru)
                // Ini akan men-trigger cascade delete ke Submissions dan Evaluations di level Database (jika diset ON DELETE CASCADE)
                // atau kita hapus manual jika perlu.
                \App\Models\Schedule::where('supervisor_id', $user->id)
                    ->orWhere('teacher_id', $user->id)
                    ->delete();

                // 5. Akhirnya hapus record user itu sendiri
                $user->delete();
            });

            return redirect()->route('admin.users.index')->with('success', 'Pengguna dan data terkait berhasil dihapus.');
        } catch (\Throwable $e) {
            // Log error untuk keperluan debugging tanpa mengekspos detail teknis ke user
            \Illuminate\Support\Facades\Log::error('Gagal menghapus user: ' . $e->getMessage());
            return back()->with('error', 'Gagal menghapus pengguna. Masih ada data yang terkait erat atau terjadi kesalahan sistem.');
        }
    }
}

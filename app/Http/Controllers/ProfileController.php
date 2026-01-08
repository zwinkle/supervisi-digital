<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Support\TeacherOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class ProfileController extends Controller
{
    /**
     * Menampilkan form kelengkapan profil.
     * Halaman ini wajib bagi pengguna baru (misal via login Google) yang datanya belum lengkap (NIP, peran, sekolah).
     */
    public function showComplete()
    {
        $user = Auth::user();
        $schools = School::orderBy('name')->get(['id','name']);
        
        // Cek apakah user sudah punya peran tertentu
        $hasTeacher = $user->schools()->wherePivot('role','teacher')->exists();
        $hasSupervisor = $user->schools()->wherePivot('role','supervisor')->exists();
        $teacherSchoolId = $user->schools()->wherePivot('role','teacher')->value('schools.id');
        $supervisorSchoolId = $user->schools()->wherePivot('role','supervisor')->value('schools.id');
        
        // Data opsi dropdown
        $classes = TeacherOptions::classes();
        $subjects = TeacherOptions::subjects();
        $teacherTypes = TeacherOptions::teacherTypes();
        
        // Pre-fill fields jika sudah ada data
        $resolvedTeacherType = $user->resolved_teacher_type;
        $resolvedTeacherSubject = $user->resolved_teacher_subject;
        $resolvedTeacherClass = $user->resolved_teacher_class;

        return view('profile.complete', compact(
            'user',
            'schools',
            'classes',
            'subjects',
            'teacherTypes',
            'hasTeacher',
            'teacherSchoolId',
            'hasSupervisor',
            'supervisorSchoolId',
            'resolvedTeacherType',
            'resolvedTeacherSubject',
            'resolvedTeacherClass'
        ));
    }

    /**
     * Menyimpan data pelengkap profil.
     * Mengupdate informasi vital seperti NIP, penugasan sekolah, dan spesialisasi guru (Mapel/Kelas).
     */
    public function storeComplete(Request $request)
    {
        $user = Auth::user();

        $subjects = TeacherOptions::subjects();
        $classes = TeacherOptions::classes();
        $teacherTypes = array_keys(TeacherOptions::teacherTypes());

        $hasTeacher = $user->schools()->wherePivot('role','teacher')->exists();
        $hasSupervisor = $user->schools()->wherePivot('role','supervisor')->exists();
        
        $rules = [
            'name' => ['required','string','max:255'],
            // Validasi NIP: angka only, panjang 8-18 karakter
            'nip' => ['required','regex:/^\d+$/','min:8','max:18'],
        ];
        
        // Validasi Kondisional: Jika user mendaftar sebagai Guru, wajib mengisi detail tipe guru
        $requiresTeacherMeta = !$hasSupervisor || $hasTeacher;
        $teacherTypeRule = $requiresTeacherMeta ? 'required' : 'nullable';
        $rules['teacher_type'] = [$teacherTypeRule, Rule::in($teacherTypes)];

        $selectedType = $request->input('teacher_type');
        $subjectRequired = $selectedType === 'subject' && ($requiresTeacherMeta || $selectedType);
        $classRequired = $selectedType === 'class' && ($requiresTeacherMeta || $selectedType);

        $rules['subject'] = [$subjectRequired ? 'required' : 'nullable', Rule::in($subjects)];
        $rules['class_name'] = [$classRequired ? 'required' : 'nullable', Rule::in($classes)];
        
        // Jika user masih fresh (belum punya role apapun), wajib memilih sekolah induk
        if (!$hasTeacher && !$hasSupervisor) {
            $rules['school_id'] = ['required','integer', Rule::exists('schools','id')];
        } else {
            $rules['school_id'] = ['nullable','integer', Rule::exists('schools','id')];
        }
        
        $validated = $request->validate($rules, [
            'nip.regex' => 'Format NIP tidak valid (harus angka).',
            'teacher_type.in' => 'Pilih jenis guru yang valid.',
            'subject.in' => 'Mata pelajaran tidak valid.',
            'class_name.in' => 'Kelas harus antara Kelas 1-6.',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
        ]);
        
        $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');
        if ($hasTeacherTypeColumn && array_key_exists('teacher_type', $validated)) {
            $user->teacher_type = $validated['teacher_type'];
        }

        // Set metadata guru sesuai tipe yang dipilih
        if (($validated['teacher_type'] ?? null) === 'subject') {
            $user->subject = $validated['subject'] ?? null;
            $user->class_name = null;
        } elseif (($validated['teacher_type'] ?? null) === 'class') {
            $user->class_name = $validated['class_name'] ?? null;
            $user->subject = null;
        } else {
            // Fallback
            if (array_key_exists('subject', $validated)) {
                $user->subject = $validated['subject'];
            }
            if (array_key_exists('class_name', $validated)) {
                $user->class_name = $validated['class_name'];
            }
        }
        $user->save();

        // Assign sekolah sebagai guru jika user belum punya role guru dan ada input school_id
        if (!$hasTeacher && !empty($validated['school_id'])) {
            $user->schools()->attach($validated['school_id'], ['role' => 'teacher']);
        }

        return redirect('/')->with('success', 'Profil berhasil dilengkapi.');
    }
}

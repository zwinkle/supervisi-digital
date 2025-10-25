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
    public function showComplete()
    {
        $user = Auth::user();
        $schools = School::orderBy('name')->get(['id','name']);
        $hasTeacher = $user->schools()->wherePivot('role','teacher')->exists();
        $hasSupervisor = $user->schools()->wherePivot('role','supervisor')->exists();
        $teacherSchoolId = $user->schools()->wherePivot('role','teacher')->value('schools.id');
        $supervisorSchoolId = $user->schools()->wherePivot('role','supervisor')->value('schools.id');
        $classes = TeacherOptions::classes();
        $subjects = TeacherOptions::subjects();
        $teacherTypes = TeacherOptions::teacherTypes();
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
            // only digits, no spaces/letters, reasonable length (8-20)
            'nip' => ['required','regex:/^\d+$/','min:8','max:18'],
        ];
        $requiresTeacherMeta = !$hasSupervisor || $hasTeacher;
        $teacherTypeRule = $requiresTeacherMeta ? 'required' : 'nullable';
        $rules['teacher_type'] = [$teacherTypeRule, Rule::in($teacherTypes)];

        $selectedType = $request->input('teacher_type');
        $subjectRequired = $selectedType === 'subject' && ($requiresTeacherMeta || $selectedType);
        $classRequired = $selectedType === 'class' && ($requiresTeacherMeta || $selectedType);

        $rules['subject'] = [$subjectRequired ? 'required' : 'nullable', Rule::in($subjects)];
        $rules['class_name'] = [$classRequired ? 'required' : 'nullable', Rule::in($classes)];
        if (!$hasTeacher && !$hasSupervisor) {
            $rules['school_id'] = ['required','integer', Rule::exists('schools','id')];
        } else {
            $rules['school_id'] = ['nullable','integer', Rule::exists('schools','id')];
        }
        $validated = $request->validate($rules, [
            'nip.regex' => 'NIP harus berupa angka saja.',
            'teacher_type.in' => 'Pilih jenis guru yang tersedia.',
            'subject.in' => 'Pilih mata pelajaran yang tersedia.',
            'class_name.in' => 'Kelas harus 1-6.',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
        ]);
        $hasTeacherTypeColumn = Schema::hasColumn('users', 'teacher_type');
        if ($hasTeacherTypeColumn && array_key_exists('teacher_type', $validated)) {
            $user->teacher_type = $validated['teacher_type'];
        }

        if (($validated['teacher_type'] ?? null) === 'subject') {
            $user->subject = $validated['subject'] ?? null;
            $user->class_name = null;
        } elseif (($validated['teacher_type'] ?? null) === 'class') {
            $user->class_name = $validated['class_name'] ?? null;
            $user->subject = null;
        } else {
            if (array_key_exists('subject', $validated)) {
                $user->subject = $validated['subject'];
            }
            if (array_key_exists('class_name', $validated)) {
                $user->class_name = $validated['class_name'];
            }
        }
        $user->save();

        // Attach as teacher only if not attached and a school_id is provided (e.g., self-filled profile)
        if (!$hasTeacher && !empty($validated['school_id'])) {
            $user->schools()->attach($validated['school_id'], ['role' => 'teacher']);
        }

        return redirect('/')->with('success', 'Profil berhasil dilengkapi.');
    }
}

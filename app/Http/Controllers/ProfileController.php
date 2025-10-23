<?php

namespace App\Http\Controllers;

use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

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
        $classes = ['1','2','3','4','5','6'];
        $subjects = [
            'Pendidikan Agama dan Budi Pekerti',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'IPAS',
            'Pendidikan Jasmani Olahraga dan Kesehatan',
            'Seni Musik',
            'Seni Rupa',
            'Seni Teater',
            'Seni Tari',
            'Bahasa Inggris',
            'Koding dan Kecerdasan Artifisial',
        ];
        return view('profile.complete', compact('user','schools','classes','subjects','hasTeacher','teacherSchoolId','hasSupervisor','supervisorSchoolId'));
    }

    public function storeComplete(Request $request)
    {
        $user = Auth::user();

        $subjects = [
            'Pendidikan Agama dan Budi Pekerti',
            'Pendidikan Pancasila',
            'Bahasa Indonesia',
            'Matematika',
            'IPAS',
            'Pendidikan Jasmani Olahraga dan Kesehatan',
            'Seni Musik',
            'Seni Rupa',
            'Seni Teater',
            'Seni Tari',
            'Bahasa Inggris',
            'Koding dan Kecerdasan Artifisial',
        ];

        $hasTeacher = $user->schools()->wherePivot('role','teacher')->exists();
        $hasSupervisor = $user->schools()->wherePivot('role','supervisor')->exists();
        $rules = [
            'name' => ['required','string','max:255'],
            // only digits, no spaces/letters, reasonable length (8-20)
            'nip' => ['required','regex:/^\d+$/','min:8','max:18'],
        ];
        if (!$hasSupervisor || $hasTeacher) {
            $rules['subject'] = ['required', Rule::in($subjects)];
            $rules['class_name'] = ['required', Rule::in(['1','2','3','4','5','6'])];
        } else {
            $rules['subject'] = ['nullable', Rule::in($subjects)];
            $rules['class_name'] = ['nullable', Rule::in(['1','2','3','4','5','6'])];
        }
        if (!$hasTeacher && !$hasSupervisor) {
            $rules['school_id'] = ['required','integer', Rule::exists('schools','id')];
        } else {
            $rules['school_id'] = ['nullable','integer', Rule::exists('schools','id')];
        }
        $validated = $request->validate($rules, [
            'nip.regex' => 'NIP harus berupa angka saja.',
            'subject.in' => 'Pilih mata pelajaran yang tersedia.',
            'class_name.in' => 'Kelas harus 1-6.',
        ]);

        $user->fill([
            'name' => $validated['name'],
            'nip' => $validated['nip'],
        ]);
        if (array_key_exists('subject', $validated)) $user->subject = $validated['subject'];
        if (array_key_exists('class_name', $validated)) $user->class_name = $validated['class_name'];
        $user->save();

        // Attach as teacher only if not attached and a school_id is provided (e.g., self-filled profile)
        if (!$hasTeacher && !empty($validated['school_id'])) {
            $user->schools()->attach($validated['school_id'], ['role' => 'teacher']);
        }

        return redirect('/')->with('success', 'Profil berhasil dilengkapi.');
    }
}

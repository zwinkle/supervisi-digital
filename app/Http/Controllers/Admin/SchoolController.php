<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->get('q', ''));
        $schools = \App\Models\School::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('address', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();
        return view('admin.schools.index', compact('schools','q'));
    }
    public function create()
    {
        return view('admin.schools.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:500'],
        ]);
        School::create($data);
        return redirect()->route('admin.dashboard')->with('success', 'Sekolah berhasil dibuat');
    }

    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:500'],
        ]);
        $school->update($data);
        return redirect()->route('admin.schools.index')->with('success', 'Sekolah diperbarui');
    }

    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'Sekolah dihapus');
    }
}

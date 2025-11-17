<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    public function index(Request $request)
    {
        $filter = Str::lower((string) $request->input('filter', 'name'));
        $allowedFilters = collect(['name', 'address']);
        if (!$allowedFilters->contains($filter)) {
            $filter = 'name';
        }

        $q = trim((string) $request->input('q', ''));
        $search = $q !== '' ? Str::lower($q) : null;

        $schoolsQuery = School::query();

        // Only apply filters when there's a search query
        if ($search !== null && $search !== '') {
            $schoolsQuery->where(function ($query) use ($filter, $search) {
                switch ($filter) {
                    case 'address':
                        $query->whereRaw('LOWER(COALESCE(address, "")) LIKE ?', ["%{$search}%"]);
                        break;
                    case 'name':
                    default:
                        $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        break;
                }
            });
        }

        $schools = $schoolsQuery->orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('admin.schools.partials.results', [
                    'schools' => $schools,
                ])->render(),
            ]);
        }

        return view('admin.schools.index', [
            'schools' => $schools,
            'q' => $q,
            'filter' => $filter,
        ]);
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

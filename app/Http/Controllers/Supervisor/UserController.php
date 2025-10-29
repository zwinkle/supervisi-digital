<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id');
        $filter = Str::lower((string) $request->input('filter', 'name'));
        $allowedFilters = collect(['name', 'email', 'teacher_type', 'school']);
        if (!$allowedFilters->contains($filter)) {
            $filter = 'name';
        }

        $q = trim((string) $request->input('q', ''));
        $search = $q !== '' ? Str::lower($q) : null;

        if ($schoolIds->isEmpty()) {
            $teachers = collect();
        } else {
            $teachersQuery = User::query()
                ->with(['schools' => function ($relation) {
                    $relation->where('school_user.role', 'teacher')->orderBy('name');
                }])
                ->whereHas('schools', function ($relation) use ($schoolIds) {
                    $relation->whereIn('schools.id', $schoolIds)
                        ->where('school_user.role', 'teacher');
                });

            if ($search !== null) {
                $teachersQuery->where(function ($query) use ($filter, $search, $schoolIds) {
                    switch ($filter) {
                        case 'email':
                            $query->whereRaw('LOWER(email) LIKE ?', ["%{$search}%"]);
                            break;
                        case 'teacher_type':
                            $query->where(function ($sub) use ($search) {
                                $sub->whereRaw('LOWER(COALESCE(teacher_type, "")) LIKE ?', ["%{$search}%"])
                                    ->orWhereRaw('LOWER(COALESCE(subject, "")) LIKE ?', ["%{$search}%"])
                                    ->orWhereRaw('LOWER(COALESCE(class_name, "")) LIKE ?', ["%{$search}%"])
                                    ->orWhereRaw("LOWER(CASE WHEN teacher_type = 'subject' THEN 'guru mata pelajaran' WHEN teacher_type = 'class' THEN 'guru kelas' ELSE '' END) LIKE ?", ["%{$search}%"]);
                            });
                            break;
                        case 'school':
                            $query->whereHas('schools', function ($relation) use ($search, $schoolIds) {
                                $relation->whereIn('schools.id', $schoolIds)
                                    ->where('school_user.role', 'teacher')
                                    ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                            });
                            break;
                        case 'name':
                        default:
                            $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                            break;
                    }
                });
            }

            $teachers = $teachersQuery->orderBy('name')->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('supervisor.users.partials.table', [
                    'teachers' => $teachers,
                ])->render(),
            ]);
        }

        return view('supervisor.users.index', [
            'teachers' => $teachers,
            'q' => $q,
            'filter' => $filter,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Menampilkan daftar guru yang berada di bawah supervisi supervisor ini.
     * Filter berdasarkan nama/NIP dan pagination.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Ambil daftar ID sekolah yang dikelola
        $schoolIds = $user->schools()->wherePivot('role','supervisor')->pluck('schools.id');

        $q = trim((string) $request->input('q', ''));
        $search = $q !== '' ? Str::lower($q) : null;
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 10;
        }

        if ($schoolIds->isEmpty()) {
            // Jika tidak mengelola sekolah sama sekali, kembalikan list kosong
            $teachers = new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage);
        } else {
            // Query user yang memiliki role 'teacher' pada sekolah-sekolah tersebut
            $teachersQuery = User::query()
                ->with(['schools' => function ($relation) {
                    $relation->where('school_user.role', 'teacher')->orderBy('name');
                }])
                ->whereHas('schools', function ($relation) use ($schoolIds) {
                    $relation->whereIn('schools.id', $schoolIds)
                        ->where('school_user.role', 'teacher');
                });

            // Logika pencarian: Nama, Email, NIP, atau Nama Sekolah
            if ($search) {
                $teachersQuery->where(function ($query) use ($search, $schoolIds) {
                    $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(nip) LIKE ?', ["%{$search}%"])
                        ->orWhereHas('schools', function ($relation) use ($search, $schoolIds) {
                            $relation->whereIn('schools.id', $schoolIds)
                                ->where('school_user.role', 'teacher')
                                ->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                        });
                });
            }

            $teachers = $teachersQuery->orderBy('name')->paginate($perPage)->withQueryString();
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
        ]);
    }
}

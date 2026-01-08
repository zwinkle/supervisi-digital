<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SchoolController extends Controller
{
    /**
     * Menampilkan daftar sekolah.
     * Fitur ini mendukung pencarian real-time (AJAX) dan navigasi halaman (pagination).
     */
    public function index(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $search = $q !== '' ? Str::lower($q) : null;
        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 10;
        }

        $schoolsQuery = School::query();

        // Filter pencarian: Cocokkan kata kunci dengan Nama Sekolah atau Alamat
        if ($search) {
            $schoolsQuery->where(function ($query) use ($search) {
                $query->whereRaw('LOWER(name) LIKE ?', ["%{$search}%"])
                    ->orWhereRaw('LOWER(COALESCE(address, \'\')) LIKE ?', ["%{$search}%"]);
            });
        }

        $schools = $schoolsQuery->orderBy('name')->paginate($perPage)->withQueryString();

        // Jika permintaan berasal dari AJAX (misal saat mengetik di kolom cari),
        // Kembalikan hanya potongan HTML tabel saja untuk update parsial tanpa reload halaman.
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
        ]);
    }

    /**
     * Menampilkan form input data sekolah baru.
     */
    public function create()
    {
        return view('admin.schools.create');
    }

    /**
     * Menyimpan data sekolah baru ke dalam sistem.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:500'],
        ]);
        School::create($data);
        return redirect()->route('admin.schools.index')->with('success', 'Sekolah berhasil dibuat');
    }

    /**
     * Menampilkan halaman edit data sekolah.
     */
    public function edit(School $school)
    {
        return view('admin.schools.edit', compact('school'));
    }

    /**
     * Memperbarui informasi sekolah yang sudah tersimpan.
     */
    public function update(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'address' => ['nullable','string','max:500'],
        ]);
        $school->update($data);
        return redirect()->route('admin.schools.index')->with('success', 'Sekolah diperbarui');
    }

    /**
     * Menghapus data sekolah dari sistem.
     * Perhatian: Data terkait mungkin akan ikut terhapus atau menyebabkan error jika ada foreign key constraint tanpa cascade.
     */
    public function destroy(School $school)
    {
        $school->delete();
        return redirect()->route('admin.schools.index')->with('success', 'Sekolah dihapus');
    }
}

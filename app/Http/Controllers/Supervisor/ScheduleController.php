<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScheduleController extends Controller
{
    /**
     * Menampilkan daftar seluruh jadwal supervisi di bawah pengelolaan supervisor ini.
     * Mendukung pemfilteran (Bulan/Tahun) dan navigasi halaman.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month');
        $year = $request->input('year', date('Y'));

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 10;
        }

        // Optimasi Query: Load data relasi yang dibutuhkan di depan (Eager Loading)
        // Termasuk data sekolah, guru, file submisi, video, dan riwayat evaluasi
        $query = Schedule::with([
                'school',
                'teacher',
                'submission.documents.file',
                'submission.videoFile',
                'evaluations',
            ])
            ->where('supervisor_id', $user->id);

        // Filter tanggal
        if ($month && $year) {
            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        } elseif ($year) {
            $query->whereYear('date', $year);
        }

        $schedules = $query->orderByDesc('date')->paginate($perPage)->withQueryString();

        // Respon Khusus AJAX: Hanya merender potongan HTML tabel (Partial View)
        // Digunakan untuk fitur pencarian/filter yang mulus tanpa reload halaman penuh
        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('schedules.partials.supervisor_list', [
                    'schedules' => $schedules,
                ])->render(),
            ]);
        }

        return view('schedules.supervisor', [
            'schedules' => $schedules,
        ]);
    }

    /**
     * Menampilkan halaman penilaian (Assessment).
     * Di sini supervisor bisa melihat status upload guru dan memberikan nilai.
     */
    public function assessment(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        // Pastikan jadwal ini milik supervisor yang sedang login
        if ($schedule->supervisor_id !== $user->id) abort(403);
        
        $schedule->load(['school','teacher','evaluations','submission.documents.file','submission.videoFile']);
        
        // Mengelompokkan evaluasi yang sudah ada berdasarkan tipenya (RPP, Pembelajaran, Asesmen)
        $evalByType = ($schedule->evaluations ?? collect())->keyBy('type');
        
        // Metadata kartu penilaian untuk UI
        $cards = [
            'rpp' => [
                'label' => 'RPP',
                'icon' => 'document',
                'description' => 'Evaluasi kelengkapan perangkat pembelajaran.'
            ],
            'pembelajaran' => [
                'label' => 'Pembelajaran',
                'icon' => 'layout-dashboard',
                'description' => 'Penilaian pelaksanaan proses belajar mengajar.'
            ],
            'asesmen' => [
                'label' => 'Asesmen',
                'icon' => 'badge-check',
                'description' => 'Kualitas instrumen penilaian yang digunakan.'
            ],
        ];
        return view('supervisor.schedules.assessment', [
            'schedule' => $schedule,
            'evalByType' => $evalByType,
            'cards' => $cards,
            'availability' => [
                // Cek ketersediaan dokumen submisi dari guru sebelum bisa dinilai
                'rpp' => $schedule->hasSubmissionFor('rpp'),
                'pembelajaran' => $schedule->hasSubmissionFor('pembelajaran'),
                'asesmen' => $schedule->hasSubmissionFor('asesmen'),
                'administrasi' => $schedule->hasSubmissionFor('administrasi'),
            ],
        ]);
    }

    /**
     * Menampilkan form untuk menjadwalkan supervisi baru.
     * Hanya menampilkan sekolah dan guru yang relevan dengan supervisor ini.
     */
    public function create(Request $request)
    {
        $user = $request->user();
        // Ambil sekolah dimana user berperan sebagai supervisor
        $schools = $user->schools()->wherePivot('role','supervisor')->orderBy('name')->get();
        $teachersBySchool = collect();

        if ($schools->isNotEmpty()) {
            // Ambil data guru yang terdaftar di sekolah-sekolah tersebut
            $teacherRecords = DB::table('school_user')
                ->join('users', 'users.id', '=', 'school_user.user_id')
                ->whereIn('school_user.school_id', $schools->pluck('id'))
                ->where('school_user.role', 'teacher')
                ->orderBy('users.name')
                ->get([
                    'school_user.school_id',
                    'users.id as user_id',
                    'users.name',
                    'users.nip',
                ]);

            // Grouping guru per sekolah untuk dropdown berjenjang di frontend
            $teachersBySchool = $teacherRecords->groupBy('school_id')->map(function ($records) {
                return $records->map(function ($record) {
                    return [
                        'id' => $record->user_id,
                        'name' => $record->name,
                        'nip' => $record->nip,
                    ];
                })->values();
            });
        }

        return view('supervisor.schedules.create', [
            'schools' => $schools,
            'teachersBySchool' => $teachersBySchool,
        ]);
    }

    /**
     * Menyimpan jadwal supervisi baru ke sistem.
     * Melakukan pengecekan validitas data sekolah, guru, dan mencegah duplikasi jadwal.
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $data = $request->validate([
            'school_id' => ['required','integer','exists:schools,id'],
            'teacher_id' => ['required','integer','exists:users,id'],
            'date' => ['required','date'],
            'title' => ['required','string','max:255'],
            'class_name' => ['required','string','max:50'],
            'notes' => ['nullable','string'],
        ]);
        
        // Validasi: Apakah sekolah ini dikelola supervisor?
        if (!$user->schools()->wherePivot('role','supervisor')->where('schools.id',$data['school_id'])->exists()) {
            return back()->withErrors(['school_id' => 'Sekolah ini tidak berada dalam pengelolaan Anda.'])->withInput();
        }
        
        // Validasi: Apakah guru terdaftar di sekolah ini?
        $belongs = DB::table('school_user')
            ->where('school_id',$data['school_id'])
            ->where('user_id',$data['teacher_id'])
            ->where('role','teacher')->exists();
        if (!$belongs) {
            return back()->withErrors(['teacher_id' => 'Guru tidak terdaftar pada sekolah ini.'])->withInput();
        }
        
        // Validasi: Judul unik per tanggal per supervisor (mencegah double entry tidak sengaja)
        $dupCreate = Schedule::where('supervisor_id', $user->id)
            ->whereDate('date', $data['date'])
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($data['title'])])
            ->exists();
        if ($dupCreate) {
            return back()->withErrors(['title' => 'Judul jadwal pada tanggal tersebut sudah digunakan. Gunakan judul lain atau ubah tanggal.'])->withInput();
        }
        
        Schedule::create([
            'school_id' => $data['school_id'],
            'supervisor_id' => $user->id,
            'teacher_id' => $data['teacher_id'],
            'date' => $data['date'],
            'title' => $data['title'] ?? 'Sesi Supervisi',
            'class_name' => $data['class_name'],
            'remarks' => $data['notes'] ?? null,
        ]);
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal dibuat');
    }

    /**
     * Menampilkan halaman edit jadwal supervisi.
     */
    public function edit(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        
        $schools = $user->schools()->wherePivot('role','supervisor')->orderBy('name')->get();
        $teachersBySchool = collect();

        if ($schools->isNotEmpty()) {
            $teacherRecords = DB::table('school_user')
                ->join('users', 'users.id', '=', 'school_user.user_id')
                ->whereIn('school_user.school_id', $schools->pluck('id'))
                ->where('school_user.role', 'teacher')
                ->orderBy('users.name')
                ->get([
                    'school_user.school_id',
                    'users.id as user_id',
                    'users.name',
                    'users.nip',
                ]);

            $teachersBySchool = $teacherRecords->groupBy('school_id')->map(function ($records) {
                return $records->map(function ($record) {
                    return [
                        'id' => $record->user_id,
                        'name' => $record->name,
                        'nip' => $record->nip,
                    ];
                })->values();
            });
        }

        return view('supervisor.schedules.edit', [
            'schedule' => $schedule,
            'schools' => $schools,
            'teachersBySchool' => $teachersBySchool,
        ]);
    }

    /**
     * Memperbarui detail jadwal supervisi yang sudah ada.
     */
    public function update(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        // Pastikan supervisor yang mengubah adalah pemilik jadwal
        if ($schedule->supervisor_id !== $user->id) abort(403);
        
        $data = $request->validate([
            'school_id' => ['required','integer','exists:schools,id'],
            'teacher_id' => ['required','integer','exists:users,id'],
            'date' => ['required','date'],
            'title' => ['nullable','string','max:255'],
            'class_name' => ['required','string','max:50'],
            'notes' => ['nullable','string'],
        ]);
        
        if (!$user->schools()->wherePivot('role','supervisor')->where('schools.id',$data['school_id'])->exists()) {
            return back()->withErrors(['school_id' => 'Sekolah ini tidak berada dalam pengelolaan Anda.'])->withInput();
        }
        $belongs = DB::table('school_user')
            ->where('school_id',$data['school_id'])
            ->where('user_id',$data['teacher_id'])
            ->where('role','teacher')->exists();
        if (!$belongs) {
            return back()->withErrors(['teacher_id' => 'Guru tidak terdaftar pada sekolah ini.'])->withInput();
        }
        
        // Cek duplikasi judul selain jadwal ini sendiri
        $dupUpdate = Schedule::where('supervisor_id', $user->id)
            ->whereDate('date', $data['date'])
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($data['title'])])
            ->where('id', '!=', $schedule->id)
            ->exists();
        if ($dupUpdate) {
            return back()->withErrors(['title' => 'Judul jadwal pada tanggal tersebut sudah digunakan. Gunakan judul lain atau ubah tanggal.'])->withInput();
        }
        
        // Mapping input 'notes' ke field database 'remarks'
        $payload = $data;
        $payload['remarks'] = $data['notes'] ?? null;
        unset($payload['notes']);
        
        $schedule->update($payload);
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal diperbarui');
    }

    /**
     * Menghapus jadwal supervisi dari sistem.
     */
    public function destroy(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->delete();
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal dihapus');
    }

    /**
     * Menandai status jadwal menjadi "Sudah Dilaksanakan".
     */
    public function conduct(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->conducted_at = now();
        $schedule->save();
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal ditandai telah dilaksanakan.');
    }

    /**
     * Menghasilkan laporan hasil supervisi dalam format PDF.
     */
    public function export(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        
        // Load data yang dibutuhkan di PDF
        $schedule->load(['school','supervisor','teacher','evaluations']);
        
        try {
            $html = view('exports.schedule_evaluation', [
                'schedule' => $schedule,
            ])->render();
            
            // Konfigurasi Dompdf
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false); // False untuk security, assets harus lokal
            $options->set('defaultPaperSize', 'a4');
            
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            $filename = 'penilaian_supervisor_schedule_'.$schedule->id.'.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="'.$filename.'"'
            ]);
        } catch (\Throwable $e) {
            report($e);
            return response('Gagal membuat PDF: '.$e->getMessage(), 500);
        }
    }

    /**
     * Mengunggah hasil evaluasi manual (File & Skor).
     * Digunakan jika metode penilaian yang dipilih tidak menggunakan sistem digital penuh.
     */
    public function uploadEvaluation(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);

        // Sanitisasi skor: ganti koma dengan titik untuk format desimal
        if ($request->has('scores')) {
            $scores = $request->input('scores');
            foreach ($scores as $key => $val) {
                 if (is_string($val) || is_numeric($val)) {
                    $scores[$key] = str_replace(',', '.', $val);
                 }
            }
            $request->merge(['scores' => $scores]);
        }

        $request->validate([
            'evaluation_file' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'scores.rpp' => 'required|numeric|min:0|max:100',
            'scores.pembelajaran' => 'required|numeric|min:0|max:100',
            'scores.asesmen' => 'required|numeric|min:0|max:100',
        ]);

        // Hapus file lama jika ada dan replace dengan yang baru
        if ($schedule->uploaded_evaluation_file && \Storage::disk('public')->exists($schedule->uploaded_evaluation_file)) {
            \Storage::disk('public')->delete($schedule->uploaded_evaluation_file);
        }

        // Store new file
        $path = $request->file('evaluation_file')->store('evaluation_files', 'public');
        
        $schedule->uploaded_evaluation_file = $path;
        $schedule->evaluation_method = 'upload'; // Set metode ke 'upload' secara eksplisit
        $schedule->evaluated_at = now(); // Tandai sudah dinilai
        
        // Simpan skor manual
        $schedule->manual_rpp_score = $request->input('scores.rpp');
        $schedule->manual_pembelajaran_score = $request->input('scores.pembelajaran');
        $schedule->manual_asesmen_score = $request->input('scores.asesmen');
        
        $schedule->save();

        return redirect()->route('supervisor.schedules.assessment', $schedule)
            ->with('success', 'File hasil supervisi dan skor berhasil diupload');
    }

    /**
     * Mengunduh kembali file evaluasi manual yang pernah diunggah.
     */
    public function downloadEvaluation(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);

        if (!$schedule->uploaded_evaluation_file || !\Storage::disk('public')->exists($schedule->uploaded_evaluation_file)) {
            return redirect()->back()->withErrors(['file' => 'File tidak ditemukan']);
        }

        return \Storage::disk('public')->download($schedule->uploaded_evaluation_file);
    }

    /**
     * Mengganti mode penilaian (Digital System vs Upload Manual).
     */
    public function updateMethod(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);

        $request->validate([
            'evaluation_method' => 'required|in:manual,upload',
        ]);

        $schedule->evaluation_method = $request->evaluation_method;
        $schedule->save();

        return response()->json(['success' => true]);
    }
}

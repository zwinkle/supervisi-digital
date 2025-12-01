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
    public function index(Request $request)
    {
        $user = $request->user();
        $q = $request->input('q');
        $filter = $request->input('filter', 'all');

        $query = Schedule::with([
                'school',
                'teacher',
                'submission.documents.file',
                'submission.videoFile',
                'evaluations',
            ])
            ->where('supervisor_id', $user->id);

        // Only apply filters when there's a search query
        if ($q && trim($q) !== '') {
            $query->where(function ($query) use ($q, $filter) {
                if ($filter === 'teacher' || $filter === 'all') {
                    $query->orWhereHas('teacher', function ($teacherQuery) use ($q) {
                        $teacherQuery->where('name', 'LIKE', '%' . $q . '%');
                    });
                }
                if ($filter === 'school' || $filter === 'all') {
                    $query->orWhereHas('school', function ($schoolQuery) use ($q) {
                        $schoolQuery->where('name', 'LIKE', '%' . $q . '%');
                    });
                }
            });
        }

        $schedules = $query->orderByDesc('date')->get();

        if ($request->wantsJson()) {
            $html = view('schedules.supervisor', compact('schedules'))->render();
            preg_match('/<div class="space-y-4" id="supervisor-schedules-results">(.*?)<\/div>\s*<\/div>\s*@push/s', $html, $matches);
            $resultsHtml = $matches[1] ?? '';
            return response()->json(['html' => $resultsHtml]);
        }

        return view('schedules.supervisor', compact('schedules'));
    }

    public function assessment(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->load(['school','teacher','evaluations','submission.documents.file','submission.videoFile']);
        $evalByType = ($schedule->evaluations ?? collect())->keyBy('type');
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
                'rpp' => $schedule->hasSubmissionFor('rpp'),
                'pembelajaran' => $schedule->hasSubmissionFor('pembelajaran'),
                'asesmen' => $schedule->hasSubmissionFor('asesmen'),
            ],
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
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

        return view('supervisor.schedules.create', [
            'schools' => $schools,
            'teachersBySchool' => $teachersBySchool,
        ]);
    }

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
        // Enforce: title must be unique per date for this supervisor
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

    public function update(Request $request, Schedule $schedule)
    {
        $user = $request->user();
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
        // Enforce: title must be unique per date for this supervisor (exclude current schedule)
        $dupUpdate = Schedule::where('supervisor_id', $user->id)
            ->whereDate('date', $data['date'])
            ->whereRaw('LOWER(title) = ?', [mb_strtolower($data['title'])])
            ->where('id', '!=', $schedule->id)
            ->exists();
        if ($dupUpdate) {
            return back()->withErrors(['title' => 'Judul jadwal pada tanggal tersebut sudah digunakan. Gunakan judul lain atau ubah tanggal.'])->withInput();
        }
        // Map notes -> remarks for DB
        $payload = $data;
        $payload['remarks'] = $data['notes'] ?? null;
        unset($payload['notes']);
        $schedule->update($payload);
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal diperbarui');
    }

    public function destroy(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->delete();
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal dihapus');
    }

    public function conduct(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->conducted_at = now();
        $schedule->save();
        return redirect()->route('supervisor.schedules')->with('success', 'Jadwal ditandai telah dilaksanakan.');
    }
    public function export(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        // Generate PDF using Dompdf directly
        $schedule->load(['school','supervisor','teacher','evaluations']);
        try {
            $html = view('exports.schedule_evaluation', [
                'schedule' => $schedule,
            ])->render();
            $options = new Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
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

    public function uploadEvaluation(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);

        $request->validate([
            'evaluation_file' => 'required|file|mimes:pdf,doc,docx|max:10240', // max 10MB
            'scores.rpp' => 'required|numeric|min:0|max:100',
            'scores.pembelajaran' => 'required|numeric|min:0|max:100',
            'scores.asesmen' => 'required|numeric|min:0|max:100',
        ]);

        // Delete old file if exists
        if ($schedule->uploaded_evaluation_file && \Storage::disk('public')->exists($schedule->uploaded_evaluation_file)) {
            \Storage::disk('public')->delete($schedule->uploaded_evaluation_file);
        }

        // Store new file
        $path = $request->file('evaluation_file')->store('evaluation_files', 'public');
        
        $schedule->uploaded_evaluation_file = $path;
        $schedule->evaluation_method = 'upload';
        $schedule->evaluated_at = now(); // Mark as evaluated
        
        // Save manual scores
        $schedule->manual_rpp_score = $request->input('scores.rpp');
        $schedule->manual_pembelajaran_score = $request->input('scores.pembelajaran');
        $schedule->manual_asesmen_score = $request->input('scores.asesmen');
        
        $schedule->save();

        return redirect()->route('supervisor.schedules.assessment', $schedule)
            ->with('success', 'File hasil supervisi dan skor berhasil diupload');
    }

    public function downloadEvaluation(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);

        if (!$schedule->uploaded_evaluation_file || !\Storage::disk('public')->exists($schedule->uploaded_evaluation_file)) {
            return redirect()->back()->withErrors(['file' => 'File tidak ditemukan']);
        }

        return \Storage::disk('public')->download($schedule->uploaded_evaluation_file);
    }

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

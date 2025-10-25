<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schedules = Schedule::with([
                'school',
                'teacher',
                'submission.rppFile',
                'submission.videoFile',
                'submission.asesmenFile',
                'submission.administrasiFile',
                'evaluations',
            ])
            ->where('supervisor_id', $user->id)
            ->orderByDesc('date')
            ->get();
        return view('schedules.supervisor', compact('schedules'));
    }

    public function assessment(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->supervisor_id !== $user->id) abort(403);
        $schedule->load(['school','teacher','evaluations']);
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
        ]);
    }

    public function create(Request $request)
    {
        $user = $request->user();
        $schools = $user->schools()->wherePivot('role','supervisor')->orderBy('name')->get();
        $teacherIds = DB::table('school_user')
            ->whereIn('school_id', $schools->pluck('id'))
            ->where('role','teacher')
            ->pluck('user_id');
        $teachers = User::whereIn('id', $teacherIds)->orderBy('name')->get();
        return view('supervisor.schedules.create', compact('schools','teachers'));
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
        $teacherIds = DB::table('school_user')
            ->whereIn('school_id', $schools->pluck('id'))
            ->where('role','teacher')
            ->pluck('user_id');
        $teachers = User::whereIn('id', $teacherIds)->orderBy('name')->get();
        return view('supervisor.schedules.edit', compact('schedule','schools','teachers'));
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
}

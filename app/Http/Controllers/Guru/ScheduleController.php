<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $schedules = Schedule::with(['school','evaluations'])
            ->where('teacher_id', $user->id)
            ->orderByDesc('date')
            ->get();
        return view('schedules.teacher', compact('schedules'));
    }

    public function export(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->teacher_id !== $user->id) abort(403);
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
            $filename = 'penilaian_guru_schedule_'.$schedule->id.'.pdf';
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

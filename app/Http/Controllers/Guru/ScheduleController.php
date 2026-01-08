<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use Dompdf\Options;

class ScheduleController extends Controller
{
    /**
     * Menampilkan daftar jadwal supervisi yang terkait dengan guru.
     * Fitur: Filter berdasarkan Bulan & Tahun, serta halaman (pagination).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $month = $request->input('month');
        $year = $request->input('year', date('Y'));

        $query = Schedule::with(['school','evaluations'])
            ->where('teacher_id', $user->id);

        if ($month && $year) {
            $query->whereYear('date', $year)
                  ->whereMonth('date', $month);
        } elseif ($year) {
            $query->whereYear('date', $year);
        }

        $perPage = (int) $request->input('per_page', 10);
        if (!in_array($perPage, [10, 20])) {
            $perPage = 10;
        }

        $schedules = $query->orderByDesc('date')->paginate($perPage)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'html' => view('schedules.partials.teacher_list', compact('schedules'))->render(),
            ]);
        }

        return view('schedules.teacher', compact('schedules'));
    }

    /**
     * Mengunduh rapor/hasil evaluasi supervisi dalam format PDF.
     */
    public function export(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->teacher_id !== $user->id) abort(403);
        
        // Load data lengkap untuk PDF
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

    /**
     * Mengunduh lampiran file hasil evaluasi (jika supervisi dilakukan dengan metode upload manual).
     */
    public function downloadEvaluation(Request $request, Schedule $schedule)
    {
        $user = $request->user();
        if ($schedule->teacher_id !== $user->id) abort(403);

        if (!$schedule->uploaded_evaluation_file || !\Storage::disk('public')->exists($schedule->uploaded_evaluation_file)) {
            return redirect()->back()->withErrors(['file' => 'File hasil supervisi tidak tersedia']);
        }

        return \Storage::disk('public')->download($schedule->uploaded_evaluation_file);
    }
}

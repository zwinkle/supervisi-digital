<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Menampilkan dashboard utama Guru.
     * Menyajikan ringkasan tugas (belum dikirim), status penilaian, dan riwayat supervisi.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Pending: Jadwal mendatang yang dokumennya belum di-upload (Submit)
        $pendingSchedules = Schedule::where('teacher_id', $user->id)
            ->where('date', '>=', now())
            ->whereDoesntHave('submission')
            ->count();
            
        // In Progress: Dokumen sudah dikirim, menunggu penilaian Supervisor
        $inProgressSchedules = Schedule::where('teacher_id', $user->id)
            ->whereHas('submission')
            ->whereNull('evaluated_at')
            ->count();
            
        // Selesai: Sudah dinilai oleh Supervisor
        $completedSchedules = Schedule::where('teacher_id', $user->id)
            ->whereNotNull('evaluated_at')
            ->count();
            
        // Widget Utama: Detail supervisi terakhir yang sudah dinilai
        $lastSupervision = Schedule::where('teacher_id', $user->id)
            ->whereNotNull('evaluated_at')
            ->orderBy('evaluated_at', 'desc')
            ->first();
            
        $lastSupervisionTitle = $lastSupervision ? $lastSupervision->title : 'Tidak ada data';
        $lastSupervisionDate = $lastSupervision ? $lastSupervision->evaluated_at->format('d M Y') : '-';
        
        // Ringkasan 2 supervisi terakhir dalam bentuk list
        $recentSupervisions = Schedule::where('teacher_id', $user->id)
            ->with(['evaluations'])
            ->orderBy('date', 'desc')
            ->limit(2)
            ->get()
            ->map(function ($schedule) {
                // Ekstrak skor per aspek
                $rppEval = $schedule->evaluations->where('type', 'rpp')->first();
                $pembelajaranEval = $schedule->evaluations->where('type', 'pembelajaran')->first();
                $asesmenEval = $schedule->evaluations->where('type', 'asesmen')->first();
                
                return [
                    'title' => $schedule->title,
                    'supervisor' => $schedule->supervisor->name ?? '-',
                    'date' => $schedule->date->format('d M Y'),
                    'rpp_score' => $rppEval ? $rppEval->total_score : '-',
                    'pembelajaran_score' => $pembelajaranEval ? $pembelajaranEval->total_score : '-',
                    'asesmen_score' => $asesmenEval ? $asesmenEval->total_score : '-',
                    'status' => $schedule->isCompleted() ? 'Selesai' : 'Dalam Proses',
                    'status_class' => $schedule->isCompleted() ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                ];
            });
        
        // Kalkulasi tren aktivitas (Mingguan/Bulanan)
        $pendingThisWeek = Schedule::where('teacher_id', $user->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereDoesntHave('submission')
            ->count();
            
        $inProgressThisMonth = Schedule::where('teacher_id', $user->id)
            ->whereHas('submission')
            ->whereNull('evaluated_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
            
        $completedThisMonth = Schedule::where('teacher_id', $user->id)
            ->whereNotNull('evaluated_at')
            ->whereMonth('evaluated_at', now()->month)
            ->whereYear('evaluated_at', now()->year)
            ->count();
        
        $pendingTrend = $pendingThisWeek . ' minggu ini';
        $inProgressTrend = $inProgressThisMonth . ' bulan ini';
        $completedTrend = $completedThisMonth . ' bulan ini';

        return view('dashboard.guru', compact(
            'pendingSchedules',
            'inProgressSchedules',
            'completedSchedules',
            'lastSupervisionTitle',
            'lastSupervisionDate',
            'recentSupervisions',
            'pendingTrend',
            'inProgressTrend',
            'completedTrend'
        ));
    }
}
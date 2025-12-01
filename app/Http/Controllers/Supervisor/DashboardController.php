<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get upcoming schedules (future dates, not conducted yet)
        $upcomingSchedules = Schedule::where('supervisor_id', $user->id)
            ->where('date', '>=', now())
            ->whereNull('conducted_at')
            ->count();
            
        // Get completed schedules (conducted)
        $completedSchedules = Schedule::where('supervisor_id', $user->id)
            ->whereNotNull('conducted_at')
            ->count();
            
        // Get pending schedules (past dates, not conducted)
        $pendingSchedules = Schedule::where('supervisor_id', $user->id)
            ->where('date', '<', now())
            ->whereNull('conducted_at')
            ->count();
            
        // Get teachers supervised by this supervisor
        $teachersSupervised = User::whereHas('teachingSchedules', function ($query) use ($user) {
            $query->where('supervisor_id', $user->id);
        })->count();
        
        // Get recent schedules for display
        $recentSchedules = Schedule::where('supervisor_id', $user->id)
            ->with(['teacher', 'school'])
            ->orderBy('date', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($schedule) {
                return [
                    'title' => $schedule->title,
                    'teacher' => $schedule->teacher->name,
                    'date' => $schedule->date->format('d M'),
                    'status' => $schedule->computedBadge()['text'],
                    'status_class' => $schedule->computedBadge()['class']
                ];
            });
        
        // Calculate trend indicators based on actual data with timestamp
        $upcomingThisWeek = Schedule::where('supervisor_id', $user->id)
            ->whereBetween('date', [now()->startOfWeek(), now()->endOfWeek()])
            ->whereNull('conducted_at')
            ->count();
            
        $completedThisMonth = Schedule::where('supervisor_id', $user->id)
            ->whereNotNull('conducted_at')
            ->whereMonth('conducted_at', now()->month)
            ->whereYear('conducted_at', now()->year)
            ->count();
            
        $pendingOverdue = Schedule::where('supervisor_id', $user->id)
            ->where('date', '<', now()->subDays(7))
            ->whereNull('conducted_at')
            ->count();
            
        $teachersThisMonth = User::whereHas('teachingSchedules', function ($query) use ($user) {
            $query->where('supervisor_id', $user->id);
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        
        $upcomingTrend = $upcomingThisWeek . ' minggu ini';
        $completedTrend = $completedThisMonth . ' bulan ini';
        $pendingTrend = $pendingOverdue . ' terlambat';
        $teachersTrend = $teachersThisMonth . ' baru bulan ini';

        return view('dashboard.supervisor', compact(
            'upcomingSchedules',
            'completedSchedules',
            'pendingSchedules',
            'teachersSupervised',
            'recentSchedules',
            'upcomingTrend',
            'completedTrend',
            'pendingTrend',
            'teachersTrend'
        ));
    }
}
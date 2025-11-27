<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @property User $user
 */
class DashboardController extends Controller
{
    /**
     * Display the user dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Check user roles
        $isAdmin = $user->is_admin;
        $isSupervisor = $user->schools()->wherePivot('role', 'supervisor')->exists();
        $isTeacher = $user->schools()->wherePivot('role', 'teacher')->exists();
        
        // If user has a specific role, redirect to their dashboard
        if ($isAdmin) {
            return redirect()->route('admin.dashboard');
        }
        
        if ($isSupervisor) {
            return redirect()->route('supervisor.dashboard');
        }
        
        if ($isTeacher) {
            return redirect()->route('guru.dashboard');
        }
        
        // For general users, show generic dashboard with statistics
        // Get user's schedules
        $userSchedules = Schedule::where('teacher_id', $user->id)
            ->orWhere('supervisor_id', $user->id)
            ->get();
            
        $totalSchedules = $userSchedules->count();
        $upcomingSchedules = $userSchedules->where('date', '>=', now())->count();
        $completedSchedules = $userSchedules->whereNotNull('evaluated_at')->count();
        
        // Calculate completion rate
        $completionRate = $totalSchedules > 0 ? round(($completedSchedules / $totalSchedules) * 100, 2) : 0;
        
        // Platform usage stats (simplified)
        $platformUsage = 1200; // This would typically come from analytics
        $personalActivity = 24; // This would typically come from user activity logs
        $upcomingEvents = $upcomingSchedules;
        
        // Trend indicators (simplified)
        $platformTrend = '+15% bulan ini';
        $activityTrend = '5 aktivitas hari ini';
        $eventsTrend = 'Minggu ini';
        $completionTrend = '+5% dari bulan lalu';
        
        // Recent platform updates (simplified)
        $recentUpdates = [
            [
                'title' => 'Platform Update',
                'description' => 'Versi baru tersedia dengan peningkatan performa',
                'time' => '2 hari yang lalu',
                'icon' => 'loader'
            ],
            [
                'title' => 'Maintenance',
                'description' => 'Jadwal maintenance rutin akan dilakukan akhir pekan',
                'time' => '5 hari yang lalu',
                'icon' => 'calendar'
            ]
        ];

        return view('dashboard.index', compact(
            'platformUsage',
            'personalActivity',
            'upcomingEvents',
            'completionRate',
            'platformTrend',
            'activityTrend',
            'eventsTrend',
            'completionTrend',
            'recentUpdates'
        ));
    }
}
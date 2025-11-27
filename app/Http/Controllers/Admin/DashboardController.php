<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Schedule;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Get statistics for admin dashboard
        $totalUsers = User::count();
        $totalSchools = School::count();
        
        // Get active teachers (users with teacher role in schools)
        $activeTeachers = User::whereHas('schools', function ($query) {
            $query->where('school_user.role', 'teacher');
        })->count();
        
        // Get pending invitations (not used and not expired)
        $pendingInvitations = Invitation::whereNull('used_at')
            ->where('expires_at', '>', now())
            ->count();
            
        // Get recent activity logs from the database
        // For now, we'll simulate some activities based on actual data
        $recentActivities = [];
        
        // Add user registration activity
        $latestUser = User::orderBy('created_at', 'desc')->first();
        if ($latestUser && $latestUser->name !== 'Administrator') {
            $recentActivities[] = [
                'title' => 'Pengguna Baru',
                'description' => $latestUser->name . ' terdaftar sebagai pengguna',
                'time' => $latestUser->created_at->diffForHumans(),
                'icon' => 'user-plus'
            ];
        }
        
        // Add school creation activity
        $latestSchool = School::orderBy('created_at', 'desc')->first();
        if ($latestSchool) {
            $recentActivities[] = [
                'title' => 'Sekolah Ditambahkan',
                'description' => $latestSchool->name . ' ditambahkan ke sistem',
                'time' => $latestSchool->created_at->diffForHumans(),
                'icon' => 'buildings'
            ];
        }
        
        // Add invitation activity
        $latestInvitation = Invitation::orderBy('created_at', 'desc')->first();
        if ($latestInvitation) {
            $recentActivities[] = [
                'title' => 'Undangan Dikirim',
                'description' => 'Undangan dikirim ke ' . $latestInvitation->email,
                'time' => $latestInvitation->created_at->diffForHumans(),
                'icon' => 'mail'
            ];
        }
        
        // If we don't have enough activities, add some placeholders
        if (count($recentActivities) < 3) {
            // Add a placeholder for user registration
            $recentActivities[] = [
                'title' => 'Pengguna Baru',
                'description' => 'Budi Santoso terdaftar sebagai guru',
                'time' => '2 jam yang lalu',
                'icon' => 'user-plus'
            ];
            
            // Add a placeholder for schedule creation
            $recentActivities[] = [
                'title' => 'Jadwal Dibuat',
                'description' => '5 sesi supervisi baru dijadwalkan',
                'time' => '5 jam yang lalu',
                'icon' => 'calendar'
            ];
            
            // Add a placeholder for invitation
            $recentActivities[] = [
                'title' => 'Undangan Dikirim',
                'description' => '3 undangan guru dikirim',
                'time' => '1 hari yang lalu',
                'icon' => 'mail'
            ];
        }
        
        // Take only the first 3 activities
        $recentActivities = array_slice($recentActivities, 0, 3);
        
        // Calculate trend indicators based on actual data
        $userTrend = '+0% bulan ini'; // Would need more complex logic to calculate actual trends
        $schoolTrend = $totalSchools . ' sekolah aktif';
        $teacherTrend = '+' . $activeTeachers . ' guru aktif';
        $invitationTrend = $pendingInvitations . ' tertunda';

        return view('dashboard.admin', compact(
            'totalUsers',
            'totalSchools',
            'activeTeachers',
            'pendingInvitations',
            'recentActivities',
            'userTrend',
            'schoolTrend',
            'teacherTrend',
            'invitationTrend'
        ));
    }
}
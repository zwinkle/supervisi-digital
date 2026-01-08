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
    /**
     * Menampilkan dashboard utama Administrator.
     * Mengumpulkan statistik global (sekolah, user, guru) dan notifikasi penting seperti undangan kedaluwarsa.
     */
    public function index(Request $request)
    {
        // Statistik umum
        $totalUsers = User::count();
        $totalSchools = School::count();
        
        // Hitung total guru yang aktif (sudah terdaftar di minimal satu sekolah)
        $activeTeachers = User::whereHas('schools', function ($query) {
            $query->where('school_user.role', 'teacher');
        })->count();
        
        // Cek jumlah undangan yang masih "sangkut" (belum dipakai tapi belum kedaluwarsa)
        $pendingInvitations = Invitation::whereNull('used_at')
            ->where('expires_at', '>', now())
            ->count();
            
        // Simulasi Log Aktivitas Terbaru (untuk keperluan demo UI)
        // Idealnya nanti diambil dari tabel 'activity_logs' jika sudah diimplementasikan.
        $recentActivities = [];
        
        // Cek jika ada user baru bergabung
        $latestUser = User::orderBy('created_at', 'desc')->first();
        if ($latestUser && $latestUser->name !== 'Administrator') {
            $recentActivities[] = [
                'title' => 'Pengguna Baru',
                'description' => $latestUser->name . ' terdaftar sebagai pengguna',
                'time' => $latestUser->created_at->diffForHumans(),
                'icon' => 'user-plus'
            ];
        }
        
        // Cek sekolah terbaru
        $latestSchool = School::orderBy('created_at', 'desc')->first();
        if ($latestSchool) {
            $recentActivities[] = [
                'title' => 'Sekolah Ditambahkan',
                'description' => $latestSchool->name . ' ditambahkan ke sistem',
                'time' => $latestSchool->created_at->diffForHumans(),
                'icon' => 'buildings'
            ];
        }
        
        // Cek undangan terbaru
        $latestInvitation = Invitation::orderBy('created_at', 'desc')->first();
        if ($latestInvitation) {
            $recentActivities[] = [
                'title' => 'Undangan Dikirim',
                'description' => 'Undangan dikirim ke ' . $latestInvitation->email,
                'time' => $latestInvitation->created_at->diffForHumans(),
                'icon' => 'mail'
            ];
        }
        
        // Placeholder / Data Dummy jika aktivitas masih sepi (agar tampilan tidak kosong melompong)
        if (count($recentActivities) < 3) {
            // Placeholder user
            $recentActivities[] = [
                'title' => 'Pengguna Baru',
                'description' => 'Budi Santoso terdaftar sebagai guru',
                'time' => '2 jam yang lalu',
                'icon' => 'user-plus'
            ];
            
            // Placeholder jadwal
            $recentActivities[] = [
                'title' => 'Jadwal Dibuat',
                'description' => '5 sesi supervisi baru dijadwalkan',
                'time' => '5 jam yang lalu',
                'icon' => 'calendar'
            ];
            
            // Placeholder undangan
            $recentActivities[] = [
                'title' => 'Undangan Dikirim',
                'description' => '3 undangan guru dikirim',
                'time' => '1 hari yang lalu',
                'icon' => 'mail'
            ];
        }
        
        // Batasi hanya 3 aktivitas
        $recentActivities = array_slice($recentActivities, 0, 3);
        
        // Kalkulasi Tren Bulanan (Bulan ini) untuk ditampilkan sebagai indikator kinerja
        $usersThisMonth = User::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $schoolsThisMonth = School::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        
        $teachersThisMonth = User::whereHas('schools', function ($query) {
            $query->where('school_user.role', 'teacher');
        })
        ->whereMonth('created_at', now()->month)
        ->whereYear('created_at', now()->year)
        ->count();
        
        $expiredInvitations = Invitation::whereNull('used_at')
            ->where('expires_at', '<=', now())
            ->count();
        
        $userTrend = $usersThisMonth . ' baru bulan ini';
        $schoolTrend = $schoolsThisMonth . ' baru bulan ini';
        $teacherTrend = $teachersThisMonth . ' baru bulan ini';
        $invitationTrend = $expiredInvitations . ' kedaluwarsa';

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
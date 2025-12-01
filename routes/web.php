<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubmissionController;
use App\Models\Schedule;
use App\Models\School;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return redirect()->route('login');
});

// Email/password authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
// Disable open registration (invite-only)
// Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
// Route::post('/register', [AuthController::class, 'register'])->name('register.attempt');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Invite acceptance (public, signed)
Route::get('/invites/accept', [\App\Http\Controllers\Auth\InviteController::class, 'show'])
    ->name('invites.accept.show')->middleware('signed');
Route::post('/invites/accept', [\App\Http\Controllers\Auth\InviteController::class, 'store'])
    ->name('invites.accept.store')->middleware('signed');

// Profile completion for teachers on first login
Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', [ProfileController::class, 'showComplete'])->name('profile.complete.show');
    Route::post('/profile/complete', [ProfileController::class, 'storeComplete'])->name('profile.complete.store');

    // Simple profile page for Google linking
    Route::get('/profile', function () {
        $user = Auth::user();
        return view('profile.index', compact('user'));
    })->name('profile.index');
    Route::post('/profile/disconnect-google', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->google_id = null;
        $user->google_email = null;
        $user->google_access_token = null;
        $user->google_refresh_token = null;
        $user->google_token_expires_at = null;
        $user->save();
        return back()->with('success', 'Tautan Google telah diputus.');
    })->name('profile.google.disconnect');

    // ADMIN routes
    Route::prefix('admin')->middleware('is_admin')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\DashboardController::class, 'index'])->name('admin.dashboard');

        // Users list/edit/deactivate
        Route::get('/users', [\App\Http\Controllers\Admin\UserController::class, 'index'])->name('admin.users.index');
        Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserController::class, 'edit'])->name('admin.users.edit');
        Route::post('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'update'])->name('admin.users.update');
        Route::post('/users/{user}/deactivate', [\App\Http\Controllers\Admin\UserController::class, 'deactivate'])->name('admin.users.deactivate');
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserController::class, 'destroy'])->name('admin.users.destroy');

        // Schools index
        Route::get('/schools', [\App\Http\Controllers\Admin\SchoolController::class, 'index'])->name('admin.schools.index');

        // Add School (controller)
        Route::get('/schools/create', [\App\Http\Controllers\Admin\SchoolController::class, 'create'])->name('admin.schools.create');
        Route::post('/schools', [\App\Http\Controllers\Admin\SchoolController::class, 'store'])->name('admin.schools.store');

        // Edit/Delete School
        Route::get('/schools/{school}/edit', [\App\Http\Controllers\Admin\SchoolController::class, 'edit'])->name('admin.schools.edit');
        Route::post('/schools/{school}', [\App\Http\Controllers\Admin\SchoolController::class, 'update'])->name('admin.schools.update');
        Route::delete('/schools/{school}', [\App\Http\Controllers\Admin\SchoolController::class, 'destroy'])->name('admin.schools.destroy');

        // Add User (disabled, invite-only)
        // Route::get('/users/create', [\App\Http\Controllers\Admin\UserController::class, 'create'])->name('admin.users.create');
        // Route::post('/users', [\App\Http\Controllers\Admin\UserController::class, 'store'])->name('admin.users.store');

        // Invitations (admin)
        Route::get('/invitations', [\App\Http\Controllers\Admin\InvitationController::class, 'index'])->name('admin.invitations.index');
        Route::get('/invitations/create', [\App\Http\Controllers\Admin\InvitationController::class, 'create'])->name('admin.invitations.create');
        Route::post('/invitations', [\App\Http\Controllers\Admin\InvitationController::class, 'store'])->name('admin.invitations.store');
        Route::post('/invitations/{invitation}/resend', [\App\Http\Controllers\Admin\InvitationController::class, 'resend'])->name('admin.invitations.resend');
        Route::post('/invitations/{invitation}/revoke', [\App\Http\Controllers\Admin\InvitationController::class, 'revoke'])->name('admin.invitations.revoke');
    });

    // GURU routes
    Route::prefix('guru')->middleware('is_teacher')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Guru\DashboardController::class, 'index'])->name('guru.dashboard');
        Route::get('/schedules', [\App\Http\Controllers\Guru\ScheduleController::class, 'index'])->name('guru.schedules');
        Route::get('/schedules/{schedule}/export', [\App\Http\Controllers\Guru\ScheduleController::class, 'export'])->name('guru.schedules.export');
        Route::get('/schedules/{schedule}/download-evaluation', [\App\Http\Controllers\Guru\ScheduleController::class, 'downloadEvaluation'])->name('guru.schedules.download-evaluation');
        // Upload submission requires linked Google
        Route::get('/schedules/{schedule}/submit', [SubmissionController::class, 'showForm'])
            ->middleware('requires_google_linked')->name('guru.submissions.show');
        Route::post('/schedules/{schedule}/submit', [SubmissionController::class, 'store'])
            ->middleware('requires_google_linked')->name('guru.submissions.store');
        Route::get('/schedules/{schedule}/submit/status', [SubmissionController::class, 'status'])
            ->middleware('requires_google_linked')->name('guru.submissions.status');
        Route::delete('/schedules/{schedule}/submit/delete/{kind}', [SubmissionController::class, 'deleteFile'])
            ->name('guru.submissions.delete');
        Route::delete('/schedules/{schedule}/submit/documents/{document}', [SubmissionController::class, 'deleteDocument'])
            ->name('guru.submissions.documents.destroy');
    });

    // SUPERVISOR routes
    Route::prefix('supervisor')->middleware(['is_supervisor','requires_google_linked'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Supervisor\DashboardController::class, 'index'])->name('supervisor.dashboard');

        // Schedules (controller)
        Route::get('/schedules', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'index'])->name('supervisor.schedules');
        Route::get('/schedules/{schedule}/export', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'export'])->name('supervisor.schedules.export');
        Route::post('/schedules/{schedule}/conduct', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'conduct'])->name('supervisor.schedules.conduct');
        // Assessment summary page
        Route::get('/schedules/{schedule}/assessment', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'assessment'])->name('supervisor.schedules.assessment');
        // Upload evaluation file
        Route::post('/schedules/{schedule}/upload-evaluation', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'uploadEvaluation'])->name('supervisor.schedules.upload-evaluation');
        Route::get('/schedules/{schedule}/download-evaluation', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'downloadEvaluation'])->name('supervisor.schedules.download-evaluation');
        Route::post('/schedules/{schedule}/update-method', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'updateMethod'])->name('supervisor.schedules.update-method');
        // Read-only submissions (uses SubmissionController view)
        Route::get('/schedules/{schedule}/files', [SubmissionController::class, 'showForm'])->name('supervisor.submissions.show');
        // Status polling for submissions (shared with teachers)
        Route::get('/schedules/{schedule}/files/status', [SubmissionController::class, 'status'])->name('supervisor.submissions.status');

        // Evaluations (rpp, pembelajaran, asesmen)
        Route::get('/schedules/{schedule}/evaluate/{type}', [\App\Http\Controllers\EvaluationController::class, 'show'])
            ->name('supervisor.evaluations.show');
        Route::post('/schedules/{schedule}/evaluate/{type}', [\App\Http\Controllers\EvaluationController::class, 'store'])
            ->name('supervisor.evaluations.store');

        Route::get('/schedules/create', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'create'])->name('supervisor.schedules.create');
        Route::post('/schedules', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'store'])->name('supervisor.schedules.store');
        Route::get('/schedules/{schedule}/edit', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'edit'])->name('supervisor.schedules.edit');
        Route::post('/schedules/{schedule}', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'update'])->name('supervisor.schedules.update');
        Route::delete('/schedules/{schedule}', [\App\Http\Controllers\Supervisor\ScheduleController::class, 'destroy'])->name('supervisor.schedules.destroy');

        // Create Teacher (controller)
        Route::get('/users', [\App\Http\Controllers\Supervisor\UserController::class, 'index'])->name('supervisor.users.index');

        // Invitations (supervisor)
        Route::get('/invitations', [\App\Http\Controllers\Supervisor\InvitationController::class, 'index'])->name('supervisor.invitations.index');
        Route::get('/invitations/create', [\App\Http\Controllers\Supervisor\InvitationController::class, 'create'])->name('supervisor.invitations.create');
        Route::post('/invitations', [\App\Http\Controllers\Supervisor\InvitationController::class, 'store'])->name('supervisor.invitations.store');
        Route::post('/invitations/{invitation}/resend', [\App\Http\Controllers\Supervisor\InvitationController::class, 'resend'])->name('supervisor.invitations.resend');
        Route::post('/invitations/{invitation}/revoke', [\App\Http\Controllers\Supervisor\InvitationController::class, 'revoke'])->name('supervisor.invitations.revoke');
    });
});

// Local debug: show resolved Google redirect URL and scopes
if (app()->environment('local')) {
    Route::get('/debug/google-config', function () {
        return response()->json([
            'app_url' => config('app.url'),
            'google_redirect' => config('services.google.redirect'),
            'google_scopes' => config('services.google.scopes'),
        ]);
    });

    // Create a sample schedule for the current user (as teacher) if none exists
    Route::post('/debug/create-sample-schedule', function () {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (!$user) return abort(401);
        $school = School::first();
        if (!$school) {
            $school = School::create(['name' => 'Sekolah Contoh']);
        }
        // Attach user as teacher if not yet
        if (!$user->schools()->wherePivot('role','teacher')->exists()) {
            $user->schools()->attach($school->id, ['role' => 'teacher']);
        }
        $schedule = Schedule::create([
            'school_id' => $school->id,
            'supervisor_id' => $user->id,
            'teacher_id' => $user->id,
            'date' => now()->toDateString(),
            'status' => 'scheduled',
            'title' => 'Sesi Supervisi Contoh',
        ]);
        return redirect()->route('schedules.mine')->with('success', 'Sample schedule created: #'.$schedule->uuid);
    });
}

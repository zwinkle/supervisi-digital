<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * Determine whether the user can view the model.
     * Admin, Supervisor terkait, dan Guru terkait bisa melihat.
     */
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        if ($schedule->supervisor_id === $user->id) return true;
        if ($schedule->teacher_id === $user->id) return true;
        return false;
    }

    /**
     * Determine whether the user can create models.
     * Admin dan Supervisor bisa membuat jadwal.
     */
    public function create(User $user): bool
    {
        if ($user->is_admin) return true;
        // Hanya supervisor yang terdaftar di sekolah yang bisa membuat
        return $user->schools()->wherePivot('role','supervisor')->exists();
    }

    /**
     * Determine whether the user can update the model.
     * Hanya Admin dan Supervisor pembuat jadwal yang bisa mengedit.
     */
    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        return $schedule->supervisor_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     * Sama dengan hak akses update.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        return $schedule->supervisor_id === $user->id;
    }
}

<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    public function view(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        if ($schedule->supervisor_id === $user->id) return true;
        if ($schedule->teacher_id === $user->id) return true;
        return false;
    }

    public function create(User $user): bool
    {
        if ($user->is_admin) return true;
        // supervisor can create
        return $user->schools()->wherePivot('role','supervisor')->exists();
    }

    public function update(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        return $schedule->supervisor_id === $user->id;
    }

    public function delete(User $user, Schedule $schedule): bool
    {
        if ($user->is_admin) return true;
        return $schedule->supervisor_id === $user->id;
    }
}

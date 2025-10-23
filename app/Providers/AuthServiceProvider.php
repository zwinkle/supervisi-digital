<?php

namespace App\Providers;

use App\Models\Schedule;
use App\Policies\SchedulePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::policy(Schedule::class, SchedulePolicy::class);
    }
}

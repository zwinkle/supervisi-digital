<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'is_admin' => App\Http\Middleware\IsAdmin::class,
            'is_supervisor' => App\Http\Middleware\IsSupervisor::class,
            'is_teacher' => App\Http\Middleware\IsTeacher::class,
            'requires_google_linked' => App\Http\Middleware\RequiresGoogleLinked::class,
        ]);
    })
    ->withProviders([
        App\Providers\AuthServiceProvider::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();

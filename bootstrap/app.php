<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\EnsureAdminAuthenticated::class,
            'user.auth' => \App\Http\Middleware\EnsureUserAuthenticated::class,
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'api.auth' => \App\Http\Middleware\ValidateApiKey::class,
            'admin.api' => \App\Http\Middleware\AdminApiKey::class,
        ]);
    })
    ->withSchedule(function ($schedule): void {
        $schedule->call(new \App\Console\Commands\ProcessReferralCommissions)->dailyAt('01:00');
        $schedule->call(new \App\Console\Commands\CheckMissingCommissions)->everyFiveMinutes();
        $schedule->call(new \App\Console\Commands\ProcessLowBalanceAlerts)->dailyAt('08:00');
        $schedule->call(new \App\Console\Commands\CleanOldLoginLogs)->daily();
        $schedule->call(new \App\Console\Commands\CleanOldPasswordResetTokens)->hourly();
        $schedule->call(new \App\Console\Commands\PollOrderStatus)->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

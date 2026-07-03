<?php

use App\Console\Commands\CheckMissingCommissions;
use App\Console\Commands\CleanOldLoginLogs;
use App\Console\Commands\CleanOldPasswordResetTokens;
use App\Console\Commands\PollOrderStatus;
use App\Console\Commands\ProcessLowBalanceAlerts;
use App\Console\Commands\ProcessReferralCommissions;
use App\Http\Middleware\AdminApiKey;
use App\Http\Middleware\EnsureAdminAuthenticated;
use App\Http\Middleware\EnsureUserAuthenticated;
use App\Http\Middleware\EnsureUserRole;
use App\Http\Middleware\SetLocale;
use App\Http\Middleware\ValidateApiKey;
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
            'admin.auth' => EnsureAdminAuthenticated::class,
            'user.auth' => EnsureUserAuthenticated::class,
            'role' => EnsureUserRole::class,
            'set.locale' => SetLocale::class,
            'api.auth' => ValidateApiKey::class,
            'admin.api' => AdminApiKey::class,
        ]);
    })
    ->withSchedule(function ($schedule): void {
        $schedule->call(new ProcessReferralCommissions)->dailyAt('01:00');
        $schedule->call(new CheckMissingCommissions)->everyFiveMinutes();
        $schedule->call(new ProcessLowBalanceAlerts)->dailyAt('08:00');
        $schedule->call(new CleanOldLoginLogs)->daily();
        $schedule->call(new CleanOldPasswordResetTokens)->hourly();
        $schedule->call(new PollOrderStatus)->everyMinute();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();

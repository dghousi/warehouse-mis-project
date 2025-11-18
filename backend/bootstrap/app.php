<?php

declare(strict_types=1);

use App\Http\Middleware\SetLocale;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->statefulApi();
        $middleware->alias([
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'set_locale' => SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->renderable(
            app(App\Modules\Common\Application\Handlers\ApiExceptionHandler::class)
        );
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command('model:prune', [
            '--model' => [
                \App\Models\ActivityLog::class,
                \App\Models\User::class,
            ],
        ])->daily()->appendOutputTo(storage_path('logs/prune.log'));

        $schedule->command('redis:monitor')
            ->hourly()
            ->appendOutputTo(storage_path('logs/redis-monitor.log'));

        $schedule->command('cache:warm --pages=5')
            ->dailyAt('02:00');
    })->create();

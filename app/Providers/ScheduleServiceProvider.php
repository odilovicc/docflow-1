<?php

namespace App\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use App\Console\Commands\CleanupActivityLogs;
use App\Console\Commands\SendTaskNotifications;
use App\Console\Commands\CacheManagement;

class ScheduleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Регистрируем команды
        if ($this->app->runningInConsole()) {
            $this->commands([
                CleanupActivityLogs::class,
                SendTaskNotifications::class,
                CacheManagement::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Настраиваем планировщик задач
        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            
            // Отправка уведомлений о задачах каждые 30 минут
            $schedule->command('notifications:send-tasks')
                ->everyThirtyMinutes()
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/scheduler.log'));
            
            // Очистка старых логов активности каждую неделю (воскресенье в 02:00)
            $schedule->command('logs:cleanup --days=90')
                ->weekly()
                ->sundays()
                ->at('02:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/cleanup.log'));
            
            // Очистка очень старых логов каждый месяц (1-го числа в 03:00)
            $schedule->command('logs:cleanup --days=365 --type=default')
                ->monthlyOn(1, '03:00')
                ->withoutOverlapping()
                ->appendOutputTo(storage_path('logs/cleanup.log'));
        });
    }
}
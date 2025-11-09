<?php

namespace App\Providers;

use App\Models\Document;
use App\Models\Task;
use App\Models\User;
use App\Models\Department;
use App\Observers\DocumentObserver;
use App\Observers\TaskObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Регистрируем observers для автоматического логирования
        Document::observe(DocumentObserver::class);
        Task::observe(TaskObserver::class);
        
        // Дополнительные observers для других моделей можно добавить здесь
        // User::observe(UserObserver::class);
        // Department::observe(DepartmentObserver::class);
    }
}

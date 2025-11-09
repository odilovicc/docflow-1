<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Database\Events\QueryExecuted;
use App\Listeners\InvalidateCacheListener;

class CacheEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // Eloquent Model Events
        'eloquent.saved: App\Models\Document' => [
            InvalidateCacheListener::class . '@handleModelSaved',
        ],
        'eloquent.deleted: App\Models\Document' => [
            InvalidateCacheListener::class . '@handleModelDeleted',
        ],
        'eloquent.saved: App\Models\Task' => [
            InvalidateCacheListener::class . '@handleModelSaved',
        ],
        'eloquent.deleted: App\Models\Task' => [
            InvalidateCacheListener::class . '@handleModelDeleted',
        ],
        'eloquent.saved: App\Models\User' => [
            InvalidateCacheListener::class . '@handleModelSaved',
        ],
        'eloquent.deleted: App\Models\User' => [
            InvalidateCacheListener::class . '@handleModelDeleted',
        ],
        'eloquent.saved: App\Models\Department' => [
            InvalidateCacheListener::class . '@handleModelSaved',
        ],
        'eloquent.deleted: App\Models\Department' => [
            InvalidateCacheListener::class . '@handleModelDeleted',
        ],
        'eloquent.saved: App\Models\Category' => [
            InvalidateCacheListener::class . '@handleModelSaved',
        ],
        'eloquent.deleted: App\Models\Category' => [
            InvalidateCacheListener::class . '@handleModelDeleted',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
        
        // Логирование медленных запросов (если включено)
        if (config('app.debug') && config('cache.log_slow_queries', false)) {
            \DB::listen(function (QueryExecuted $query) {
                if ($query->time > 1000) { // Более 1 секунды
                    \Log::warning('Slow query detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $query->time . 'ms'
                    ]);
                }
            });
        }
    }
}
<?php

namespace App\Listeners;

use App\Services\CacheService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class InvalidateCacheListener implements ShouldQueue
{
    use InteractsWithQueue;

    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Handle model saved event
     */
    public function handleModelSaved($event): void
    {
        $model = $event->model ?? $event;
        
        $this->invalidateByModel($model);
    }

    /**
     * Handle model deleted event
     */
    public function handleModelDeleted($event): void
    {
        $model = $event->model ?? $event;
        
        $this->invalidateByModel($model);
    }

    /**
     * Invalidate cache based on model type
     */
    private function invalidateByModel($model): void
    {
        $modelClass = get_class($model);
        
        match($modelClass) {
            'App\Models\Document' => $this->invalidateDocumentCache($model),
            'App\Models\Task' => $this->invalidateTaskCache($model),
            'App\Models\User' => $this->invalidateUserCache($model),
            'App\Models\Department' => $this->invalidateDepartmentCache(),
            'App\Models\Category' => $this->invalidateCategoryCache(),
            default => null,
        };
    }

    /**
     * Invalidate document-related cache
     */
    private function invalidateDocumentCache($document): void
    {
        $this->cacheService->invalidateDocumentsCache();
        $this->cacheService->invalidateAdminStats();
        
        // Инвалидируем кеш workflow если документ имеет workflow
        if ($document->workflow_id) {
            \Cache::forget("document.workflow.{$document->id}");
        }
    }

    /**
     * Invalidate task-related cache
     */
    private function invalidateTaskCache($task): void
    {
        if ($task->assigned_to) {
            $this->cacheService->invalidateTasksCache($task->assigned_to);
        }
        
        $this->cacheService->invalidateAdminStats();
    }

    /**
     * Invalidate user-related cache
     */
    private function invalidateUserCache($user): void
    {
        $this->cacheService->invalidateUsersCache();
        $this->cacheService->invalidateUserPermissions($user->id);
        $this->cacheService->invalidateTasksCache($user->id);
        $this->cacheService->invalidateActivitiesCache($user->id);
        $this->cacheService->invalidateAdminStats();
    }

    /**
     * Invalidate department-related cache
     */
    private function invalidateDepartmentCache(): void
    {
        \Cache::forget('departments.list');
        $this->cacheService->invalidateUsersCache();
        $this->cacheService->invalidateDocumentsCache();
        $this->cacheService->invalidateAdminStats();
    }

    /**
     * Invalidate category-related cache
     */
    private function invalidateCategoryCache(): void
    {
        \Cache::forget('categories.list');
        $this->cacheService->invalidateDocumentsCache();
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Document;
use App\Models\User;
use App\Models\Department;
use App\Models\Task;
use App\Models\Category;
use Carbon\Carbon;

class CacheService
{
    /**
     * Время жизни кеша по умолчанию (в секундах)
     */
    const DEFAULT_TTL = 3600; // 1 час
    const SHORT_TTL = 300;    // 5 минут
    const LONG_TTL = 86400;   // 24 часа

    /**
     * Ключи для кеширования
     */
    const KEYS = [
        'documents_list' => 'documents.list.%s',
        'documents_count' => 'documents.count.%s',
        'users_list' => 'users.list.%s',
        'departments_list' => 'departments.list',
        'categories_list' => 'categories.list',
        'tasks_count' => 'tasks.count.%s',
        'admin_stats' => 'admin.stats.%s',
        'user_permissions' => 'user.permissions.%s',
        'document_workflow' => 'document.workflow.%s',
        'recent_activities' => 'activities.recent.%s',
    ];

    /**
     * Получить кешированный список документов
     */
    public function getDocumentsList(array $filters = [], int $page = 1, int $perPage = 15): mixed
    {
        $cacheKey = sprintf(self::KEYS['documents_list'], md5(serialize($filters) . $page . $perPage));
        
        return Cache::remember($cacheKey, self::DEFAULT_TTL, function() use ($filters, $page, $perPage) {
            $query = Document::withOptimizedRelations();

            // Применяем фильтры с использованием оптимизированных скоупов
            if (!empty($filters['status']) && !empty($filters['department_id'])) {
                // Используем составной индекс department_id + status
                $query->byDepartmentAndStatus($filters['department_id'], $filters['status']);
            } elseif (!empty($filters['status'])) {
                $query->byStatus($filters['status']);
            } elseif (!empty($filters['department_id'])) {
                $query->byDepartmentAndStatus($filters['department_id']);
            }
            
            if (!empty($filters['category_id'])) {
                $query->byCategoryWithDate($filters['category_id']);
            } else {
                $query->orderBy('created_at', 'desc');
            }
            
            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            return $query->paginate($perPage, ['*'], 'page', $page);
        });
    }

    /**
     * Получить количество документов по статусам
     */
    public function getDocumentsCount(?string $status = null): int
    {
        $cacheKey = sprintf(self::KEYS['documents_count'], $status ?? 'all');
        
        return Cache::remember($cacheKey, self::SHORT_TTL, function() use ($status) {
            $query = Document::query();
            
            if ($status) {
                $query->where('status', $status);
            }
            
            return $query->count();
        });
    }

    /**
     * Получить кешированный список пользователей
     */
    public function getUsersList(array $filters = []): Collection
    {
        $cacheKey = sprintf(self::KEYS['users_list'], md5(serialize($filters)));
        
        return Cache::remember($cacheKey, self::LONG_TTL, function() use ($filters) {
            $query = User::with(['department', 'roles']);

            if (!empty($filters['department_id'])) {
                $query->where('department_id', $filters['department_id']);
            }
            
            if (!empty($filters['role'])) {
                $query->role($filters['role']);
            }
            
            if (!empty($filters['active'])) {
                $query->where('is_active', true);
            }

            return $query->orderBy('name')->get();
        });
    }

    /**
     * Получить кешированный список департаментов
     */
    public function getDepartmentsList(): Collection
    {
        return Cache::remember(self::KEYS['departments_list'], self::LONG_TTL, function() {
            return Department::with('head')->orderBy('name')->get();
        });
    }

    /**
     * Получить кешированный список категорий
     */
    public function getCategoriesList(): Collection
    {
        return Cache::remember(self::KEYS['categories_list'], self::LONG_TTL, function() {
            return Category::orderBy('name')->get();
        });
    }

    /**
     * Получить количество задач пользователя
     */
    public function getUserTasksCount(int $userId, ?string $status = null): int
    {
        $cacheKey = sprintf(self::KEYS['tasks_count'], $userId . '.' . ($status ?? 'all'));
        
        return Cache::remember($cacheKey, self::SHORT_TTL, function() use ($userId, $status) {
            $query = Task::where('assigned_to', $userId);
            
            if ($status) {
                $query->where('status', $status);
            }
            
            return $query->count();
        });
    }

    /**
     * Получить кешированную статистику для админ-панели
     */
    public function getAdminStats(string $period = 'month'): array
    {
        $cacheKey = sprintf(self::KEYS['admin_stats'], $period);
        
        return Cache::remember($cacheKey, self::SHORT_TTL, function() use ($period) {
            $startDate = $this->getStartDate($period);
            
            return [
                'total_documents' => Document::count(),
                'total_users' => User::count(),
                'total_tasks' => Task::count(),
                'new_documents' => Document::where('created_at', '>=', $startDate)->count(),
                'completed_tasks' => Task::where('status', 'completed')
                                       ->where('finished_at', '>=', $startDate)->count(),
                'pending_documents' => Document::where('status', 'pending')->count(),
                'overdue_tasks' => Task::where('due_date', '<', now())
                                     ->whereNotIn('status', ['completed', 'cancelled'])->count(),
                'active_workflows' => Document::whereNotIn('status', ['completed', 'cancelled'])->count(),
            ];
        });
    }

    /**
     * Получить кешированные разрешения пользователя
     */
    public function getUserPermissions(int $userId): array
    {
        $cacheKey = sprintf(self::KEYS['user_permissions'], $userId);
        
        return Cache::remember($cacheKey, self::LONG_TTL, function() use ($userId) {
            $user = User::with('roles.permissions')->find($userId);
            
            if (!$user) {
                return [];
            }
            
            return $user->getAllPermissions()->pluck('name')->toArray();
        });
    }

    /**
     * Получить кешированную информацию о workflow документа
     */
    public function getDocumentWorkflow(int $documentId): mixed
    {
        $cacheKey = sprintf(self::KEYS['document_workflow'], $documentId);
        
        return Cache::remember($cacheKey, self::DEFAULT_TTL, function() use ($documentId) {
            return Document::with(['workflow.steps', 'tasks.assignee'])
                          ->find($documentId);
        });
    }

    /**
     * Получить последние активности пользователя
     */
    public function getRecentActivities(int $userId, int $limit = 10): mixed
    {
        $cacheKey = sprintf(self::KEYS['recent_activities'], $userId . '.' . $limit);
        
        return Cache::remember($cacheKey, self::SHORT_TTL, function() use ($userId, $limit) {
            return \Spatie\Activitylog\Models\Activity::where('causer_id', $userId)
                ->with('subject')
                ->orderBy('created_at', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Инвалидация кеша документов
     */
    public function invalidateDocumentsCache(): void
    {
        $this->forgetByPattern('documents.*');
    }

    /**
     * Инвалидация кеша пользователей
     */
    public function invalidateUsersCache(): void
    {
        $this->forgetByPattern('users.*');
    }

    /**
     * Инвалидация кеша задач
     */
    public function invalidateTasksCache(?int $userId = null): void
    {
        if ($userId) {
            Cache::forget(sprintf(self::KEYS['tasks_count'], $userId . '.all'));
            Cache::forget(sprintf(self::KEYS['tasks_count'], $userId . '.pending'));
            Cache::forget(sprintf(self::KEYS['tasks_count'], $userId . '.completed'));
        } else {
            $this->forgetByPattern('tasks.*');
        }
    }

    /**
     * Инвалидация кеша статистики админ-панели
     */
    public function invalidateAdminStats(): void
    {
        $this->forgetByPattern('admin.stats.*');
    }

    /**
     * Инвалидация кеша разрешений пользователя
     */
    public function invalidateUserPermissions(int $userId): void
    {
        Cache::forget(sprintf(self::KEYS['user_permissions'], $userId));
    }

    /**
     * Инвалидация кеша активностей
     */
    public function invalidateActivitiesCache(?int $userId = null): void
    {
        if ($userId) {
            $this->forgetByPattern("activities.recent.{$userId}.*");
        } else {
            $this->forgetByPattern('activities.recent.*');
        }
    }

    /**
     * Полная очистка всего кеша
     */
    public function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Получить статистику кеша
     */
    public function getCacheStats(): array
    {
        // Для database драйвера получаем количество записей
        try {
            $cacheCount = \DB::table('cache')->count();
            $cacheSize = \DB::table('cache')
                ->selectRaw('SUM(LENGTH(value)) as total_size')
                ->value('total_size') ?? 0;
            
            return [
                'driver' => config('cache.default'),
                'entries_count' => $cacheCount,
                'total_size' => $cacheSize,
                'size_formatted' => $this->formatBytes($cacheSize),
            ];
        } catch (\Exception $e) {
            return [
                'driver' => config('cache.default'),
                'entries_count' => 0,
                'total_size' => 0,
                'size_formatted' => '0 B',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Удалить кеш по шаблону (для database драйвера)
     */
    private function forgetByPattern(string $pattern): void
    {
        try {
            // Для database драйвера используем SQL LIKE
            $pattern = str_replace('*', '%', $pattern);
            \DB::table('cache')
              ->where('key', 'LIKE', $pattern)
              ->delete();
        } catch (\Exception $e) {
            // Игнорируем ошибки, это не критично
        }
    }

    /**
     * Получить начальную дату для периода
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Форматировать байты в человеко-читаемый формат
     */
    private function formatBytes(int $size): string
    {
        if ($size == 0) return '0 B';
        
        $base = log($size, 1024);
        $suffixes = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        return round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];
    }
}
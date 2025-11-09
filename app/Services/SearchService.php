<?php

namespace App\Services;

use App\Models\Document;
use App\Models\Task;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Scout\Builder;

class SearchService
{
    /**
     * Выполнить глобальный поиск по документам и задачам
     */
    public function globalSearch(string $query, array $filters = [], int $limit = 50): array
    {
        $results = [];
        
        // Поиск по документам
        $documents = $this->searchDocuments($query, $filters, $limit);
        $results['documents'] = [
            'data' => $documents,
            'count' => $documents->count(),
            'type' => 'documents'
        ];

        // Поиск по задачам
        $tasks = $this->searchTasks($query, $filters, $limit);
        $results['tasks'] = [
            'data' => $tasks,
            'count' => $tasks->count(), 
            'type' => 'tasks'
        ];

        $results['total'] = $results['documents']['count'] + $results['tasks']['count'];

        return $results;
    }

    /**
     * Поиск по документам
     */
    public function searchDocuments(string $query, array $filters = [], int $limit = 20): Collection
    {
        $searchBuilder = Document::search($query);

        // Применяем фильтры
        if (!empty($filters['status'])) {
            $searchBuilder->where('status', $filters['status']);
        }

        if (!empty($filters['department_id'])) {
            $searchBuilder->where('department_name', $this->getDepartmentName($filters['department_id']));
        }

        if (!empty($filters['category_id'])) {
            $searchBuilder->where('category_name', $this->getCategoryName($filters['category_id']));
        }

        if (!empty($filters['author_id'])) {
            $searchBuilder->where('author_name', $this->getUserName($filters['author_id']));
        }

        // Применяем лимит и загружаем связанные модели
        return $searchBuilder
            ->take($limit)
            ->get()
            ->load(['author:id,name', 'category:id,name', 'department:id,name']);
    }

    /**
     * Поиск по задачам
     */
    public function searchTasks(string $query, array $filters = [], int $limit = 20): Collection
    {
        $searchBuilder = Task::search($query);

        // Применяем фильтры
        if (!empty($filters['status'])) {
            $searchBuilder->where('status', $filters['status']);
        }

        if (!empty($filters['assignee_id'])) {
            $searchBuilder->where('assignee_name', $this->getUserName($filters['assignee_id']));
        }

        if (!empty($filters['priority'])) {
            $searchBuilder->where('priority', $filters['priority']);
        }

        if (!empty($filters['type'])) {
            $searchBuilder->where('type', $filters['type']);
        }

        // Применяем лимит и загружаем связанные модели
        return $searchBuilder
            ->take($limit)
            ->get()
            ->load(['assignee:id,name', 'document:id,title', 'document.department:id,name']);
    }

    /**
     * Автодополнение для поиска
     */
    public function autocomplete(string $query, string $type = 'all', int $limit = 10): array
    {
        $results = [];

        if ($type === 'all' || $type === 'documents') {
            $documents = Document::search($query)
                ->take($limit)
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id,
                        'text' => $doc->title,
                        'type' => 'document',
                        'url' => route('documents.show', $doc->id)
                    ];
                });
            
            $results = array_merge($results, $documents->toArray());
        }

        if ($type === 'all' || $type === 'tasks') {
            $tasks = Task::search($query)
                ->take($limit)
                ->get()
                ->map(function($task) {
                    return [
                        'id' => $task->id,
                        'text' => $task->title,
                        'type' => 'task',
                        'url' => route('tasks.show', $task->id)
                    ];
                });
            
            $results = array_merge($results, $tasks->toArray());
        }

        return array_slice($results, 0, $limit);
    }

    /**
     * Расширенный поиск с множественными критериями
     */
    public function advancedSearch(array $criteria): array
    {
        $results = [];

        // Поиск документов
        if (!empty($criteria['documents'])) {
            $documentsQuery = Document::query();
            
            if (!empty($criteria['documents']['text'])) {
                $documentsQuery->where(function($q) use ($criteria) {
                    $q->where('title', 'LIKE', "%{$criteria['documents']['text']}%")
                      ->orWhere('description', 'LIKE', "%{$criteria['documents']['text']}%");
                });
            }

            if (!empty($criteria['documents']['status'])) {
                $documentsQuery->where('status', $criteria['documents']['status']);
            }

            if (!empty($criteria['documents']['date_from'])) {
                $documentsQuery->where('created_at', '>=', $criteria['documents']['date_from']);
            }

            if (!empty($criteria['documents']['date_to'])) {
                $documentsQuery->where('created_at', '<=', $criteria['documents']['date_to']);
            }

            $results['documents'] = $documentsQuery
                ->withOptimizedRelations()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        // Поиск задач
        if (!empty($criteria['tasks'])) {
            $tasksQuery = Task::query();
            
            if (!empty($criteria['tasks']['text'])) {
                $tasksQuery->where(function($q) use ($criteria) {
                    $q->where('title', 'LIKE', "%{$criteria['tasks']['text']}%")
                      ->orWhere('description', 'LIKE', "%{$criteria['tasks']['text']}%");
                });
            }

            if (!empty($criteria['tasks']['status'])) {
                $tasksQuery->where('status', $criteria['tasks']['status']);
            }

            if (!empty($criteria['tasks']['assignee_id'])) {
                $tasksQuery->where('assigned_to', $criteria['tasks']['assignee_id']);
            }

            if (!empty($criteria['tasks']['overdue'])) {
                $tasksQuery->overdue();
            }

            $results['tasks'] = $tasksQuery
                ->withOptimizedRelations()
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();
        }

        return $results;
    }

    /**
     * Индексировать все модели
     */
    public function indexAllModels(): array
    {
        $stats = [];

        // Индексируем документы
        \Artisan::call('scout:import', ['model' => Document::class]);
        $stats['documents'] = Document::count();

        // Индексируем задачи  
        \Artisan::call('scout:import', ['model' => Task::class]);
        $stats['tasks'] = Task::count();

        return $stats;
    }

    /**
     * Очистить все индексы
     */
    public function flushAllIndexes(): void
    {
        \Artisan::call('scout:flush', ['model' => Document::class]);
        \Artisan::call('scout:flush', ['model' => Task::class]);
    }

    /**
     * Получить статистику поиска
     */
    public function getSearchStats(): array
    {
        return [
            'indexed_documents' => Document::count(),
            'indexed_tasks' => Task::count(),
            'search_driver' => config('scout.driver'),
            'queue_enabled' => config('scout.queue'),
        ];
    }

    /**
     * Вспомогательные методы для получения имен
     */
    private function getDepartmentName(int $id): ?string
    {
        return \Cache::remember("department_name_{$id}", 3600, function() use ($id) {
            return \App\Models\Department::find($id)?->name;
        });
    }

    private function getCategoryName(int $id): ?string
    {
        return \Cache::remember("category_name_{$id}", 3600, function() use ($id) {
            return \App\Models\Category::find($id)?->name;
        });
    }

    private function getUserName(int $id): ?string
    {
        return \Cache::remember("user_name_{$id}", 3600, function() use ($id) {
            return \App\Models\User::find($id)?->name;
        });
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\SearchService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;

class SearchController extends Controller
{
    protected SearchService $searchService;

    public function __construct(SearchService $searchService)
    {
        $this->searchService = $searchService;
    }

    /**
     * Главная страница поиска
     */
    public function index(Request $request): View
    {
        $query = $request->get('q');
        $results = null;

        if ($query) {
            $filters = [
                'status' => $request->get('status'),
                'department_id' => $request->get('department_id'),
                'category_id' => $request->get('category_id'),
            ];

            $results = $this->searchService->globalSearch($query, array_filter($filters));
        }

        $departments = \App\Models\Department::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();

        return view('search.index', compact('query', 'results', 'departments', 'categories'));
    }

    /**
     * API для глобального поиска
     */
    public function globalSearch(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'sometimes|integer|min:1|max:100',
        ]);

        $query = $request->get('q');
        $filters = [
            'status' => $request->get('status'),
            'department_id' => $request->get('department_id'),
            'category_id' => $request->get('category_id'),
            'assignee_id' => $request->get('assignee_id'),
        ];

        $limit = $request->get('limit', 20);

        $results = $this->searchService->globalSearch($query, array_filter($filters), $limit);

        return response()->json([
            'success' => true,
            'query' => $query,
            'results' => $results,
            'filters_applied' => array_filter($filters),
        ]);
    }

    /**
     * API для автодополнения
     */
    public function autocomplete(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:1|max:255',
            'type' => 'sometimes|string|in:all,documents,tasks',
            'limit' => 'sometimes|integer|min:1|max:20',
        ]);

        $query = $request->get('q');
        $type = $request->get('type', 'all');
        $limit = $request->get('limit', 10);

        $results = $this->searchService->autocomplete($query, $type, $limit);

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    /**
     * Расширенный поиск
     */
    public function advanced(Request $request)
    {
        if ($request->isMethod('GET')) {
            $departments = \App\Models\Department::orderBy('name')->get();
            $categories = \App\Models\Category::orderBy('name')->get();
            $users = \App\Models\User::orderBy('name')->get();

            return view('search.advanced', compact('departments', 'categories', 'users'));
        }

        // POST запрос для выполнения расширенного поиска
        $criteria = $request->only(['documents', 'tasks']);
        $results = $this->searchService->advancedSearch($criteria);

        $departments = \App\Models\Department::orderBy('name')->get();
        $categories = \App\Models\Category::orderBy('name')->get();
        $users = \App\Models\User::orderBy('name')->get();

        return view('search.advanced', compact('results', 'criteria', 'departments', 'categories', 'users'));
    }

    /**
     * Поиск только по документам
     */
    public function documents(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = $request->get('q');
        $filters = [
            'status' => $request->get('status'),
            'department_id' => $request->get('department_id'),
            'category_id' => $request->get('category_id'),
            'author_id' => $request->get('author_id'),
        ];

        $limit = $request->get('limit', 20);

        $documents = $this->searchService->searchDocuments($query, array_filter($filters), $limit);

        return response()->json([
            'success' => true,
            'query' => $query,
            'documents' => $documents,
            'count' => $documents->count(),
        ]);
    }

    /**
     * Поиск только по задачам
     */
    public function tasks(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2|max:255',
            'limit' => 'sometimes|integer|min:1|max:50',
        ]);

        $query = $request->get('q');
        $filters = [
            'status' => $request->get('status'),
            'assignee_id' => $request->get('assignee_id'),
            'priority' => $request->get('priority'),
            'type' => $request->get('type'),
        ];

        $limit = $request->get('limit', 20);

        $tasks = $this->searchService->searchTasks($query, array_filter($filters), $limit);

        return response()->json([
            'success' => true,
            'query' => $query,
            'tasks' => $tasks,
            'count' => $tasks->count(),
        ]);
    }

    /**
     * Управление индексами (только для админов)
     */
    public function manageIndexes(Request $request)
    {
        // Middleware должен быть настроен в маршруте

        if ($request->isMethod('POST')) {
            $action = $request->get('action');

            switch ($action) {
                case 'reindex':
                    $stats = $this->searchService->indexAllModels();
                    return redirect()->back()->with('success', 'Индексы обновлены. Проиндексировано: ' . json_encode($stats));

                case 'flush':
                    $this->searchService->flushAllIndexes();
                    return redirect()->back()->with('success', 'Все индексы очищены');

                default:
                    return redirect()->back()->with('error', 'Неизвестное действие');
            }
        }

        $stats = $this->searchService->getSearchStats();

        return view('search.manage', compact('stats'));
    }
}

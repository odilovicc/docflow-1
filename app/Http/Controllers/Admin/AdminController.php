<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Document;
use App\Models\Department;
use App\Models\Task;
use App\Models\Category;
use App\Models\Workflow;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class AdminController extends Controller
{
    protected CacheService $cacheService;

    public function __construct(CacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Admin dashboard
     */
    public function dashboard(): View
    {
        $stats = $this->getSystemStats();
        $recentActivity = $this->getRecentActivity();
        $taskStats = $this->getTaskStatistics();
        
        return view('admin.dashboard', compact('stats', 'recentActivity', 'taskStats'));
    }

    /**
     * System statistics
     */
    private function getSystemStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('created_at', '>=', now()->subDays(30))->count(),
            'total_documents' => Document::count(),
            'documents_this_month' => Document::where('created_at', '>=', now()->startOfMonth())->count(),
            'total_departments' => Department::count(),
            'total_tasks' => Task::count(),
            'overdue_tasks' => Task::where('due_date', '<', now())
                                 ->whereNotIn('status', ['completed', 'cancelled'])
                                 ->count(),
            'pending_tasks' => Task::where('status', 'pending')->count(),
        ];
    }

    /**
     * Recent system activity
     */
    private function getRecentActivity(): \Illuminate\Database\Eloquent\Collection
    {
        return \Spatie\Activitylog\Models\Activity::with(['causer', 'subject'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();
    }

    /**
     * Task statistics
     */
    private function getTaskStatistics(): array
    {
        $tasksPerDepartment = Task::join('documents', 'tasks.document_id', '=', 'documents.id')
            ->join('departments', 'documents.department_id', '=', 'departments.id')
            ->select('departments.name', DB::raw('count(*) as total'))
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('total', 'desc')
            ->get();

        $tasksPerStatus = Task::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->get();

        $overdueByDepartment = Task::join('documents', 'tasks.document_id', '=', 'documents.id')
            ->join('departments', 'documents.department_id', '=', 'departments.id')
            ->where('tasks.due_date', '<', now())
            ->whereNotIn('tasks.status', ['completed', 'cancelled'])
            ->select('departments.name', DB::raw('count(*) as total'))
            ->groupBy('departments.id', 'departments.name')
            ->orderBy('total', 'desc')
            ->get();

        return [
            'per_department' => $tasksPerDepartment,
            'per_status' => $tasksPerStatus,
            'overdue_by_department' => $overdueByDepartment,
        ];
    }

    /**
     * Users management
     */
    public function users(Request $request): View
    {
        $query = User::with(['department', 'roles']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('role')) {
            $query->role($request->role);
        }

        $users = $query->orderBy('name')->paginate(20);
        $departments = Department::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.users', compact('users', 'departments', 'roles'));
    }

    /**
     * System settings
     */
    public function settings(): View
    {
        $categories = Category::orderBy('name')->get();
        $workflows = Workflow::with('steps')->orderBy('name')->get();
        $roles = Role::with('permissions')->orderBy('name')->get();

        return view('admin.settings', compact('categories', 'workflows', 'roles'));
    }

    /**
     * Reports
     */
    public function reports(Request $request): View
    {
        $period = $request->get('period', 'month');
        $startDate = $this->getStartDate($period);

        $documentStats = $this->getDocumentReports($startDate);
        $taskReports = $this->getTaskReports($startDate);
        $departmentPerformance = $this->getDepartmentPerformance($startDate);

        return view('admin.reports', compact(
            'documentStats', 
            'taskReports', 
            'departmentPerformance',
            'period'
        ));
    }

    /**
     * Get start date for reports
     */
    private function getStartDate(string $period): Carbon
    {
        return match($period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    /**
     * Document reports
     */
    private function getDocumentReports(Carbon $startDate): array
    {
        return [
            'created' => Document::where('created_at', '>=', $startDate)->count(),
            'completed' => Document::where('status', 'completed')
                                  ->where('updated_at', '>=', $startDate)->count(),
            'by_category' => Document::join('categories', 'documents.category_id', '=', 'categories.id')
                                   ->where('documents.created_at', '>=', $startDate)
                                   ->select('categories.name', DB::raw('count(*) as total'))
                                   ->groupBy('categories.id', 'categories.name')
                                   ->orderBy('total', 'desc')
                                   ->get(),
            'by_status' => Document::where('created_at', '>=', $startDate)
                                 ->select('status', DB::raw('count(*) as total'))
                                 ->groupBy('status')
                                 ->get(),
        ];
    }

    /**
     * Task reports
     */
    private function getTaskReports(Carbon $startDate): array
    {
        return [
            'created' => Task::where('created_at', '>=', $startDate)->count(),
            'completed' => Task::where('status', 'completed')
                              ->where('finished_at', '>=', $startDate)->count(),
            'overdue' => Task::where('due_date', '<', now())
                           ->whereNotIn('status', ['completed', 'cancelled'])
                           ->where('created_at', '>=', $startDate)->count(),
            'avg_completion_time' => Task::where('status', 'completed')
                                       ->where('finished_at', '>=', $startDate)
                                       ->whereNotNull('started_at')
                                       ->whereNotNull('finished_at')
                                       ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, started_at, finished_at)) as avg_hours')
                                       ->value('avg_hours'),
        ];
    }

    /**
     * Department performance
     */
    private function getDepartmentPerformance(Carbon $startDate): \Illuminate\Database\Eloquent\Collection
    {
        return Department::select('departments.*')
            ->selectSub(function($query) use ($startDate) {
                $query->from('tasks')
                      ->join('documents', 'tasks.document_id', '=', 'documents.id')
                      ->whereColumn('documents.department_id', 'departments.id')
                      ->where('tasks.created_at', '>=', $startDate)
                      ->selectRaw('count(*)');
            }, 'total_tasks')
            ->selectSub(function($query) use ($startDate) {
                $query->from('tasks')
                      ->join('documents', 'tasks.document_id', '=', 'documents.id')
                      ->whereColumn('documents.department_id', 'departments.id')
                      ->where('tasks.status', 'completed')
                      ->where('tasks.finished_at', '>=', $startDate)
                      ->selectRaw('count(*)');
            }, 'completed_tasks')
            ->selectSub(function($query) {
                $query->from('tasks')
                      ->join('documents', 'tasks.document_id', '=', 'documents.id')
                      ->whereColumn('documents.department_id', 'departments.id')
                      ->where('tasks.due_date', '<', now())
                      ->whereNotIn('tasks.status', ['completed', 'cancelled'])
                      ->selectRaw('count(*)');
            }, 'overdue_tasks')
            ->get();
    }

    /**
     * System logs
     */
    public function logs(Request $request): View
    {
        $query = \Spatie\Activitylog\Models\Activity::with(['causer', 'subject']);

        // Фильтры
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('log_name', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('user')) {
            $query->where('causer_id', $request->user);
        }

        if ($request->filled('log_name')) {
            $query->where('log_name', $request->log_name);
        }

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(25);

        // Данные для фильтров и статистики
        $users = User::orderBy('name')->get(['id', 'name']);
        $logTypes = \Spatie\Activitylog\Models\Activity::distinct()->pluck('log_name')->filter();
        $events = \Spatie\Activitylog\Models\Activity::distinct()->pluck('event')->filter();
        
        // Статистика
        $totalLogs = \Spatie\Activitylog\Models\Activity::count();
        $todayLogs = \Spatie\Activitylog\Models\Activity::whereDate('created_at', today())->count();
        $activeUsers = \Spatie\Activitylog\Models\Activity::distinct('causer_id')
                        ->whereDate('created_at', '>=', now()->subDays(7))
                        ->whereNotNull('causer_id')
                        ->count();

        // Размер базы данных (приблизительный для SQLite)
        $dbSize = 0;
        try {
            $dbPath = database_path('database.sqlite');
            if (file_exists($dbPath)) {
                $dbSize = filesize($dbPath) / (1024 * 1024); // В МБ
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки получения размера файла
        }

        return view('admin.logs', compact(
            'logs', 'users', 'logTypes', 'events', 
            'totalLogs', 'todayLogs', 'activeUsers', 'dbSize'
        ));
    }

    /**
     * Clean up old activity logs
     */
    public function logsCleanup(Request $request)
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:9999',
            'type' => 'nullable|string',
        ]);

        try {
            $days = (int) $request->days;
            $type = $request->type;
            
            // Вызываем команду очистки
            \Illuminate\Support\Facades\Artisan::call('logs:cleanup', [
                '--days' => $days,
                '--type' => $type,
            ]);

            $output = \Illuminate\Support\Facades\Artisan::output();
            
            // Извлекаем количество удаленных записей из вывода команды
            preg_match('/Successfully deleted (\d+) activity logs/', $output, $matches);
            $deletedCount = isset($matches[1]) ? (int) $matches[1] : 0;

            return redirect()->route('admin.logs')
                ->with('success', "Успешно удалено {$deletedCount} записей логов старше {$days} дней.");
                
        } catch (\Exception $e) {
            return redirect()->route('admin.logs')
                ->with('error', 'Ошибка при очистке логов: ' . $e->getMessage());
        }
    }

    /**
     * Get badge color for activity event
     */
    private function getEventBadgeColor(string $event): string
    {
        return match($event) {
            'created' => 'success',
            'updated' => 'warning',
            'deleted' => 'danger',
            'restored' => 'info',
            'login' => 'primary',
            'logout' => 'secondary',
            default => 'light',
        };
    }
}
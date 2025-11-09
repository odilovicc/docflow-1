<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Spatie\Activitylog\Facades\LogBatch;

class TaskController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of tasks.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $query = Task::with(['document', 'workflowStep', 'assignedToUser', 'assignedByUser']);

        // Фильтруем по назначенному пользователю (по умолчанию только свои задачи)
        if (!$request->has('all_tasks') || !$user->hasRole(['Admin', 'DepartmentHead'])) {
            $query->where('assigned_to', $user->id);
        }

        // Фильтрация по статусу
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Фильтрация по просроченным
        if ($request->boolean('overdue')) {
            $query->where('due_date', '<', now())
                  ->whereNotIn('status', ['completed', 'cancelled']);
        }

        $tasks = $query->orderBy('due_date', 'asc')
                      ->orderBy('created_at', 'desc')
                      ->paginate(15);

        return view('tasks.index', compact('tasks'));
    }

    /**
     * Display the specified task.
     */
    public function show(Task $task): View
    {
        $this->authorize('view', $task);
        
        $task->load(['document', 'workflowStep', 'assignedToUser', 'assignedByUser']);
        
        return view('tasks.show', compact('task'));
    }

    /**
     * Start working on task
     */
    public function start(Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        if (!$task->isPending()) {
            return back()->with('error', 'Задача не может быть запущена в текущем состоянии.');
        }

        LogBatch::startBatch();

        $task->start();

        activity()
            ->performedOn($task)
            ->causedBy(Auth::user())
            ->log('Задача взята в работу');

        LogBatch::endBatch();

        return redirect()->route('tasks.show', $task)
                        ->with('success', 'Задача взята в работу.');
    }

    /**
     * Complete task
     */
    public function complete(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task);

        $request->validate([
            'comment' => 'nullable|string|max:1000',
        ]);

        if ($task->isCompleted()) {
            return back()->with('error', 'Задача уже выполнена.');
        }

        LogBatch::startBatch();

        $task->complete($request->comment);

        activity()
            ->performedOn($task)
            ->causedBy(Auth::user())
            ->withProperties(['comment' => $request->comment])
            ->log('Задача выполнена');

        LogBatch::endBatch();

        return redirect()->route('tasks.index')
                        ->with('success', 'Задача выполнена.');
    }

    /**
     * Cancel task
     */
    public function cancel(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('delete', $task);

        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        LogBatch::startBatch();

        $task->update([
            'status' => 'cancelled',
            'comment' => $request->reason,
        ]);

        activity()
            ->performedOn($task)
            ->causedBy(Auth::user())
            ->withProperties(['reason' => $request->reason])
            ->log('Задача отменена');

        LogBatch::endBatch();

        return redirect()->route('tasks.index')
                        ->with('success', 'Задача отменена.');
    }

    /**
     * Show my tasks dashboard
     */
    public function myTasks(): View
    {
        $user = Auth::user();
        
        $pendingTasks = $user->assignedTasks()
                            ->where('status', 'pending')
                            ->with(['document', 'workflowStep'])
                            ->orderBy('due_date', 'asc')
                            ->take(10)
                            ->get();

        $inProgressTasks = $user->assignedTasks()
                               ->where('status', 'in_progress')
                               ->with(['document', 'workflowStep'])
                               ->orderBy('started_at', 'asc')
                               ->take(10)
                               ->get();

        $overdueTasks = $user->assignedTasks()
                            ->where('due_date', '<', now())
                            ->whereNotIn('status', ['completed', 'cancelled'])
                            ->with(['document', 'workflowStep'])
                            ->count();

        $completedThisWeek = $user->assignedTasks()
                                 ->where('status', 'completed')
                                 ->where('finished_at', '>=', now()->startOfWeek())
                                 ->count();

        return view('tasks.my-tasks', compact(
            'pendingTasks',
            'inProgressTasks', 
            'overdueTasks',
            'completedThisWeek'
        ));
    }
}

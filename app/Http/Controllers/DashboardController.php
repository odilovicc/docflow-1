<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Task;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Статистика документов
        $myDocuments = Document::where('author_id', $user->id)->count();
        $departmentDocuments = $user->department_id 
            ? Document::where('department_id', $user->department_id)->count() 
            : 0;
        $pendingDocuments = $user->department_id 
            ? Document::where('department_id', $user->department_id)
                     ->where('status', 'pending')
                     ->count()
            : 0;
        
        // Статистика задач
        $myTasks = Task::where('assigned_to', $user->id)->count();
        $pendingTasks = Task::where('assigned_to', $user->id)
                           ->where('status', 'pending')
                           ->count();
        $overdueTasks = Task::where('assigned_to', $user->id)
                           ->where('status', '!=', 'completed')
                           ->where('due_date', '<', now())
                           ->count();
        
        // Недавние документы
        $recentDocuments = Document::with(['author', 'category', 'department'])
            ->where(function($query) use ($user) {
                $query->where('author_id', $user->id)
                      ->orWhere('department_id', $user->department_id);
            })
            ->latest()
            ->take(5)
            ->get();
        
        // Мои задачи
        $myRecentTasks = Task::with(['document', 'assignee'])
            ->where('assigned_to', $user->id)
            ->latest()
            ->take(5)
            ->get();
        
        // Статистика для админов
        $adminStats = null;
        if ($user->hasRole('admin')) {
            $adminStats = [
                'total_documents' => Document::count(),
                'total_tasks' => Task::count(),
                'total_departments' => Department::count(),
                'active_workflows' => Document::where('status', 'in_progress')->count(),
            ];
        }
        
        return view('dashboard', compact(
            'myDocuments',
            'departmentDocuments', 
            'pendingDocuments',
            'myTasks',
            'pendingTasks',
            'overdueTasks',
            'recentDocuments',
            'myRecentTasks',
            'adminStats'
        ));
    }
}
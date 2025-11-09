<?php

use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Department management routes (только для Admin)
    Route::resource('departments', DepartmentController::class)
        ->middleware('permission:department.manage');
    
    // Document management routes
    Route::resource('documents', DocumentController::class);
    
    // Workflow routes
    Route::post('/documents/{document}/workflow/transition', [App\Http\Controllers\WorkflowController::class, 'transition'])
        ->name('workflow.transition');
    Route::post('/documents/{document}/workflow/start', [App\Http\Controllers\WorkflowController::class, 'start'])
        ->name('workflow.start');
    Route::get('/documents/{document}/workflow/history', [App\Http\Controllers\WorkflowController::class, 'history'])
        ->name('workflow.history');
    
    // Tasks routes
    Route::resource('tasks', App\Http\Controllers\TaskController::class)->only(['index', 'show']);
    Route::get('/my-tasks', [App\Http\Controllers\TaskController::class, 'myTasks'])->name('tasks.my');
    Route::post('/tasks/{task}/start', [App\Http\Controllers\TaskController::class, 'start'])->name('tasks.start');
    Route::post('/tasks/{task}/complete', [App\Http\Controllers\TaskController::class, 'complete'])->name('tasks.complete');
    Route::post('/tasks/{task}/cancel', [App\Http\Controllers\TaskController::class, 'cancel'])->name('tasks.cancel');
    
    // Notifications routes
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/dropdown', [App\Http\Controllers\NotificationController::class, 'dropdown'])->name('notifications.dropdown');
    Route::get('/notifications/unread-count', [App\Http\Controllers\NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
    Route::post('/notifications/{id}/mark-read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::post('/notifications/mark-all-read', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
    Route::delete('/notifications/{id}', [App\Http\Controllers\NotificationController::class, 'delete'])->name('notifications.delete');
    
    // Search routes
    Route::prefix('search')->group(function () {
        Route::get('/', [App\Http\Controllers\SearchController::class, 'index'])->name('search.index');
        Route::get('/advanced', [App\Http\Controllers\SearchController::class, 'advanced'])->name('search.advanced');
        Route::post('/advanced', [App\Http\Controllers\SearchController::class, 'advanced']);
        
        // API маршруты для поиска
        Route::get('/api/global', [App\Http\Controllers\SearchController::class, 'globalSearch'])->name('search.api.global');
        Route::get('/api/autocomplete', [App\Http\Controllers\SearchController::class, 'autocomplete'])->name('search.api.autocomplete');
        Route::get('/api/documents', [App\Http\Controllers\SearchController::class, 'documents'])->name('search.api.documents');
        Route::get('/api/tasks', [App\Http\Controllers\SearchController::class, 'tasks'])->name('search.api.tasks');
        
        // Управление индексами (только админы)
        Route::get('/manage', [App\Http\Controllers\SearchController::class, 'manageIndexes'])->name('search.manage')->middleware('role:Admin');
        Route::post('/manage', [App\Http\Controllers\SearchController::class, 'manageIndexes'])->middleware('role:Admin');
    });

    // Admin routes (только для администраторов)
    Route::prefix('admin')->middleware('role:Admin')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Admin\AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [App\Http\Controllers\Admin\AdminController::class, 'users'])->name('admin.users');
        Route::get('/reports', [App\Http\Controllers\Admin\AdminController::class, 'reports'])->name('admin.reports');
        Route::get('/settings', [App\Http\Controllers\Admin\AdminController::class, 'settings'])->name('admin.settings');
        Route::get('/logs', [App\Http\Controllers\Admin\AdminController::class, 'logs'])->name('admin.logs');
        Route::post('/logs/cleanup', [App\Http\Controllers\Admin\AdminController::class, 'logsCleanup'])->name('admin.logs.cleanup');
        
        // Workflow management routes
        Route::resource('workflows', App\Http\Controllers\Admin\WorkflowController::class, ['as' => 'admin']);
        Route::post('/workflows/{workflow}/toggle-active', [App\Http\Controllers\Admin\WorkflowController::class, 'toggleActive'])
            ->name('admin.workflows.toggle-active');
    });
});

require __DIR__.'/auth.php';

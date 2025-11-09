<?php

namespace App\Observers;

use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskObserver
{
    /**
     * Handle the Task "created" event.
     */
    public function created(Task $task): void
    {
        activity('task')
            ->causedBy(Auth::user())
            ->performedOn($task)
            ->withProperties([
                'task_title' => $task->title,
                'document_title' => $task->document->title ?? null,
                'assigned_to' => $task->assignedToUser->name ?? null,
                'due_date' => $task->due_date?->format('Y-m-d H:i:s'),
                'event' => 'created'
            ])
            ->log('Создана задача: ' . $task->title);
    }

    /**
     * Handle the Task "updated" event.
     */
    public function updated(Task $task): void
    {
        $changes = $task->getChanges();
        $original = $task->getOriginal();
        
        // Исключаем системные поля
        $excludeFields = ['updated_at', 'created_at'];
        $changes = array_diff_key($changes, array_flip($excludeFields));
        
        if (empty($changes)) {
            return;
        }

        $properties = [
            'task_title' => $task->title,
            'document_title' => $task->document->title ?? null,
            'changes' => $changes,
            'old_values' => array_intersect_key($original, $changes),
            'event' => 'updated'
        ];

        // Специальная обработка изменения статуса
        if (isset($changes['status'])) {
            $oldStatus = $original['status'] ?? 'unknown';
            $newStatus = $changes['status'];
            
            $statusLabels = [
                'pending' => 'Ожидает',
                'in_progress' => 'В работе',
                'completed' => 'Завершена',
                'cancelled' => 'Отменена'
            ];
            
            $oldStatusLabel = $statusLabels[$oldStatus] ?? $oldStatus;
            $newStatusLabel = $statusLabels[$newStatus] ?? $newStatus;
            $description = "Изменён статус задачи «{$task->title}» с «{$oldStatusLabel}» на «{$newStatusLabel}»";
        } else {
            $description = 'Обновлена задача: ' . $task->title;
        }

        activity('task')
            ->causedBy(Auth::user())
            ->performedOn($task)
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Handle the Task "deleted" event.
     */
    public function deleted(Task $task): void
    {
        activity('task')
            ->causedBy(Auth::user())
            ->performedOn($task)
            ->withProperties([
                'task_title' => $task->title,
                'document_title' => $task->document->title ?? null,
                'assigned_to' => $task->assignedToUser->name ?? null,
                'event' => 'deleted'
            ])
            ->log('Удалена задача: ' . $task->title);
    }

    /**
     * Handle the Task "restored" event.
     */
    public function restored(Task $task): void
    {
        activity('task')
            ->causedBy(Auth::user())
            ->performedOn($task)
            ->withProperties([
                'task_title' => $task->title,
                'document_title' => $task->document->title ?? null,
                'event' => 'restored'
            ])
            ->log('Восстановлена задача: ' . $task->title);
    }

    /**
     * Handle the Task "force deleted" event.
     */
    public function forceDeleted(Task $task): void
    {
        activity('task')
            ->causedBy(Auth::user())
            ->performedOn($task)
            ->withProperties([
                'task_title' => $task->title,
                'document_title' => $task->document->title ?? null,
                'event' => 'force_deleted'
            ])
            ->log('Окончательно удалена задача: ' . $task->title);
    }
}

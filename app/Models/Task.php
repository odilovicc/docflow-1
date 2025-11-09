<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Laravel\Scout\Searchable;

class Task extends Model
{
    use LogsActivity, Searchable;

    protected $fillable = [
        'document_id',
        'workflow_step_id', 
        'assigned_to',
        'assigned_by',
        'title',
        'description',
        'status',
        'due_date',
        'started_at',
        'finished_at',
        'comment'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'started_at' => 'datetime', 
        'finished_at' => 'datetime',
    ];

    /**
     * Геттер для обратной совместимости с assignee_id
     */
    public function getAssigneeIdAttribute()
    {
        return $this->assigned_to;
    }

    /**
     * Сеттер для обратной совместимости с assignee_id
     */
    public function setAssigneeIdAttribute($value)
    {
        $this->assigned_to = $value;
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class);
    }

    public function workflowStep(): BelongsTo
    {
        return $this->belongsTo(WorkflowStep::class);
    }

    public function assignedToUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
    
    /**
     * Алиас для assignedToUser для удобства использования
     */
    public function assignee(): BelongsTo
    {
        return $this->assignedToUser();
    }

    public function assignedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isCompleted();
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    public function complete($comment = null): void
    {
        $this->update([
            'status' => 'completed',
            'finished_at' => now(),
            'comment' => $comment
        ]);
    }

    public function start(): void
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now()
        ]);
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="px-2 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">Ожидает</span>',
            'in_progress' => '<span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">В процессе</span>',
            'completed' => '<span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Завершена</span>',
            'cancelled' => '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Отменена</span>',
            default => '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Неизвестно</span>',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        if ($this->isOverdue()) {
            return '<span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Просрочена</span>';
        }
        
        if ($this->due_date && $this->due_date->diffInHours() <= 24) {
            return '<span class="px-2 py-1 text-xs rounded-full bg-orange-100 text-orange-800">Срочно</span>';
        }

        return '<span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">Обычная</span>';
    }

    /**
     * Scope для оптимизированной загрузки с связанными моделями
     */
    public function scopeWithOptimizedRelations($query)
    {
        return $query->with([
            'assignee:id,name,email,department_id',
            'assigner:id,name,email',
            'document:id,title,status,department_id',
            'document.department:id,name'
        ]);
    }

    /**
     * Scope для фильтрации задач по исполнителю и статусу (использует составной индекс)
     */
    public function scopeByAssigneeAndStatus($query, $assigneeId, $status = null)
    {
        $query->where('assigned_to', $assigneeId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query;
    }

    /**
     * Scope для получения просроченных задач (использует составной индекс)
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope для получения активных задач пользователя
     */
    public function scopeActiveForUser($query, $userId)
    {
        return $query->where('assigned_to', $userId)
                    ->whereIn('status', ['pending', 'in_progress']);
    }

    /**
     * Scope для фильтрации по документу и статусу (использует составной индекс)
     */
    public function scopeByDocumentAndStatus($query, $documentId, $status = null)
    {
        $query->where('document_id', $documentId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query;
    }

    /**
     * Scope для сортировки по приоритету и дате (использует составной индекс)
     */
    public function scopeOrderByPriority($query)
    {
        return $query->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'asc');
    }

    /**
     * Scope для фильтрации по типу задачи
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope для получения задач за период с оптимизацией
     */
    public function scopeInPeriod($query, $startDate, $endDate = null)
    {
        $query->where('created_at', '>=', $startDate);
        
        if ($endDate) {
            $query->where('created_at', '<=', $endDate);
        }
        
        return $query;
    }

    /**
     * Scope для получения статистики по задачам с группировкой
     */
    public function scopeStatsForUser($query, $userId)
    {
        return $query->selectRaw('
            status,
            COUNT(*) as count,
            COUNT(CASE WHEN due_date < NOW() AND status NOT IN ("completed", "cancelled") THEN 1 END) as overdue_count
        ')
        ->where('assigned_to', $userId)
        ->groupBy('status');
    }

    /**
     * Scope для получения задач по отделу через связь с документом
     */
    public function scopeByDepartment($query, $departmentId)
    {
        return $query->whereHas('document', function($q) use ($departmentId) {
            $q->where('department_id', $departmentId);
        });
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'priority' => $this->priority,
            'assignee_name' => $this->assignee?->name,
            'assigner_name' => $this->assigner?->name,
            'document_title' => $this->document?->title,
            'department_name' => $this->document?->department?->name,
            'due_date' => $this->due_date?->toDateString(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        // Индексируем только незавершенные задачи
        return !in_array($this->status, ['cancelled', 'deleted']);
    }

    /**
     * Get the Scout search key name.
     */
    public function getScoutKeyName(): string
    {
        return 'id';
    }

    /**
     * Get the search index name.
     */
    public function searchableAs(): string
    {
        return 'tasks_index';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'title', 'description', 'status', 'assigned_to', 
                'due_date', 'started_at', 'finished_at', 'comment'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

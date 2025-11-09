<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Laravel\Scout\Searchable;

class Document extends Model implements HasMedia
{
    use InteractsWithMedia, LogsActivity, Searchable;

    protected $fillable = [
        'title',
        'description',
        'author_id',
        'category_id',
        'department_id',
        'workflow_id',
        'status',
        'current_step',
        'due_date',
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
        ];
    }

    /**
     * Define media collections
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')
            ->acceptsMimeTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png']);
    }

    /**
     * Get the author of the document.
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get the category of the document.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get the department of the document.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the workflow of the document.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }

    /**
     * Get all tasks for the document.
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get status badge color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'bg-gray-100 text-gray-800',
            'in_progress' => 'bg-yellow-100 text-yellow-800',
            'approved' => 'bg-green-100 text-green-800',
            'rejected' => 'bg-red-100 text-red-800',
            'completed' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get human readable status
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Черновик',
            'in_progress' => 'В работе',
            'approved' => 'Одобрен',
            'rejected' => 'Отклонён',
            'completed' => 'Завершён',
            default => 'Неизвестно',
        };
    }

    /**
     * Scope для оптимизированной загрузки с связанными моделями
     */
    public function scopeWithOptimizedRelations($query)
    {
        return $query->with([
            'author:id,name,email,department_id',
            'author.department:id,name',
            'category:id,name',
            'department:id,name',
            'workflow:id,name',
            'tasks' => function($query) {
                $query->select('id', 'document_id', 'assigned_to', 'title', 'status', 'created_at')
                      ->with('assignee:id,name,email');
            }
        ]);
    }

    /**
     * Scope для фильтрации по статусу с использованием индекса
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope для фильтрации по отделу и статусу (использует составной индекс)
     */
    public function scopeByDepartmentAndStatus($query, $departmentId, $status = null)
    {
        $query->where('department_id', $departmentId);
        
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query;
    }

    /**
     * Scope для фильтрации по категории с сортировкой по дате
     */
    public function scopeByCategoryWithDate($query, $categoryId)
    {
        return $query->where('category_id', $categoryId)
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Scope для поиска по тексту
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('title', 'LIKE', "%{$search}%")
              ->orWhere('description', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Scope для получения активных документов (не завершенных)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope для получения просроченных документов
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->whereNotIn('status', ['completed', 'cancelled']);
    }

    /**
     * Scope для получения документов за период (оптимизирован индексом)
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
     * Scope для получения документов с задачами (eager loading)
     */
    public function scopeWithTasks($query)
    {
        return $query->with([
            'tasks' => function($q) {
                $q->select('id', 'document_id', 'title', 'status', 'assigned_to', 'due_date')
                  ->with('assignee:id,name');
            }
        ]);
    }

    /**
     * Activity log options
     */
    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'author_name' => $this->author?->name,
            'department_name' => $this->department?->name,
            'category_name' => $this->category?->name,
            'workflow_name' => $this->workflow?->name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        // Индексируем только активные документы
        return !in_array($this->status, ['deleted', 'cancelled']);
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
        return 'documents_index';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'current_step', 'workflow_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

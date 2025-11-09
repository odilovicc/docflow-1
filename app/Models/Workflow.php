<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Workflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'initial_state',
        'states',
        'transitions',
        'category_id',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'states' => 'array',
            'transitions' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the category that owns the workflow.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Get all workflow steps.
     */
    public function steps(): HasMany
    {
        return $this->hasMany(WorkflowStep::class)->orderBy('order');
    }

    /**
     * Get documents using this workflow.
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }
}

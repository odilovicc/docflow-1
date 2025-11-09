<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStep extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'state',
        'order',
        'required_roles',
        'required_permissions',
        'sla_days',
        'auto_assign',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'required_roles' => 'array',
            'required_permissions' => 'array',
            'auto_assign' => 'boolean',
        ];
    }

    /**
     * Get the workflow that owns the step.
     */
    public function workflow(): BelongsTo
    {
        return $this->belongsTo(Workflow::class);
    }
}

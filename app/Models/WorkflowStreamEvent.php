<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkflowStreamEvent extends Model
{
    protected $fillable = [
        'task_execution_id',
        'event_type',
        'task_id',
        'workflow_run_id',
        'node_id',
        'event_data',
        'event_timestamp',
    ];

    protected $casts = [
        'event_data' => 'array',
        'event_timestamp' => 'datetime',
    ];

    public function taskExecution(): BelongsTo
    {
        return $this->belongsTo(TaskExecution::class);
    }

    public function scopeByType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    public function scopeByWorkflowRun($query, string $workflowRunId)
    {
        return $query->where('workflow_run_id', $workflowRunId);
    }
}

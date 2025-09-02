<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    protected $fillable = [
        'name',
        'description',
        'dify_workflow_id',
        'input_schema',
        'is_active',
    ];

    protected $casts = [
        'input_schema' => 'array',
        'is_active' => 'boolean',
    ];

    public function executions(): HasMany
    {
        return $this->hasMany(TaskExecution::class);
    }

    public function difyWorkflow(): BelongsTo
    {
        return $this->belongsTo(DifyWorkflow::class);
    }
}

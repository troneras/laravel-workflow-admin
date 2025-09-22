<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TaskExecution extends Model
{
    protected $fillable = [
        'task_execution_id',
        'task_id',
        'start_time',
        'end_time',
        'duration',
        'tokens',
        'status',
        'input',
        'output',
        'track',
        'metadata',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'input' => 'array',
        'output' => 'array',
        'track' => 'array',
        'metadata' => 'array',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function streamEvents(): HasMany
    {
        return $this->hasMany(WorkflowStreamEvent::class);
    }

    public function webhookAttempts(): HasMany
    {
        return $this->hasMany(WebhookAttempt::class);
    }

    public function isApiExecution(): bool
    {
        return isset($this->metadata['api_execution']) && $this->metadata['api_execution'] === true;
    }

    public function hasWebhookUrl(): bool
    {
        return !empty($this->metadata['webhook_url']);
    }

    public function getWebhookUrl(): ?string
    {
        return $this->metadata['webhook_url'] ?? null;
    }

    public function getLatestWebhookAttempt(): ?WebhookAttempt
    {
        return $this->webhookAttempts()->latest('attempted_at')->first();
    }
}

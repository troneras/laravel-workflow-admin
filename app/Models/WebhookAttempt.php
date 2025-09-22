<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookAttempt extends Model
{
    protected $fillable = [
        'task_execution_id',
        'webhook_url',
        'payload',
        'http_status',
        'response_body',
        'error_message',
        'response_time_ms',
        'attempt_number',
        'status',
        'attempted_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'attempted_at' => 'datetime',
        'response_time_ms' => 'decimal:2',
    ];

    public function taskExecution(): BelongsTo
    {
        return $this->belongsTo(TaskExecution::class);
    }

    public function isSuccessful(): bool
    {
        return $this->status === 'success' &&
               $this->http_status >= 200 &&
               $this->http_status < 300;
    }

    public function isRetryable(): bool
    {
        return $this->status === 'failed' &&
               ($this->http_status >= 500 || $this->http_status === null);
    }
}
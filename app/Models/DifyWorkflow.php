<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DifyWorkflow extends Model
{
    protected $fillable = [
        'name',
        'description',
        'workflow_id',
        'api_key',
        'is_active',
        'status',
        'status_message',
        'last_status_check',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_status_check' => 'datetime',
    ];

    protected $hidden = [
        'api_key',
    ];

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function isHealthy(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function canExecute(): bool
    {
        return $this->isHealthy() && $this->status !== 'syncing';
    }

    public function markAsError(string $message): void
    {
        $this->update([
            'status' => 'error',
            'status_message' => $message,
            'last_status_check' => now(),
        ]);
    }

    public function markAsActive(?string $message = null): void
    {
        $this->update([
            'status' => 'active',
            'status_message' => $message,
            'last_status_check' => now(),
        ]);
    }

    public function scopeHealthy($query)
    {
        return $query->where('is_active', true)->where('status', 'active');
    }
}

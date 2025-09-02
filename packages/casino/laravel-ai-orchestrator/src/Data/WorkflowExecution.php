<?php

namespace Casino\LaravelAiOrchestrator\Data;

use DateTimeInterface;

class WorkflowExecution
{
    public function __construct(
        public readonly int $executionId,
        public readonly string $taskExecutionId,
        public readonly string $status,
        public readonly ?string $webhookUrl,
        public readonly ?DateTimeInterface $createdAt,
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            executionId: $data['execution_id'],
            taskExecutionId: $data['task_execution_id'],
            status: $data['status'],
            webhookUrl: $data['webhook_url'] ?? null,
            createdAt: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
        );
    }

    /**
     * Check if execution is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if execution is running
     */
    public function isRunning(): bool
    {
        return $this->status === 'running';
    }

    /**
     * Check if execution is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if execution failed
     */
    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    /**
     * Check if execution is finished (completed or failed)
     */
    public function isFinished(): bool
    {
        return $this->isCompleted() || $this->isFailed();
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'execution_id' => $this->executionId,
            'task_execution_id' => $this->taskExecutionId,
            'status' => $this->status,
            'webhook_url' => $this->webhookUrl,
            'created_at' => $this->createdAt?->format('c'),
            'is_pending' => $this->isPending(),
            'is_running' => $this->isRunning(),
            'is_completed' => $this->isCompleted(),
            'is_failed' => $this->isFailed(),
            'is_finished' => $this->isFinished(),
        ];
    }
}
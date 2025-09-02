<?php

namespace Casino\LaravelAiOrchestrator\Data;

use DateTimeInterface;

class ExecutionResult
{
    public function __construct(
        public readonly int $id,
        public readonly string $taskExecutionId,
        public readonly ?string $taskName,
        public readonly string $status,
        public readonly ?DateTimeInterface $startTime,
        public readonly ?DateTimeInterface $endTime,
        public readonly ?int $duration,
        public readonly ?int $tokens,
        public readonly ?array $output,
        public readonly ?array $metadata,
        public readonly ?DateTimeInterface $updatedAt,
        public readonly array $progress = [],
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        $execution = $data['execution'] ?? $data;
        $progress = $data['progress'] ?? [];

        return new self(
            id: $execution['id'],
            taskExecutionId: $execution['task_execution_id'],
            taskName: $execution['task_name'] ?? null,
            status: $execution['status'],
            startTime: isset($execution['start_time']) ? new \DateTime($execution['start_time']) : null,
            endTime: isset($execution['end_time']) ? new \DateTime($execution['end_time']) : null,
            duration: $execution['duration'] ?? null,
            tokens: $execution['tokens'] ?? null,
            output: $execution['output'] ?? null,
            metadata: $execution['metadata'] ?? null,
            updatedAt: isset($execution['updated_at']) ? new \DateTime($execution['updated_at']) : null,
            progress: $progress,
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
     * Get execution duration in seconds
     */
    public function getDurationInSeconds(): ?int
    {
        return $this->duration;
    }

    /**
     * Get execution duration formatted
     */
    public function getDurationFormatted(): ?string
    {
        if (!$this->duration) {
            return null;
        }

        $minutes = floor($this->duration / 60);
        $seconds = $this->duration % 60;

        if ($minutes > 0) {
            return sprintf('%dm %ds', $minutes, $seconds);
        }

        return sprintf('%ds', $seconds);
    }

    /**
     * Get output text (if available)
     */
    public function getOutputText(): ?string
    {
        if (!$this->output) {
            return null;
        }

        // Try common output formats
        return $this->output['text'] 
            ?? $this->output['result'] 
            ?? $this->output['answer']
            ?? json_encode($this->output);
    }

    /**
     * Get error message (if failed)
     */
    public function getErrorMessage(): ?string
    {
        if (!$this->isFailed() || !$this->output) {
            return null;
        }

        return $this->output['error'] 
            ?? $this->output['message']
            ?? 'Execution failed';
    }

    /**
     * Get progress percentage (0-100)
     */
    public function getProgressPercentage(): int
    {
        if ($this->isCompleted()) {
            return 100;
        }

        if ($this->isFailed()) {
            return 0;
        }

        if ($this->isPending()) {
            return 0;
        }

        if ($this->isRunning()) {
            // Estimate progress based on events (if available)
            $totalEvents = $this->progress['total_events'] ?? 0;
            if ($totalEvents > 0) {
                return min(90, max(10, $totalEvents * 10)); // Rough estimate
            }
            return 50; // Default running state
        }

        return 0;
    }

    /**
     * Get latest progress events
     */
    public function getLatestEvents(): array
    {
        return $this->progress['latest_events'] ?? [];
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'task_execution_id' => $this->taskExecutionId,
            'task_name' => $this->taskName,
            'status' => $this->status,
            'start_time' => $this->startTime?->format('c'),
            'end_time' => $this->endTime?->format('c'),
            'duration' => $this->duration,
            'duration_formatted' => $this->getDurationFormatted(),
            'tokens' => $this->tokens,
            'output' => $this->output,
            'output_text' => $this->getOutputText(),
            'error_message' => $this->getErrorMessage(),
            'metadata' => $this->metadata,
            'updated_at' => $this->updatedAt?->format('c'),
            'progress' => $this->progress,
            'progress_percentage' => $this->getProgressPercentage(),
            'latest_events' => $this->getLatestEvents(),
            'is_pending' => $this->isPending(),
            'is_running' => $this->isRunning(),
            'is_completed' => $this->isCompleted(),
            'is_failed' => $this->isFailed(),
            'is_finished' => $this->isFinished(),
        ];
    }
}
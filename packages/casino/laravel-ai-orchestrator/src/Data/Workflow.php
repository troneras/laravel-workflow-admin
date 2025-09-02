<?php

namespace Casino\LaravelAiOrchestrator\Data;

use DateTimeInterface;

class Workflow
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?string $description,
        public readonly string $workflowId,
        public readonly string $status,
        public readonly ?DateTimeInterface $createdAt,
        public readonly array $exampleInputs = [],
    ) {}

    /**
     * Create from API response array
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: $data['id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            workflowId: $data['workflow_id'],
            status: $data['status'],
            createdAt: isset($data['created_at']) ? new \DateTime($data['created_at']) : null,
            exampleInputs: $data['example_inputs'] ?? [],
        );
    }

    /**
     * Check if workflow is healthy
     */
    public function isHealthy(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if workflow can execute
     */
    public function canExecute(): bool
    {
        return $this->isHealthy();
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'workflow_id' => $this->workflowId,
            'status' => $this->status,
            'created_at' => $this->createdAt?->format('c'),
            'example_inputs' => $this->exampleInputs,
            'is_healthy' => $this->isHealthy(),
            'can_execute' => $this->canExecute(),
        ];
    }
}
<?php

namespace Casino\LaravelAiOrchestrator\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;

class WorkflowCollection implements IteratorAggregate, Countable
{
    /**
     * @param Workflow[] $workflows
     */
    public function __construct(
        protected array $workflows = []
    ) {}

    /**
     * Create from API response
     */
    public static function fromArray(array $data): self
    {
        $workflows = array_map(
            fn(array $workflow) => Workflow::fromArray($workflow),
            $data['workflows'] ?? []
        );

        return new self($workflows);
    }

    /**
     * Get workflow by name
     */
    public function byName(string $name): ?Workflow
    {
        foreach ($this->workflows as $workflow) {
            if ($workflow->name === $name) {
                return $workflow;
            }
        }

        return null;
    }

    /**
     * Get workflow by ID
     */
    public function byId(int $id): ?Workflow
    {
        foreach ($this->workflows as $workflow) {
            if ($workflow->id === $id) {
                return $workflow;
            }
        }

        return null;
    }

    /**
     * Filter workflows by status
     */
    public function whereStatus(string $status): self
    {
        $filtered = array_filter(
            $this->workflows,
            fn(Workflow $workflow) => $workflow->status === $status
        );

        return new self(array_values($filtered));
    }

    /**
     * Get all workflows as array
     */
    public function toArray(): array
    {
        return array_map(
            fn(Workflow $workflow) => $workflow->toArray(),
            $this->workflows
        );
    }

    /**
     * Get iterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->workflows);
    }

    /**
     * Count workflows
     */
    public function count(): int
    {
        return count($this->workflows);
    }

    /**
     * Check if collection is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->workflows);
    }

    /**
     * Get first workflow
     */
    public function first(): ?Workflow
    {
        return $this->workflows[0] ?? null;
    }

    /**
     * Get all workflows
     */
    public function all(): array
    {
        return $this->workflows;
    }
}
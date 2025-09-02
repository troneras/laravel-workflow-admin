<?php

namespace Casino\LaravelAiOrchestrator\Contracts;

use Casino\LaravelAiOrchestrator\Data\ExecutionResult;
use Casino\LaravelAiOrchestrator\Data\WorkflowCollection;
use Casino\LaravelAiOrchestrator\Data\WorkflowExecution;

interface AiOrchestratorInterface
{
    /**
     * Discover available workflows
     */
    public function workflows(): WorkflowCollection;

    /**
     * Execute a workflow with flexible task management
     */
    public function execute(string $workflow, array $inputs, ?string $taskGroup = null, ?array $context = null, ?string $webhookUrl = null): WorkflowExecution;

    /**
     * Legacy execute method by task ID
     * @deprecated Use execute() with workflow name instead
     */
    public function executeByTaskId(int $taskId, array $inputs, ?string $webhookUrl = null, ?string $referenceId = null): WorkflowExecution;


    /**
     * Get execution status
     */
    public function status(int $executionId): ExecutionResult;

    /**
     * Get execution status by UUID
     */
    public function statusByUuid(string $taskExecutionId): ExecutionResult;

    /**
     * Wait for execution to complete (polling)
     */
    public function waitFor(int $executionId, int $timeoutSeconds = 300, int $pollIntervalSeconds = 2): ExecutionResult;

    /**
     * Query executions by various filters
     */
    public function executions(array $filters = []): array;

    /**
     * Query executions by task group
     */
    public function executionsByTaskGroup(string $taskGroup, array $additionalFilters = []): array;

    /**
     * Query executions by service and operation
     */
    public function executionsByService(string $service, ?string $operation = null, array $additionalFilters = []): array;

    /**
     * Cancel an execution (if supported)
     */
    public function cancel(int $executionId): bool;

    /**
     * Set the service name for this instance
     */
    public function setServiceName(string $serviceName): self;

    /**
     * Set a callback URL for webhooks
     */
    public function setCallbackUrl(string $callbackUrl): self;
}
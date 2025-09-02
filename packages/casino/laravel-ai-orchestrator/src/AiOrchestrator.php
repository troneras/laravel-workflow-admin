<?php

namespace Casino\LaravelAiOrchestrator;

use Casino\LaravelAiOrchestrator\Contracts\AiOrchestratorInterface;
use Casino\LaravelAiOrchestrator\Data\ExecutionResult;
use Casino\LaravelAiOrchestrator\Data\WorkflowCollection;
use Casino\LaravelAiOrchestrator\Data\WorkflowExecution;
use Casino\LaravelAiOrchestrator\Exceptions\ApiException;
use Casino\LaravelAiOrchestrator\Exceptions\ExecutionException;
use Casino\LaravelAiOrchestrator\Exceptions\TimeoutException;
use Casino\LaravelAiOrchestrator\Exceptions\WorkflowNotFoundException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiOrchestrator implements AiOrchestratorInterface
{
    protected ?string $serviceName = null;
    protected ?string $callbackUrl = null;

    public function __construct(
        protected string $orchestratorUrl = '',
        protected ?string $apiKey = null,
        protected int $timeout = 30,
    ) {
        $this->orchestratorUrl = $orchestratorUrl ?: config('ai-orchestrator.orchestrator_url');
        $this->apiKey = $apiKey ?: config('ai-orchestrator.api.key');
        $this->timeout = $timeout ?: config('ai-orchestrator.api.timeout', 30);
        $this->serviceName = config('ai-orchestrator.service.name');
        $this->callbackUrl = config('ai-orchestrator.service.callback_url');
    }

    /**
     * Discover available workflows
     */
    public function workflows(): WorkflowCollection
    {
        $cacheKey = $this->getCacheKey('workflows');
        
        if (config('ai-orchestrator.cache.enabled') && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return WorkflowCollection::fromArray($data);
        }

        $response = $this->makeRequest('GET', '/api/orchestrator/workflows');
        
        if (!$response->successful()) {
            throw new ApiException('Failed to fetch workflows: ' . $response->body(), $response->status());
        }

        $data = $response->json('data');
        
        if (config('ai-orchestrator.cache.enabled')) {
            Cache::put($cacheKey, $data, config('ai-orchestrator.cache.ttl.workflows', 300));
        }

        return WorkflowCollection::fromArray($data);
    }

    /**
     * Execute a workflow with flexible task management
     */
    public function execute(string $workflow, array $inputs, ?string $taskGroup = null, ?array $context = null, ?string $webhookUrl = null): WorkflowExecution
    {
        $payload = [
            'workflow' => $workflow,
            'inputs' => $inputs,
        ];

        // Add task group if provided
        if ($taskGroup) {
            $payload['task_group'] = $taskGroup;
        }

        // Add context if provided
        if ($context) {
            $payload['context'] = $context;
        } else if ($this->serviceName) {
            // Auto-generate context from service name if not provided
            $payload['context'] = [
                'service' => $this->serviceName,
            ];
        }

        // Add webhook URL
        if ($webhookUrl || $this->callbackUrl) {
            $payload['webhook_url'] = $webhookUrl ?: $this->callbackUrl;
        }

        $response = $this->makeRequest('POST', '/api/orchestrator/execute', $payload);

        if (!$response->successful()) {
            if ($response->status() === 404) {
                throw new WorkflowNotFoundException("Workflow '{$workflow}' not found or not healthy");
            }
            
            $error = $response->json('error') ?? 'Execution failed';
            throw new ExecutionException($error, $response->status());
        }

        $data = $response->json('data');
        
        $this->logOperation('execute', [
            'workflow' => $workflow,
            'task_group' => $taskGroup,
            'execution_id' => $data['execution_id'],
            'status' => $data['status'],
        ]);

        return WorkflowExecution::fromArray($data);
    }

    /**
     * Legacy execute method by task ID (deprecated)
     * @deprecated Use execute() with workflow name instead
     */
    public function executeByTaskId(int $taskId, array $inputs, ?string $webhookUrl = null, ?string $referenceId = null): WorkflowExecution
    {
        $payload = [
            'task_id' => $taskId,
            'inputs' => $inputs,
            'service_name' => $this->serviceName,
            'reference_id' => $referenceId,
        ];

        if ($webhookUrl || $this->callbackUrl) {
            $payload['webhook_url'] = $webhookUrl ?: $this->callbackUrl;
        }

        $response = $this->makeRequest('POST', '/api/orchestrator/execute', $payload);

        if (!$response->successful()) {
            $error = $response->json('error') ?? 'Execution failed';
            throw new ExecutionException($error, $response->status());
        }

        $data = $response->json('data');
        
        $this->logOperation('execute_by_task_id', [
            'task_id' => $taskId,
            'execution_id' => $data['execution_id'],
            'status' => $data['status'],
        ]);

        return WorkflowExecution::fromArray($data);
    }


    /**
     * Get execution status
     */
    public function status(int $executionId): ExecutionResult
    {
        $cacheKey = $this->getCacheKey("execution.{$executionId}");
        
        if (config('ai-orchestrator.cache.enabled') && Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return ExecutionResult::fromArray($data);
        }

        $response = $this->makeRequest('GET', "/api/orchestrator/executions/{$executionId}/status");

        if (!$response->successful()) {
            if ($response->status() === 404) {
                throw new ExecutionException("Execution {$executionId} not found", 404);
            }
            
            throw new ApiException('Failed to fetch execution status: ' . $response->body(), $response->status());
        }

        $data = $response->json('data');
        
        // Cache only finished executions
        if (config('ai-orchestrator.cache.enabled') && 
            in_array($data['execution']['status'], ['completed', 'failed'])) {
            Cache::put($cacheKey, $data, config('ai-orchestrator.cache.ttl.executions', 60));
        }

        return ExecutionResult::fromArray($data);
    }

    /**
     * Get execution status by UUID
     */
    public function statusByUuid(string $taskExecutionId): ExecutionResult
    {
        // This would require an additional endpoint in the orchestrator service
        // For now, we'll need to search through executions or add a new endpoint
        throw new \BadMethodCallException('statusByUuid not yet implemented - add endpoint to orchestrator service');
    }

    /**
     * Wait for execution to complete (polling)
     */
    public function waitFor(int $executionId, int $timeoutSeconds = 300, int $pollIntervalSeconds = 2): ExecutionResult
    {
        $startTime = time();
        $endTime = $startTime + $timeoutSeconds;

        while (time() < $endTime) {
            $result = $this->status($executionId);
            
            if ($result->isFinished()) {
                $this->logOperation('wait_completed', [
                    'execution_id' => $executionId,
                    'status' => $result->status,
                    'wait_duration' => time() - $startTime,
                ]);
                
                return $result;
            }
            
            sleep($pollIntervalSeconds);
        }

        $this->logOperation('wait_timeout', [
            'execution_id' => $executionId,
            'timeout_seconds' => $timeoutSeconds,
        ]);

        throw new TimeoutException("Execution {$executionId} did not complete within {$timeoutSeconds} seconds");
    }

    /**
     * Query executions by various filters
     */
    public function executions(array $filters = []): array
    {
        $response = $this->makeRequest('GET', '/api/orchestrator/executions', $filters);

        if (!$response->successful()) {
            throw new ApiException('Failed to fetch executions: ' . $response->body(), $response->status());
        }

        return $response->json('data');
    }

    /**
     * Query executions by task group
     */
    public function executionsByTaskGroup(string $taskGroup, array $additionalFilters = []): array
    {
        $filters = array_merge(['task_group' => $taskGroup], $additionalFilters);
        return $this->executions($filters);
    }

    /**
     * Query executions by service and operation
     */
    public function executionsByService(string $service, ?string $operation = null, array $additionalFilters = []): array
    {
        $filters = array_merge(['service' => $service], $additionalFilters);
        
        if ($operation) {
            $filters['operation'] = $operation;
        }
        
        return $this->executions($filters);
    }

    /**
     * Cancel an execution (if supported)
     */
    public function cancel(int $executionId): bool
    {
        // This would require implementation in the orchestrator service
        throw new \BadMethodCallException('Cancel operation not yet supported by orchestrator service');
    }

    /**
     * Set the service name for this instance
     */
    public function setServiceName(string $serviceName): self
    {
        $this->serviceName = $serviceName;
        return $this;
    }

    /**
     * Set a callback URL for webhooks
     */
    public function setCallbackUrl(string $callbackUrl): self
    {
        $this->callbackUrl = $callbackUrl;
        return $this;
    }

    /**
     * Make an HTTP request to the orchestrator service
     */
    protected function makeRequest(string $method, string $endpoint, array $data = []): Response
    {
        $client = $this->getHttpClient();
        
        $this->logOperation('api_request', [
            'method' => $method,
            'endpoint' => $endpoint,
            'url' => $this->orchestratorUrl . $endpoint,
        ]);

        try {
            return match(strtoupper($method)) {
                'GET' => $client->get($endpoint, $data),
                'POST' => $client->post($endpoint, $data),
                'PUT' => $client->put($endpoint, $data),
                'PATCH' => $client->patch($endpoint, $data),
                'DELETE' => $client->delete($endpoint, $data),
                default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}"),
            };
        } catch (\Exception $e) {
            $this->logOperation('api_error', [
                'method' => $method,
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);
            
            throw new ApiException("API request failed: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Get configured HTTP client
     */
    protected function getHttpClient(): PendingRequest
    {
        $client = Http::baseUrl($this->orchestratorUrl)
            ->timeout($this->timeout)
            ->acceptJson();

        if ($this->apiKey) {
            $client->withToken($this->apiKey);
        }

        // Add retry logic
        $retryTimes = config('ai-orchestrator.api.retry.times', 3);
        $retryDelay = config('ai-orchestrator.api.retry.delay', 100);
        
        if ($retryTimes > 0) {
            $client->retry($retryTimes, $retryDelay);
        }

        return $client;
    }

    /**
     * Get cache key for a given identifier
     */
    protected function getCacheKey(string $key): string
    {
        $prefix = config('ai-orchestrator.cache.prefix', 'ai-orchestrator');
        return "{$prefix}.{$key}";
    }

    /**
     * Log an operation if logging is enabled
     */
    protected function logOperation(string $operation, array $context = []): void
    {
        if (!config('ai-orchestrator.logging.enabled')) {
            return;
        }

        $level = config('ai-orchestrator.logging.level', 'info');
        $channel = config('ai-orchestrator.logging.channel');
        
        $logger = $channel ? Log::channel($channel) : Log::class;
        
        $context['service'] = $this->serviceName;
        $context['orchestrator_url'] = $this->orchestratorUrl;
        
        $logger::log($level, "AI Orchestrator: {$operation}", $context);
    }
}
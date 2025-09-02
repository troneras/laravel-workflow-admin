<?php

namespace Casino\LaravelAiOrchestrator\Facades;

use Casino\LaravelAiOrchestrator\Contracts\AiOrchestratorInterface;
use Casino\LaravelAiOrchestrator\Data\ExecutionResult;
use Casino\LaravelAiOrchestrator\Data\WorkflowCollection;
use Casino\LaravelAiOrchestrator\Data\WorkflowExecution;
use Illuminate\Support\Facades\Facade;

/**
 * AI Orchestrator Facade
 * 
 * @method static WorkflowCollection workflows()
 * @method static WorkflowExecution execute(string $workflow, array $inputs, ?string $taskGroup = null, ?array $context = null, ?string $webhookUrl = null)
 * @method static WorkflowExecution executeByTaskId(int $taskId, array $inputs, ?string $webhookUrl = null, ?string $referenceId = null)
 * @method static ExecutionResult status(int $executionId)
 * @method static ExecutionResult statusByUuid(string $taskExecutionId)
 * @method static ExecutionResult waitFor(int $executionId, int $timeoutSeconds = 300, int $pollIntervalSeconds = 2)
 * @method static array executions(array $filters = [])
 * @method static array executionsByTaskGroup(string $taskGroup, array $additionalFilters = [])
 * @method static array executionsByService(string $service, ?string $operation = null, array $additionalFilters = [])
 * @method static bool cancel(int $executionId)
 * @method static AiOrchestratorInterface setServiceName(string $serviceName)
 * @method static AiOrchestratorInterface setCallbackUrl(string $callbackUrl)
 * 
 * @see AiOrchestratorInterface
 */
class AI extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return AiOrchestratorInterface::class;
    }

    /**
     * Convenience method: Translate text
     * 
     * Usage: AI::translate("Hello world", "spanish")
     */
    public static function translate(string $text, string $targetLanguage, ?string $webhookUrl = null): WorkflowExecution
    {
        return static::execute('translate', [
            'text' => $text,
            'target_language' => $targetLanguage,
        ], 'translations', null, $webhookUrl);
    }

    /**
     * Convenience method: Summarize content
     * 
     * Usage: AI::summarize($longText)
     */
    public static function summarize(string $content, ?int $maxWords = null, ?string $webhookUrl = null): WorkflowExecution
    {
        $inputs = ['content' => $content];
        if ($maxWords) {
            $inputs['max_words'] = $maxWords;
        }

        return static::execute('summarize', $inputs, 'summaries', null, $webhookUrl);
    }

    /**
     * Convenience method: Analyze content
     * 
     * Usage: AI::analyze($data, 'sentiment')
     */
    public static function analyze(string $content, string $analysisType = 'general', ?string $webhookUrl = null): WorkflowExecution
    {
        return static::execute('analyze', [
            'content' => $content,
            'analysis_type' => $analysisType,
        ], 'analyses', ['operation' => $analysisType], $webhookUrl);
    }

    /**
     * Convenience method: Generate content
     * 
     * Usage: AI::generate("Write a blog post about AI", ["tone" => "professional"])
     */
    public static function generate(string $prompt, array $options = [], ?string $webhookUrl = null): WorkflowExecution
    {
        $inputs = array_merge(['prompt' => $prompt], $options);

        return static::execute('generate', $inputs, 'generations', null, $webhookUrl);
    }

    /**
     * Convenience method: Extract information
     * 
     * Usage: AI::extract($document, ['email', 'phone', 'address'])
     */
    public static function extract(string $content, array $fields, ?string $webhookUrl = null): WorkflowExecution
    {
        return static::execute('extract', [
            'content' => $content,
            'fields' => $fields,
        ], 'extractions', null, $webhookUrl);
    }

    /**
     * Execute workflow with promise-like syntax
     * 
     * Usage: AI::workflow("translate")->inputs(["text" => "Hello"])->then($callback)
     */
    public static function workflow(string $workflowName): WorkflowExecutor
    {
        return new WorkflowExecutor(static::getFacadeRoot(), $workflowName);
    }
}

/**
 * Workflow executor for fluent interface
 */
class WorkflowExecutor
{
    protected array $inputs = [];
    protected ?string $taskGroup = null;
    protected ?array $context = null;
    protected ?string $webhookUrl = null;
    protected ?\Closure $successCallback = null;
    protected ?\Closure $failureCallback = null;

    public function __construct(
        protected AiOrchestratorInterface $orchestrator,
        protected string $workflowName
    ) {}

    /**
     * Set inputs for the workflow
     */
    public function inputs(array $inputs): self
    {
        $this->inputs = $inputs;
        return $this;
    }

    /**
     * Set task group for grouping executions
     */
    public function taskGroup(string $taskGroup): self
    {
        $this->taskGroup = $taskGroup;
        return $this;
    }

    /**
     * Set task name (alias for taskGroup for backward compatibility)
     */
    public function task(string $taskName): self
    {
        return $this->taskGroup($taskName);
    }

    /**
     * Set execution context
     */
    public function context(array $context): self
    {
        $this->context = $context;
        return $this;
    }

    /**
     * Set webhook URL
     */
    public function webhook(string $webhookUrl): self
    {
        $this->webhookUrl = $webhookUrl;
        return $this;
    }

    /**
     * Set reference ID (shortcut for context)
     */
    public function reference(string $referenceId): self
    {
        if (!$this->context) {
            $this->context = [];
        }
        $this->context['reference_id'] = $referenceId;
        return $this;
    }

    /**
     * Set operation (shortcut for context)
     */
    public function operation(string $operation): self
    {
        if (!$this->context) {
            $this->context = [];
        }
        $this->context['operation'] = $operation;
        return $this;
    }

    /**
     * Set success callback
     */
    public function then(\Closure $callback): self
    {
        $this->successCallback = $callback;
        return $this;
    }

    /**
     * Set failure callback
     */
    public function catch(\Closure $callback): self
    {
        $this->failureCallback = $callback;
        return $this;
    }

    /**
     * Execute the workflow
     */
    public function execute(): WorkflowExecution
    {
        try {
            $execution = $this->orchestrator->execute(
                $this->workflowName,
                $this->inputs,
                $this->taskGroup,
                $this->context,
                $this->webhookUrl
            );

            // If there's a success callback and no webhook, wait for completion
            if ($this->successCallback && !$this->webhookUrl) {
                $result = $this->orchestrator->waitFor($execution->executionId);
                if ($result->isCompleted()) {
                    call_user_func($this->successCallback, $result);
                } elseif ($result->isFailed() && $this->failureCallback) {
                    call_user_func($this->failureCallback, $result);
                }
            }

            return $execution;
        } catch (\Exception $e) {
            if ($this->failureCallback) {
                call_user_func($this->failureCallback, $e);
                // Return a dummy execution if callback handles the error
                return new WorkflowExecution(-1, '', 'failed', null, now());
            } else {
                throw $e;
            }
        }
    }

    /**
     * Execute and wait for completion
     */
    public function wait(int $timeoutSeconds = 300): ExecutionResult
    {
        $execution = $this->execute();
        return $this->orchestrator->waitFor($execution->executionId, $timeoutSeconds);
    }

    /**
     * Dispatch (execute asynchronously)
     */
    public function dispatch(): WorkflowExecution
    {
        return $this->execute();
    }
}
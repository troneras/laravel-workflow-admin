<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\RunDifyWorkflow;
use App\Models\DifyWorkflow;
use App\Models\Task;
use App\Models\TaskExecution;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class WorkflowOrchestratorController extends Controller
{
    /**
     * List available workflows for discovery
     */
    public function workflows(): JsonResponse
    {
        $workflows = DifyWorkflow::healthy()
            ->select('id', 'name', 'description', 'workflow_id', 'status', 'created_at')
            ->get()
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'description' => $workflow->description,
                    'workflow_id' => $workflow->workflow_id,
                    'status' => $workflow->status,
                    'created_at' => $workflow->created_at,
                    // Get example input schema from related tasks
                    'example_inputs' => $workflow->tasks()
                        ->whereNotNull('input_schema')
                        ->first()?->input_schema ?? [],
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'workflows' => $workflows,
                'total' => $workflows->count(),
            ],
        ]);
    }

    /**
     * Get workflow details including available tasks
     */
    public function workflow(DifyWorkflow $workflow): JsonResponse
    {
        if (!$workflow->isHealthy()) {
            return response()->json([
                'success' => false,
                'error' => 'Workflow is not healthy',
                'status' => $workflow->status,
                'status_message' => $workflow->status_message,
            ], 400);
        }

        $tasks = $workflow->tasks()
            ->select('id', 'name', 'description', 'input_schema', 'created_at')
            ->get()
            ->map(function ($task) {
                return [
                    'id' => $task->id,
                    'name' => $task->name,
                    'description' => $task->description,
                    'input_schema' => $task->input_schema,
                    'created_at' => $task->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'workflow' => [
                    'id' => $workflow->id,
                    'name' => $workflow->name,
                    'description' => $workflow->description,
                    'workflow_id' => $workflow->workflow_id,
                    'status' => $workflow->status,
                    'created_at' => $workflow->created_at,
                ],
                'tasks' => $tasks,
            ],
        ]);
    }

    /**
     * Execute a workflow with flexible task management
     */
    public function execute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'workflow' => 'required|string', // Can be workflow name or ID
            'task_group' => 'nullable|string|max:100', // Optional task grouping identifier
            'inputs' => 'required|array',
            'context' => 'nullable|array',
            'context.service' => 'nullable|string|max:100',
            'context.operation' => 'nullable|string|max:100',
            'context.reference_id' => 'nullable|string|max:255',
            'webhook_url' => 'nullable|url',
            // Legacy support
            'task_id' => 'nullable|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        // Legacy support: if task_id is provided, use old logic
        if ($request->has('task_id')) {
            return $this->executeLegacy($request);
        }

        // Find workflow by name or ID
        $workflowQuery = DifyWorkflow::healthy();
        if (Str::isUuid($request->workflow)) {
            $workflowQuery->where('workflow_id', $request->workflow);
        } else {
            $workflowQuery->where('name', $request->workflow);
        }

        $workflow = $workflowQuery->first();
        if (!$workflow) {
            return response()->json([
                'success' => false,
                'error' => 'Workflow not found or not healthy',
                'workflow' => $request->workflow,
            ], 404);
        }

        // Find or create task based on task_group
        $task = $this->findOrCreateTask($workflow, $request->task_group, $request->context);

        // Create execution with metadata
        $metadata = [
            'webhook_url' => $request->webhook_url,
            'api_execution' => true,
            'created_via' => 'api',
        ];

        // Add context to metadata if provided
        if ($request->has('context')) {
            $metadata = array_merge($metadata, [
                'service_name' => $request->context['service'] ?? null,
                'operation' => $request->context['operation'] ?? null,
                'reference_id' => $request->context['reference_id'] ?? null,
            ]);
        }

        $execution = $task->executions()->create([
            'task_execution_id' => (string) Str::uuid(),
            'status' => 'pending',
            'input' => $request->inputs,
            'start_time' => now(),
            'metadata' => $metadata,
        ]);

        // Dispatch the job
        RunDifyWorkflow::dispatch($execution);

        Log::info('API workflow execution initiated', [
            'execution_id' => $execution->id,
            'workflow_name' => $workflow->name,
            'task_name' => $task->name,
            'task_group' => $request->task_group,
            'context' => $request->context ?? [],
            'webhook_url' => $request->webhook_url,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'execution_id' => $execution->id,
                'task_execution_id' => $execution->task_execution_id,
                'workflow_name' => $workflow->name,
                'task_name' => $task->name,
                'status' => $execution->status,
                'webhook_url' => $request->webhook_url,
                'created_at' => $execution->created_at,
            ],
        ], 201);
    }

    /**
     * Legacy execute method for backward compatibility
     */
    protected function executeLegacy(Request $request): JsonResponse
    {
        $task = Task::with('difyWorkflow')->findOrFail($request->task_id);

        // Check if workflow can execute
        if (!$task->difyWorkflow) {
            return response()->json([
                'success' => false,
                'error' => 'No workflow associated with this task',
            ], 400);
        }

        if (!$task->difyWorkflow->canExecute()) {
            return response()->json([
                'success' => false,
                'error' => 'Workflow is not available for execution',
                'status' => $task->difyWorkflow->status,
                'status_message' => $task->difyWorkflow->status_message,
            ], 400);
        }

        // Create execution with API metadata
        $execution = $task->executions()->create([
            'task_execution_id' => (string) Str::uuid(),
            'status' => 'pending',
            'input' => $request->inputs,
            'start_time' => now(),
            'metadata' => [
                'webhook_url' => $request->webhook_url,
                'service_name' => $request->service_name,
                'reference_id' => $request->reference_id,
                'api_execution' => true,
                'created_via' => 'api',
            ],
        ]);

        // Dispatch the job
        RunDifyWorkflow::dispatch($execution);

        Log::info('API workflow execution initiated (legacy)', [
            'execution_id' => $execution->id,
            'task_id' => $task->id,
            'task_name' => $task->name,
            'service_name' => $request->service_name,
            'reference_id' => $request->reference_id,
            'webhook_url' => $request->webhook_url,
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'execution_id' => $execution->id,
                'task_execution_id' => $execution->task_execution_id,
                'status' => $execution->status,
                'webhook_url' => $request->webhook_url,
                'created_at' => $execution->created_at,
            ],
        ], 201);
    }

    /**
     * Find or create a task based on task_group
     */
    protected function findOrCreateTask(DifyWorkflow $workflow, ?string $taskGroup, ?array $context): Task
    {
        // If task_group is provided, use it as the task name
        if ($taskGroup) {
            $task = $workflow->tasks()->where('name', $taskGroup)->first();
            
            if (!$task) {
                // Create task with the group name
                $task = $workflow->tasks()->create([
                    'name' => $taskGroup,
                    'description' => 'Auto-created task for API execution group: ' . $taskGroup,
                    'input_schema' => [], // Will be inferred from executions
                ]);

                Log::info('Created new task for API execution', [
                    'workflow_id' => $workflow->id,
                    'task_name' => $taskGroup,
                ]);
            }

            return $task;
        }

        // If no task_group, create a generic task name based on service and operation
        $taskName = 'api-task';
        if (isset($context['service'])) {
            $taskName = $context['service'];
            if (isset($context['operation'])) {
                $taskName .= '-' . $context['operation'];
            }
        }

        // Find or create task with generated name
        $task = $workflow->tasks()->where('name', $taskName)->first();
        
        if (!$task) {
            $task = $workflow->tasks()->create([
                'name' => $taskName,
                'description' => 'Auto-created task for API execution',
                'input_schema' => [],
            ]);

            Log::info('Created new task for API execution', [
                'workflow_id' => $workflow->id,
                'task_name' => $taskName,
                'context' => $context,
            ]);
        }

        return $task;
    }

    /**
     * Get execution status
     */
    public function status(TaskExecution $execution): JsonResponse
    {
        // Load stream events for detailed status
        $execution->load(['streamEvents' => function($query) {
            $query->orderBy('event_timestamp', 'desc')->limit(10);
        }, 'task:id,name']);

        $latestEvents = $execution->streamEvents()
            ->whereIn('event_type', ['workflow_started', 'node_started', 'node_finished', 'workflow_finished'])
            ->orderBy('event_timestamp', 'desc')
            ->limit(5)
            ->get()
            ->map(function($event) {
                return [
                    'event_type' => $event->event_type,
                    'event_timestamp' => $event->event_timestamp,
                    'node_id' => $event->node_id,
                    'summary' => $this->getEventSummary($event),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'execution' => [
                    'id' => $execution->id,
                    'task_execution_id' => $execution->task_execution_id,
                    'task_name' => $execution->task?->name,
                    'status' => $execution->status,
                    'start_time' => $execution->start_time,
                    'end_time' => $execution->end_time,
                    'duration' => $execution->duration,
                    'tokens' => $execution->tokens,
                    'output' => $execution->output,
                    'metadata' => $execution->metadata,
                    'updated_at' => $execution->updated_at,
                ],
                'progress' => [
                    'total_events' => $execution->streamEvents()->count(),
                    'latest_events' => $latestEvents,
                    'is_complete' => in_array($execution->status, ['completed', 'failed']),
                ],
            ],
        ]);
    }


    /**
     * Webhook callback for execution completion (called internally)
     */
    public function handleWebhook(TaskExecution $execution): void
    {
        $webhookService = app(WebhookService::class);
        $webhookService->sendWebhook($execution);
    }

    /**
     * Get event summary for progress tracking
     */
    protected function getEventSummary($event): string
    {
        $data = $event->event_data['data'] ?? [];
        
        switch ($event->event_type) {
            case 'workflow_started':
                return 'Workflow execution started';
            case 'node_started':
                return 'Started: ' . ($data['title'] ?? 'Unknown node');
            case 'node_finished':
                $status = $data['status'] ?? 'unknown';
                $title = $data['title'] ?? 'Unknown node';
                return "Finished: {$title} ({$status})";
            case 'workflow_finished':
                $status = $data['status'] ?? 'unknown';
                return "Workflow completed: {$status}";
            case 'text_chunk':
                $text = $data['text'] ?? '';
                return 'Output: ' . substr($text, 0, 50) . (strlen($text) > 50 ? '...' : '');
            default:
                return ucfirst(str_replace('_', ' ', $event->event_type));
        }
    }

    /**
     * Query executions by task group or context
     */
    public function executions(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'task_group' => 'nullable|string',
            'service' => 'nullable|string',
            'operation' => 'nullable|string',
            'reference_id' => 'nullable|string',
            'status' => 'nullable|in:pending,running,completed,failed',
            'limit' => 'nullable|integer|min:1|max:100',
            'offset' => 'nullable|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'Validation failed',
                'details' => $validator->errors(),
            ], 422);
        }

        $query = TaskExecution::with(['task:id,name,dify_workflow_id', 'task.difyWorkflow:id,name'])
            ->where('metadata->api_execution', true);

        // Filter by task group (task name)
        if ($request->has('task_group')) {
            $query->whereHas('task', function($q) use ($request) {
                $q->where('name', $request->task_group);
            });
        }

        // Filter by service
        if ($request->has('service')) {
            $query->where('metadata->service_name', $request->service);
        }

        // Filter by operation
        if ($request->has('operation')) {
            $query->where('metadata->operation', $request->operation);
        }

        // Filter by reference ID
        if ($request->has('reference_id')) {
            $query->where('metadata->reference_id', $request->reference_id);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Order by most recent first
        $query->orderBy('created_at', 'desc');

        // Pagination
        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);
        
        $total = $query->count();
        $executions = $query->skip($offset)->take($limit)->get();

        $results = $executions->map(function ($execution) {
            return [
                'execution_id' => $execution->id,
                'task_execution_id' => $execution->task_execution_id,
                'workflow_name' => $execution->task->difyWorkflow->name ?? null,
                'task_name' => $execution->task->name ?? null,
                'status' => $execution->status,
                'service' => $execution->metadata['service_name'] ?? null,
                'operation' => $execution->metadata['operation'] ?? null,
                'reference_id' => $execution->metadata['reference_id'] ?? null,
                'start_time' => $execution->start_time,
                'end_time' => $execution->end_time,
                'duration' => $execution->duration,
                'tokens' => $execution->tokens,
                'created_at' => $execution->created_at,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => [
                'executions' => $results,
                'pagination' => [
                    'total' => $total,
                    'limit' => $limit,
                    'offset' => $offset,
                    'has_more' => ($offset + $limit) < $total,
                ],
            ],
        ]);
    }
}
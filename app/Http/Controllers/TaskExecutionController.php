<?php

namespace App\Http\Controllers;

use App\Jobs\RunDifyWorkflow;
use App\Jobs\CheckWorkflowHealth;
use App\Models\Task;
use App\Models\TaskExecution;
use App\Services\WebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class TaskExecutionController extends Controller
{
    public function index(Task $task): Response
    {
        $executions = $task->executions()->latest()->paginate(20);

        return Inertia::render('Tasks/Executions/Index', [
            'task' => $task,
            'executions' => $executions,
        ]);
    }

    public function store(Request $request, Task $task)
    {
        // Use the task's input schema directly (it's already cast to array)
        $inputData = $task->input_schema ?? [];

        Log::info('TaskExecution created', [
            'task_id' => $task->id,
            'task_name' => $task->name,
            'input_data' => $inputData,
        ]);

        // Load the task with its workflow
        $task->load('difyWorkflow');
        
        // Check if workflow can execute
        if (!$task->difyWorkflow) {
            return redirect()->route('tasks.executions.index', $task)
                ->withErrors(['workflow' => 'No workflow associated with this task.']);
        }

        if (!$task->difyWorkflow->canExecute()) {
            // Trigger a health check to update status
            CheckWorkflowHealth::dispatch($task->difyWorkflow);
            
            return redirect()->route('tasks.executions.index', $task)
                ->withErrors([
                    'workflow' => 'Workflow is not available for execution. Status: ' . 
                                 $task->difyWorkflow->status . 
                                 ($task->difyWorkflow->status_message ? ' - ' . $task->difyWorkflow->status_message : '')
                ]);
        }

        $execution = $task->executions()->create([
            'task_execution_id' => (string) Str::uuid(),
            'status' => 'pending',
            'input' => $inputData,
            'start_time' => now(),
        ]);

        RunDifyWorkflow::dispatch($execution);

        return redirect()->route('tasks.executions.index', $task)
            ->with('success', 'Workflow execution started.');
    }

    public function show(Task $task, TaskExecution $execution): Response
    {
        $webhookService = app(WebhookService::class);

        return Inertia::render('Tasks/Executions/Show', [
            'task' => $task,
            'execution' => $execution->load(['streamEvents', 'webhookAttempts']),
            'streamEvents' => $execution->streamEvents()
                ->orderBy('event_timestamp')
                ->get()
                ->groupBy('event_type'),
            'webhookStatus' => $webhookService->getWebhookStatus($execution),
            'webhookAttempts' => $execution->webhookAttempts()
                ->orderBy('attempted_at', 'desc')
                ->get(),
        ]);
    }

    public function status(Task $task, TaskExecution $execution)
    {
        // Load fresh data with stream events
        $execution->load(['streamEvents' => function($query) {
            $query->orderBy('event_timestamp', 'desc')->limit(10);
        }]);
        
        return response()->json([
            'execution' => [
                'id' => $execution->id,
                'status' => $execution->status,
                'start_time' => $execution->start_time,
                'end_time' => $execution->end_time,
                'duration' => $execution->duration,
                'tokens' => $execution->tokens,
                'output' => $execution->output,
                'updated_at' => $execution->updated_at,
            ],
            'stream_events' => $execution->streamEvents->map(function($event) {
                return [
                    'event_type' => $event->event_type,
                    'event_timestamp' => $event->event_timestamp,
                    'data' => $event->event_data,
                ];
            }),
            'stream_events_count' => $execution->streamEvents()->count(),
            'latest_events' => $execution->streamEvents()
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
                }),
        ]);
    }

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
     * Resend webhook for an execution
     */
    public function resendWebhook(Task $task, TaskExecution $execution)
    {
        if (!$execution->hasWebhookUrl()) {
            return response()->json([
                'success' => false,
                'message' => 'This execution does not have a webhook URL configured.',
            ], 400);
        }

        try {
            $webhookService = app(WebhookService::class);
            $attempt = $webhookService->retryWebhook($execution);

            return response()->json([
                'success' => true,
                'message' => 'Webhook resent successfully.',
                'attempt' => [
                    'id' => $attempt->id,
                    'attempt_number' => $attempt->attempt_number,
                    'status' => $attempt->status,
                    'http_status' => $attempt->http_status,
                    'attempted_at' => $attempt->attempted_at,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to resend webhook', [
                'execution_id' => $execution->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend webhook: ' . $e->getMessage(),
            ], 500);
        }
    }
}

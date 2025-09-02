<?php

namespace App\Http\Controllers;

use App\Jobs\RunDifyWorkflow;
use App\Jobs\CheckWorkflowHealth;
use App\Models\Task;
use App\Models\TaskExecution;
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
        $validated = $request->validate([
            'input' => 'nullable|array',
        ]);

        // Debug: Log what input was received
        Log::info('TaskExecution store - input received', [
            'task_id' => $task->id,
            'task_name' => $task->name,
            'task_input_schema' => $task->input_schema,
            'request_input' => $request->input('input'),
            'validated_input' => $validated['input'] ?? null,
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
            'input' => $validated['input'] ?? [],
            'start_time' => now(),
        ]);

        RunDifyWorkflow::dispatch($execution);

        return redirect()->route('tasks.executions.index', $task)
            ->with('success', 'Workflow execution started.');
    }

    public function show(Task $task, TaskExecution $execution): Response
    {
        return Inertia::render('Tasks/Executions/Show', [
            'task' => $task,
            'execution' => $execution->load('streamEvents'),
            'streamEvents' => $execution->streamEvents()
                ->orderBy('event_timestamp')
                ->get()
                ->groupBy('event_type'),
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
}

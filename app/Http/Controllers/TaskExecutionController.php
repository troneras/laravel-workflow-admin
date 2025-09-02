<?php

namespace App\Http\Controllers;

use App\Jobs\RunDifyWorkflow;
use App\Jobs\CheckWorkflowHealth;
use App\Models\Task;
use App\Models\TaskExecution;
use Illuminate\Http\Request;
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
}

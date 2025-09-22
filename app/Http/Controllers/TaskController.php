<?php

namespace App\Http\Controllers;

use App\Models\DifyWorkflow;
use App\Models\Task;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TaskController extends Controller
{
    public function index(): Response
    {
        $tasks = Task::with(['difyWorkflow', 'executions' => function ($query) {
            $query->latest()->limit(1);
        }])->latest()->get();

        $workflows = DifyWorkflow::where('is_active', true)->get();

        return Inertia::render('Tasks/Index', [
            'tasks' => $tasks,
            'workflows' => $workflows,
        ]);
    }

    public function create(): Response
    {
        // Show all active workflows, regardless of health status
        // Users should be able to create tasks even if workflow is temporarily unhealthy
        $workflows = DifyWorkflow::where('is_active', true)->get();

        return Inertia::render('Tasks/Create', [
            'workflows' => $workflows,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dify_workflow_id' => 'required|exists:dify_workflows,id',
            'input_schema' => 'nullable|array',
        ]);

        $task = Task::create($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task created successfully.');
    }

    public function show(Task $task): Response
    {
        $workflows = DifyWorkflow::where('is_active', true)->get();
        
        return Inertia::render('Tasks/Show', [
            'task' => $task->load(['difyWorkflow', 'executions']),
            'workflows' => $workflows,
        ]);
    }

    public function edit(Task $task): Response
    {
        // Show all active workflows, regardless of health status
        // Users should be able to edit tasks even if workflow is temporarily unhealthy
        $workflows = DifyWorkflow::where('is_active', true)->get();

        return Inertia::render('Tasks/Edit', [
            'task' => $task->load('difyWorkflow'),
            'workflows' => $workflows,
        ]);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'dify_workflow_id' => 'required|exists:dify_workflows,id',
            'input_schema' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index')
            ->with('success', 'Task updated successfully.');
    }

    public function destroy(Task $task)
    {
        $task->delete();

        return redirect()->route('tasks.index')
            ->with('success', 'Task deleted successfully.');
    }
}

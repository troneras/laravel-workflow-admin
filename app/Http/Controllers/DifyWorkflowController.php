<?php

namespace App\Http\Controllers;

use App\Jobs\CheckWorkflowHealth;
use App\Models\DifyWorkflow;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DifyWorkflowController extends Controller
{
    public function index(): Response
    {
        $workflows = DifyWorkflow::withCount('tasks')->latest()->get();

        return Inertia::render('DifyWorkflows/Index', [
            'workflows' => $workflows,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('DifyWorkflows/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workflow_id' => 'required|string|unique:dify_workflows',
            'api_key' => 'required|string',
        ]);

        DifyWorkflow::create($validated);

        return redirect()->route('dify-workflows.index')
            ->with('success', 'Dify workflow created successfully.');
    }

    public function edit(DifyWorkflow $difyWorkflow): Response
    {
        return Inertia::render('DifyWorkflows/Edit', [
            'workflow' => $difyWorkflow->makeVisible('api_key'),
        ]);
    }

    public function update(Request $request, DifyWorkflow $difyWorkflow)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'workflow_id' => 'required|string|unique:dify_workflows,workflow_id,' . $difyWorkflow->id,
            'api_key' => 'required|string',
            'is_active' => 'boolean',
        ]);

        $difyWorkflow->update($validated);

        return redirect()->route('dify-workflows.index')
            ->with('success', 'Dify workflow updated successfully.');
    }

    public function destroy(DifyWorkflow $difyWorkflow)
    {
        if ($difyWorkflow->tasks()->exists()) {
            return redirect()->route('dify-workflows.index')
                ->with('error', 'Cannot delete workflow with associated tasks.');
        }

        $difyWorkflow->delete();

        return redirect()->route('dify-workflows.index')
            ->with('success', 'Dify workflow deleted successfully.');
    }

    public function checkHealth(DifyWorkflow $difyWorkflow)
    {
        CheckWorkflowHealth::dispatch($difyWorkflow);

        return redirect()->route('dify-workflows.index')
            ->with('success', 'Workflow health check initiated.');
    }
}

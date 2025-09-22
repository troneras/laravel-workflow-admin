<?php

namespace App\Jobs;

use App\Http\Controllers\Api\WorkflowOrchestratorController;
use App\Models\TaskExecution;
use App\Services\DifyService;
use App\Services\SettingsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunDifyWorkflow implements ShouldQueue
{
    use Queueable;

    public $timeout;

    public function __construct(
        protected TaskExecution $execution
    ) {
        // Set timeout from settings
        try {
            $settingsService = app(SettingsService::class);
            $this->timeout = $settingsService->getJobTimeout();
        } catch (\Exception $e) {
            $this->timeout = 14400; // Fallback to 4 hours
        }
    }

    public function middleware()
    {
        return [];
    }

    public function handle(DifyService $difyService): void
    {
        try {
            $this->execution->update([
                'status' => 'running',
                'start_time' => now(),
            ]);

            $task = $this->execution->task->load('difyWorkflow');
            $workflow = $task->difyWorkflow;

            if (! $workflow || ! $workflow->is_active) {
                throw new \Exception('Workflow not found or inactive');
            }

            // Use streaming execution instead of blocking
            $result = $difyService->runWorkflowStreaming($workflow, $this->execution, [
                'inputs' => $this->execution->input,
                'user' => 'task-'.$task->id,
            ]);

            if ($result['success']) {
                // Streaming execution updates the task execution in real-time
                // Just log the successful completion
                $this->execution->refresh(); // Get latest data

                Log::info('Streaming workflow execution completed successfully', [
                    'execution_id' => $this->execution->id,
                    'task_id' => $task->id,
                    'task_name' => $task->name,
                    'workflow_id' => $workflow->workflow_id,
                    'workflow_name' => $workflow->name,
                    'final_status' => $this->execution->status,
                    'duration_seconds' => $this->execution->duration,
                    'tokens_used' => $this->execution->tokens,
                    'stream_events_count' => $this->execution->streamEvents()->count(),
                ]);
            } else {
                // Handle streaming failure
                $duration = $this->execution->start_time
                    ? now()->diffInSeconds($this->execution->start_time)
                    : 0;

                $this->execution->update([
                    'status' => 'failed',
                    'end_time' => now(),
                    'duration' => $duration,
                    'output' => ['error' => $result['error']],
                ]);

                Log::error('Streaming workflow execution failed', [
                    'execution_id' => $this->execution->id,
                    'task_id' => $task->id,
                    'task_name' => $task->name,
                    'workflow_id' => $workflow->workflow_id,
                    'workflow_name' => $workflow->name,
                    'duration_seconds' => $duration,
                    'error' => $result['error'],
                    'http_status' => $result['status'] ?? null,
                    'input_data' => $this->execution->input,
                ]);
            }

            // Send webhook if this is an API execution
            $this->sendWebhookIfNeeded();
        } catch (\Exception $e) {
            Log::error('RunDifyWorkflow job failed', [
                'execution_id' => $this->execution->id,
                'task_id' => $this->execution->task_id,
                'workflow_id' => $task->difyWorkflow->workflow_id ?? 'unknown',
                'workflow_name' => $task->difyWorkflow->name ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'input_data' => $this->execution->input,
            ]);

            $this->execution->update([
                'status' => 'failed',
                'end_time' => now(),
                'duration' => now()->diffInSeconds($this->execution->start_time),
                'output' => ['error' => $e->getMessage()],
            ]);

            // Send webhook if this is an API execution (even on failure)
            $this->sendWebhookIfNeeded();
        }
    }

    /**
     * Send webhook if this execution was initiated via API
     */
    protected function sendWebhookIfNeeded(): void
    {
        // Check if this is an API execution with webhook URL
        if (! $this->execution->metadata || ! isset($this->execution->metadata['api_execution'])) {
            return;
        }

        try {
            $orchestrator = new WorkflowOrchestratorController;
            $orchestrator->handleWebhook($this->execution);
        } catch (\Exception $e) {
            Log::error('Failed to send webhook after execution completion', [
                'execution_id' => $this->execution->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}

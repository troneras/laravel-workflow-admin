<?php

namespace App\Jobs;

use App\Models\DifyWorkflow;
use App\Services\DifyService;
use App\Services\SettingsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CheckWorkflowHealth implements ShouldQueue
{
    use Queueable;

    public $timeout = 300; // 5 minutes for health checks

    public function __construct(
        public ?DifyWorkflow $workflow = null,
        public bool $force = false
    ) {}

    public function handle(DifyService $difyService): void
    {
        Log::info('CheckWorkflowHealth job started', [
            'workflow_provided' => $this->workflow ? $this->workflow->id : null,
            'timestamp' => now()->toDateTimeString(),
        ]);

        $workflows = $this->workflow
            ? collect([$this->workflow])
            : DifyWorkflow::where('is_active', true)
                ->when(!$this->force, function ($query) {
                    $query->where(function ($query) {
                        $query->whereNull('last_status_check')
                            ->orWhere('last_status_check', '<', now()->subMinutes(15));
                    });
                })
                ->get();

        Log::info('Found workflows to check', [
            'count' => $workflows->count(),
            'workflow_ids' => $workflows->pluck('id')->toArray(),
        ]);

        foreach ($workflows as $workflow) {
            try {
                Log::info('Checking workflow health', [
                    'workflow_id' => $workflow->workflow_id,
                    'name' => $workflow->name,
                ]);

                $result = $difyService->checkWorkflowHealth($workflow);
                
                if ($result['success']) {
                    Log::info('Workflow health check completed successfully', [
                        'workflow_id' => $workflow->workflow_id,
                        'name' => $workflow->name,
                        'status' => $result['status'],
                        'message' => $result['message'],
                    ]);
                } else {
                    Log::warning('Workflow health check failed', [
                        'workflow_id' => $workflow->workflow_id,
                        'name' => $workflow->name,
                        'status' => $result['status'] ?? 'unknown',
                        'error_message' => $result['message'] ?? 'No error message provided',
                        'workflow_status' => $workflow->fresh()->status,
                        'workflow_status_message' => $workflow->fresh()->status_message,
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Workflow health check failed', [
                    'workflow_id' => $workflow->workflow_id,
                    'error' => $e->getMessage(),
                ]);

                $workflow->markAsError('Health check failed: ' . $e->getMessage());
            }
        }
    }
}

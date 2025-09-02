<?php

namespace App\Console\Commands;

use App\Jobs\CheckWorkflowHealth;
use App\Models\DifyWorkflow;
use Illuminate\Console\Command;

class CheckWorkflowHealthCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dify:check-workflow-health {--workflow-id= : Specific workflow ID to check} {--force : Force check all workflows regardless of last check time}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check the health status of Dify workflows';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $workflowId = $this->option('workflow-id');
        
        if ($workflowId) {
            $workflow = DifyWorkflow::where('workflow_id', $workflowId)->first();
            
            if (!$workflow) {
                $this->error("Workflow with ID '{$workflowId}' not found.");
                return 1;
            }
            
            $this->info("Checking health for workflow: {$workflow->name}");
            CheckWorkflowHealth::dispatch($workflow);
        } else {
            if ($this->option('force')) {
                $count = DifyWorkflow::where('is_active', true)->count();
                $this->info("Force checking health for {$count} active workflows...");
                CheckWorkflowHealth::dispatch(null, true);
            } else {
                $count = DifyWorkflow::where('is_active', true)->count();
                $this->info("Checking health for {$count} active workflows...");
                CheckWorkflowHealth::dispatch();
            }
        }
        
        $this->info('Workflow health check jobs have been dispatched.');
        return 0;
    }
}

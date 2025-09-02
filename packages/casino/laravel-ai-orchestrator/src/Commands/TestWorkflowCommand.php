<?php

namespace Casino\LaravelAiOrchestrator\Commands;

use Casino\LaravelAiOrchestrator\Facades\AI;
use Illuminate\Console\Command;

class TestWorkflowCommand extends Command
{
    protected $signature = 'ai-orchestrator:test 
                            {workflow? : Workflow name to test}
                            {--list : List available workflows}
                            {--inputs= : JSON string of inputs}
                            {--wait : Wait for execution to complete}';

    protected $description = 'Test AI Orchestrator workflows';

    public function handle(): int
    {
        try {
            if ($this->option('list')) {
                return $this->listWorkflows();
            }

            $workflowName = $this->argument('workflow');
            if (!$workflowName) {
                $this->error('Please specify a workflow name or use --list to see available workflows');
                return 1;
            }

            return $this->testWorkflow($workflowName);
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
    }

    protected function listWorkflows(): int
    {
        $this->info('Fetching available workflows...');
        
        $workflows = AI::workflows();
        
        if ($workflows->isEmpty()) {
            $this->warn('No workflows found.');
            return 0;
        }

        $this->info("Found {$workflows->count()} workflow(s):");
        $this->newLine();

        foreach ($workflows as $workflow) {
            $status = $workflow->isHealthy() ? '<info>✓</info>' : '<error>✗</error>';
            $this->line("  {$status} <comment>{$workflow->name}</comment> ({$workflow->status})");
            
            if ($workflow->description) {
                $this->line("    {$workflow->description}");
            }

            if (!empty($workflow->exampleInputs)) {
                $this->line('    Example inputs: ' . json_encode($workflow->exampleInputs, JSON_PRETTY_PRINT));
            }
            
            $this->newLine();
        }

        return 0;
    }

    protected function testWorkflow(string $workflowName): int
    {
        $this->info("Testing workflow: {$workflowName}");

        // Parse inputs
        $inputs = [];
        if ($inputsJson = $this->option('inputs')) {
            $inputs = json_decode($inputsJson, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->error('Invalid JSON in --inputs option');
                return 1;
            }
        } else {
            // Prompt for basic inputs
            $inputs = $this->promptForInputs($workflowName);
        }

        $this->info('Inputs: ' . json_encode($inputs, JSON_PRETTY_PRINT));
        $this->info('Executing workflow...');

        $execution = AI::executeByName($workflowName, $inputs);

        $this->info("Execution started:");
        $this->line("  ID: {$execution->executionId}");
        $this->line("  UUID: {$execution->taskExecutionId}");
        $this->line("  Status: {$execution->status}");

        if ($this->option('wait')) {
            $this->info('Waiting for completion...');
            $bar = $this->output->createProgressBar();
            $bar->setFormat('debug');
            $bar->start();

            try {
                $result = AI::waitFor($execution->executionId, 300, 2);
                $bar->finish();
                $this->newLine(2);

                $this->displayResult($result);
            } catch (\Exception $e) {
                $bar->finish();
                $this->newLine();
                $this->error("Wait failed: {$e->getMessage()}");
                return 1;
            }
        } else {
            $this->info('Use --wait to wait for completion, or check status with:');
            $this->line("  php artisan ai-orchestrator:status {$execution->executionId}");
        }

        return 0;
    }

    protected function promptForInputs(string $workflowName): array
    {
        // Get workflow details to see example inputs
        try {
            $workflows = AI::workflows();
            $workflow = $workflows->byName($workflowName);
            
            if ($workflow && !empty($workflow->exampleInputs)) {
                $this->info('Example inputs for this workflow:');
                $this->line(json_encode($workflow->exampleInputs, JSON_PRETTY_PRINT));
                $this->newLine();
            }
        } catch (\Exception $e) {
            // Ignore errors getting workflow details
        }

        // Common input prompts
        $inputs = [];
        
        if ($this->confirm('Add text input?', true)) {
            $inputs['text'] = $this->ask('Text input', 'Hello, world!');
        }

        if ($this->confirm('Add more inputs?', false)) {
            while (true) {
                $key = $this->ask('Input key (empty to finish)');
                if (empty($key)) {
                    break;
                }
                
                $value = $this->ask("Value for '{$key}'");
                $inputs[$key] = $value;
            }
        }

        return $inputs;
    }

    protected function displayResult($result): void
    {
        $this->info('Execution completed:');
        $this->line("  Status: {$result->status}");
        $this->line("  Duration: {$result->getDurationFormatted()}");
        $this->line("  Tokens: {$result->tokens}");

        if ($result->isCompleted() && $result->output) {
            $this->info('Output:');
            if ($outputText = $result->getOutputText()) {
                $this->line($outputText);
            } else {
                $this->line(json_encode($result->output, JSON_PRETTY_PRINT));
            }
        }

        if ($result->isFailed()) {
            $this->error('Execution failed:');
            $this->line($result->getErrorMessage() ?: 'Unknown error');
        }

        if (!empty($result->getLatestEvents())) {
            $this->newLine();
            $this->info('Latest events:');
            foreach ($result->getLatestEvents() as $event) {
                $this->line("  {$event['event_type']}: {$event['summary']}");
            }
        }
    }
}
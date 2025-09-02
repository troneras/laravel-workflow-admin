<?php

namespace App\Services;

use App\Models\DifyWorkflow;
use App\Models\TaskExecution;
use App\Models\WorkflowStreamEvent;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DifyService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.dify.base_url', 'https://api.dify.ai');
    }

    public function runWorkflow(DifyWorkflow $workflow, array $data): array
    {
        try {
            // Check if workflow can execute
            if (!$workflow->canExecute()) {
                return [
                    'success' => false,
                    'error' => 'Workflow is not available for execution. Status: ' . $workflow->status,
                ];
            }

            $url = $this->baseUrl . '/v1/workflows/run';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $workflow->api_key,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'inputs' => $data['inputs'] ?? [],
                'response_mode' => 'blocking',
                'user' => $data['user'] ?? 'default-user',
            ]);

            if ($response->successful()) {
                $responseData = $response->json();
                
                // Mark workflow as active on successful execution
                $workflow->markAsActive('Workflow executed successfully');
                
                Log::info('Dify API workflow execution successful', [
                    'workflow_id' => $workflow->workflow_id,
                    'workflow_name' => $workflow->name,
                    'workflow_run_id' => $responseData['workflow_run_id'] ?? null,
                    'total_tokens' => $responseData['data']['total_tokens'] ?? 0,
                    'execution_time' => $responseData['data']['elapsed_time'] ?? null,
                    'outputs_count' => count($responseData['data']['outputs'] ?? []),
                ]);
                
                return [
                    'success' => true,
                    'data' => $responseData,
                    'workflow_run_id' => $responseData['workflow_run_id'] ?? null,
                ];
            }

            // Handle API errors and update workflow status
            $errorMessage = 'Failed to run workflow. HTTP ' . $response->status();
            if ($response->status() >= 400 && $response->status() < 500) {
                // Parse error response to determine if it's a workflow issue or input issue
                $responseData = json_decode($response->body(), true);
                $errorCode = $responseData['code'] ?? '';
                
                // Don't mark workflow as unhealthy for input validation errors
                $inputValidationErrors = ['invalid_param', 'missing_param', 'validation_error'];
                
                if (!in_array($errorCode, $inputValidationErrors) && $response->status() !== 400) {
                    // Only mark as error for authentication, permissions, workflow not found, etc.
                    if (in_array($response->status(), [401, 403, 404, 422])) {
                        $workflow->markAsError($errorMessage . ': ' . $response->body());
                    }
                }
                // For input validation errors (400, invalid_param), don't affect workflow health
            }

            $responseBody = $response->body();
            $errorDetails = null;
            
            // Try to parse JSON error response  
            if ($responseBody) {
                $decoded = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                    $errorDetails = $decoded['message'];
                }
            }

            Log::error('Dify API workflow execution error', [
                'workflow_id' => $workflow->workflow_id,
                'workflow_name' => $workflow->name,
                'http_status' => $response->status(),
                'error_details' => $errorDetails,
                'response_body' => substr($responseBody, 0, 500),
                'api_endpoint' => $this->baseUrl . '/v1/workflows/run',
                'request_inputs' => $data['inputs'] ?? [],
                'request_user' => $data['user'] ?? 'default-user',
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            // Mark workflow as error on exception
            $workflow->markAsError('Exception during workflow execution: ' . $e->getMessage());
            
            Log::error('Dify service exception', [
                'workflow_id' => $workflow->workflow_id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function runWorkflowStreaming(DifyWorkflow $workflow, TaskExecution $execution, array $data): array
    {
        try {
            // Check if workflow can execute
            if (!$workflow->canExecute()) {
                return [
                    'success' => false,
                    'error' => 'Workflow is not available for execution. Status: ' . $workflow->status,
                ];
            }

            $url = $this->baseUrl . '/v1/workflows/run';
            
            // Make streaming request
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $workflow->api_key,
                'Content-Type' => 'application/json',
                'Accept' => 'text/event-stream',
            ])->timeout(300)->post($url, [
                'inputs' => $data['inputs'] ?? [],
                'response_mode' => 'streaming',
                'user' => $data['user'] ?? 'default-user',
            ]);

            if ($response->successful()) {
                // Process the streaming response
                $this->processStreamingResponse($response->body(), $execution);
                
                // Mark workflow as active on successful execution
                $workflow->markAsActive('Workflow executed successfully via streaming');
                
                Log::info('Dify API streaming workflow execution initiated', [
                    'workflow_id' => $workflow->workflow_id,
                    'workflow_name' => $workflow->name,
                    'execution_id' => $execution->id,
                    'stream_size' => strlen($response->body()),
                ]);
                
                return [
                    'success' => true,
                    'message' => 'Streaming execution completed',
                ];
            }

            // Handle API errors
            $responseBody = $response->body();
            $errorDetails = null;
            
            // Try to parse JSON error response  
            if ($responseBody) {
                $decoded = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                    $errorDetails = $decoded['message'];
                }
            }

            // Handle API errors and update workflow status
            $errorMessage = 'Failed to run streaming workflow. HTTP ' . $response->status();
            
            // Only mark workflow as unhealthy for certain error types
            if ($response->status() >= 400 && $response->status() < 500) {
                // Parse error response to determine if it's a workflow issue or input issue
                $responseData = json_decode($responseBody, true);
                $errorCode = $responseData['code'] ?? '';
                
                // Don't mark workflow as unhealthy for input validation errors
                $inputValidationErrors = ['invalid_param', 'missing_param', 'validation_error'];
                
                if (!in_array($errorCode, $inputValidationErrors) && $response->status() !== 400) {
                    // Only mark as error for authentication, permissions, workflow not found, etc.
                    // Skip 400 Bad Request which is typically input validation
                    if (in_array($response->status(), [401, 403, 404, 422])) {
                        $workflow->markAsError($errorMessage . ': ' . ($errorDetails ?: $responseBody));
                    }
                }
                // For input validation errors (400, invalid_param), don't affect workflow health
            }

            // Determine if this is an input validation error
            $responseData = json_decode($responseBody, true);
            $errorCode = $responseData['code'] ?? '';
            $isInputValidationError = in_array($errorCode, ['invalid_param', 'missing_param', 'validation_error']) || $response->status() === 400;
            
            Log::error('Dify API streaming workflow execution error', [
                'workflow_id' => $workflow->workflow_id,
                'workflow_name' => $workflow->name,
                'execution_id' => $execution->id,
                'http_status' => $response->status(),
                'error_code' => $errorCode,
                'is_input_validation_error' => $isInputValidationError,
                'workflow_marked_unhealthy' => !$isInputValidationError && in_array($response->status(), [401, 403, 404, 422]),
                'error_details' => $errorDetails,
                'response_body' => substr($responseBody, 0, 500),
                'api_endpoint' => $url,
                'request_inputs' => $data['inputs'] ?? [],
                'request_user' => $data['user'] ?? 'default-user',
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
                'status' => $response->status(),
            ];
            
        } catch (\Exception $e) {
            // Mark workflow as error on exception
            $workflow->markAsError('Exception during streaming workflow execution: ' . $e->getMessage());
            
            Log::error('Dify streaming service exception', [
                'workflow_id' => $workflow->workflow_id,
                'execution_id' => $execution->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    protected function processStreamingResponse(string $streamData, TaskExecution $execution): void
    {
        $lines = explode("\n", $streamData);
        $buffer = '';
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip empty lines and comments
            if (empty($line) || str_starts_with($line, ':')) {
                continue;
            }
            
            // Handle data lines
            if (str_starts_with($line, 'data: ')) {
                $eventData = substr($line, 6);
                
                // Skip ping events and empty data
                if ($eventData === '' || $eventData === '[DONE]') {
                    continue;
                }
                
                try {
                    $decoded = json_decode($eventData, true);
                    if (json_last_error() === JSON_ERROR_NONE && isset($decoded['event'])) {
                        $this->storeStreamEvent($decoded, $execution);
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to parse stream event', [
                        'execution_id' => $execution->id,
                        'raw_data' => $eventData,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
    }

    protected function storeStreamEvent(array $eventData, TaskExecution $execution): void
    {
        try {
            $event = WorkflowStreamEvent::create([
                'task_execution_id' => $execution->id,
                'event_type' => $eventData['event'],
                'task_id' => $eventData['task_id'] ?? null,
                'workflow_run_id' => $eventData['workflow_run_id'] ?? null,
                'node_id' => $eventData['data']['node_id'] ?? null,
                'event_data' => $eventData,
                'event_timestamp' => isset($eventData['data']['created_at']) 
                    ? now()->setTimestamp($eventData['data']['created_at'])
                    : now(),
            ]);

            // Update execution based on event type
            $this->updateExecutionFromEvent($eventData, $execution);
            
            Log::debug('Stream event stored', [
                'execution_id' => $execution->id,
                'event_type' => $eventData['event'],
                'event_id' => $event->id,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to store stream event', [
                'execution_id' => $execution->id,
                'event_data' => $eventData,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function updateExecutionFromEvent(array $eventData, TaskExecution $execution): void
    {
        switch ($eventData['event']) {
            case 'workflow_started':
                $execution->update([
                    'task_execution_id' => $eventData['workflow_run_id'] ?? $execution->task_execution_id,
                    'start_time' => now(),
                    'status' => 'running',
                ]);
                break;
                
            case 'workflow_finished':
                $finishedData = $eventData['data'] ?? [];
                $startTime = $execution->start_time ?: now();
                
                // Map Dify status to our database enum values
                $difyStatus = $finishedData['status'] ?? 'succeeded';
                $mappedStatus = match($difyStatus) {
                    'succeeded' => 'completed',
                    'failed' => 'failed',
                    'stopped' => 'failed',
                    default => 'completed',
                };
                
                Log::debug('Workflow finished - status mapping', [
                    'execution_id' => $execution->id,
                    'dify_status' => $difyStatus,
                    'mapped_status' => $mappedStatus,
                    'total_tokens' => $finishedData['total_tokens'] ?? 0,
                ]);
                
                $execution->update([
                    'status' => $mappedStatus,
                    'end_time' => now(),
                    'duration' => $finishedData['elapsed_time'] ?? now()->diffInSeconds($startTime),
                    'output' => $finishedData['outputs'] ?? [],
                    'tokens' => $finishedData['total_tokens'] ?? 0,
                    'track' => $execution->streamEvents()->get()->toArray(),
                ]);
                break;
        }
    }

    public function checkWorkflowHealth(DifyWorkflow $workflow): array
    {
        try {
            // Validate credentials by making a simple API call that doesn't execute the workflow
            // We'll use the workflow run endpoint with invalid parameters to test auth without executing
            $url = $this->baseUrl . '/v1/workflows/run';
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $workflow->api_key,
                'Content-Type' => 'application/json',
            ])->post($url, [
                // Send minimal data to test authentication
                'response_mode' => 'blocking',
                'user' => 'health-check',
            ]);

            // Check different response scenarios
            if ($response->successful()) {
                // Unexpected success - this means the workflow actually ran with empty inputs
                $workflow->markAsActive('Health check successful (workflow executed)');
                
                return [
                    'success' => true,
                    'status' => 'active',  
                    'message' => 'Workflow is healthy and executable',
                ];
            } elseif ($response->status() === 400) {
                // Bad request usually means auth is OK but missing required inputs - this is expected
                $responseBody = $response->body();
                $decoded = json_decode($responseBody, true);
                
                // If we get a validation error about inputs, auth is working
                if (json_last_error() === JSON_ERROR_NONE && 
                    isset($decoded['message']) && 
                    (str_contains($decoded['message'], 'input') || str_contains($decoded['message'], 'payload'))) {
                    
                    $workflow->markAsActive('Health check successful (auth validated)');
                    
                    return [
                        'success' => true,
                        'status' => 'active',
                        'message' => 'Workflow credentials are valid',
                    ];
                }
            } elseif ($response->status() === 401) {
                // Unauthorized - invalid API key
                $workflow->markAsError('Invalid API credentials');
                
                return [
                    'success' => false,
                    'status' => 'error',
                    'message' => 'Health check failed: Invalid API credentials',
                ];
            }

            // Handle API errors with detailed information
            $responseBody = $response->body();
            $errorMessage = 'Health check failed. HTTP ' . $response->status();
            
            // Try to parse JSON error response
            $errorDetails = null;
            if ($responseBody) {
                $decoded = json_decode($responseBody, true);
                if (json_last_error() === JSON_ERROR_NONE && isset($decoded['message'])) {
                    $errorDetails = $decoded['message'];
                    $errorMessage .= ': ' . $errorDetails;
                } else {
                    $errorMessage .= ': ' . substr($responseBody, 0, 200);
                }
            }

            if ($response->status() >= 400 && $response->status() < 500) {
                $workflow->markAsError($errorMessage);
            }

            Log::warning('Dify API health check failed', [
                'workflow_id' => $workflow->workflow_id,
                'workflow_name' => $workflow->name,
                'http_status' => $response->status(),
                'error_details' => $errorDetails,
                'response_body' => substr($responseBody, 0, 500),
                'api_endpoint' => $this->baseUrl . '/v1/workflows',
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'message' => $errorMessage,
            ];
        } catch (\Exception $e) {
            $workflow->markAsError('Health check exception: ' . $e->getMessage());
            
            Log::error('Workflow health check failed', [
                'workflow_id' => $workflow->workflow_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getWorkflowStatus(DifyWorkflow $workflow, string $workflowRunId): array
    {
        try {
            $url = $this->baseUrl . '/v1/workflows/run/' . $workflowRunId;
            
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $workflow->api_key,
            ])->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Failed to get workflow status',
                'status' => $response->status(),
            ];
        } catch (\Exception $e) {
            Log::error('Dify service exception', [
                'workflow_run_id' => $workflowRunId,
                'message' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
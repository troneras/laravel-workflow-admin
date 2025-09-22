<?php

namespace App\Services;

use App\Models\TaskExecution;
use App\Models\WebhookAttempt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WebhookService
{
    /**
     * Send webhook for task execution with tracking
     */
    public function sendWebhook(TaskExecution $execution, bool $isRetry = false): ?WebhookAttempt
    {
        $webhookUrl = $execution->getWebhookUrl();

        if (!$webhookUrl) {
            return null;
        }

        // Determine attempt number
        $attemptNumber = $isRetry
            ? ($execution->webhookAttempts()->max('attempt_number') ?? 0) + 1
            : 1;

        // Prepare payload
        $payload = $this->buildWebhookPayload($execution);

        // Create webhook attempt record
        $attempt = $execution->webhookAttempts()->create([
            'webhook_url' => $webhookUrl,
            'payload' => $payload,
            'attempt_number' => $attemptNumber,
            'status' => 'pending',
            'attempted_at' => now(),
        ]);

        try {
            $startTime = microtime(true);

            $response = Http::timeout(30)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Laravel-Workflow-Admin/1.0',
                    'X-Webhook-Attempt' => $attemptNumber,
                    'X-Execution-ID' => $execution->task_execution_id,
                ])
                ->post($webhookUrl, $payload);

            $responseTime = (microtime(true) - $startTime) * 1000;

            // Update attempt with response
            $attempt->update([
                'http_status' => $response->status(),
                'response_body' => $response->body(),
                'response_time_ms' => $responseTime,
                'status' => $response->successful() ? 'success' : 'failed',
            ]);

            if ($response->successful()) {
                Log::info('Webhook sent successfully', [
                    'execution_id' => $execution->id,
                    'webhook_url' => $webhookUrl,
                    'attempt_number' => $attemptNumber,
                    'response_status' => $response->status(),
                    'response_time_ms' => $responseTime,
                ]);
            } else {
                Log::warning('Webhook failed with HTTP error', [
                    'execution_id' => $execution->id,
                    'webhook_url' => $webhookUrl,
                    'attempt_number' => $attemptNumber,
                    'response_status' => $response->status(),
                    'response_body' => $response->body(),
                ]);
            }

        } catch (\Exception $e) {
            $responseTime = (microtime(true) - $startTime) * 1000;

            $attempt->update([
                'error_message' => $e->getMessage(),
                'response_time_ms' => $responseTime,
                'status' => 'failed',
            ]);

            Log::error('Webhook failed with exception', [
                'execution_id' => $execution->id,
                'webhook_url' => $webhookUrl,
                'attempt_number' => $attemptNumber,
                'error' => $e->getMessage(),
            ]);
        }

        return $attempt;
    }

    /**
     * Build webhook payload
     */
    protected function buildWebhookPayload(TaskExecution $execution): array
    {
        return [
            'execution_id' => $execution->id,
            'task_execution_id' => $execution->task_execution_id,
            'status' => $execution->status,
            'output' => $execution->output,
            'duration' => $execution->duration,
            'tokens' => $execution->tokens,
            'service_name' => $execution->metadata['service_name'] ?? null,
            'operation' => $execution->metadata['operation'] ?? null,
            'reference_id' => $execution->metadata['reference_id'] ?? null,
            'started_at' => $execution->start_time?->toISOString(),
            'completed_at' => $execution->end_time?->toISOString(),
            'created_at' => $execution->created_at->toISOString(),
            'webhook_sent_at' => now()->toISOString(),
        ];
    }

    /**
     * Retry webhook for failed attempts
     */
    public function retryWebhook(TaskExecution $execution): ?WebhookAttempt
    {
        if (!$execution->hasWebhookUrl()) {
            throw new \InvalidArgumentException('Execution does not have a webhook URL');
        }

        return $this->sendWebhook($execution, true);
    }

    /**
     * Get webhook status summary for execution
     */
    public function getWebhookStatus(TaskExecution $execution): array
    {
        if (!$execution->hasWebhookUrl()) {
            return [
                'has_webhook' => false,
                'webhook_url' => null,
                'total_attempts' => 0,
                'last_attempt' => null,
                'last_status' => null,
            ];
        }

        $attempts = $execution->webhookAttempts()
            ->orderBy('attempted_at', 'desc')
            ->get();

        $lastAttempt = $attempts->first();

        return [
            'has_webhook' => true,
            'webhook_url' => $execution->getWebhookUrl(),
            'total_attempts' => $attempts->count(),
            'successful_attempts' => $attempts->where('status', 'success')->count(),
            'failed_attempts' => $attempts->where('status', 'failed')->count(),
            'last_attempt' => $lastAttempt ? [
                'id' => $lastAttempt->id,
                'attempt_number' => $lastAttempt->attempt_number,
                'status' => $lastAttempt->status,
                'http_status' => $lastAttempt->http_status,
                'response_time_ms' => $lastAttempt->response_time_ms,
                'attempted_at' => $lastAttempt->attempted_at,
                'error_message' => $lastAttempt->error_message,
            ] : null,
            'last_status' => $lastAttempt?->status,
            'is_retryable' => $lastAttempt?->isRetryable() ?? false,
        ];
    }
}
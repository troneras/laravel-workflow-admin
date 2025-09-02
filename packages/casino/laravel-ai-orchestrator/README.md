# Laravel AI Orchestrator

A Laravel package for integrating with AI workflow orchestration services. This package provides a clean, fluent interface for executing AI workflows from any Laravel microservice without the need for local infrastructure.

## Features

- **Clean API**: Simple, intuitive facade interface (`AI::translate()`, `AI::summarize()`, etc.)
- **Workflow Discovery**: Automatically discover available workflows from the orchestrator service
- **Async Execution**: Execute workflows with webhook callbacks for non-blocking operations
- **Synchronous Support**: Wait for execution completion with polling
- **Fluent Interface**: Chain methods for complex workflow configurations
- **Error Handling**: Comprehensive exception handling with meaningful error messages
- **Caching**: Built-in caching for workflows and execution results
- **Logging**: Configurable logging for all operations
- **Type Safety**: Full type hints and phpDoc annotations

## Installation

1. **Install the package** (when published to Packagist):
```bash
composer require casino/laravel-ai-orchestrator
```

2. **Publish configuration**:
```bash
php artisan vendor:publish --tag=ai-orchestrator-config
```

3. **Configure environment variables**:
```env
# Required
AI_ORCHESTRATOR_URL=http://your-orchestrator-service:8088
AI_ORCHESTRATOR_SERVICE_NAME=your-microservice-name

# Optional
AI_ORCHESTRATOR_API_KEY=your-api-key
AI_ORCHESTRATOR_TIMEOUT=30
AI_ORCHESTRATOR_CALLBACK_URL=http://your-service.com/webhooks/ai-orchestrator
AI_ORCHESTRATOR_WEBHOOK_SECRET=your-webhook-secret
```

## Quick Start

### Simple Usage

```php
use Casino\LaravelAiOrchestrator\Facades\AI;

// Translate text - automatically groups under "translations" task group
$execution = AI::translate("Hello world", "spanish");
echo "Execution ID: " . $execution->executionId;

// Summarize content - groups under "summaries"
$execution = AI::summarize($longText);

// Analyze sentiment - groups under "analyses" with operation context
$execution = AI::analyze($text, "sentiment");
```

### Workflow Discovery

```php
// Get all available workflows
$workflows = AI::workflows();

foreach ($workflows as $workflow) {
    echo $workflow->name . " - " . $workflow->description . "\n";
    echo "Status: " . $workflow->status . "\n";
    echo "Inputs: " . json_encode($workflow->exampleInputs) . "\n";
}

// Find specific workflow
$translateWorkflow = $workflows->byName('translate');
if ($translateWorkflow && $translateWorkflow->canExecute()) {
    // Use the workflow
}
```

### Execute with Task Groups

```php
// Execute with automatic task creation and grouping
$execution = AI::execute('translate', [
    'text' => 'Hello world',
    'target_language' => 'spanish'
], 'cms-translations'); // All CMS translations grouped together

// Execute with context for better tracking
$execution = AI::execute('summarize', [
    'content' => $article
], 'article-summaries', [
    'service' => 'cms',
    'operation' => 'daily-digest',
    'reference_id' => 'article-123'
]);

// Query all executions for a task group
$results = AI::executionsByTaskGroup('cms-translations');
foreach ($results['executions'] as $exec) {
    echo "Translation {$exec['execution_id']}: {$exec['status']}\n";
}
```

### Asynchronous Execution with Webhooks

```php
// Set up webhook endpoint in your routes/web.php
Route::post('/webhooks/ai-orchestrator', function (Request $request) {
    $executionId = $request->input('execution_id');
    $status = $request->input('status');
    $output = $request->input('output');
    
    // Handle completed workflow
    if ($status === 'completed') {
        // Process the results
        ProcessAiResult::dispatch($executionId, $output);
    }
});

// Execute with webhook callback
$execution = AI::executeByName('translate', [
    'text' => 'Hello world',
    'target_language' => 'spanish'
], null, route('webhooks.ai-orchestrator'), 'unique-reference-123');
```

### Synchronous Execution (Wait for Results)

```php
// Execute and wait for completion
$result = AI::workflow('translate')
    ->inputs(['text' => 'Hello world', 'target_language' => 'spanish'])
    ->wait(300); // 5-minute timeout

if ($result->isCompleted()) {
    echo "Translation: " . $result->getOutputText();
} else {
    echo "Failed: " . $result->getErrorMessage();
}
```

### Fluent Interface

```php
// Complex workflow execution with task grouping and context
AI::workflow('document-analysis')
    ->inputs(['document' => $documentContent])
    ->taskGroup('legal-documents')  // Group all legal doc analyses
    ->context([
        'service' => 'legal-dept',
        'operation' => 'contract-review',
        'reference_id' => 'contract-2024-001'
    ])
    ->webhook('https://myservice.com/webhook')
    ->then(function ($result) {
        // Success callback
        Log::info('Analysis completed', $result->toArray());
    })
    ->catch(function ($error) {
        // Error callback
        Log::error('Analysis failed', ['error' => $error->getMessage()]);
    })
    ->execute();

// Simpler fluent syntax with operation tracking
AI::workflow('translate')
    ->inputs(['text' => $content, 'target_language' => 'fr'])
    ->taskGroup('product-descriptions')
    ->operation('catalog-update-2024')
    ->reference('product-456')
    ->dispatch();
```

### Monitoring Execution Status

```php
// Check execution status
$result = AI::status($executionId);

echo "Status: " . $result->status . "\n";
echo "Progress: " . $result->getProgressPercentage() . "%\n";

if ($result->isRunning()) {
    echo "Latest events:\n";
    foreach ($result->getLatestEvents() as $event) {
        echo "  " . $event['summary'] . "\n";
    }
}

// Poll until completion
$finalResult = AI::waitFor($executionId, $timeoutSeconds = 300, $pollInterval = 2);
```

## Advanced Usage

### Query Executions

```php
// Query all executions for your service
$results = AI::executionsByService('cms');

// Query by service and operation
$results = AI::executionsByService('cms', 'daily-digest');

// Query with filters
$results = AI::executions([
    'task_group' => 'article-summaries',
    'status' => 'completed',
    'limit' => 50
]);

// Pagination
$page1 = AI::executions(['limit' => 20, 'offset' => 0]);
$page2 = AI::executions(['limit' => 20, 'offset' => 20]);
```

### Custom Service Configuration

```php
// Set custom service name and callback URL
$result = AI::setServiceName('my-custom-service')
    ->setCallbackUrl('https://myapp.com/custom-webhook')
    ->execute('translate', ['text' => 'Hello'], 'custom-translations');
```

### Error Handling

```php
use Casino\LaravelAiOrchestrator\Exceptions\{
    ApiException,
    ExecutionException,
    WorkflowNotFoundException,
    TimeoutException
};

try {
    $execution = AI::executeByName('nonexistent-workflow', ['input' => 'data']);
} catch (WorkflowNotFoundException $e) {
    Log::error('Workflow not found: ' . $e->getMessage());
} catch (ExecutionException $e) {
    Log::error('Execution failed: ' . $e->getMessage());
} catch (ApiException $e) {
    Log::error('API error: ' . $e->getMessage());
} catch (TimeoutException $e) {
    Log::error('Operation timed out: ' . $e->getMessage());
}
```

### Caching

The package automatically caches workflows and completed executions. Configure in `config/ai-orchestrator.php`:

```php
'cache' => [
    'enabled' => true,
    'store' => 'redis', // null for default
    'prefix' => 'ai-orchestrator',
    'ttl' => [
        'workflows' => 300, // 5 minutes
        'executions' => 60, // 1 minute
    ],
],
```

## Console Commands

### Test Workflows
```bash
# List available workflows
php artisan ai-orchestrator:test --list

# Test a workflow interactively
php artisan ai-orchestrator:test translate

# Test with specific inputs
php artisan ai-orchestrator:test translate --inputs='{"text":"Hello","target_language":"spanish"}' --wait
```

## Configuration

The package configuration file `config/ai-orchestrator.php` contains all available options:

```php
return [
    'orchestrator_url' => env('AI_ORCHESTRATOR_URL'),
    'api' => [
        'key' => env('AI_ORCHESTRATOR_API_KEY'),
        'timeout' => env('AI_ORCHESTRATOR_TIMEOUT', 30),
        'retry' => [
            'times' => env('AI_ORCHESTRATOR_RETRY_TIMES', 3),
            'delay' => env('AI_ORCHESTRATOR_RETRY_DELAY', 100),
        ],
    ],
    'webhook' => [
        'enabled' => env('AI_ORCHESTRATOR_WEBHOOK_ENABLED', true),
        'secret' => env('AI_ORCHESTRATOR_WEBHOOK_SECRET'),
        'path' => env('AI_ORCHESTRATOR_WEBHOOK_PATH', '/webhooks/ai-orchestrator'),
    ],
    'service' => [
        'name' => env('AI_ORCHESTRATOR_SERVICE_NAME', env('APP_NAME')),
        'callback_url' => env('AI_ORCHESTRATOR_CALLBACK_URL'),
    ],
    'logging' => [
        'enabled' => env('AI_ORCHESTRATOR_LOG_ENABLED', true),
        'channel' => env('AI_ORCHESTRATOR_LOG_CHANNEL'),
        'level' => env('AI_ORCHESTRATOR_LOG_LEVEL', 'info'),
    ],
    'cache' => [
        'enabled' => env('AI_ORCHESTRATOR_CACHE_ENABLED', true),
        'store' => env('AI_ORCHESTRATOR_CACHE_STORE'),
        'prefix' => env('AI_ORCHESTRATOR_CACHE_PREFIX', 'ai-orchestrator'),
        'ttl' => [
            'workflows' => env('AI_ORCHESTRATOR_CACHE_WORKFLOWS_TTL', 300),
            'executions' => env('AI_ORCHESTRATOR_CACHE_EXECUTIONS_TTL', 60),
        ],
    ],
];
```

## API Reference

### AI Facade Methods

- `AI::workflows()` - Get all available workflows
- `AI::execute($workflow, $inputs, $taskGroup, $context, $webhookUrl)` - Execute with flexible task management
- `AI::executeByTaskId($taskId, $inputs, $webhookUrl, $referenceId)` - Legacy: Execute by task ID
- `AI::status($executionId)` - Get execution status
- `AI::waitFor($executionId, $timeout, $pollInterval)` - Wait for completion
- `AI::executions($filters)` - Query executions with filters
- `AI::executionsByTaskGroup($taskGroup, $filters)` - Query by task group
- `AI::executionsByService($service, $operation, $filters)` - Query by service
- `AI::setServiceName($name)` - Set service name
- `AI::setCallbackUrl($url)` - Set callback URL

### Convenience Methods

- `AI::translate($text, $targetLanguage)` - Translate text (task group: "translations")
- `AI::summarize($content, $maxWords)` - Summarize content (task group: "summaries")
- `AI::analyze($content, $analysisType)` - Analyze content (task group: "analyses")
- `AI::generate($prompt, $options)` - Generate content (task group: "generations")
- `AI::extract($content, $fields)` - Extract information (task group: "extractions")

### Fluent Interface

- `AI::workflow($name)->inputs($data)->taskGroup($group)->wait()` - Fluent execution
- `->taskGroup($group)` - Set task group for organization
- `->context($array)` - Set execution context
- `->operation($name)` - Set operation name
- `->webhook($url)` - Set webhook URL
- `->reference($id)` - Set reference ID
- `->then($callback)` - Success callback
- `->catch($callback)` - Error callback
- `->dispatch()` - Execute asynchronously
- `->wait($timeout)` - Execute and wait

## Requirements

- PHP 8.2+
- Laravel 11.0+ or 12.0+
- Guzzle HTTP client 7.0+

## License

MIT License. See LICENSE file for details.
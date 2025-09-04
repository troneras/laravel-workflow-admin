# Laravel Dify Integration 

A modern Laravel 12 application that integrates with Dify AI workflows, providing a complete task management system with real-time execution monitoring.

## 🚀 Features

- **Dify Workflow Integration**: Connect and manage Dify AI workflows
- **Task Management**: Create tasks linked to workflows with configurable input schemas
- **Real-time Execution Monitoring**: Live updates on workflow execution progress
- **Stream Event Tracking**: Capture and display detailed workflow execution events
- **Health Monitoring**: Automatic workflow health checks with intelligent error classification
- **REST API for Microservices**: External API for workflow execution with automatic task grouping
- **Laravel Package**: Ready-to-use package for Laravel microservices integration
- **Modern UI**: Built with Vue.js, TypeScript, and Shadcn-vue components
- **Type-safe Routing**: Laravel Wayfinder integration for seamless backend-frontend communication

## 🛠 Tech Stack

### Backend
- **Laravel 12** - PHP framework
- **SQLite** - Database
- **Laravel Queues** - Background job processing
- **Pest** - Modern testing framework

### Frontend
- **Vue.js 3** - Frontend framework
- **TypeScript** - Type safety
- **Inertia.js** - SPA-like experience without API complexity
- **Shadcn-vue** - Modern component library
- **Tailwind CSS 4** - Utility-first CSS framework
- **Vite** - Fast build tool

### Integration
- **Laravel Wayfinder** - Type-safe route generation
- **Dify API** - AI workflow execution with streaming support

## 📋 Prerequisites

- PHP 8.2+
- Node.js 18+
- Composer
- SQLite

## 🔧 Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd laravel-dify-example
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   ```

6. **Build frontend assets**
   ```bash
   npm run build
   ```

## 🚀 Development

### Start development environment
```bash
# All-in-one development server (Laravel + Queue + Vite + Logs)
composer run dev

# Or individual services:
php artisan serve          # Laravel server
php artisan queue:work     # Queue worker
npm run dev               # Vite dev server
```

### Build for production
```bash
npm run build              # Production build
npm run build:ssr         # Build with SSR support
```

### Code quality
```bash
npm run lint              # ESLint
npm run format            # Prettier formatting
composer run test         # Run Pest tests
```

## 📁 Project Structure

```
├── app/
│   ├── Http/Controllers/     # Laravel controllers
│   │   ├── Api/
│   │   │   └── WorkflowOrchestratorController.php  # API for microservices
│   │   ├── DifyWorkflowController.php
│   │   ├── TaskController.php
│   │   └── TaskExecutionController.php
│   ├── Models/              # Eloquent models
│   │   ├── DifyWorkflow.php
│   │   ├── Task.php
│   │   ├── TaskExecution.php
│   │   └── WorkflowStreamEvent.php
│   ├── Services/            # Business logic
│   │   └── DifyService.php
│   └── Jobs/               # Background jobs
│       ├── RunDifyWorkflow.php
│       └── CheckWorkflowHealth.php
├── packages/casino/laravel-ai-orchestrator/  # Laravel package for clients
├── resources/js/
│   ├── Pages/              # Inertia page components
│   │   ├── DifyWorkflows/
│   │   ├── Tasks/
│   │   └── Tasks/Executions/
│   ├── components/ui/      # Shadcn-vue components
│   └── routes/            # Wayfinder generated routes
└── docs/                  # Documentation
    └── dify/             # Dify API documentation
```

## 🔄 Workflow

### 1. Workflow Management
- Import Dify workflows via API credentials
- Monitor workflow health status
- Automatic health checks every 15 minutes

### 2. Task Creation
- Create tasks linked to specific workflows
- Define input schemas (JSON format)
- Configure task parameters and validation

### 3. Task Execution
- Trigger workflow execution with real-time monitoring
- Stream events captured and stored
- Live UI updates via polling
- Intelligent error handling

### 4. Monitoring & Debugging
- Detailed execution logs
- Stream event timeline
- Health status indicators
- Error classification (input vs system errors)

## 🔧 Configuration

### Dify Integration
1. **Add Dify Workflow**:
   - Go to Dify Workflows → Create
   - Enter workflow name, description, and API credentials
   - System automatically validates workflow health

2. **Create Task**:
   - Go to Tasks → Create
   - Select a healthy workflow
   - Define input schema (JSON format)
   - Example schema:
     ```json
     [
       {
         "name": "question",
         "type": "string", 
         "required": true,
         "default": "What can you tell me?"
       }
     ]
     ```

3. **Execute Task**:
   - Navigate to task executions
   - Click "Run Workflow"
   - Monitor real-time progress
   - View detailed stream events

### Queue Configuration
The application uses Laravel queues for background processing:
```bash
# Start queue worker for production
php artisan queue:work

# Or use supervisor for production deployment
```

### Health Monitoring
Workflows are automatically checked for health:
- **Active**: Workflow responding correctly
- **Error**: System-level issues (auth, permissions, not found)
- **Syncing**: Temporary state during health checks

**Important**: Input validation errors (400, `invalid_param`) do NOT mark workflows as unhealthy.

## 🧪 Testing

Run the test suite with Pest:
```bash
composer run test           # All tests
php artisan test --filter=ExampleTest  # Specific test
```

## 📊 Key Features Detail

### Real-time Execution Monitoring
- **Polling System**: Frontend polls execution status every 2 seconds
- **Stream Events**: Captures workflow_started, node_started, node_finished, etc.
- **Auto-stop**: Polling stops when execution completes
- **Type-safe API**: Uses Wayfinder for route generation

### Intelligent Error Handling
- **Input Errors**: 400 status with `invalid_param` → Task fails, workflow stays healthy
- **System Errors**: 401/403/404/422 → Workflow marked as unhealthy
- **Exception Handling**: Proper error logging and workflow status management

### Modern Architecture
- **Inertia.js**: No API endpoints needed, seamless SPA experience
- **Wayfinder**: Type-safe route generation between Laravel and TypeScript
- **Component-driven**: Shadcn-vue for consistent UI components
- **Reactive**: Vue 3 Composition API with TypeScript

## 📝 API Integration

### External Microservice API

The application provides a REST API for external microservices to execute workflows:

#### Execute Workflow
```bash
POST /api/orchestrator/execute
{
    "workflow": "translate",              # Workflow name or ID
    "task_group": "cms-translations",     # Optional: Group related executions
    "inputs": {
        "text": "Hello world",
        "target_language": "spanish"
    },
    "context": {                          # Optional: Execution context
        "service": "cms",
        "operation": "bulk-translate",
        "reference_id": "batch-001"
    },
    "webhook_url": "https://cms.example.com/webhook"  # Optional
}
```

#### Query Executions
```bash
GET /api/orchestrator/executions?task_group=cms-translations&status=completed
```

#### Check Status
```bash
GET /api/orchestrator/executions/{execution_id}/status
```

#### Laravel Package
For Laravel applications, use the [AI Orchestrator package](packages/casino/laravel-ai-orchestrator):
```php
use Casino\LaravelAiOrchestrator\Facades\AI;

// Simple execution
AI::translate("Hello world", "spanish");

// With task grouping
AI::execute('summarize', ['content' => $article], 'article-summaries');

// Query executions
$results = AI::executionsByTaskGroup('cms-translations');
```

### Dify Workflow Execution
The application integrates with Dify's streaming API:

```php
// Execute workflow with streaming
$result = $difyService->runWorkflowStreaming($workflow, $execution, [
    'inputs' => ['question' => 'What is AI?'],
    'user' => 'user-123',
]);
```

### Stream Event Processing
Events are parsed and stored in real-time:
- `workflow_started`
- `node_started` 
- `node_finished`
- `workflow_finished`
- `text_chunk` (for output)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Run tests and linting
5. Submit a pull request

## 📄 License

[Add your license information here]

## 🆘 Support

For support and questions:
- Check the documentation in `/docs`
- Review the CLAUDE.md file for development guidance
- Open an issue on GitHub

---

Built with ❤️ using Laravel 12, Vue.js, and Dify AI
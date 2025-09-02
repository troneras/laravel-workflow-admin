# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Development Commands

**Start development environment:**
```bash
composer run dev    # Runs Laravel server, queue worker, logs, and Vite dev server concurrently on port 8088
```

**Alternative development commands:**
```bash
npm run dev         # Vite dev server only
php artisan serve   # Laravel server only (default port 8000)
php artisan queue:work  # Queue worker only
php artisan pail    # Log viewer
```

**Build and quality checks:**
```bash
npm run build       # Production build
npm run build:ssr   # Build with server-side rendering
npm run lint        # ESLint with auto-fix
npm run format      # Prettier formatting
npm run format:check # Check Prettier formatting
composer run test   # Run Pest tests
php artisan test    # Alternative test command
```

**Development with SSR:**
```bash
composer run dev:ssr # Builds SSR then runs all services with SSR server
```

## Architecture Overview

This is a **Laravel 12 application** implementing an **AI Orchestrator** for Dify workflow management. Built with **Vue.js/TypeScript frontend** using **Inertia.js** for seamless SPA-like experience.

### Core Application Features
- **Dify Workflow Management**: Import, monitor, and execute AI workflows with health checking
- **Task Management**: Create tasks linked to workflows with configurable input schemas  
- **Real-time Execution Monitoring**: Stream event processing with live UI updates via polling
- **Background Processing**: Laravel queues for async workflow execution and health monitoring

### Technology Stack
- **Backend**: Laravel 12 with SQLite database, Pest testing, Laravel Queues
- **Frontend**: Vue.js 3 with TypeScript, compiled with Vite
- **Bridge**: Inertia.js for server-driven SPA without API complexity
- **UI**: Shadcn-vue components with Tailwind CSS 4 and Lucide icons
- **Type Safety**: Laravel Wayfinder generates TypeScript route definitions
- **External Integration**: Dify AI platform via HTTP streaming API

## Domain Models & Architecture

### Core Models
- `DifyWorkflow`: Represents imported AI workflows with health status tracking (`active`, `error`, `syncing`)
- `Task`: Configurable tasks linked to workflows with JSON input schemas
- `TaskExecution`: Individual workflow runs with streaming event storage
- `WorkflowStreamEvent`: Real-time events captured during workflow execution

### Key Services
- `DifyService`: Handles all Dify API integration (workflow execution, health checks, streaming)
- `Jobs`: `RunDifyWorkflow` (async execution), `CheckWorkflowHealth` (periodic monitoring)

### Health Management System
Workflows have intelligent health monitoring that distinguishes between:
- **Input validation errors** (400, `invalid_param`) → Task fails but workflow remains healthy
- **System errors** (401, 403, 404, 422) → Workflow marked as unhealthy
- **Health checks**: Every 15 minutes via scheduled command

## File Structure Patterns

### Backend Structure
- `app/Http/Controllers/DifyWorkflowController.php` - Workflow CRUD operations
- `app/Http/Controllers/TaskController.php` - Task management
- `app/Http/Controllers/TaskExecutionController.php` - Execution monitoring with polling endpoints
- `app/Services/DifyService.php` - Core Dify API integration with streaming support
- `app/Models/` - Eloquent models with relationships and business logic
- `app/Jobs/` - Background job classes for async processing

### Frontend Structure
- `resources/js/Pages/` - Inertia page components organized by feature
- `resources/js/Pages/DifyWorkflows/` - Workflow management pages
- `resources/js/Pages/Tasks/` - Task management and execution monitoring
- `resources/js/components/ui/` - Shadcn-vue component library
- `resources/js/routes/` - Wayfinder generated TypeScript route definitions
- `resources/js/wayfinder/` - Type-safe route helpers

### Configuration
- `config/inertia.php` - SSR enabled on port 13714, testing configuration
- `components.json` - Shadcn-vue configuration with Tailwind CSS 4
- `vite.config.ts` - Wayfinder integration, Vue SFC support, Tailwind CSS plugin

## Key Development Patterns

### Type-Safe Routing
Uses Laravel Wayfinder to generate TypeScript definitions from Laravel routes. Routes are available as type-safe functions in the frontend.

### Streaming API Integration  
`DifyService::runWorkflowStreaming()` processes Server-Sent Events, parses JSON event data, and stores events in real-time for UI consumption.

### Real-time UI Updates
Frontend uses polling every 2 seconds to fetch execution status and stream events, automatically stopping when execution completes.

### Queue-Based Processing
Workflow executions run in background jobs to prevent timeouts, with status updates stored in database for frontend polling.

## Testing

Uses **Pest** testing framework (not PHPUnit). Test configuration in `phpunit.xml` with SQLite in-memory database.

**Run specific tests:**
```bash
php artisan test --filter=ExampleTest
composer run test
```

## Important Implementation Notes

- Server runs on port 8088 (not default 8000) when using `composer run dev`
- SSR is enabled and configured for production deployment
- Workflow health checks distinguish between input validation vs system errors
- Stream event processing handles malformed JSON gracefully with logging
- All Dify API interactions include comprehensive error logging and status management
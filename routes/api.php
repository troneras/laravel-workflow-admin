<?php

use App\Http\Controllers\Api\WorkflowOrchestratorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned the "api" middleware group. Make something great!
|
*/

// Workflow Orchestrator API - for external microservices
Route::prefix('orchestrator')->name('orchestrator.')->group(function () {
    
    // Workflow discovery
    Route::get('workflows', [WorkflowOrchestratorController::class, 'workflows'])
        ->name('workflows');
    
    Route::get('workflows/{workflow}', [WorkflowOrchestratorController::class, 'workflow'])
        ->name('workflow');
    
    // Workflow execution
    Route::post('execute', [WorkflowOrchestratorController::class, 'execute'])
        ->name('execute');
    
    // Execution management
    Route::get('executions', [WorkflowOrchestratorController::class, 'executions'])
        ->name('executions');
    
    Route::get('executions/{execution}/status', [WorkflowOrchestratorController::class, 'status'])
        ->name('execution.status');
    
});

// Internal webhook endpoint (called by our jobs when execution completes)
Route::post('internal/webhook/{execution}', [WorkflowOrchestratorController::class, 'handleWebhook'])
    ->name('internal.webhook');
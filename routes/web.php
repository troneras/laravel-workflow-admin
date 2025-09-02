<?php

use App\Http\Controllers\DifyWorkflowController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskExecutionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::resource('dify-workflows', DifyWorkflowController::class)->except(['show'])->middleware(['auth', 'verified']);
Route::post('dify-workflows/{difyWorkflow}/check-health', [DifyWorkflowController::class, 'checkHealth'])
    ->middleware(['auth', 'verified'])
    ->name('dify-workflows.check-health');

Route::resource('tasks', TaskController::class)->middleware(['auth', 'verified']);
Route::resource('tasks.executions', TaskExecutionController::class)->only(['index', 'store', 'show'])->middleware(['auth', 'verified']);

// API endpoint for real-time execution status
Route::get('api/tasks/{task}/executions/{execution}/status', [TaskExecutionController::class, 'status'])
    ->middleware(['auth', 'verified'])
    ->name('tasks.executions.status');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

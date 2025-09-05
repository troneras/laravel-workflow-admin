<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DifyWorkflowController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskExecutionController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::resource('dify-workflows', DifyWorkflowController::class)->except(['show'])->middleware(['auth', 'verified']);
Route::post('dify-workflows/{difyWorkflow}/check-health', [DifyWorkflowController::class, 'checkHealth'])
    ->middleware(['auth', 'verified'])
    ->name('dify-workflows.check-health');

Route::resource('tasks', TaskController::class)->middleware(['auth', 'verified']);
Route::resource('tasks.executions', TaskExecutionController::class)->only(['index', 'store', 'show'])->middleware(['auth', 'verified']);

// Removed: test-queue-metrics, real-time execution status, and queue metrics routes

// Minimal API for dashboard (authenticated)
Route::prefix('api')->middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard/metrics', [DashboardController::class, 'metrics'])
        ->name('dashboard.metrics');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

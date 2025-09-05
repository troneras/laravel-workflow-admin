<?php

namespace App\Http\Controllers;

use App\Services\SettingsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(private SettingsService $settings) {}

    public function index(): Response
    {
        return Inertia::render('Dashboard');
    }

    public function metrics(Request $request): JsonResponse
    {
        // Period filter
        $period = $request->query('period', '1h');

        $since = match ($period) {
            '15m' => now()->subMinutes(15),
            '1h' => now()->subHour(),
            '24h' => now()->subDay(),
            '7d' => now()->subDays(7),
            default => now()->subHour(),
        };

        $limit = match ($period) {
            '15m' => 10,
            '1h' => 10,
            '24h' => 15,
            '7d' => 20,
            default => 10,
        };

        $cacheKey = 'dashboard:metrics:'.$period;
        $payload = Cache::remember($cacheKey, 5, function () use ($since, $limit, $period) {
            // Only return recent executions for the dashboard
            $recentExecutions = DB::table('task_executions')
                ->join('tasks', 'task_executions.task_id', '=', 'tasks.id')
                ->select(
                    'task_executions.id',
                    'task_executions.status',
                    'task_executions.created_at',
                    'task_executions.duration',
                    'tasks.name as task_name',
                    'tasks.id as task_id'
                )
                ->where('task_executions.created_at', '>=', $since)
                ->orderBy('task_executions.created_at', 'desc')
                ->limit($limit)
                ->get();

            // Aggregate counts for the period
            $stats = DB::table('task_executions')
                ->selectRaw("\n                    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_count,\n                    COUNT(CASE WHEN status = 'failed' THEN 1 END) as failed_count,\n                    COUNT(CASE WHEN status = 'running' THEN 1 END) as running_count\n                ")
                ->where('created_at', '>=', $since)
                ->first();

            // Per-task aggregated stats for visualization
            $tasksStats = DB::table('task_executions')
                ->join('tasks', 'task_executions.task_id', '=', 'tasks.id')
                ->selectRaw("\n                    tasks.name as task_name,\n                    COUNT(*) as total_count,\n                    COUNT(CASE WHEN task_executions.status = 'completed' THEN 1 END) as completed_count,\n                    COUNT(CASE WHEN task_executions.status = 'failed' THEN 1 END) as failed_count,\n                    COUNT(CASE WHEN task_executions.status = 'running' THEN 1 END) as running_count\n                ")
                ->where('task_executions.created_at', '>=', $since)
                ->groupBy('tasks.name')
                ->orderByDesc('total_count')
                ->limit(10)
                ->get();

            return [
                'recent_executions' => $recentExecutions,
                'period' => $period,
                'stats' => [
                    'completed' => (int) ($stats->completed_count ?? 0),
                    'failed' => (int) ($stats->failed_count ?? 0),
                    'running' => (int) ($stats->running_count ?? 0),
                ],
                'tasks_stats' => $tasksStats,
                'timestamp' => now()->toIso8601String(),
            ];
        });

        return response()->json($payload);
    }
}

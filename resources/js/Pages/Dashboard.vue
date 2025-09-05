<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, watch, computed } from 'vue';

import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Activity, CheckCircle, Clock, Loader2, XCircle, ChevronRight } from 'lucide-vue-next';
// @ts-ignore types provided at runtime
import { Bar } from 'vue-chartjs';
// @ts-ignore types provided at runtime
import { Chart as ChartJS, BarElement, CategoryScale, LinearScale, Tooltip, Legend } from 'chart.js';

ChartJS.register(BarElement, CategoryScale, LinearScale, Tooltip, Legend);
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';

// Removed InitialMetrics - not used

interface TaskStatsItem {
    task_name: string;
    total_count: number;
    completed_count: number;
    failed_count: number;
    running_count: number;
}

interface QueueMetrics {
    metrics: {
        pending_jobs: number;
        processing_jobs: number;
        failed_jobs: number;
        recent_failed_jobs: number;
        // rate limiting metrics removed
        queue_health: 'healthy' | 'warning' | 'critical';
    };
    recent_executions: Array<{
        id: number;
        status: string;
        created_at: string;
        duration: number | null;
        task_name: string;
        task_id: number;
    }>;
    tasks_stats: TaskStatsItem[];
    hourly_stats: {
        completed: number;
        failed: number;
        running: number;
        avg_duration: number;
    };
}

// No props required

const metrics = ref<QueueMetrics | null>(null);
const period = ref<'15m' | '1h' | '24h' | '7d'>('1h');
const periodLabel = computed(() => {
    switch (period.value) {
        case '15m':
            return 'Last 15 Minutes';
        case '1h':
            return 'Last 1 Hour';
        case '24h':
            return 'Last 24 Hours';
        case '7d':
            return 'Last 7 Days';
        default:
            return 'Last 1 Hour';
    }
});

const chartData = computed(() => {
    const labels = (metrics.value?.tasks_stats ?? []).map((t: any) => t.task_name);
    const completed = (metrics.value?.tasks_stats ?? []).map((t: any) => t.completed_count);
    const failed = (metrics.value?.tasks_stats ?? []).map((t: any) => t.failed_count);
    const running = (metrics.value?.tasks_stats ?? []).map((t: any) => t.running_count);
    return {
        labels,
        datasets: [
            { label: 'Completed', data: completed, backgroundColor: 'rgba(34,197,94,0.7)' },
            { label: 'Failed', data: failed, backgroundColor: 'rgba(239,68,68,0.7)' },
            { label: 'Running', data: running, backgroundColor: 'rgba(59,130,246,0.7)' },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: 'y' as const,
    plugins: {
        legend: { position: 'bottom' as const },
        tooltip: { mode: 'index' as const, intersect: false },
    },
    scales: {
        x: { stacked: true },
        y: { stacked: true },
    },
};
const loading = ref(false);
const error = ref<string | null>(null);
let intervalId: ReturnType<typeof setInterval> | null = null;

const fetchMetrics = async () => {
    try {
        loading.value = true;
        error.value = null;
        
        const response = await fetch(`/api/dashboard/metrics?period=${period.value}`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        metrics.value = {
            metrics: {
                pending_jobs: 0,
                processing_jobs: 0,
                failed_jobs: 0,
                recent_failed_jobs: 0,
                queue_health: 'healthy',
            },
            recent_executions: data.recent_executions ?? [],
            tasks_stats: data.tasks_stats ?? [],
            hourly_stats: {
                completed: data.stats?.completed ?? 0,
                failed: data.stats?.failed ?? 0,
                running: data.stats?.running ?? 0,
                avg_duration: 0,
            },
        } as any;
    } catch (err) {
        error.value = 'Failed to fetch queue metrics';
        console.error('Error fetching metrics:', err);
    } finally {
        loading.value = false;
    }
};

// Queue Status card removed

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'completed': return CheckCircle;
        case 'failed': return XCircle;
        case 'running': return Loader2;
        default: return Clock;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'completed': return 'text-green-600';
        case 'failed': return 'text-red-600';
        case 'running': return 'text-blue-600';
        default: return 'text-gray-600';
    }
};

const getStatusDotClass = (status: string) => {
    switch (status) {
        case 'completed':
            return 'bg-green-500';
        case 'failed':
            return 'bg-red-500';
        case 'running':
            return 'bg-blue-500';
        default:
            return 'bg-gray-400';
    }
};

const formatDuration = (seconds: number | null) => {
    if (!seconds) return '-';
    if (seconds < 60) return `${seconds}s`;
    return `${Math.floor(seconds / 60)}m ${seconds % 60}s`;
};

const formatTime = (dateString: string) => {
    return new Date(dateString).toLocaleTimeString();
};

onMounted(() => {
    fetchMetrics();
    // Refresh every 5 seconds
    intervalId = setInterval(fetchMetrics, 5000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

watch(period, () => {
    fetchMetrics();
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <!-- Controls -->
            <div class="flex items-center justify-end gap-2">
                <label class="text-sm text-muted-foreground">Period</label>
                <select
                    v-model="period"
                    class="h-9 rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring"
                >
                    <option value="15m">Last 15 minutes</option>
                    <option value="1h">Last 1 hour</option>
                    <option value="24h">Last 24 hours</option>
                    <option value="7d">Last 7 days</option>
                </select>
            </div>
            

            <!-- Metrics cards removed: Pending, Processing, Failed -->

            <div class="grid gap-4 lg:grid-cols-2">
                <!-- Queue Status card removed -->

                <!-- Hourly Stats -->
                <Card>
                    <CardHeader>
                        <CardTitle>{{ periodLabel }} Statistics</CardTitle>
                        <CardDescription>
                            Execution performance in the last hour
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="grid grid-cols-3 gap-4">
                                <div>
                                    <p class="text-sm text-muted-foreground">Completed</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ metrics?.hourly_stats.completed ?? 0 }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Failed</p>
                                    <p class="text-2xl font-bold text-red-600">
                                        {{ metrics?.hourly_stats.failed ?? 0 }}
                                    </p>
                                </div>
                                <div>
                                    <p class="text-sm text-muted-foreground">Running</p>
                                    <p class="text-2xl font-bold text-blue-600">
                                        {{ metrics?.hourly_stats.running ?? 0 }}
                                    </p>
                                </div>
                            </div>
                            
                            <div class="pt-4 border-t">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-muted-foreground">Average Duration</span>
                                    <span class="text-sm font-medium">
                                        {{ formatDuration(metrics?.hourly_stats.avg_duration ?? 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Per-Task Performance -->
                <Card>
                    <CardHeader>
                        <CardTitle>Per-Task {{ periodLabel }} Performance</CardTitle>
                        <CardDescription>
                            Completed, Failed, and Running counts per task
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="h-80">
                            <Bar :data="chartData" :options="chartOptions" />
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Executions -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Executions</CardTitle>
                    <CardDescription>
                        Latest workflow execution activity
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="metrics?.recent_executions.length" class="space-y-2">
                        <div 
                            v-for="execution in metrics.recent_executions" 
                            :key="execution.id"
                            class="group flex items-center justify-between rounded-lg border bg-card/40 hover:bg-card/60 transition-colors px-3 py-2"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <span :class="['h-2.5 w-2.5 rounded-full', getStatusDotClass(execution.status)]"></span>
                                <div class="min-w-0">
                                    <Link :href="`/tasks/${execution.task_id}`" class="block">
                                        <p class="truncate text-sm font-medium group-hover:underline">{{ execution.task_name }}</p>
                                    </Link>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatTime(execution.created_at) }} • {{ formatDuration(execution.duration) }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <Badge :variant="execution.status === 'completed' ? 'default' : execution.status === 'failed' ? 'destructive' : 'secondary'" class="h-5 px-2 text-[11px] leading-none">
                                    {{ execution.status }}
                                </Badge>
                                <Link :href="`/tasks/${execution.task_id}/executions/${execution.id}`" title="View execution details" class="inline-flex items-center gap-1 text-xs text-primary hover:underline">
                                    <span>View details</span>
                                    <ChevronRight class="h-3.5 w-3.5" />
                                </Link>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        No recent executions
                    </div>
                </CardContent>
            </Card>

            <!-- Auto-refresh indicator -->
            <div class="flex items-center justify-center gap-2 text-sm text-muted-foreground">
                <Activity class="h-4 w-4" />
                <span>Auto-refreshing every 5 seconds</span>
            </div>
        </div>
    </AppLayout>
</template>
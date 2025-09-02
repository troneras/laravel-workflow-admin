<template>
    <Head title="Task Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="container mx-auto max-w-7xl py-8">
                <!-- Enhanced Header Section -->
                <div
                    class="relative mb-8 overflow-hidden rounded-2xl border border-white/20 bg-white shadow-xl dark:border-slate-700/50 dark:bg-slate-800"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-purple-600/5 to-indigo-600/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-indigo-400/10"
                    ></div>
                    <div class="relative p-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg"
                                    >
                                        <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-white">
                                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <h1
                                            class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-3xl font-bold text-transparent dark:from-white dark:to-gray-200"
                                        >
                                            {{ task.name }}
                                        </h1>
                                        <div class="mt-1 flex items-center gap-2">
                                            <Badge :variant="task.is_active ? 'default' : 'secondary'" class="text-sm font-medium">
                                                {{ task.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                            <span class="text-muted-foreground">•</span>
                                            <p class="font-medium text-muted-foreground">{{ task.description || 'No description' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    @click="$inertia.visit(taskRoutes.executions.index(task.id))"
                                    class="bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-200 hover:from-blue-600 hover:to-indigo-700"
                                >
                                    View All Executions
                                </Button>
                                <Button
                                    variant="outline"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-slate-700"
                                    @click="$inertia.visit(taskRoutes.edit(task.id))"
                                >
                                    Edit Task
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid min-w-0 gap-8">
                    <!-- Enhanced Task Details Card -->
                    <Card class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                                    <div class="flex h-4 w-4 items-center justify-center rounded-sm bg-white">
                                        <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                    </div>
                                </div>
                                Task Configuration
                            </CardTitle>
                            <p class="text-muted-foreground">Detailed task settings and workflow information</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- Workflow Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <div class="mb-3 flex items-center justify-between">
                                        <dt class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Workflow</dt>
                                        <div class="h-2 w-2 rounded-full" :class="task.dify_workflow ? 'bg-green-500' : 'bg-gray-400'"></div>
                                    </div>
                                    <dd class="font-medium text-gray-900 dark:text-white">
                                        {{ task.dify_workflow?.name || 'No workflow assigned' }}
                                    </dd>
                                </div>

                                <!-- Workflow ID Card -->
                                <div
                                    v-if="task.dify_workflow"
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Workflow ID</dt>
                                    <dd class="rounded-lg border bg-gray-50 px-3 py-2 font-mono text-sm dark:border-slate-500 dark:bg-slate-600">
                                        {{ task.dify_workflow.workflow_id }}
                                    </dd>
                                </div>

                                <!-- Status Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Status</dt>
                                    <dd>
                                        <Badge :variant="task.is_active ? 'default' : 'secondary'" class="px-3 py-1 text-sm font-medium">
                                            {{ task.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </dd>
                                </div>

                                <!-- Created Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Created</dt>
                                    <dd class="text-sm text-gray-700 dark:text-gray-300">{{ formatDate(task.created_at) }}</dd>
                                </div>

                                <!-- Updated Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Last Updated</dt>
                                    <dd class="text-sm text-gray-700 dark:text-gray-300">{{ formatDate(task.updated_at) }}</dd>
                                </div>
                            </div>

                            <!-- Input Schema Section -->
                            <div v-if="task.input_schema" class="mt-8">
                                <div class="mb-4 flex items-center gap-3">
                                    <div class="flex h-6 w-6 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-600">
                                        <div class="h-2 w-2 rounded-full bg-white"></div>
                                    </div>
                                    <h3 class="text-lg font-semibold">Input Schema</h3>
                                </div>
                                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-slate-600 dark:bg-slate-700">
                                    <div
                                        class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 dark:border-green-700 dark:from-green-900/30 dark:to-emerald-900/30"
                                    >
                                        <pre class="overflow-auto p-6 font-mono text-sm leading-relaxed text-green-900 dark:text-green-100">{{
                                            JSON.stringify(task.input_schema, null, 2)
                                        }}</pre>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Enhanced Recent Executions Card -->
                    <Card class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-50/30 via-indigo-50/20 to-blue-50/30 dark:from-purple-900/20 dark:via-indigo-900/15 dark:to-blue-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600">
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                                Recent Executions
                            </CardTitle>
                            <p class="text-muted-foreground">Latest execution history and performance metrics</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div v-if="task.executions.length > 0" class="overflow-x-auto">
                                <Table>
                                    <TableHeader>
                                        <TableRow class="border-b border-gray-200 dark:border-slate-700">
                                            <TableHead class="text-left font-semibold">Execution ID</TableHead>
                                            <TableHead class="text-left font-semibold">Status</TableHead>
                                            <TableHead class="text-left font-semibold">Started</TableHead>
                                            <TableHead class="text-left font-semibold">Duration</TableHead>
                                            <TableHead class="text-left font-semibold">Tokens</TableHead>
                                            <TableHead class="text-right font-semibold">Actions</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        <TableRow
                                            v-for="execution in task.executions"
                                            :key="execution.id"
                                            class="border-b border-gray-100 transition-colors hover:bg-gray-50/50 dark:border-slate-700 dark:hover:bg-slate-700/50"
                                        >
                                            <TableCell class="font-mono text-sm">
                                                <div class="flex items-center gap-2">
                                                    <div class="h-2 w-2 rounded-full" :class="getStatusDotColor(execution.status)"></div>
                                                    <span class="rounded-md bg-gray-100 px-2 py-1 dark:bg-slate-600">
                                                        {{ execution.task_execution_id.slice(0, 8) }}...
                                                    </span>
                                                </div>
                                            </TableCell>
                                            <TableCell>
                                                <Badge :variant="getStatusVariant(execution.status)" class="font-medium">
                                                    {{ execution.status }}
                                                </Badge>
                                            </TableCell>
                                            <TableCell class="text-sm text-gray-700 dark:text-gray-300">
                                                {{ formatDate(execution.start_time) }}
                                            </TableCell>
                                            <TableCell>
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    {{ execution.duration ? `${execution.duration}s` : '-' }}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <span class="font-semibold text-gray-900 dark:text-white">
                                                    {{ execution.tokens ? execution.tokens.toLocaleString() : '-' }}
                                                </span>
                                            </TableCell>
                                            <TableCell class="text-right">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    class="transition-all duration-200 hover:border-blue-300 hover:bg-blue-50 dark:hover:border-blue-700 dark:hover:bg-blue-900/20"
                                                    @click="$inertia.visit(taskRoutes.executions.show([task.id, execution.id]))"
                                                >
                                                    View Details
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    </TableBody>
                                </Table>
                            </div>
                            <div v-else class="py-12 text-center">
                                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-slate-700">
                                    <div class="h-6 w-6 rounded-full bg-gray-400"></div>
                                </div>
                                <h3 class="mb-2 text-lg font-semibold text-gray-900 dark:text-white">No executions yet</h3>
                                <p class="text-muted-foreground">This task hasn't been executed yet. Run the workflow to see execution history.</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import taskRoutes from '@/routes/tasks';
import type { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface TaskExecution {
    id: number;
    task_execution_id: string;
    status: string;
    start_time: string | null;
    duration: number | null;
    tokens: number | null;
}

interface DifyWorkflow {
    id: number;
    name: string;
    workflow_id: string;
}

interface Task {
    id: number;
    name: string;
    description: string | null;
    input_schema: any;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    dify_workflow?: DifyWorkflow;
    executions: TaskExecution[];
}

const props = defineProps<{
    task: Task;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: taskRoutes.index().url,
    },
    {
        title: props.task.name,
        href: taskRoutes.show(props.task.id).url,
    },
];

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
            return 'default';
        case 'running':
            return 'outline';
        case 'failed':
            return 'destructive';
        default:
            return 'secondary';
    }
};

// Enhanced status helper for colored dots
const getStatusDotColor = (status: string) => {
    switch (status) {
        case 'completed':
        case 'succeeded':
            return 'bg-green-500';
        case 'running':
            return 'bg-blue-500 animate-pulse';
        case 'failed':
            return 'bg-red-500';
        case 'pending':
            return 'bg-yellow-500';
        default:
            return 'bg-gray-500';
    }
};
</script>

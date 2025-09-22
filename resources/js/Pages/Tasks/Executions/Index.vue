<template>
    <Head title="Task Executions" />

    <AppLayout :breadcrumbs="breadcrumbs">
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
                                        <Play class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h1
                                            class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-3xl font-bold text-transparent dark:from-white dark:to-gray-200"
                                        >
                                            Task Executions
                                        </h1>
                                        <div class="mt-1 flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                            <p class="font-medium text-muted-foreground">{{ task.name }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    @click="runTask"
                                    :disabled="!task.is_active"
                                    class="bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-200 hover:from-blue-600 hover:to-indigo-700"
                                >
                                    <Play class="mr-2 h-4 w-4" /> Run Workflow
                                </Button>
                                <Button
                                    variant="outline"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-slate-700"
                                    @click="$inertia.visit(taskRoutes.show(task.id))"
                                >
                                    Back to Task
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Executions Table -->
                <Card class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20"
                    ></div>
                    <div class="relative p-6">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                                <Play class="h-4 w-4 text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold">Execution History</h2>
                                <p class="text-sm text-muted-foreground">Track all workflow executions and their status</p>
                            </div>
                        </div>
                    </div>
                    <CardContent class="relative p-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow class="border-b border-gray-200 dark:border-slate-700">
                                        <TableHead class="text-left font-semibold">Execution ID</TableHead>
                                        <TableHead class="text-left font-semibold">Status</TableHead>
                                        <TableHead class="text-left font-semibold">Started</TableHead>
                                        <TableHead class="text-left font-semibold">Completed</TableHead>
                                        <TableHead class="text-left font-semibold">Duration</TableHead>
                                        <TableHead class="text-left font-semibold">Tokens</TableHead>
                                        <TableHead class="text-right font-semibold">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="execution in executions.data"
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
                                        <TableCell class="text-sm text-gray-700 dark:text-gray-300">
                                            {{ formatDate(execution.end_time) }}
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
                    </CardContent>
                </Card>

                <!-- Enhanced Pagination -->
                <div v-if="executions.last_page > 1" class="mt-8 flex justify-center">
                    <nav class="rounded-xl border border-gray-200 bg-white p-2 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <div class="flex gap-1">
                            <Button
                                v-for="page in executions.last_page"
                                :key="page"
                                :variant="page === executions.current_page ? 'default' : 'outline'"
                                size="sm"
                                class="transition-all duration-200"
                                :class="
                                    page === executions.current_page
                                        ? 'bg-gradient-to-r from-blue-500 to-indigo-600'
                                        : 'hover:bg-gray-50 dark:hover:bg-slate-700'
                                "
                                @click="$inertia.visit(executions.path + '?page=' + page)"
                            >
                                {{ page }}
                            </Button>
                        </div>
                    </nav>
                </div>
            </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import taskRoutes from '@/routes/tasks';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Play } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

interface TaskExecution {
    id: number;
    task_execution_id: string;
    status: string;
    start_time: string | null;
    end_time: string | null;
    duration: number | null;
    tokens: number | null;
}

interface TaskInputField {
    name: string;
    type: string;
    required: boolean;
    default?: any;
}

interface Task {
    id: number;
    name: string;
    description?: string;
    input_schema?: TaskInputField[];
    is_active: boolean;
}

interface PaginatedExecutions {
    data: TaskExecution[];
    current_page: number;
    last_page: number;
    path: string;
}

interface Props {
    task: Task;
    executions: PaginatedExecutions;
}

const props = defineProps<Props>();

// Create reactive copy of executions to avoid prop mutation
const executions = ref<PaginatedExecutions>({
    ...props.executions,
    data: [...props.executions.data], // Deep copy the data array
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: taskRoutes.index().url,
    },
    {
        title: props.task.name,
        href: taskRoutes.show(props.task.id).url,
    },
    {
        title: 'Executions',
        href: taskRoutes.executions.index(props.task.id).url,
    },
];

const runningExecutions = ref(new Set<number>());
let pollingInterval: NodeJS.Timeout | null = null;

const checkRunningExecutions = () => {
    executions.value.data.forEach((execution) => {
        if (execution.status === 'running' || execution.status === 'pending') {
            runningExecutions.value.add(execution.id);
        }
    });
};

const pollExecutionStatus = async () => {
    if (runningExecutions.value.size === 0) return;

    try {
        const promises = Array.from(runningExecutions.value).map(async (executionId) => {
            const statusUrl = taskRoutes.executions.status.url([props.task.id, executionId]);
            const response = await fetch(statusUrl);
            if (response.ok) {
                const data = await response.json();

                // Find and update the execution in the list
                const executionIndex = executions.value.data.findIndex((e) => e.id === executionId);
                if (executionIndex !== -1) {
                    executions.value.data[executionIndex] = {
                        ...executions.value.data[executionIndex],
                        ...data.execution,
                    };

                    // Remove from polling if completed or failed
                    if (data.execution.status === 'completed' || data.execution.status === 'failed') {
                        runningExecutions.value.delete(executionId);
                    }
                }
            }
        });

        await Promise.all(promises);
    } catch (error) {
        console.error('Error polling execution status:', error);
    }
};

onMounted(() => {
    checkRunningExecutions();
    if (runningExecutions.value.size > 0) {
        pollingInterval = setInterval(pollExecutionStatus, 2000);
    }
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

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

const runTask = () => {
    // No need to send input data anymore - the backend will use the task's predefined input
    router.post(
        taskRoutes.executions.store(props.task.id),
        {},
        {
            onSuccess: () => {
                // Start polling for new executions after successful task creation
                setTimeout(() => {
                    checkRunningExecutions();
                    if (runningExecutions.value.size > 0 && !pollingInterval) {
                        pollingInterval = setInterval(pollExecutionStatus, 2000);
                    }
                }, 1000);
            },
        },
    );
};
</script>

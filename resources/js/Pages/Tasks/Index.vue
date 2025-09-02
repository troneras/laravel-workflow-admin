<template>
    <Head title="Tasks" />

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
                                        <Plus class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h1
                                            class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-3xl font-bold text-transparent dark:from-white dark:to-gray-200"
                                        >
                                            Task Management
                                        </h1>
                                        <div class="mt-1 flex items-center gap-2">
                                            <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                            <p class="font-medium text-muted-foreground">Manage and monitor workflow tasks</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    @click="$inertia.visit(taskRoutes.create())"
                                    class="bg-gradient-to-r from-blue-500 to-indigo-600 transition-all duration-200 hover:from-blue-600 hover:to-indigo-700"
                                >
                                    <Plus class="mr-2 h-4 w-4" /> Create New Task
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Tasks Table -->
                <Card class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700">
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20"
                    ></div>
                    <div class="relative p-6">
                        <div class="mb-6 flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                                <Plus class="h-4 w-4 text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold">Task Overview</h2>
                                <p class="text-sm text-muted-foreground">Monitor all your workflow tasks and their current status</p>
                            </div>
                        </div>
                    </div>
                    <CardContent class="relative p-0">
                        <div class="overflow-x-auto">
                            <Table>
                                <TableHeader>
                                    <TableRow class="border-b border-gray-200 dark:border-slate-700">
                                        <TableHead class="text-left font-semibold">Task Name</TableHead>
                                        <TableHead class="text-left font-semibold">Description</TableHead>
                                        <TableHead class="text-left font-semibold">Workflow</TableHead>
                                        <TableHead class="text-left font-semibold">Status</TableHead>
                                        <TableHead class="text-left font-semibold">Last Execution</TableHead>
                                        <TableHead class="text-right font-semibold">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="task in tasks"
                                        :key="task.id"
                                        class="cursor-pointer border-b border-gray-100 transition-colors hover:bg-gray-50/50 dark:border-slate-700 dark:hover:bg-slate-700/50"
                                        @click="$inertia.visit(taskRoutes.executions.index(task.id))"
                                    >
                                        <TableCell class="font-medium">
                                            <div class="flex items-center gap-3">
                                                <div class="h-2 w-2 rounded-full" :class="task.is_active ? 'bg-green-500' : 'bg-gray-400'"></div>
                                                <span class="text-gray-900 dark:text-white">{{ task.name }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="max-w-xs text-sm text-gray-700 dark:text-gray-300">
                                            <span class="block truncate">{{ task.description || '-' }}</span>
                                        </TableCell>
                                        <TableCell>
                                            <span
                                                v-if="task.dify_workflow"
                                                class="inline-flex items-center gap-2 rounded-md bg-blue-50 px-2 py-1 font-mono text-sm text-blue-700 transition-colors hover:bg-blue-100 dark:bg-blue-900/20 dark:text-blue-300 dark:hover:bg-blue-900/30"
                                                @click.stop="$inertia.visit(difyWorkflowRoutes.edit(task.dify_workflow.id))"
                                            >
                                                <div class="h-1.5 w-1.5 rounded-full bg-blue-500"></div>
                                                {{ task.dify_workflow.name }}
                                            </span>
                                            <span v-else class="text-sm text-gray-400">No workflow</span>
                                        </TableCell>
                                        <TableCell>
                                            <Badge :variant="task.is_active ? 'default' : 'secondary'" class="font-medium">
                                                {{ task.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                v-if="task.executions && task.executions.length > 0"
                                                :variant="getStatusVariant(task.executions[0].status)"
                                                class="cursor-pointer font-medium transition-opacity hover:opacity-80"
                                                @click.stop="$inertia.visit(taskRoutes.executions.show(task.id, task.executions[0].id))"
                                            >
                                                {{ task.executions[0].status }}
                                            </Badge>
                                            <span v-else class="text-sm text-gray-400">No executions</span>
                                        </TableCell>
                                        <TableCell class="text-right">
                                            <div class="flex justify-end gap-1">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="transition-colors hover:bg-blue-50 dark:hover:bg-blue-900/20"
                                                    @click.stop="$inertia.visit(taskRoutes.executions.index(task.id))"
                                                    title="View Executions"
                                                >
                                                    <History class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="transition-colors hover:bg-green-50 dark:hover:bg-green-900/20"
                                                    @click.stop="runTask(task)"
                                                    title="Run Task"
                                                >
                                                    <Play class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="transition-colors hover:bg-yellow-50 dark:hover:bg-yellow-900/20"
                                                    @click.stop="$inertia.visit(taskRoutes.edit(task.id))"
                                                    title="Edit Task"
                                                >
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="transition-colors hover:bg-red-50 dark:hover:bg-red-900/20"
                                                    @click.stop="deleteTask(task)"
                                                    title="Delete Task"
                                                >
                                                    <Trash2 class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>
                    </CardContent>
                </Card>
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
import difyWorkflowRoutes from '@/routes/dify-workflows';
import taskRoutes from '@/routes/tasks';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Edit, History, Play, Plus, Trash2 } from 'lucide-vue-next';

interface DifyWorkflow {
    id: number;
    name: string;
    workflow_id: string;
}

interface Task {
    id: number;
    name: string;
    description: string | null;
    is_active: boolean;
    dify_workflow: DifyWorkflow | null;
    executions: Array<{
        id: number;
        status: string;
    }>;
}

interface Props {
    tasks: Task[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Tasks',
        href: taskRoutes.index().url,
    },
];

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

const runTask = (task: Task) => {
    router.post(taskRoutes.executions.store(task.id));
};

const deleteTask = (task: Task) => {
    if (confirm(`Are you sure you want to delete "${task.name}"?`)) {
        router.delete(taskRoutes.destroy(task.id));
    }
};
</script>

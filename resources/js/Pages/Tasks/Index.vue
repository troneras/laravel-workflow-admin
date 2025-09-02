<template>
    <Head title="Tasks" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 dark:from-slate-900 dark:via-slate-800 dark:to-slate-900">
            <div class="container mx-auto max-w-7xl py-8">
                <!-- Enhanced Header Section -->
                <div
                    class="page-header"
                >
                    <div class="page-header-gradient"></div>
                    <div class="page-header-content">
                        <div class="page-header-layout">
                            <div class="page-header-info">
                                <div class="page-header-title-group">
                                    <div class="page-header-icon">
                                        <Plus class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h1 class="page-title">Task Management</h1>
                                        <div class="page-subtitle">
                                            <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                            <p class="font-medium text-muted-foreground">Manage and monitor workflow tasks</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="page-actions">
                                <Button
                                    @click="$inertia.visit(taskRoutes.create())"
                                    class="btn-gradient shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                                >
                                    <Plus class="mr-2 h-4 w-4" /> Create New Task
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Tasks Table -->
                <Card class="enhanced-card">
                    <div class="enhanced-card-gradient"></div>
                    <div class="enhanced-card-header relative p-6">
                        <div class="enhanced-card-title">
                            <div class="enhanced-card-icon">
                                <Plus class="h-4 w-4 text-white" />
                            </div>
                            <div>
                                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Task Overview</h2>
                                <p class="text-sm text-muted-foreground mt-1">Monitor all your workflow tasks and their current status</p>
                            </div>
                        </div>
                    </div>
                    <CardContent class="enhanced-card-content p-0">
                        <div class="enhanced-table">
                            <Table>
                                <TableHeader class="enhanced-table-header">
                                    <TableRow class="border-b-2 border-gray-200 dark:border-slate-600 bg-gray-50/50 dark:bg-slate-700/50">
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Task Name</TableHead>
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Description</TableHead>
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Workflow</TableHead>
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Status</TableHead>
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Last Execution</TableHead>
                                        <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300 text-right">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow
                                        v-for="task in tasks"
                                        :key="task.id"
                                        class="enhanced-table-row group cursor-pointer hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-indigo-50/30 dark:hover:from-blue-900/10 dark:hover:to-indigo-900/10 hover:shadow-sm transition-all duration-300"
                                        @click="$inertia.visit(taskRoutes.executions.index(task.id))"
                                    >
                                        <TableCell class="py-4 px-6">
                                            <div class="flex items-center gap-3">
                                                <div class="status-dot" :class="getStatusDotColor(task.is_active ? 'active' : 'inactive')"></div>
                                                <div class="flex flex-col">
                                                    <span class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">{{ task.name }}</span>
                                                    <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ task.id }}</span>
                                                </div>
                                            </div>
                                        </TableCell>
                                        <TableCell class="py-4 px-6 max-w-xs">
                                            <div class="text-sm text-gray-700 dark:text-gray-300">
                                                <span class="block truncate" :title="task.description || 'No description'">{{ task.description || '-' }}</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="py-4 px-6">
                                            <span
                                                v-if="task.dify_workflow"
                                                class="workflow-indicator cursor-pointer hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200"
                                                @click.stop="$inertia.visit(difyWorkflowRoutes.edit(task.dify_workflow.id))"
                                            >
                                                <div class="h-1.5 w-1.5 rounded-full bg-blue-500 animate-pulse"></div>
                                                {{ task.dify_workflow.name }}
                                            </span>
                                            <span v-else class="text-sm text-gray-400 italic">No workflow assigned</span>
                                        </TableCell>
                                        <TableCell class="py-4 px-6">
                                            <Badge 
                                                :variant="task.is_active ? 'default' : 'secondary'" 
                                                class="font-medium shadow-sm hover:shadow-md transition-shadow"
                                                :class="task.is_active ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white' : ''"
                                            >
                                                {{ task.is_active ? 'Active' : 'Inactive' }}
                                            </Badge>
                                        </TableCell>
                                        <TableCell class="py-4 px-6">
                                            <Badge
                                                v-if="task.executions && task.executions.length > 0"
                                                :variant="getStatusVariant(task.executions[0].status)"
                                                class="cursor-pointer font-medium transition-all duration-200 hover:scale-105 shadow-sm"
                                                @click.stop="$inertia.visit(taskRoutes.executions.show(task.id, task.executions[0].id))"
                                            >
                                                <div class="status-dot mr-1" :class="getStatusDotColor(task.executions[0].status)"></div>
                                                {{ task.executions[0].status }}
                                            </Badge>
                                            <div v-else class="flex items-center gap-2 text-sm text-gray-400">
                                                <div class="h-1.5 w-1.5 rounded-full bg-gray-300"></div>
                                                <span class="italic">No executions</span>
                                            </div>
                                        </TableCell>
                                        <TableCell class="py-4 px-6 text-right">
                                            <div class="flex justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 transition-all duration-200 hover:scale-110"
                                                    @click.stop="$inertia.visit(taskRoutes.executions.index(task.id))"
                                                    title="View Executions"
                                                >
                                                    <History class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 hover:bg-green-50 hover:text-green-700 dark:hover:bg-green-900/20 dark:hover:text-green-300 transition-all duration-200 hover:scale-110"
                                                    @click.stop="runTask(task)"
                                                    title="Run Task"
                                                    :disabled="!task.is_active"
                                                >
                                                    <Play class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20 dark:hover:text-amber-300 transition-all duration-200 hover:scale-110"
                                                    @click.stop="$inertia.visit(taskRoutes.edit(task.id))"
                                                    title="Edit Task"
                                                >
                                                    <Edit class="h-4 w-4" />
                                                </Button>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 dark:hover:text-red-300 transition-all duration-200 hover:scale-110"
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

// Enhanced status helper for colored dots
const getStatusDotColor = (status: string) => {
    switch (status) {
        case 'completed':
        case 'succeeded':
        case 'active':
            return 'bg-green-500';
        case 'running':
            return 'bg-blue-500 animate-pulse';
        case 'failed':
            return 'bg-red-500';
        case 'pending':
            return 'bg-yellow-500';
        case 'inactive':
            return 'bg-gray-400';
        default:
            return 'bg-gray-500';
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

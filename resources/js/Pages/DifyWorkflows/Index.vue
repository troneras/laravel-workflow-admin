<template>
    <Head title="Dify Workflows" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-7xl py-8">
            <!-- Enhanced Header Section -->
            <div class="page-header">
                <div class="page-header-gradient"></div>
                <div class="page-header-content">
                    <div class="page-header-layout">
                        <div class="page-header-info">
                            <div class="page-header-title-group">
                                <div class="page-header-icon">
                                    <Plus class="h-6 w-6 text-white" />
                                </div>
                                <div>
                                    <h1 class="page-title">Dify Workflows</h1>
                                    <div class="page-subtitle">
                                        <div class="h-2 w-2 rounded-full bg-blue-500 animate-pulse"></div>
                                        <p class="font-medium text-muted-foreground">Import and manage your Dify workflows</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="page-actions">
                            <Button
                                @click="$inertia.visit(difyWorkflows.create())"
                                class="btn-gradient shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105"
                            >
                                <Plus class="mr-2 h-4 w-4" /> Add Workflow
                            </Button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Workflows Table -->
            <Card class="enhanced-card">
                <div class="enhanced-card-gradient"></div>
                <div class="enhanced-card-header relative p-6">
                    <div class="enhanced-card-title">
                        <div class="enhanced-card-icon">
                            <Plus class="h-4 w-4 text-white" />
                        </div>
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Workflow Overview</h2>
                            <p class="text-sm text-muted-foreground mt-1">Manage all imported Dify workflows and their configurations</p>
                        </div>
                    </div>
                </div>
                <CardContent class="enhanced-card-content p-0">
                    <div class="enhanced-table">
                        <Table>
                            <TableHeader class="enhanced-table-header">
                                <TableRow class="border-b-2 border-gray-200 dark:border-slate-600 bg-gray-50/50 dark:bg-slate-700/50">
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Name</TableHead>
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Description</TableHead>
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Workflow ID</TableHead>
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Status</TableHead>
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300">Tasks</TableHead>
                                    <TableHead class="enhanced-table-head py-4 px-6 text-xs uppercase tracking-wider font-bold text-gray-700 dark:text-gray-300 text-right">Actions</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                                <TableRow
                                    v-for="workflow in workflows"
                                    :key="workflow.id"
                                    class="enhanced-table-row group cursor-pointer hover:bg-gradient-to-r hover:from-blue-50/30 hover:to-indigo-50/30 dark:hover:from-blue-900/10 dark:hover:to-indigo-900/10 hover:shadow-sm transition-all duration-300"
                                    @click="$inertia.visit(difyWorkflows.edit(workflow.id))">
                                    <TableCell class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <div class="status-dot" :class="getStatusDotColor(workflow.is_active ? 'active' : 'inactive')"></div>
                                            <div class="flex flex-col">
                                                <span class="font-semibold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">{{ workflow.name }}</span>
                                                <span class="text-xs text-gray-500 dark:text-gray-400">ID: {{ workflow.id }}</span>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-4 px-6 max-w-xs">
                                        <div class="text-sm text-gray-700 dark:text-gray-300">
                                            <span class="block truncate" :title="workflow.description || 'No description'">{{ workflow.description || '-' }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-4 px-6">
                                        <span class="font-mono text-xs text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">{{ workflow.workflow_id }}</span>
                                    </TableCell>
                                    <TableCell class="py-4 px-6">
                                        <Badge 
                                            :variant="workflow.is_active ? 'default' : 'secondary'" 
                                            class="font-medium shadow-sm hover:shadow-md transition-shadow"
                                            :class="workflow.is_active ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white' : ''"
                                        >
                                            {{ workflow.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="py-4 px-6">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ workflow.tasks_count }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ workflow.tasks_count === 1 ? 'task' : 'tasks' }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="py-4 px-6 text-right">
                                        <div class="flex justify-end gap-1 opacity-60 group-hover:opacity-100 transition-opacity duration-200">
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-900/20 dark:hover:text-amber-300 transition-all duration-200 hover:scale-110"
                                                @click.stop="$inertia.visit(difyWorkflows.edit(workflow.id))"
                                                title="Edit Workflow"
                                            >
                                                <Edit class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="ghost"
                                                size="sm"
                                                class="h-8 w-8 p-0 hover:bg-red-50 hover:text-red-700 dark:hover:bg-red-900/20 dark:hover:text-red-300 transition-all duration-200 hover:scale-110"
                                                @click.stop="deleteWorkflow(workflow)"
                                                title="Delete Workflow"
                                                :disabled="workflow.tasks_count > 0"
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
    </AppLayout>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import difyWorkflows from '@/routes/dify-workflows';
import type { BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/vue3';
import { Edit, Plus, Trash2 } from 'lucide-vue-next';

interface DifyWorkflow {
    id: number;
    name: string;
    description: string | null;
    workflow_id: string;
    is_active: boolean;
    tasks_count: number;
}

interface Props {
    workflows: DifyWorkflow[];
}

defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dify Workflows',
        href: difyWorkflows.index().url,
    },
];

const deleteWorkflow = (workflow: DifyWorkflow) => {
    if (confirm(`Are you sure you want to delete "${workflow.name}"?`)) {
        router.delete(difyWorkflows.destroy(workflow.id));
    }
};

// Helper for colored status dots
const getStatusDotColor = (status: string) => {
    switch (status) {
        case 'active':
            return 'bg-green-500';
        case 'inactive':
            return 'bg-gray-400';
        default:
            return 'bg-gray-500';
    }
};
</script>

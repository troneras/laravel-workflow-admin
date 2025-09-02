<template>
  <Head title="Dify Workflows" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Dify Workflows</h1>
        <Button @click="$inertia.visit(difyWorkflows.create())">
          <Plus class="mr-2 h-4 w-4" /> Add Workflow
        </Button>
      </div>

      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Workflow ID</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Tasks</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="workflow in workflows" :key="workflow.id">
                <TableCell class="font-medium">{{ workflow.name }}</TableCell>
                <TableCell>{{ workflow.description || '-' }}</TableCell>
                <TableCell class="font-mono text-sm">{{ workflow.workflow_id }}</TableCell>
                <TableCell>
                  <Badge :variant="workflow.is_active ? 'default' : 'secondary'">
                    {{ workflow.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </TableCell>
                <TableCell>{{ workflow.tasks_count }}</TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      @click="$inertia.visit(difyWorkflows.edit(workflow.id))"
                    >
                      <Edit class="h-4 w-4" />
                    </Button>
                    <Button
                      variant="destructive"
                      size="sm"
                      @click="deleteWorkflow(workflow)"
                      :disabled="workflow.tasks_count > 0"
                    >
                      <Trash2 class="h-4 w-4" />
                    </Button>
                  </div>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Plus, Edit, Trash2 } from 'lucide-vue-next'
import difyWorkflows from '@/routes/dify-workflows'

interface DifyWorkflow {
  id: number
  name: string
  description: string | null
  workflow_id: string
  is_active: boolean
  tasks_count: number
}

interface Props {
  workflows: DifyWorkflow[]
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Dify Workflows',
    href: difyWorkflows.index().url,
  },
]

const deleteWorkflow = (workflow: DifyWorkflow) => {
  if (confirm(`Are you sure you want to delete "${workflow.name}"?`)) {
    router.delete(difyWorkflows.destroy(workflow.id))
  }
}
</script>
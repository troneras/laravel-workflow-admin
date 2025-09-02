<template>
  <Head title="Tasks" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">Tasks</h1>
        <Button @click="$inertia.visit(taskRoutes.create())">
          <Plus class="mr-2 h-4 w-4" /> Create Task
        </Button>
      </div>

      <Card>
        <CardContent class="p-0">
          <Table>
            <TableHeader>
              <TableRow>
                <TableHead>Name</TableHead>
                <TableHead>Description</TableHead>
                <TableHead>Workflow</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Last Execution</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="task in tasks" :key="task.id">
                <TableCell class="font-medium">{{ task.name }}</TableCell>
                <TableCell>{{ task.description || '-' }}</TableCell>
                <TableCell class="font-mono text-sm">{{ task.dify_workflow?.name || 'No workflow' }}</TableCell>
                <TableCell>
                  <Badge :variant="task.is_active ? 'default' : 'secondary'">
                    {{ task.is_active ? 'Active' : 'Inactive' }}
                  </Badge>
                </TableCell>
                <TableCell>
                  <Badge v-if="task.executions && task.executions.length > 0" :variant="getStatusVariant(task.executions[0].status)">
                    {{ task.executions[0].status }}
                  </Badge>
                  <span v-else class="text-gray-400">No executions</span>
                </TableCell>
                <TableCell class="text-right">
                  <div class="flex justify-end space-x-1">
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="$inertia.visit(taskRoutes.executions.index(task.id))"
                    >
                      <History class="h-4 w-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="runTask(task)"
                    >
                      <Play class="h-4 w-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="$inertia.visit(taskRoutes.edit(task.id))"
                    >
                      <Edit class="h-4 w-4" />
                    </Button>
                    <Button
                      variant="ghost"
                      size="sm"
                      @click="deleteTask(task)"
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
import { Plus, Play, History, Edit, Trash2 } from 'lucide-vue-next'
import taskRoutes from '@/routes/tasks'

interface DifyWorkflow {
  id: number
  name: string
  workflow_id: string
}

interface Task {
  id: number
  name: string
  description: string | null
  is_active: boolean
  dify_workflow: DifyWorkflow | null
  executions: Array<{
    id: number
    status: string
  }>
}

interface Props {
  tasks: Task[]
}

defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Tasks',
    href: taskRoutes.index().url,
  },
]

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'completed':
      return 'default'
    case 'running':
      return 'outline'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const runTask = (task: Task) => {
  router.post(taskRoutes.executions.store(task.id))
}

const deleteTask = (task: Task) => {
  if (confirm(`Are you sure you want to delete "${task.name}"?`)) {
    router.delete(taskRoutes.destroy(task.id))
  }
}
</script>
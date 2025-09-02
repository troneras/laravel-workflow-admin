<template>
  <Head title="Task Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold">{{ task.name }}</h1>
        <p class="text-muted-foreground">{{ task.description || 'No description' }}</p>
      </div>
      <div class="flex gap-2">
        <Button
          @click="$inertia.visit(taskRoutes.executions.index(task.id))"
        >
          View All Executions
        </Button>
        <Button
          variant="outline"
          @click="$inertia.visit(taskRoutes.edit(task.id))"
        >
          Edit Task
        </Button>
      </div>
    </div>

    <div class="grid gap-6">
      <Card>
        <CardHeader>
          <CardTitle>Task Details</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Workflow</dt>
              <dd class="mt-1 text-sm">{{ task.dify_workflow?.name || 'No workflow assigned' }}</dd>
            </div>
            <div v-if="task.dify_workflow">
              <dt class="text-sm font-medium text-muted-foreground">Workflow ID</dt>
              <dd class="mt-1 text-sm font-mono">{{ task.dify_workflow.workflow_id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Status</dt>
              <dd class="mt-1">
                <Badge :variant="task.is_active ? 'default' : 'secondary'">
                  {{ task.is_active ? 'Active' : 'Inactive' }}
                </Badge>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Created</dt>
              <dd class="mt-1 text-sm">{{ formatDate(task.created_at) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Updated</dt>
              <dd class="mt-1 text-sm">{{ formatDate(task.updated_at) }}</dd>
            </div>
          </dl>
          <div v-if="task.input_schema" class="mt-4">
            <dt class="text-sm font-medium text-muted-foreground mb-2">Input Schema</dt>
            <pre class="bg-muted p-4 rounded-md overflow-auto text-sm">{{ JSON.stringify(task.input_schema, null, 2) }}</pre>
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader>
          <CardTitle>Recent Executions</CardTitle>
        </CardHeader>
        <CardContent>
          <Table v-if="task.executions.length > 0">
            <TableHeader>
              <TableRow>
                <TableHead>Execution ID</TableHead>
                <TableHead>Status</TableHead>
                <TableHead>Start Time</TableHead>
                <TableHead>Duration</TableHead>
                <TableHead>Tokens</TableHead>
                <TableHead class="text-right">Actions</TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow v-for="execution in task.executions" :key="execution.id">
                <TableCell class="font-mono text-sm">
                  {{ execution.task_execution_id.slice(0, 8) }}...
                </TableCell>
                <TableCell>
                  <Badge :variant="getStatusVariant(execution.status)">
                    {{ execution.status }}
                  </Badge>
                </TableCell>
                <TableCell>{{ formatDate(execution.start_time) }}</TableCell>
                <TableCell>{{ execution.duration ? `${execution.duration}s` : '-' }}</TableCell>
                <TableCell>{{ execution.tokens || '-' }}</TableCell>
                <TableCell class="text-right">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="$inertia.visit(taskRoutes.executions.show([task.id, execution.id]))"
                  >
                    View
                  </Button>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
          <div v-else class="text-center py-8 text-muted-foreground">
            No executions yet
          </div>
        </CardContent>
      </Card>
    </div>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import taskRoutes from '@/routes/tasks'

interface TaskExecution {
  id: number
  task_execution_id: string
  status: string
  start_time: string | null
  duration: number | null
  tokens: number | null
}

interface DifyWorkflow {
  id: number
  name: string
  workflow_id: string
}

interface Task {
  id: number
  name: string
  description: string | null
  input_schema: any
  is_active: boolean
  created_at: string
  updated_at: string
  dify_workflow?: DifyWorkflow
  executions: TaskExecution[]
}

const props = defineProps<{
  task: Task
}>()

const breadcrumbs: BreadcrumbItem[] = [
  {
    title: 'Tasks',
    href: taskRoutes.index().url,
  },
  {
    title: props.task.name,
    href: taskRoutes.show(props.task.id).url,
  },
]

const formatDate = (date: string | null) => {
  if (!date) return '-'
  return new Date(date).toLocaleString()
}

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
</script>
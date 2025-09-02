<template>
  <div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold">Task Executions</h1>
        <p class="text-muted-foreground">{{ task.name }}</p>
      </div>
      <div class="flex gap-2">
        <Button @click="runTask" :disabled="!task.is_active">
          <Play class="mr-2 h-4 w-4" /> Run Workflow
        </Button>
        <Button
          variant="outline"
          @click="$inertia.visit(taskRoutes.show(task.id))"
        >
          Back to Task
        </Button>
      </div>
    </div>

    <Card>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead>Execution ID</TableHead>
              <TableHead>Status</TableHead>
              <TableHead>Start Time</TableHead>
              <TableHead>End Time</TableHead>
              <TableHead>Duration</TableHead>
              <TableHead>Tokens</TableHead>
              <TableHead class="text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow v-for="execution in executions.data" :key="execution.id">
              <TableCell class="font-mono text-sm">
                {{ execution.task_execution_id.slice(0, 8) }}...
              </TableCell>
              <TableCell>
                <Badge :variant="getStatusVariant(execution.status)">
                  {{ execution.status }}
                </Badge>
              </TableCell>
              <TableCell>{{ formatDate(execution.start_time) }}</TableCell>
              <TableCell>{{ formatDate(execution.end_time) }}</TableCell>
              <TableCell>{{ execution.duration ? `${execution.duration}s` : '-' }}</TableCell>
              <TableCell>{{ execution.tokens || '-' }}</TableCell>
              <TableCell class="text-right">
                <Button
                  variant="outline"
                  size="sm"
                  @click="$inertia.visit(taskRoutes.executions.show([task.id, execution.id]))"
                >
                  View Details
                </Button>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <div v-if="executions.last_page > 1" class="mt-4 flex justify-center">
      <nav class="flex gap-2">
        <Button
          v-for="page in executions.last_page"
          :key="page"
          :variant="page === executions.current_page ? 'default' : 'outline'"
          size="sm"
          @click="$inertia.visit(executions.path + '?page=' + page)"
        >
          {{ page }}
        </Button>
      </nav>
    </div>
  </div>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
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
import { Play } from 'lucide-vue-next'
import taskRoutes from '@/routes/tasks'

interface TaskExecution {
  id: number
  task_execution_id: string
  status: string
  start_time: string | null
  end_time: string | null
  duration: number | null
  tokens: number | null
}

interface Task {
  id: number
  name: string
  is_active: boolean
}

interface PaginatedExecutions {
  data: TaskExecution[]
  current_page: number
  last_page: number
  path: string
}

interface Props {
  task: Task
  executions: PaginatedExecutions
}

const props = defineProps<Props>()

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

const runTask = () => {
  router.post(taskRoutes.executions.store(props.task.id))
}
</script>
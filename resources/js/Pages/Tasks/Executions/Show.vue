<template>
  <div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold">Execution Details</h1>
        <p class="text-muted-foreground">{{ task.name }} - {{ execution.task_execution_id }}</p>
      </div>
      <Button
        variant="outline"
        @click="$inertia.visit(taskRoutes.executions.index(task.id))"
      >
        Back to Executions
      </Button>
    </div>

    <div class="grid gap-6">
      <Card>
        <CardHeader>
          <CardTitle>Execution Information</CardTitle>
        </CardHeader>
        <CardContent>
          <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Status</dt>
              <dd class="mt-1">
                <Badge :variant="getStatusVariant(execution.status)" class="text-base">
                  {{ execution.status }}
                </Badge>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Execution ID</dt>
              <dd class="mt-1 text-sm font-mono">{{ execution.task_execution_id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Start Time</dt>
              <dd class="mt-1 text-sm">{{ formatDate(execution.start_time) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">End Time</dt>
              <dd class="mt-1 text-sm">{{ formatDate(execution.end_time) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Duration</dt>
              <dd class="mt-1 text-sm">{{ execution.duration ? `${execution.duration} seconds` : '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Tokens Used</dt>
              <dd class="mt-1 text-sm">{{ execution.tokens || '-' }}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <Card v-if="execution.input && Object.keys(execution.input).length > 0">
        <CardHeader>
          <CardTitle>Input Data</CardTitle>
        </CardHeader>
        <CardContent>
          <pre class="bg-muted p-4 rounded-md overflow-auto text-sm">{{ JSON.stringify(execution.input, null, 2) }}</pre>
        </CardContent>
      </Card>

      <Card v-if="execution.output">
        <CardHeader>
          <CardTitle>Output Data</CardTitle>
        </CardHeader>
        <CardContent>
          <pre class="bg-muted p-4 rounded-md overflow-auto text-sm">{{ JSON.stringify(execution.output, null, 2) }}</pre>
        </CardContent>
      </Card>

      <Card v-if="execution.track && execution.track.length > 0">
        <CardHeader>
          <CardTitle>Execution Track (Logs)</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="(log, index) in execution.track" :key="index" class="border-l-2 border-muted pl-4">
              <div class="font-medium">Step {{ index + 1 }}</div>
              <pre class="bg-muted p-2 rounded-md overflow-auto text-sm mt-2">{{ JSON.stringify(log, null, 2) }}</pre>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import taskRoutes from '@/routes/tasks'

interface TaskExecution {
  id: number
  task_execution_id: string
  status: string
  start_time: string | null
  end_time: string | null
  duration: number | null
  tokens: number | null
  input: any
  output: any
  track: any[]
}

interface Task {
  id: number
  name: string
}

interface Props {
  task: Task
  execution: TaskExecution
}

defineProps<Props>()

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
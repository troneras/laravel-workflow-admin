<template>
  <div class="container mx-auto py-8">
    <div class="flex justify-between items-center mb-6">
      <div>
        <h1 class="text-3xl font-bold">Execution Details</h1>
        <p class="text-muted-foreground">{{ task.name }} - {{ currentExecution.task_execution_id }}</p>
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
                <Badge :variant="getStatusVariant(currentExecution.status)" class="text-base">
                  {{ currentExecution.status }}
                </Badge>
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Execution ID</dt>
              <dd class="mt-1 text-sm font-mono">{{ currentExecution.task_execution_id }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Start Time</dt>
              <dd class="mt-1 text-sm">{{ formatDate(currentExecution.start_time) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">End Time</dt>
              <dd class="mt-1 text-sm">{{ formatDate(currentExecution.end_time) }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Duration</dt>
              <dd class="mt-1 text-sm">{{ currentExecution.duration ? `${currentExecution.duration} seconds` : '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-muted-foreground">Tokens Used</dt>
              <dd class="mt-1 text-sm">{{ currentExecution.tokens || '-' }}</dd>
            </div>
          </dl>
        </CardContent>
      </Card>

      <Card v-if="currentExecution.input && Object.keys(currentExecution.input).length > 0">
        <CardHeader>
          <CardTitle>Input Data</CardTitle>
        </CardHeader>
        <CardContent>
          <pre class="bg-muted p-4 rounded-md overflow-auto text-sm">{{ JSON.stringify(currentExecution.input, null, 2) }}</pre>
        </CardContent>
      </Card>

      <Card v-if="currentExecution.output">
        <CardHeader>
          <CardTitle>Output Data</CardTitle>
        </CardHeader>
        <CardContent>
          <pre class="bg-muted p-4 rounded-md overflow-auto text-sm">{{ JSON.stringify(currentExecution.output, null, 2) }}</pre>
        </CardContent>
      </Card>

      <Card v-if="currentStreamEvents && Object.keys(currentStreamEvents).length > 0">
        <CardHeader>
          <CardTitle>Stream Events</CardTitle>
          <p class="text-sm text-muted-foreground">Real-time workflow execution events</p>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="(events, eventType) in currentStreamEvents" :key="eventType">
              <h4 class="font-medium text-sm capitalize mb-2">{{ eventType.replace(/_/g, ' ') }}</h4>
              <div class="space-y-2">
                <div v-for="(event, index) in events" :key="index" class="border-l-2 border-muted pl-4">
                  <div class="text-xs text-muted-foreground">{{ formatDate(event.event_timestamp) }}</div>
                  <pre class="bg-muted p-2 rounded-md overflow-auto text-sm mt-1">{{ JSON.stringify(event.event_data, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <Card v-if="currentExecution.track && currentExecution.track.length > 0">
        <CardHeader>
          <CardTitle>Execution Track (Logs)</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="space-y-4">
            <div v-for="(log, index) in currentExecution.track" :key="index" class="border-l-2 border-muted pl-4">
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
import { ref, onMounted, onUnmounted } from 'vue'
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

interface StreamEvent {
  event_type: string
  event_timestamp: string
  event_data: any
}

interface Props {
  task: Task
  execution: TaskExecution
  streamEvents?: Record<string, StreamEvent[]>
}

const props = defineProps<Props>()
const currentExecution = ref(props.execution)
const currentStreamEvents = ref(props.streamEvents || {})
let pollingInterval: NodeJS.Timeout | null = null

const pollExecutionStatus = async () => {
  if (currentExecution.value.status === 'completed' || currentExecution.value.status === 'failed') {
    if (pollingInterval) {
      clearInterval(pollingInterval)
      pollingInterval = null
    }
    return
  }

  try {
    const statusUrl = taskRoutes.executions.status.url([props.task.id, currentExecution.value.id])
    const response = await fetch(statusUrl)
    if (response.ok) {
      const data = await response.json()
      currentExecution.value = {
        ...currentExecution.value,
        ...data.execution
      }
      
      // Update stream events
      if (data.stream_events && data.stream_events.length > 0) {
        const groupedEvents = data.stream_events.reduce((acc: any, event: StreamEvent) => {
          if (!acc[event.event_type]) acc[event.event_type] = []
          acc[event.event_type].push(event)
          return acc
        }, {})
        currentStreamEvents.value = groupedEvents
      }
      
      // Stop polling if execution is complete
      if (data.execution.status === 'completed' || data.execution.status === 'failed') {
        if (pollingInterval) {
          clearInterval(pollingInterval)
          pollingInterval = null
        }
      }
    }
  } catch (error) {
    console.error('Error polling execution status:', error)
  }
}

onMounted(() => {
  if (currentExecution.value.status === 'running' || currentExecution.value.status === 'pending') {
    pollingInterval = setInterval(pollExecutionStatus, 2000)
  }
})

onUnmounted(() => {
  if (pollingInterval) {
    clearInterval(pollingInterval)
  }
})

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
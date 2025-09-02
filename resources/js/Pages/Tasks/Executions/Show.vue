<template>
  <Head title="Execution Details" />

  <AppLayout :breadcrumbs="breadcrumbs">
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

      <!-- Workflow Execution Timeline -->
      <Card v-if="groupedNodeEvents && Object.keys(groupedNodeEvents).length > 0">
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <div class="w-2 h-2 bg-primary rounded-full"></div>
            Workflow Execution Timeline
          </CardTitle>
          <p class="text-sm text-muted-foreground">Step-by-step execution flow with detailed node information</p>
        </CardHeader>
        <CardContent>
          <div class="space-y-6">
            <!-- Workflow Started Event -->
            <div v-if="workflowStartEvent" class="relative">
              <div class="flex items-start gap-4">
                <div class="flex flex-col items-center">
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center border-2 border-blue-500">
                    <PlayIcon class="h-5 w-5 text-blue-600" />
                  </div>
                  <div class="w-0.5 h-8 bg-border mt-2"></div>
                </div>
                <div class="flex-1 pb-8">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-blue-700">Workflow Started</h3>
                    <Badge variant="outline" class="text-xs">
                      {{ formatDate(workflowStartEvent.event_timestamp) }}
                    </Badge>
                  </div>
                  <p class="text-sm text-muted-foreground mt-1">Execution ID: {{ workflowStartEvent.event_data.workflow_run_id }}</p>
                  <Collapsible v-if="workflowStartEvent.event_data.data?.inputs">
                    <CollapsibleTrigger asChild>
                      <Button variant="ghost" size="sm" class="mt-2 p-0 h-auto">
                        <ChevronRightIcon class="h-4 w-4 mr-1" />
                        View Input Parameters
                      </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent class="mt-2">
                      <div class="bg-muted p-3 rounded-md">
                        <pre class="text-xs overflow-auto">{{ JSON.stringify(workflowStartEvent.event_data.data.inputs, null, 2) }}</pre>
                      </div>
                    </CollapsibleContent>
                  </Collapsible>
                </div>
              </div>
            </div>

            <!-- Node Events -->
            <div v-for="(nodeGroup, nodeId) in groupedNodeEvents" :key="nodeId" class="relative">
              <div class="flex items-start gap-4">
                <div class="flex flex-col items-center">
                  <div :class="getNodeIconClass(nodeGroup.nodeType)">
                    <component :is="getNodeIcon(nodeGroup.nodeType)" class="h-5 w-5" :class="getNodeIconColor(nodeGroup.nodeType)" />
                  </div>
                  <div v-if="!isLastNode(nodeId)" class="w-0.5 h-8 bg-border mt-2"></div>
                </div>
                <div class="flex-1 pb-8">
                  <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold" :class="getNodeTitleColor(nodeGroup.nodeType)">
                      {{ nodeGroup.title }} Node
                      <Badge variant="secondary" class="ml-2 text-xs">{{ nodeGroup.nodeType }}</Badge>
                    </h3>
                    <div class="flex items-center gap-2">
                      <Badge :variant="getStatusVariant(nodeGroup.status)" class="text-xs">
                        {{ nodeGroup.status }}
                      </Badge>
                      <Tooltip>
                        <TooltipTrigger>
                          <ClockIcon class="h-4 w-4 text-muted-foreground" />
                        </TooltipTrigger>
                        <TooltipContent>
                          <p>Duration: {{ nodeGroup.duration }}s</p>
                        </TooltipContent>
                      </Tooltip>
                    </div>
                  </div>
                  
                  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Started</h4>
                      <p class="text-sm">{{ formatTime(nodeGroup.startedAt) }}</p>
                    </div>
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Completed</h4>
                      <p class="text-sm">{{ formatTime(nodeGroup.finishedAt) }}</p>
                    </div>
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Duration</h4>
                      <p class="text-sm">{{ nodeGroup.duration }}s</p>
                    </div>
                  </div>

                  <!-- LLM Node specific information -->
                  <div v-if="nodeGroup.nodeType === 'llm' && nodeGroup.llmData">
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 border border-purple-200 rounded-md p-4 mb-4">
                      <h4 class="text-sm font-medium text-purple-900 mb-2 flex items-center gap-2">
                        <CpuChipIcon class="h-4 w-4" />
                        AI Processing Details
                      </h4>
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                        <div>
                          <span class="font-medium text-purple-700">Model:</span>
                          <p class="text-purple-600 mt-1">{{ nodeGroup.llmData.model_name }}</p>
                        </div>
                        <div>
                          <span class="font-medium text-purple-700">Tokens:</span>
                          <p class="text-purple-600 mt-1">{{ nodeGroup.llmData.total_tokens }}</p>
                        </div>
                        <div>
                          <span class="font-medium text-purple-700">Cost:</span>
                          <p class="text-purple-600 mt-1">${{ nodeGroup.llmData.total_price }}</p>
                        </div>
                        <div>
                          <span class="font-medium text-purple-700">Latency:</span>
                          <p class="text-purple-600 mt-1">{{ Math.round(nodeGroup.llmData.latency * 1000) }}ms</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Input/Output Data -->
                  <Accordion type="single" collapsible class="w-full">
                    <AccordionItem value="inputs" v-if="nodeGroup.inputs">
                      <AccordionTrigger class="text-sm py-2">
                        <div class="flex items-center gap-2">
                          <ArrowRightIcon class="h-4 w-4 text-green-600" />
                          Input Data
                        </div>
                      </AccordionTrigger>
                      <AccordionContent>
                        <div class="bg-green-50 border border-green-200 p-3 rounded-md">
                          <pre class="text-xs overflow-auto text-green-800">{{ JSON.stringify(nodeGroup.inputs, null, 2) }}</pre>
                        </div>
                      </AccordionContent>
                    </AccordionItem>
                    
                    <AccordionItem value="outputs" v-if="nodeGroup.outputs">
                      <AccordionTrigger class="text-sm py-2">
                        <div class="flex items-center gap-2">
                          <ArrowLeftIcon class="h-4 w-4 text-blue-600" />
                          Output Data
                        </div>
                      </AccordionTrigger>
                      <AccordionContent>
                        <div class="bg-blue-50 border border-blue-200 p-3 rounded-md">
                          <pre class="text-xs overflow-auto text-blue-800">{{ JSON.stringify(nodeGroup.outputs, null, 2) }}</pre>
                        </div>
                      </AccordionContent>
                    </AccordionItem>
                  </Accordion>
                </div>
              </div>
            </div>

            <!-- Workflow Finished Event -->
            <div v-if="workflowFinishedEvent" class="relative">
              <div class="flex items-start gap-4">
                <div class="flex flex-col items-center">
                  <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center border-2 border-green-500">
                    <CheckIcon class="h-5 w-5 text-green-600" />
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-green-700">Workflow Completed</h3>
                    <Badge variant="outline" class="text-xs">
                      {{ formatDate(workflowFinishedEvent.event_timestamp) }}
                    </Badge>
                  </div>
                  <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3">
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Total Steps</h4>
                      <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.total_steps }}</p>
                    </div>
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Total Duration</h4>
                      <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.elapsed_time }}s</p>
                    </div>
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Total Tokens</h4>
                      <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.total_tokens }}</p>
                    </div>
                    <div class="bg-muted p-3 rounded-md">
                      <h4 class="text-xs font-medium text-muted-foreground mb-1">Status</h4>
                      <Badge :variant="getStatusVariant(workflowFinishedEvent.event_data.data.status)">
                        {{ workflowFinishedEvent.event_data.data.status }}
                      </Badge>
                    </div>
                  </div>
                  <Collapsible v-if="workflowFinishedEvent.event_data.data?.outputs">
                    <CollapsibleTrigger asChild>
                      <Button variant="ghost" size="sm" class="mt-3 p-0 h-auto">
                        <ChevronRightIcon class="h-4 w-4 mr-1" />
                        View Final Output
                      </Button>
                    </CollapsibleTrigger>
                    <CollapsibleContent class="mt-2">
                      <div class="bg-green-50 border border-green-200 p-3 rounded-md">
                        <pre class="text-xs overflow-auto text-green-800">{{ JSON.stringify(workflowFinishedEvent.event_data.data.outputs, null, 2) }}</pre>
                      </div>
                    </CollapsibleContent>
                  </Collapsible>
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
  </AppLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'
import AppLayout from '@/layouts/AppLayout.vue'
import { Head } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible'
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion'
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip'
import { 
  PlayIcon, 
  CheckIcon, 
  ClockIcon, 
  CpuChipIcon, 
  ArrowRightIcon, 
  ArrowLeftIcon, 
  ChevronRightIcon,
  StopIcon
} from '@heroicons/vue/24/outline'
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
  {
    title: 'Execution Details',
    href: taskRoutes.executions.show([props.task.id, props.execution.id]).url,
  },
]

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
    case 'succeeded':
      return 'default'
    case 'running':
      return 'outline'
    case 'failed':
      return 'destructive'
    default:
      return 'secondary'
  }
}

const formatTime = (timestamp: string | null) => {
  if (!timestamp) return '-'
  return new Date(timestamp).toLocaleTimeString()
}

// Process stream events into grouped node data
const groupedNodeEvents = computed(() => {
  if (!currentStreamEvents.value || Object.keys(currentStreamEvents.value).length === 0) {
    return {}
  }

  const allEvents = Object.values(currentStreamEvents.value).flat()
  const nodeGroups: Record<string, any> = {}

  // Group events by node_id
  for (const event of allEvents) {
    const nodeId = event.event_data?.data?.node_id
    if (!nodeId) continue

    if (!nodeGroups[nodeId]) {
      nodeGroups[nodeId] = {
        nodeId,
        nodeType: event.event_data.data.node_type,
        title: event.event_data.data.title,
        index: event.event_data.data.index,
        status: 'pending',
        startedAt: null,
        finishedAt: null,
        duration: 0,
        inputs: null,
        outputs: null,
        llmData: null
      }
    }

    const group = nodeGroups[nodeId]
    const eventData = event.event_data.data

    if (event.event_type === 'node_started') {
      group.startedAt = event.event_timestamp
    } else if (event.event_type === 'node_finished') {
      group.finishedAt = event.event_timestamp
      group.status = eventData.status || 'completed'
      group.duration = eventData.elapsed_time || 0
      group.inputs = eventData.inputs
      group.outputs = eventData.outputs
      
      // Extract LLM specific data
      if (eventData.node_type === 'llm' && eventData.process_data?.usage) {
        group.llmData = {
          model_name: eventData.process_data.model_name,
          total_tokens: eventData.process_data.usage.total_tokens,
          total_price: eventData.process_data.usage.total_price,
          latency: eventData.process_data.usage.latency
        }
      }
    }
  }

  // Sort by index
  const sortedGroups = Object.fromEntries(
    Object.entries(nodeGroups).sort(([,a], [,b]) => a.index - b.index)
  )

  return sortedGroups
})

// Get workflow start event
const workflowStartEvent = computed(() => {
  if (!currentStreamEvents.value?.workflow_started) return null
  return currentStreamEvents.value.workflow_started[0] || null
})

// Get workflow finished event
const workflowFinishedEvent = computed(() => {
  if (!currentStreamEvents.value?.workflow_finished) return null
  return currentStreamEvents.value.workflow_finished[0] || null
})

// Helper functions for node styling
const getNodeIcon = (nodeType: string) => {
  switch (nodeType) {
    case 'start':
      return PlayIcon
    case 'llm':
      return CpuChipIcon
    case 'end':
      return StopIcon
    default:
      return CpuChipIcon
  }
}

const getNodeIconClass = (nodeType: string) => {
  const baseClass = 'w-10 h-10 rounded-full flex items-center justify-center border-2'
  switch (nodeType) {
    case 'start':
      return `${baseClass} bg-green-100 border-green-500`
    case 'llm':
      return `${baseClass} bg-purple-100 border-purple-500`
    case 'end':
      return `${baseClass} bg-orange-100 border-orange-500`
    default:
      return `${baseClass} bg-gray-100 border-gray-500`
  }
}

const getNodeIconColor = (nodeType: string) => {
  switch (nodeType) {
    case 'start':
      return 'text-green-600'
    case 'llm':
      return 'text-purple-600'
    case 'end':
      return 'text-orange-600'
    default:
      return 'text-gray-600'
  }
}

const getNodeTitleColor = (nodeType: string) => {
  switch (nodeType) {
    case 'start':
      return 'text-green-700'
    case 'llm':
      return 'text-purple-700'
    case 'end':
      return 'text-orange-700'
    default:
      return 'text-gray-700'
  }
}

const isLastNode = (nodeId: string) => {
  const nodeIds = Object.keys(groupedNodeEvents.value)
  return nodeIds[nodeIds.length - 1] === nodeId
}
</script>
<template>
  <div class="container mx-auto py-8 max-w-2xl">
    <div class="mb-6">
      <h1 class="text-3xl font-bold">Edit Task</h1>
    </div>

    <Card>
      <CardHeader>
        <CardTitle>Task Details</CardTitle>
        <CardDescription>
          Update task configuration
        </CardDescription>
      </CardHeader>
      <CardContent>
        <form @submit.prevent="submit" class="space-y-4">
          <div class="space-y-2">
            <Label for="name">Name</Label>
            <Input
              id="name"
              v-model="form.name"
              placeholder="Enter task name"
              :class="{ 'border-destructive': form.errors.name }"
            />
            <p v-if="form.errors.name" class="text-sm text-destructive">
              {{ form.errors.name }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="description">Description</Label>
            <Textarea
              id="description"
              v-model="form.description"
              placeholder="Enter task description (optional)"
              :class="{ 'border-destructive': form.errors.description }"
            />
            <p v-if="form.errors.description" class="text-sm text-destructive">
              {{ form.errors.description }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="dify_workflow_id">Workflow</Label>
            <Select v-model="form.dify_workflow_id">
              <SelectTrigger>
                <SelectValue placeholder="Select a workflow" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="workflow in workflows" :key="workflow.id" :value="workflow.id.toString()">
                  {{ workflow.name }}
                </SelectItem>
              </SelectContent>
            </Select>
            <p v-if="form.errors.dify_workflow_id" class="text-sm text-destructive">
              {{ form.errors.dify_workflow_id }}
            </p>
          </div>

          <div class="space-y-2">
            <Label for="input_schema">Input Schema (JSON)</Label>
            <Textarea
              id="input_schema"
              v-model="inputSchemaJson"
              placeholder='{"key": "value"}'
              rows="4"
              :class="{ 'border-destructive': form.errors.input_schema || jsonError }"
            />
            <p v-if="jsonError" class="text-sm text-destructive">
              {{ jsonError }}
            </p>
            <p v-if="form.errors.input_schema" class="text-sm text-destructive">
              {{ form.errors.input_schema }}
            </p>
          </div>

          <div class="flex items-center space-x-2">
            <Switch
              id="is_active"
              v-model:checked="form.is_active"
            />
            <Label for="is_active">Active</Label>
          </div>

          <div class="flex justify-end gap-2 pt-4">
            <Button
              type="button"
              variant="outline"
              @click="$inertia.visit(taskRoutes.index())"
            >
              Cancel
            </Button>
            <Button type="submit" :disabled="form.processing">
              Update Task
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Switch } from '@/components/ui/switch'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import taskRoutes from '@/routes/tasks'

interface DifyWorkflow {
  id: number
  name: string
  description: string | null
  workflow_id: string
  is_active: boolean
}

interface Task {
  id: number
  name: string
  description: string | null
  dify_workflow_id: number
  input_schema: any
  is_active: boolean
  dify_workflow: DifyWorkflow
}

interface Props {
  task: Task
  workflows: DifyWorkflow[]
}

const props = defineProps<Props>()

const form = useForm({
  name: props.task.name,
  description: props.task.description || '',
  dify_workflow_id: props.task.dify_workflow_id.toString(),
  input_schema: props.task.input_schema,
  is_active: props.task.is_active,
})

const inputSchemaJson = ref('')
const jsonError = ref('')

onMounted(() => {
  if (props.task.input_schema) {
    inputSchemaJson.value = JSON.stringify(props.task.input_schema, null, 2)
  }
})

watch(inputSchemaJson, (value) => {
  if (!value) {
    form.input_schema = null
    jsonError.value = ''
    return
  }

  try {
    form.input_schema = JSON.parse(value)
    jsonError.value = ''
  } catch {
    jsonError.value = 'Invalid JSON format'
  }
})

const submit = () => {
  form.put(taskRoutes.update(props.task.id))
}
</script>
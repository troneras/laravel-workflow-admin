<template>
    <Head title="Execution Details" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-7xl py-8">
                <!-- Enhanced Header Section -->
                <div
                    class="relative mb-8 overflow-hidden rounded-2xl border border-white/20 bg-white shadow-xl dark:border-slate-700/50 dark:bg-slate-800"
                >
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-blue-600/5 via-purple-600/5 to-indigo-600/5 dark:from-blue-400/10 dark:via-purple-400/10 dark:to-indigo-400/10"
                    ></div>
                    <div class="relative p-8">
                        <div class="flex flex-col gap-6 lg:flex-row lg:items-center lg:justify-between">
                            <div class="space-y-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 shadow-lg"
                                    >
                                        <CpuChipIcon class="h-6 w-6 text-white" />
                                    </div>
                                    <div>
                                        <h1
                                            class="bg-gradient-to-r from-gray-900 to-gray-700 bg-clip-text text-3xl font-bold text-transparent dark:from-white dark:to-gray-200"
                                        >
                                            Execution Details
                                        </h1>
                                        <div class="mt-1 flex items-center gap-2">
                                            <Badge :variant="getStatusVariant(currentExecution.status)" class="text-sm font-medium">
                                                {{ currentExecution.status }}
                                            </Badge>
                                            <span class="text-muted-foreground">•</span>
                                            <span class="font-mono text-sm text-muted-foreground">{{ currentExecution.task_execution_id }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-blue-500"></div>
                                    <p class="font-medium text-muted-foreground">{{ task.name }}</p>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 sm:flex-row">
                                <Button
                                    variant="outline"
                                    class="transition-colors hover:bg-gray-50 dark:hover:bg-slate-700"
                                    @click="$inertia.visit(taskRoutes.executions.index(task.id))"
                                >
                                    <ArrowLeftIcon class="mr-2 h-4 w-4" />
                                    Back to Executions
                                </Button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid min-w-0 gap-8">
                    <!-- Enhanced Execution Information Card -->
                    <Card class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                                    <CheckIcon class="h-4 w-4 text-white" />
                                </div>
                                Execution Overview
                            </CardTitle>
                            <p class="text-muted-foreground">Comprehensive execution metrics and timing information</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                                <!-- Status Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <div class="mb-3 flex items-center justify-between">
                                        <dt class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Status</dt>
                                        <div class="h-2 w-2 rounded-full" :class="getStatusDotColor(currentExecution.status)"></div>
                                    </div>
                                    <dd class="flex items-center gap-2">
                                        <Badge :variant="getStatusVariant(currentExecution.status)" class="px-3 py-1 text-sm font-medium">
                                            {{ currentExecution.status }}
                                        </Badge>
                                    </dd>
                                </div>

                                <!-- Execution ID Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Execution ID</dt>
                                    <dd class="rounded-lg border bg-gray-50 px-3 py-2 font-mono text-sm dark:border-slate-500 dark:bg-slate-600">
                                        {{ currentExecution.task_execution_id }}
                                    </dd>
                                </div>

                                <!-- Duration Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Duration</dt>
                                    <dd class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ workflowFinishedEvent?.event_data?.data?.elapsed_time ? `${workflowFinishedEvent.event_data.data.elapsed_time}s` : currentExecution.duration ? `${currentExecution.duration}s` : '-' }}
                                    </dd>
                                </div>

                                <!-- Start Time Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Started At</dt>
                                    <dd class="text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <ClockIcon class="h-4 w-4 text-green-500" />
                                            {{ formatDate(currentExecution.start_time) }}
                                        </div>
                                    </dd>
                                </div>

                                <!-- End Time Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Completed At</dt>
                                    <dd class="text-sm text-gray-700 dark:text-gray-300">
                                        <div class="flex items-center gap-2">
                                            <ClockIcon class="h-4 w-4 text-blue-500" />
                                            {{ formatDate(currentExecution.end_time) }}
                                        </div>
                                    </dd>
                                </div>

                                <!-- Tokens Card -->
                                <div
                                    class="group rounded-xl border border-gray-100 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-slate-600 dark:bg-slate-700"
                                >
                                    <dt class="mb-3 text-sm font-medium tracking-wide text-muted-foreground uppercase">Tokens Used</dt>
                                    <dd class="text-2xl font-bold text-gray-900 dark:text-white">
                                        {{ currentExecution.tokens ? currentExecution.tokens.toLocaleString() : '-' }}
                                    </dd>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- API Execution Metadata Card -->
                    <Card
                        v-if="currentExecution.metadata && currentExecution.metadata.api_execution"
                        class="relative overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-orange-50/30 via-yellow-50/20 to-amber-50/30 dark:from-orange-900/20 dark:via-yellow-900/15 dark:to-amber-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-orange-500 to-amber-600">
                                    <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                </div>
                                API Execution Details
                            </CardTitle>
                            <p class="text-muted-foreground">Information about this API-initiated execution and webhook delivery</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- API Metadata -->
                                <div class="space-y-4">
                                    <h3 class="text-lg font-semibold">Execution Context</h3>
                                    <div class="space-y-3">
                                        <div v-if="currentExecution.metadata.service_name" class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Service:</span>
                                            <Badge variant="outline">{{ currentExecution.metadata.service_name }}</Badge>
                                        </div>
                                        <div v-if="currentExecution.metadata.operation" class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Operation:</span>
                                            <Badge variant="outline">{{ currentExecution.metadata.operation }}</Badge>
                                        </div>
                                        <div v-if="currentExecution.metadata.reference_id" class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Reference ID:</span>
                                            <code class="rounded bg-muted px-2 py-1 text-xs">{{ currentExecution.metadata.reference_id }}</code>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Created via:</span>
                                            <Badge variant="secondary">{{ currentExecution.metadata.created_via || 'API' }}</Badge>
                                        </div>
                                    </div>
                                </div>

                                <!-- Webhook Information -->
                                <div class="space-y-4">
                                    <div class="flex items-center justify-between">
                                        <h3 class="text-lg font-semibold">Webhook Status</h3>
                                        <Button
                                            v-if="webhookStatus.has_webhook && webhookStatus.is_retryable"
                                            size="sm"
                                            variant="outline"
                                            @click="resendWebhook"
                                            :disabled="resendingWebhook"
                                        >
                                            {{ resendingWebhook ? 'Sending...' : 'Resend Webhook' }}
                                        </Button>
                                    </div>

                                    <div v-if="webhookStatus.has_webhook" class="space-y-3">
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">URL:</span>
                                            <code class="rounded bg-muted px-2 py-1 text-xs max-w-64 truncate">{{ webhookStatus.webhook_url }}</code>
                                        </div>
                                        <div class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Attempts:</span>
                                            <div class="flex items-center gap-2">
                                                <Badge :variant="webhookStatus.successful_attempts > 0 ? 'default' : 'destructive'">
                                                    {{ webhookStatus.total_attempts }} total
                                                </Badge>
                                                <span class="text-xs text-muted-foreground">
                                                    ({{ webhookStatus.successful_attempts }} success, {{ webhookStatus.failed_attempts }} failed)
                                                </span>
                                            </div>
                                        </div>
                                        <div v-if="webhookStatus.last_attempt" class="flex items-center justify-between">
                                            <span class="text-sm font-medium text-muted-foreground">Last Status:</span>
                                            <div class="flex items-center gap-2">
                                                <Badge
                                                    :variant="webhookStatus.last_attempt.status === 'success' ? 'default' : 'destructive'"
                                                >
                                                    {{ webhookStatus.last_attempt.status }}
                                                </Badge>
                                                <span v-if="webhookStatus.last_attempt.http_status" class="text-xs text-muted-foreground">
                                                    HTTP {{ webhookStatus.last_attempt.http_status }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div v-else class="text-sm text-muted-foreground">
                                        No webhook configured for this execution.
                                    </div>
                                </div>
                            </div>

                            <!-- Webhook Attempts History -->
                            <div v-if="webhookStatus.has_webhook && webhookAttempts.length > 0" class="mt-6">
                                <Collapsible>
                                    <CollapsibleTrigger asChild>
                                        <Button variant="ghost" class="w-full justify-start">
                                            <ChevronRightIcon class="mr-2 h-4 w-4" />
                                            View Webhook Attempt History ({{ webhookAttempts.length }})
                                        </Button>
                                    </CollapsibleTrigger>
                                    <CollapsibleContent class="mt-4">
                                        <div class="space-y-4">
                                            <div
                                                v-for="attempt in webhookAttempts"
                                                :key="attempt.id"
                                                class="rounded-lg border border-gray-200 p-4 dark:border-gray-700"
                                            >
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="flex items-center gap-2">
                                                        <Badge :variant="attempt.status === 'success' ? 'default' : 'destructive'">
                                                            Attempt #{{ attempt.attempt_number }}
                                                        </Badge>
                                                        <span class="text-sm text-muted-foreground">
                                                            {{ formatDate(attempt.attempted_at) }}
                                                        </span>
                                                    </div>
                                                    <div class="flex items-center gap-2">
                                                        <span v-if="attempt.http_status" class="text-sm">
                                                            HTTP {{ attempt.http_status }}
                                                        </span>
                                                        <span v-if="attempt.response_time_ms" class="text-xs text-muted-foreground">
                                                            {{ Math.round(attempt.response_time_ms) }}ms
                                                        </span>
                                                    </div>
                                                </div>
                                                <div v-if="attempt.error_message" class="text-sm text-red-600 dark:text-red-400">
                                                    {{ attempt.error_message }}
                                                </div>
                                                <div v-if="attempt.response_body && attempt.response_body.length < 200" class="text-xs text-muted-foreground mt-2">
                                                    Response: {{ attempt.response_body }}
                                                </div>
                                            </div>
                                        </div>
                                    </CollapsibleContent>
                                </Collapsible>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Enhanced Input Data Card -->
                    <Card
                        v-if="currentExecution.input && Object.keys(currentExecution.input).length > 0"
                        class="relative min-w-0 overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-green-50/30 via-emerald-50/20 to-teal-50/30 dark:from-green-900/20 dark:via-emerald-900/15 dark:to-teal-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-green-500 to-emerald-600">
                                    <ArrowRightIcon class="h-4 w-4 text-white" />
                                </div>
                                Input Parameters
                            </CardTitle>
                            <p class="text-muted-foreground">Initial data provided to the workflow execution</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="group relative">
                                <div class="absolute top-4 right-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 text-xs"
                                        @click="copyToClipboard(JSON.stringify(currentExecution.input, null, 2))"
                                    >
                                        Copy JSON
                                    </Button>
                                </div>
                                <div
                                    style="overflow-x: auto; width: 100%"
                                    class="rounded-xl border border-green-200 bg-gradient-to-br from-green-50 to-emerald-50 dark:border-green-700 dark:from-green-900/30 dark:to-emerald-900/30"
                                >
                                    <pre
                                        class="p-6 font-mono text-sm leading-relaxed text-green-900 dark:text-green-100"
                                        style="white-space: pre; width: max-content"
                                        >{{ JSON.stringify(currentExecution.input, null, 2) }}</pre
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Enhanced Output Data Card -->
                    <Card
                        v-if="currentExecution.output"
                        class="relative min-w-0 overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-blue-50/30 via-indigo-50/20 to-purple-50/30 dark:from-blue-900/20 dark:via-indigo-900/15 dark:to-purple-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600">
                                    <ArrowLeftIcon class="h-4 w-4 text-white" />
                                </div>
                                Output Results
                            </CardTitle>
                            <p class="text-muted-foreground">Final data generated by the workflow execution</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="group relative">
                                <div class="absolute top-4 right-4 opacity-0 transition-opacity duration-200 group-hover:opacity-100">
                                    <Button
                                        size="sm"
                                        variant="outline"
                                        class="h-8 text-xs"
                                        @click="copyToClipboard(JSON.stringify(currentExecution.output, null, 2))"
                                    >
                                        Copy JSON
                                    </Button>
                                </div>
                                <div
                                    style="overflow-x: auto; width: 100%"
                                    class="rounded-xl border border-blue-200 bg-gradient-to-br from-blue-50 to-indigo-50 dark:border-blue-700 dark:from-blue-900/30 dark:to-indigo-900/30"
                                >
                                    <pre
                                        class="p-6 font-mono text-sm leading-relaxed text-blue-900 dark:text-blue-100"
                                        style="white-space: pre; width: max-content"
                                        >{{ JSON.stringify(currentExecution.output, null, 2) }}</pre
                                    >
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Enhanced Workflow Execution Timeline -->
                    <Card
                        v-if="groupedNodeEvents && Object.keys(groupedNodeEvents).length > 0"
                        class="relative min-w-0 overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-purple-50/30 via-indigo-50/20 to-blue-50/30 dark:from-purple-900/20 dark:via-indigo-900/15 dark:to-blue-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-purple-500 to-indigo-600">
                                    <div class="h-2 w-2 rounded-full bg-white"></div>
                                </div>
                                Workflow Execution Timeline
                            </CardTitle>
                            <p class="text-muted-foreground">Step-by-step execution flow with detailed node information and real-time progress</p>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-6">
                                <!-- Workflow Started Event -->
                                <div v-if="workflowStartEvent" class="relative">
                                    <div class="flex items-start gap-4">
                                        <div class="flex flex-col items-center">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-blue-500 bg-blue-100">
                                                <PlayIcon class="h-5 w-5 text-blue-600" />
                                            </div>
                                            <div class="mt-2 h-8 w-0.5 bg-border"></div>
                                        </div>
                                        <div class="min-w-0 flex-1 pb-8">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-semibold text-blue-700">Workflow Started</h3>
                                                <Badge variant="outline" class="text-xs">
                                                    {{ formatDate(workflowStartEvent.event_timestamp) }}
                                                </Badge>
                                            </div>
                                            <p class="mt-1 text-sm text-muted-foreground">
                                                Execution ID: {{ workflowStartEvent.event_data.workflow_run_id }}
                                            </p>
                                            <Collapsible v-if="workflowStartEvent.event_data.data?.inputs">
                                                <CollapsibleTrigger asChild>
                                                    <Button variant="ghost" size="sm" class="mt-2 h-auto p-0">
                                                        <ChevronRightIcon class="mr-1 h-4 w-4" />
                                                        View Input Parameters
                                                    </Button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent class="mt-2">
                                                    <div style="overflow-x: auto; width: 100%">
                                                        <pre class="rounded-md bg-muted p-3 text-xs" style="white-space: pre; width: max-content">{{
                                                            JSON.stringify(workflowStartEvent.event_data.data.inputs, null, 2)
                                                        }}</pre>
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
                                                <component
                                                    :is="getNodeIcon(nodeGroup.nodeType)"
                                                    class="h-5 w-5"
                                                    :class="getNodeIconColor(nodeGroup.nodeType)"
                                                />
                                            </div>
                                            <div v-if="!isLastNode(nodeId)" class="mt-2 h-8 w-0.5 bg-border"></div>
                                        </div>
                                        <div class="min-w-0 flex-1 pb-8">
                                            <div class="mb-2 flex items-center justify-between">
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

                                            <div class="mb-4 grid min-w-0 grid-cols-1 gap-4 md:grid-cols-3">
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Started</h4>
                                                    <p class="text-sm">{{ formatTime(nodeGroup.startedAt) }}</p>
                                                </div>
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Completed</h4>
                                                    <p class="text-sm">{{ formatTime(nodeGroup.finishedAt) }}</p>
                                                </div>
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Duration</h4>
                                                    <p class="text-sm">{{ nodeGroup.duration }}s</p>
                                                </div>
                                            </div>

                                            <!-- LLM Node specific information -->
                                            <div v-if="nodeGroup.nodeType === 'llm' && nodeGroup.llmData">
                                                <div class="mb-4 rounded-md border border-purple-200 bg-gradient-to-r from-purple-50 to-pink-50 p-4">
                                                    <h4 class="mb-2 flex items-center gap-2 text-sm font-medium text-purple-900">
                                                        <CpuChipIcon class="h-4 w-4" />
                                                        AI Processing Details
                                                    </h4>
                                                    <div class="grid min-w-0 grid-cols-2 gap-3 text-xs md:grid-cols-4">
                                                        <div class="min-w-0">
                                                            <span class="font-medium text-purple-700">Model:</span>
                                                            <p class="mt-1 truncate text-purple-600">{{ nodeGroup.llmData.model_name }}</p>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <span class="font-medium text-purple-700">Tokens:</span>
                                                            <p class="mt-1 text-purple-600">{{ nodeGroup.llmData.total_tokens }}</p>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <span class="font-medium text-purple-700">Cost:</span>
                                                            <p class="mt-1 text-purple-600">${{ nodeGroup.llmData.total_price || '0.00' }}</p>
                                                        </div>
                                                        <div class="min-w-0">
                                                            <span class="font-medium text-purple-700">Latency:</span>
                                                            <p class="mt-1 text-purple-600">{{ Math.round(nodeGroup.llmData.latency * 1000) }}ms</p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Input/Output Data -->
                                            <Accordion type="single" collapsible class="w-full">
                                                <AccordionItem value="inputs" v-if="nodeGroup.inputs">
                                                    <AccordionTrigger class="py-2 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <ArrowRightIcon class="h-4 w-4 text-green-600" />
                                                            Input Data
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div style="overflow-x: auto; width: 100%">
                                                            <pre
                                                                class="rounded-md border border-green-200 bg-green-50 p-3 text-xs text-green-800"
                                                                style="white-space: pre; width: max-content"
                                                                >{{ JSON.stringify(nodeGroup.inputs, null, 2) }}</pre
                                                            >
                                                        </div>
                                                    </AccordionContent>
                                                </AccordionItem>

                                                <AccordionItem value="outputs" v-if="nodeGroup.outputs">
                                                    <AccordionTrigger class="py-2 text-sm">
                                                        <div class="flex items-center gap-2">
                                                            <ArrowLeftIcon class="h-4 w-4 text-blue-600" />
                                                            Output Data
                                                        </div>
                                                    </AccordionTrigger>
                                                    <AccordionContent>
                                                        <div style="overflow-x: auto; width: 100%">
                                                            <pre
                                                                class="rounded-md border border-blue-200 bg-blue-50 p-3 text-xs text-blue-800"
                                                                style="white-space: pre; width: max-content"
                                                                >{{ JSON.stringify(nodeGroup.outputs, null, 2) }}</pre
                                                            >
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
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-green-500 bg-green-100"
                                            >
                                                <CheckIcon class="h-5 w-5 text-green-600" />
                                            </div>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h3 class="text-lg font-semibold text-green-700">Workflow Completed</h3>
                                                <Badge variant="outline" class="text-xs">
                                                    {{ formatDate(workflowFinishedEvent.event_timestamp) }}
                                                </Badge>
                                            </div>
                                            <div class="mt-3 grid min-w-0 grid-cols-2 gap-4 md:grid-cols-4">
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Total Steps</h4>
                                                    <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.total_steps }}</p>
                                                </div>
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Total Duration</h4>
                                                    <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.elapsed_time }}s</p>
                                                </div>
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Total Tokens</h4>
                                                    <p class="text-sm font-semibold">{{ workflowFinishedEvent.event_data.data.total_tokens }}</p>
                                                </div>
                                                <div class="rounded-md bg-muted p-3">
                                                    <h4 class="mb-1 text-xs font-medium text-muted-foreground">Status</h4>
                                                    <Badge :variant="getStatusVariant(workflowFinishedEvent.event_data.data.status)">
                                                        {{ workflowFinishedEvent.event_data.data.status }}
                                                    </Badge>
                                                </div>
                                            </div>
                                            <Collapsible v-if="workflowFinishedEvent.event_data.data?.outputs">
                                                <CollapsibleTrigger asChild>
                                                    <Button variant="ghost" size="sm" class="mt-3 h-auto p-0">
                                                        <ChevronRightIcon class="mr-1 h-4 w-4" />
                                                        View Final Output
                                                    </Button>
                                                </CollapsibleTrigger>
                                                <CollapsibleContent class="mt-2">
                                                    <div style="overflow-x: auto; width: 100%">
                                                        <pre
                                                            class="rounded-md border border-green-200 bg-green-50 p-3 text-xs text-green-800"
                                                            style="white-space: pre; width: max-content"
                                                            >{{ JSON.stringify(workflowFinishedEvent.event_data.data.outputs, null, 2) }}</pre
                                                        >
                                                    </div>
                                                </CollapsibleContent>
                                            </Collapsible>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Enhanced Execution Track (Logs) -->
                    <Card
                        v-if="currentExecution.track && currentExecution.track.length > 0"
                        class="relative min-w-0 overflow-hidden border-0 bg-white shadow-xl ring-1 ring-gray-100 dark:bg-slate-800 dark:ring-slate-700"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-amber-50/30 via-orange-50/20 to-red-50/30 dark:from-amber-900/20 dark:via-orange-900/15 dark:to-red-900/20"
                        ></div>
                        <CardHeader class="relative pb-6">
                            <CardTitle class="flex items-center gap-3 text-xl font-semibold">
                                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600">
                                    <ClockIcon class="h-4 w-4 text-white" />
                                </div>
                                Execution Logs & Tracking
                            </CardTitle>
                            <p class="text-muted-foreground">Detailed step-by-step execution logs and debugging information</p>
                        </CardHeader>
                        <CardContent class="relative">
                            <div class="space-y-6">
                                <div v-for="(log, index) in currentExecution.track" :key="index" class="group">
                                    <div class="flex items-start gap-4">
                                        <!-- Step indicator -->
                                        <div class="flex flex-col items-center">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full border-2 border-amber-300 bg-gradient-to-br from-amber-100 to-orange-100"
                                            >
                                                <span class="text-sm font-semibold text-amber-700">{{ index + 1 }}</span>
                                            </div>
                                            <div v-if="index < currentExecution.track.length - 1" class="mt-2 h-8 w-0.5 bg-amber-200"></div>
                                        </div>

                                        <!-- Log content -->
                                        <div class="min-w-0 flex-1">
                                            <div
                                                class="rounded-xl border border-amber-200 bg-white p-6 shadow-sm transition-all duration-200 hover:shadow-md dark:border-amber-700 dark:bg-slate-700"
                                            >
                                                <div class="mb-4 flex items-center justify-between">
                                                    <h3 class="text-lg font-semibold text-amber-800 dark:text-amber-200">Step {{ index + 1 }}</h3>
                                                    <div class="flex items-center gap-2">
                                                        <Badge
                                                            variant="outline"
                                                            class="border-amber-200 bg-amber-50 text-xs text-amber-700 dark:border-amber-700 dark:bg-amber-900/50 dark:text-amber-300"
                                                        >
                                                            Log Entry
                                                        </Badge>
                                                        <Button
                                                            size="sm"
                                                            variant="outline"
                                                            class="h-8 text-xs opacity-0 transition-opacity duration-200 group-hover:opacity-100"
                                                            @click="copyToClipboard(JSON.stringify(log, null, 2))"
                                                        >
                                                            Copy
                                                        </Button>
                                                    </div>
                                                </div>
                                                <div
                                                    style="overflow-x: auto; width: 100%"
                                                    class="rounded-lg border border-amber-200 bg-gradient-to-br from-amber-50 to-orange-50 dark:border-amber-700 dark:from-amber-900/30 dark:to-orange-900/30"
                                                >
                                                    <pre
                                                        class="p-4 font-mono text-sm leading-relaxed text-amber-900 dark:text-amber-100"
                                                        style="white-space: pre; width: max-content"
                                                        >{{ JSON.stringify(log, null, 2) }}</pre
                                                    >
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Accordion, AccordionContent, AccordionItem, AccordionTrigger } from '@/components/ui/accordion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import AppLayout from '@/layouts/AppLayout.vue';
import taskRoutes from '@/routes/tasks';
import type { BreadcrumbItem } from '@/types';
import { ArrowLeftIcon, ArrowRightIcon, CheckIcon, ChevronRightIcon, ClockIcon, CpuChipIcon, PlayIcon, StopIcon } from '@heroicons/vue/24/outline';
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';

interface TaskExecution {
    id: number;
    task_execution_id: string;
    status: string;
    start_time: string | null;
    end_time: string | null;
    duration: number | null;
    tokens: number | null;
    input: any;
    output: any;
    track: any[];
}

interface Task {
    id: number;
    name: string;
}

interface StreamEvent {
    event_type: string;
    event_timestamp: string;
    event_data: any;
}

interface WebhookAttempt {
    id: number;
    attempt_number: number;
    status: string;
    http_status: number | null;
    response_time_ms: number | null;
    attempted_at: string;
    error_message: string | null;
    response_body: string | null;
}

interface WebhookStatus {
    has_webhook: boolean;
    webhook_url: string | null;
    total_attempts: number;
    successful_attempts: number;
    failed_attempts: number;
    last_attempt: WebhookAttempt | null;
    last_status: string | null;
    is_retryable: boolean;
}

interface Props {
    task: Task;
    execution: TaskExecution;
    streamEvents?: Record<string, StreamEvent[]>;
    webhookStatus: WebhookStatus;
    webhookAttempts: WebhookAttempt[];
}

const props = defineProps<Props>();

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
];

const currentExecution = ref(props.execution);
const currentStreamEvents = ref(props.streamEvents || {});
const resendingWebhook = ref(false);
let pollingInterval: NodeJS.Timeout | null = null;

const pollExecutionStatus = async () => {
    if (currentExecution.value.status === 'completed' || currentExecution.value.status === 'failed') {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        return;
    }

    try {
        const statusUrl = taskRoutes.executions.status.url([props.task.id, currentExecution.value.id]);
        const response = await fetch(statusUrl);
        if (response.ok) {
            const data = await response.json();
            currentExecution.value = {
                ...currentExecution.value,
                ...data.execution,
            };

            // Update stream events
            if (data.stream_events && data.stream_events.length > 0) {
                const groupedEvents = data.stream_events.reduce((acc: any, event: StreamEvent) => {
                    if (!acc[event.event_type]) acc[event.event_type] = [];
                    acc[event.event_type].push(event);
                    return acc;
                }, {});
                currentStreamEvents.value = groupedEvents;
            }

            // Stop polling if execution is complete
            if (data.execution.status === 'completed' || data.execution.status === 'failed') {
                if (pollingInterval) {
                    clearInterval(pollingInterval);
                    pollingInterval = null;
                }
            }
        }
    } catch (error) {
        console.error('Error polling execution status:', error);
    }
};

onMounted(() => {
    if (currentExecution.value.status === 'running' || currentExecution.value.status === 'pending') {
        pollingInterval = setInterval(pollExecutionStatus, 2000);
    }
});

onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});

const formatDate = (date: string | null) => {
    if (!date) return '-';
    return new Date(date).toLocaleString();
};

const getStatusVariant = (status: string) => {
    switch (status) {
        case 'completed':
        case 'succeeded':
            return 'default';
        case 'running':
            return 'outline';
        case 'failed':
            return 'destructive';
        default:
            return 'secondary';
    }
};

const formatTime = (timestamp: string | null) => {
    if (!timestamp) return '-';
    return new Date(timestamp).toLocaleTimeString();
};

// Process stream events into grouped node data
const groupedNodeEvents = computed(() => {
    if (!currentStreamEvents.value || Object.keys(currentStreamEvents.value).length === 0) {
        return {};
    }

    const allEvents = Object.values(currentStreamEvents.value).flat();
    const nodeGroups: Record<string, any> = {};

    // Group events by node_id
    for (const event of allEvents) {
        const nodeId = event.event_data?.data?.node_id;
        if (!nodeId) continue;

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
                llmData: null,
            };
        }

        const group = nodeGroups[nodeId];
        const eventData = event.event_data.data;

        if (event.event_type === 'node_started') {
            group.startedAt = event.event_timestamp;
        } else if (event.event_type === 'node_finished') {
            group.finishedAt = event.event_timestamp;
            group.status = eventData.status || 'completed';
            group.duration = eventData.elapsed_time || 0;
            group.inputs = eventData.inputs;
            group.outputs = eventData.outputs;

            // Extract LLM specific data
            if (eventData.node_type === 'llm' && eventData.process_data) {
                group.llmData = {
                    model_name: eventData.process_data.model_name || 'Unknown',
                    total_tokens: eventData.process_data.usage?.total_tokens || eventData.process_data.total_tokens || 0,
                    total_price: eventData.process_data.usage?.total_price || eventData.process_data.total_price || '0.00',
                    latency: eventData.process_data.usage?.latency || eventData.process_data.latency || 0,
                };
            }
        }
    }

    // Sort by index
    const sortedGroups = Object.fromEntries(Object.entries(nodeGroups).sort(([, a], [, b]) => a.index - b.index));

    return sortedGroups;
});

// Get workflow start event
const workflowStartEvent = computed(() => {
    if (!currentStreamEvents.value?.workflow_started) return null;
    return currentStreamEvents.value.workflow_started[0] || null;
});

// Get workflow finished event
const workflowFinishedEvent = computed(() => {
    if (!currentStreamEvents.value?.workflow_finished) return null;
    return currentStreamEvents.value.workflow_finished[0] || null;
});

// Helper functions for node styling
const getNodeIcon = (nodeType: string) => {
    switch (nodeType) {
        case 'start':
            return PlayIcon;
        case 'llm':
            return CpuChipIcon;
        case 'end':
            return StopIcon;
        default:
            return CpuChipIcon;
    }
};

const getNodeIconClass = (nodeType: string) => {
    const baseClass = 'w-10 h-10 rounded-full flex items-center justify-center border-2';
    switch (nodeType) {
        case 'start':
            return `${baseClass} bg-green-100 border-green-500`;
        case 'llm':
            return `${baseClass} bg-purple-100 border-purple-500`;
        case 'end':
            return `${baseClass} bg-orange-100 border-orange-500`;
        default:
            return `${baseClass} bg-gray-100 border-gray-500`;
    }
};

const getNodeIconColor = (nodeType: string) => {
    switch (nodeType) {
        case 'start':
            return 'text-green-600';
        case 'llm':
            return 'text-purple-600';
        case 'end':
            return 'text-orange-600';
        default:
            return 'text-gray-600';
    }
};

const getNodeTitleColor = (nodeType: string) => {
    switch (nodeType) {
        case 'start':
            return 'text-green-700';
        case 'llm':
            return 'text-purple-700';
        case 'end':
            return 'text-orange-700';
        default:
            return 'text-gray-700';
    }
};

const isLastNode = (nodeId: string) => {
    const nodeIds = Object.keys(groupedNodeEvents.value);
    return nodeIds[nodeIds.length - 1] === nodeId;
};

// Enhanced status helper for colored dots
const getStatusDotColor = (status: string) => {
    switch (status) {
        case 'completed':
        case 'succeeded':
            return 'bg-green-500';
        case 'running':
            return 'bg-blue-500 animate-pulse';
        case 'failed':
            return 'bg-red-500';
        case 'pending':
            return 'bg-yellow-500';
        default:
            return 'bg-gray-500';
    }
};

// Copy to clipboard functionality
const copyToClipboard = async (text: string) => {
    try {
        await navigator.clipboard.writeText(text);
        // You could add a toast notification here if you have one
        console.log('Copied to clipboard');
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

// Resend webhook functionality
const resendWebhook = async () => {
    if (resendingWebhook.value) return;

    resendingWebhook.value = true;

    try {
        const response = await fetch(`/tasks/${props.task.id}/executions/${props.execution.id}/resend-webhook`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = await response.json();

        if (data.success) {
            console.log('Webhook resent successfully');
            // Refresh the page to see the new webhook attempt
            window.location.reload();
        } else {
            console.error('Failed to resend webhook:', data.message);
            alert('Failed to resend webhook: ' + data.message);
        }
    } catch (error) {
        console.error('Error resending webhook:', error);
        alert('An error occurred while resending the webhook');
    } finally {
        resendingWebhook.value = false;
    }
};
</script>

<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';

import HeadingSmall from '@/components/HeadingSmall.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';

interface QueueSettings {
    dify_max_jobs_per_minute: number;
    dify_max_concurrent_jobs: number;
    dify_retry_after_seconds: number;
}

// Removed Metrics interface and related props

interface Props {
    queueSettings: QueueSettings;
    settingsGroups: Record<string, string>;
}

const props = defineProps<Props>();

const form = useForm({
    dify_max_jobs_per_minute: props.queueSettings.dify_max_jobs_per_minute,
    dify_max_concurrent_jobs: props.queueSettings.dify_max_concurrent_jobs,
    dify_retry_after_seconds: props.queueSettings.dify_retry_after_seconds,
});

const submit = () => {
    form.post('/settings/system/queue', {
        preserveScroll: true,
    });
};

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'System Settings',
        href: '/settings/system',
    },
];
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="System Settings" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall 
                    title="System Settings" 
                    description="Configure system-wide settings and queue behavior" 
                />

                <Card>
                    <CardHeader>
                        <CardTitle>Queue Configuration</CardTitle>
                        <CardDescription>
                            Configure queue behavior for workflow executions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="grid gap-4">
                                <div class="space-y-2">
                                    <Label for="max_jobs_per_minute">Max Jobs Per Minute</Label>
                                    <Input 
                                        id="max_jobs_per_minute"
                                        v-model="form.dify_max_jobs_per_minute" 
                                        type="number" 
                                        min="1" 
                                        max="60"
                                        :disabled="form.processing"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Maximum workflow executions per minute (default: 10)
                                    </p>
                                    <div v-if="form.errors.dify_max_jobs_per_minute" class="text-sm text-destructive">
                                        {{ form.errors.dify_max_jobs_per_minute }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="max_concurrent_jobs">Max Concurrent Jobs</Label>
                                    <Input 
                                        id="max_concurrent_jobs"
                                        v-model="form.dify_max_concurrent_jobs" 
                                        type="number" 
                                        min="1" 
                                        max="10"
                                        :disabled="form.processing"
                                    />
                                    <p class="text-sm text-muted-foreground">
                                        Maximum simultaneous executions (default: 3)
                                    </p>
                                    <div v-if="form.errors.dify_max_concurrent_jobs" class="text-sm text-destructive">
                                        {{ form.errors.dify_max_concurrent_jobs }}
                                    </div>
                                </div>
                                
                                <div class="space-y-2">
                                    <Label for="retry_delay">Retry Delay (seconds)</Label>
                                    <Input 
                                        id="retry_delay"
                                        v-model="form.dify_retry_after_seconds" 
                                        type="number" 
                                        min="10" 
                                        max="300"
                                        :disabled="form.processing"
                                    />
                                    <p class="text-sm text-muted-foreground">Delay before retrying delayed jobs (default: 60)</p>
                                    <div v-if="form.errors.dify_retry_after_seconds" class="text-sm text-destructive">
                                        {{ form.errors.dify_retry_after_seconds }}
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-center gap-3">
                                <Button type="submit" :disabled="form.processing">
                                    Save Settings
                                </Button>
                                <span v-if="form.processing" class="text-sm text-muted-foreground">
                                    Saving...
                                </span>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
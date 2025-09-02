<template>
    <Head title="Create Dify Workflow" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto max-w-2xl py-8">
            <div class="mb-6">
                <h1 class="text-3xl font-bold">Create Dify Workflow</h1>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Workflow Details</CardTitle>
                    <CardDescription> Create a new Dify workflow to process tasks </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div class="space-y-2">
                            <Label for="name">Name</Label>
                            <Input id="name" v-model="form.name" :class="{ 'border-red-500': form.errors.name }" required />
                            <p v-if="form.errors.name" class="text-sm text-red-600">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Description</Label>
                            <Textarea id="description" v-model="form.description" :class="{ 'border-red-500': form.errors.description }" rows="3" />
                            <p v-if="form.errors.description" class="text-sm text-red-600">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="workflow_id">Workflow ID</Label>
                            <Input id="workflow_id" v-model="form.workflow_id" :class="{ 'border-red-500': form.errors.workflow_id }" required />
                            <p v-if="form.errors.workflow_id" class="text-sm text-red-600">
                                {{ form.errors.workflow_id }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="api_key">API Key</Label>
                            <Input id="api_key" v-model="form.api_key" type="password" :class="{ 'border-red-500': form.errors.api_key }" required />
                            <p v-if="form.errors.api_key" class="text-sm text-red-600">
                                {{ form.errors.api_key }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between pt-4">
                            <Button type="button" variant="outline" @click="$inertia.visit(difyWorkflows.index())"> Cancel </Button>
                            <Button type="submit" :disabled="form.processing"> Create Workflow </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/AppLayout.vue';
import difyWorkflows from '@/routes/dify-workflows';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dify Workflows',
        href: difyWorkflows.index().url,
    },
    {
        title: 'Create',
        href: difyWorkflows.create().url,
    },
];

const form = useForm({
    name: '',
    description: '',
    workflow_id: '',
    api_key: '',
});

const submit = () => {
    form.post(difyWorkflows.store());
};
</script>

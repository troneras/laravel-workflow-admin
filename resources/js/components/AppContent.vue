<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <SidebarInset
        v-if="props.variant === 'sidebar'"
        :class="[
            'min-h-screen bg-background',
            'px-4 py-4 md:px-6 lg:px-8',
            // Match sidebar border styling with a subtle left border on inset content
            'border-l',
            className,
        ]"
    >
        <div class="mx-auto w-full max-w-7xl grid gap-4">
            <slot />
        </div>
    </SidebarInset>
    <main
        v-else
        class="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl px-4 py-4 md:px-6 lg:px-8 bg-background"
        :class="className"
    >
        <slot />
    </main>
</template>

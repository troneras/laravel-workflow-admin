<script setup lang="ts">
import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { Link, usePage } from '@inertiajs/vue3';

interface ToolItem {
    title: string;
    icon: any;
    href?: string;
    onClick?: () => void;
}

defineProps<{
    items: ToolItem[];
}>();

const page = usePage();
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel class="text-gray-900 dark:text-white font-semibold uppercase tracking-wide text-xs mb-2">Tools</SidebarGroupLabel>
        <SidebarMenu class="space-y-1">
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <!-- Link items (with href) -->
                <SidebarMenuButton
                    v-if="item.href"
                    as-child
                    :is-active="item.href === page.url"
                    :tooltip="item.title"
                    class="transition-all duration-200"
                >
                    <Link :href="item.href" class="flex items-center gap-3 px-3 py-2 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 dark:hover:text-blue-300" :class="item.href === page.url ? 'bg-gradient-to-r from-blue-500 to-indigo-600 text-white shadow-md' : ''">
                        <component :is="item.icon" class="h-4 w-4" />
                        <span class="font-medium">{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>

                <!-- Click items (with onClick) -->
                <SidebarMenuButton
                    v-else-if="item.onClick"
                    :tooltip="item.title"
                    class="transition-all duration-200"
                    @click="item.onClick"
                >
                    <div class="flex items-center gap-3 px-3 py-2 transition-all duration-200 hover:bg-blue-50 hover:text-blue-700 dark:hover:bg-blue-900/20 dark:hover:text-blue-300 cursor-pointer">
                        <component :is="item.icon" class="h-4 w-4" />
                        <span class="font-medium">{{ item.title }}</span>
                    </div>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
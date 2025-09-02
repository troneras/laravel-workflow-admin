<script setup lang="ts">
import { SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';

interface Props {
    items: NavItem[];
    class?: string;
}

defineProps<Props>();
</script>

<template>
    <SidebarGroup :class="`group-data-[collapsible=icon]:p-0 ${$props.class || ''}`">
        <SidebarGroupContent>
            <SidebarMenu>
                <SidebarMenuItem v-for="item in items" :key="item.title">
                    <SidebarMenuButton class="text-gray-600 hover:text-blue-700 hover:bg-blue-50 dark:text-gray-300 dark:hover:text-blue-300 dark:hover:bg-blue-900/20 transition-all duration-200 rounded-lg" as-child>
                        <a :href="typeof item.href === 'string' ? item.href : item.href.url" target="_blank" rel="noopener noreferrer" class="flex items-center gap-3 px-3 py-2">
                            <component :is="item.icon" class="h-4 w-4" />
                            <span class="font-medium">{{ item.title }}</span>
                        </a>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarGroupContent>
    </SidebarGroup>
</template>

<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavTool from '@/components/NavTool.vue';
import NavUser from '@/components/NavUser.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import difyWorkflows from '@/routes/dify-workflows';
import tasks from '@/routes/tasks';
import { type AppPageProps, type NavItem } from '@/types';
import { openDifyPopup } from '@/utils/popup';
import { Link, usePage } from '@inertiajs/vue3';
import { Activity, BookOpen, Folder, LayoutGrid, ListTodo, MonitorSpeaker, Workflow } from 'lucide-vue-next';
import AppLogo from './AppLogo.vue';

const page = usePage<AppPageProps>();

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Workflows',
        href: difyWorkflows.index(),
        icon: Workflow,
    },
    {
        title: 'Tasks',
        href: tasks.index(),
        icon: ListTodo,
    },
];

const toolItems = [
    {
        title: 'Queue Monitor',
        href: '/horizon',
        icon: Activity,
    },
    {
        title: 'Dify Interface',
        icon: MonitorSpeaker,
        onClick: () => openDifyPopup(page.props.settings.difyUrl),
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
            <NavTool :items="toolItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

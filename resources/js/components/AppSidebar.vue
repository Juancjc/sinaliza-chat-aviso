<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { LayoutGrid, MessagesSquare } from '@lucide/vue';
import { computed } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCurrentUrl } from '@/composables/useCurrentUrl';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
];

const footerNavItems: NavItem[] = [];
const page = usePage();
const sidebarGroups = computed(() => page.props.sidebarGroups);
const { isCurrentUrl } = useCurrentUrl();
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

            <SidebarGroup class="px-2 py-0">
                <SidebarGroupLabel>Meus grupos</SidebarGroupLabel>
                <SidebarMenu>
                    <SidebarMenuItem
                        v-for="group in sidebarGroups"
                        :key="group.id"
                    >
                        <SidebarMenuButton
                            as-child
                            :is-active="
                                isCurrentUrl(`/grupos/${group.id}/chat`)
                            "
                            :tooltip="group.nome"
                        >
                            <Link :href="`/grupos/${group.id}/chat`">
                                <MessagesSquare />
                                <span>{{ group.nome }}</span>
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>

                    <li
                        v-if="!sidebarGroups.length"
                        class="px-2 py-1 text-xs text-sidebar-foreground/60 group-data-[collapsible=icon]:hidden"
                    >
                        Nenhum grupo disponível
                    </li>
                </SidebarMenu>
            </SidebarGroup>
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>

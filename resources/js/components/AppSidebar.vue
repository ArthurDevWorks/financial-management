<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import SidebarItem from '@/components/SidebarItem.vue';
import { footerNavItems, navSections } from '@/lib/navigation';
</script>

<template>
    <aside
        class="flex h-full w-64 flex-col border-r border-sidebar-border bg-sidebar"
    >
        <!-- LOGO -->
        <div
            class="border-b border-sidebar-border px-5 py-5 text-sidebar-foreground"
        >
            <AppLogo />
        </div>

        <!-- MENU POR MÓDULOS -->
        <nav class="flex-1 overflow-y-auto px-3 py-5">
            <div
                v-for="section in navSections"
                :key="section.title"
                class="mb-6 last:mb-0"
            >
                <p
                    class="mb-2 px-2 pb-1 text-[0.6875rem] font-semibold tracking-[0.06em] text-muted-foreground uppercase"
                >
                    {{ section.title }}
                </p>

                <div class="space-y-0.5">
                    <SidebarItem
                        v-for="item in section.items"
                        :key="item.href"
                        :label="item.label"
                        :icon="item.icon"
                        :href="item.href"
                    />
                </div>
            </div>
        </nav>

        <!-- FOOTER -->
        <div class="border-t border-sidebar-border px-3 py-3">
            <div class="space-y-0.5">
                <SidebarItem
                    v-for="item in footerNavItems"
                    :key="item.href"
                    :label="item.label"
                    :icon="item.icon"
                    :href="item.href"
                    :method="item.method"
                    :danger="item.danger"
                />
            </div>
        </div>
    </aside>
</template>

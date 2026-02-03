<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue'
import type { BreadcrumbItemType } from '@/types';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});
</script>

<template>
    <div class="flex h-screen bg-slate-900">
        <AppSidebar />
         <!-- Conteúdo -->
        <main class="flex-1 overflow-y-auto p-8">
            <slot />
        </main>
    </div>
</template>

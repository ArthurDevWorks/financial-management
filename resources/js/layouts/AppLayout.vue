<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import type { BreadcrumbItemType } from '@/types';

interface Props {
  breadcrumbs?: BreadcrumbItemType[];
  wide?: boolean;
}

withDefaults(defineProps<Props>(), {
  breadcrumbs: () => [],
  wide: false,
});
</script>

<template>
  <div class="flex h-screen bg-background">
    <AppSidebar />
    <main class="flex-1 overflow-y-auto">
      <div
        class="mx-auto"
        :class="wide ? 'max-w-7xl' : 'max-w-6xl'"
      >
        <slot />
      </div>
    </main>
  </div>
</template>

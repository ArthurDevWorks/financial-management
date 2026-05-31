<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import type { FunctionalComponent } from 'vue';
import type { LucideProps } from 'lucide-vue-next';

const props = defineProps<{
  label: string;
  icon: FunctionalComponent<LucideProps>;
  href: string;
  method?: 'get' | 'post';
  danger?: boolean;
}>();

const page = usePage();

const isActive = () => page.url.startsWith(props.href);
</script>

<template>
  <Link
    :href="href"
    :method="method ?? 'get'"
    as="button"
    class="flex w-full items-center gap-3 rounded-md px-3 py-2 text-sm font-medium transition-all duration-150"
    :class="[
      isActive() && !danger
        ? 'bg-primary/10 text-primary'
        : '',
      danger && 'text-destructive hover:bg-destructive/10',
      !isActive() && !danger && 'text-muted-foreground hover:bg-white/[0.04] hover:text-foreground',
    ]"
  >
    <component :is="icon" class="h-[18px] w-[18px] shrink-0 opacity-70" :class="isActive() && !danger && 'opacity-100'" />
    <span>{{ label }}</span>
  </Link>
</template>

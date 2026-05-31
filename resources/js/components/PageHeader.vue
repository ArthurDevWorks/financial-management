<script setup lang="ts">
interface Props {
  title: string;
  description?: string;
}

defineProps<Props>();
</script>

<template>
  <div class="mb-8 flex items-start justify-between gap-4">
    <div class="min-w-0">
      <h1 class="text-2xl font-bold tracking-tight text-foreground">
        {{ title }}
      </h1>
      <p v-if="description" class="mt-1 text-[0.9375rem] text-muted-foreground">
        {{ description }}
      </p>
    </div>
    <div class="flex shrink-0 items-center gap-3">
      <slot name="actions" />
    </div>
  </div>
</template>

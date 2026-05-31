<script setup lang="ts">
interface Props {
  variant?: 'header' | 'sidebar';
}

defineProps<Props>();
</script>

<template>
  <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
    <slot />
  </div>
  <div v-else class="flex min-h-screen w-full">
    <slot />
  </div>
</template>

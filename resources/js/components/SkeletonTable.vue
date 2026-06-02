<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';

withDefaults(defineProps<{
  rows?: number;
  columns?: number;
}>(), {
  rows: 5,
  columns: 4,
});
</script>

<template>
  <div>
    <div class="space-y-4">
      <div
        v-for="row in rows"
        :key="row"
        class="flex items-center gap-4"
        :style="{ paddingLeft: '1rem', paddingRight: '1rem' }"
      >
        <Skeleton
          v-for="col in columns"
          :key="col"
          class="h-5"
          :style="{
            width: col === columns ? '80px' : `${100 / columns}%`,
            maxWidth: col === columns ? '80px' : undefined,
          }"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Skeleton } from '@/components/ui/skeleton';
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border bg-card p-6">
        <div class="mb-3 flex items-start justify-between">
            <Skeleton class="h-4 w-24" />
            <Skeleton class="h-[42px] w-[42px] rounded-lg" />
        </div>
        <Skeleton class="mt-2 h-8 w-32" />
        <div class="mt-3 flex items-center gap-2">
            <Skeleton class="h-5 w-16 rounded-full" />
            <Skeleton class="h-3 w-20" />
        </div>
    </div>
</template>

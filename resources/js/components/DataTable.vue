<script setup lang="ts">
import type { LucideProps } from 'lucide-vue-next';
import type { FunctionalComponent } from 'vue';

interface Props {
    title?: string;
    description?: string;
    total?: number;
    empty?: boolean;
    emptyTitle?: string;
    emptyDescription?: string;
    emptyIcon?: FunctionalComponent<LucideProps>;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border bg-card">
        <div
            v-if="title"
            class="flex items-center justify-between border-b border-border px-6 py-4"
        >
            <div>
                <h3 class="text-base font-semibold text-foreground">
                    {{ title }}
                </h3>
                <p v-if="description" class="text-sm text-muted-foreground">
                    {{ description }}
                </p>
            </div>
            <slot name="header-actions" />
        </div>

        <div
            v-if="empty"
            class="flex flex-col items-center justify-center py-16 text-center"
        >
            <div v-if="emptyIcon" class="mb-4 text-muted-foreground opacity-15">
                <component :is="emptyIcon" class="h-16 w-16" />
            </div>
            <p class="font-medium text-muted-foreground">
                {{ emptyTitle || 'Nenhum registro encontrado' }}
            </p>
            <p
                v-if="emptyDescription"
                class="mt-1 text-sm text-muted-foreground"
            >
                {{ emptyDescription }}
            </p>
            <slot name="empty-actions" />
        </div>

        <div v-else class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <slot name="head" />
                </thead>
                <tbody>
                    <slot name="body" />
                </tbody>
            </table>
        </div>
    </div>
</template>

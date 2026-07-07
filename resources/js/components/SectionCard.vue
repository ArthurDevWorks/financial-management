<script setup lang="ts">
interface Props {
    title?: string;
    description?: string;
}

defineProps<Props>();
</script>

<template>
    <div class="overflow-hidden rounded-xl border border-border bg-card">
        <div
            v-if="title || $slots['header-actions']"
            class="flex items-center justify-between border-b border-border px-6 py-4"
        >
            <div class="min-w-0">
                <h3
                    v-if="title"
                    class="text-base font-semibold text-foreground"
                >
                    {{ title }}
                </h3>
                <p
                    v-if="description"
                    class="mt-0.5 text-sm text-muted-foreground"
                >
                    {{ description }}
                </p>
            </div>
            <div
                v-if="$slots['header-actions']"
                class="flex shrink-0 items-center gap-2"
            >
                <slot name="header-actions" />
            </div>
        </div>

        <div class="px-6 py-5">
            <slot />
        </div>

        <div v-if="$slots.footer" class="border-t border-border px-6 py-3">
            <slot name="footer" />
        </div>
    </div>
</template>

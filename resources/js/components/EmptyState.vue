<script setup lang="ts">
import { Button } from '@/components/ui/button';
import type { LucideProps } from 'lucide-vue-next';
import type { FunctionalComponent } from 'vue';

interface Props {
    icon?: FunctionalComponent<LucideProps>;
    title: string;
    description?: string;
    actionLabel?: string;
}

defineProps<Props>();

interface Emits {
    (e: 'action'): void;
}

const emit = defineEmits<Emits>();
</script>

<template>
    <div class="flex flex-col items-center justify-center py-16 text-center">
        <div v-if="icon" class="mb-4 text-muted-foreground opacity-15">
            <component :is="icon" class="h-16 w-16" />
        </div>
        <h3 class="text-lg font-semibold text-foreground">{{ title }}</h3>
        <p
            v-if="description"
            class="mt-1 max-w-sm text-sm text-muted-foreground"
        >
            {{ description }}
        </p>
        <Button v-if="actionLabel" class="mt-6" @click="emit('action')">
            {{ actionLabel }}
        </Button>
        <div v-if="$slots.actions" class="mt-6">
            <slot name="actions" />
        </div>
    </div>
</template>

<script setup lang="ts">
import type { LucideProps } from 'lucide-vue-next';
import type { FunctionalComponent } from 'vue';

type SummaryVariant =
    | 'default'
    | 'revenue'
    | 'expense'
    | 'investment'
    | 'profit';

interface Props {
    label: string;
    value: string;
    variant?: SummaryVariant;
    icon?: FunctionalComponent<LucideProps>;
    trend?: number;
    trendLabel?: string;
}

withDefaults(defineProps<Props>(), {
    variant: 'default',
});

const variantStyles: Record<
    SummaryVariant,
    { border: string; icon: string; value: string; trend: string }
> = {
    default: {
        border: 'before:bg-primary',
        icon: 'bg-primary/10 text-primary',
        value: 'text-foreground',
        trend: 'bg-primary/10 text-primary',
    },
    revenue: {
        border: 'before:bg-revenue',
        icon: 'bg-revenue/10 text-revenue',
        value: 'text-revenue',
        trend: 'bg-revenue/10 text-revenue',
    },
    expense: {
        border: 'before:bg-destructive',
        icon: 'bg-destructive/10 text-destructive',
        value: 'text-destructive',
        trend: 'bg-destructive/10 text-destructive',
    },
    investment: {
        border: 'before:bg-investment',
        icon: 'bg-investment/10 text-investment',
        value: 'text-investment',
        trend: 'bg-investment/10 text-investment',
    },
    profit: {
        border: 'before:bg-accent',
        icon: 'bg-accent/10 text-accent',
        value: 'text-accent',
        trend: 'bg-accent/10 text-accent',
    },
};
</script>

<template>
    <div
        class="group relative overflow-hidden rounded-xl border border-border bg-card p-6 transition-all duration-200 hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
        :class="variantStyles[variant].border"
    >
        <div class="mb-3 flex items-start justify-between">
            <div>
                <p class="text-sm font-medium text-muted-foreground">
                    {{ label }}
                </p>
            </div>
            <div
                v-if="icon"
                class="flex h-[42px] w-[42px] shrink-0 items-center justify-center rounded-lg transition-colors"
                :class="variantStyles[variant].icon"
            >
                <component :is="icon" class="h-5 w-5" />
            </div>
        </div>

        <p
            class="text-[1.75rem] leading-tight font-bold tracking-tight"
            :class="variantStyles[variant].value"
        >
            {{ value }}
        </p>

        <div v-if="trend !== undefined" class="mt-2 flex items-center gap-2">
            <span
                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[0.75rem] font-semibold"
                :class="[
                    trend >= 0
                        ? variantStyles[variant].trend
                        : 'bg-destructive/10 text-destructive',
                ]"
            >
                <template v-if="trend >= 0">&#9650;</template>
                <template v-else>&#9660;</template>
                {{ trend >= 0 ? '+' : '' }}{{ trend }}%
            </span>
            <span v-if="trendLabel" class="text-xs text-muted-foreground">{{
                trendLabel
            }}</span>
        </div>
    </div>
</template>

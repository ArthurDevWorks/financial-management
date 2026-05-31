<script setup lang="ts">
import { cn } from '@/lib/utils';

type BadgeSemantic = 'revenue' | 'expense' | 'investment' | 'default';

interface Props {
  variant?: BadgeSemantic;
}

const props = withDefaults(defineProps<Props>(), {
  variant: 'default',
});

const semanticStyles: Record<BadgeSemantic, string> = {
  revenue:
    'bg-revenue/10 text-revenue border border-revenue/20',
  expense:
    'bg-destructive/10 text-destructive border border-destructive/20',
  investment:
    'bg-investment/10 text-investment border border-investment/20',
  default:
    'bg-surface/50 text-muted-foreground border border-border',
};
</script>

<template>
  <span
    :class="
      cn(
        'inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold whitespace-nowrap',
        semanticStyles[props.variant],
      )
    "
  >
    <slot />
  </span>
</template>

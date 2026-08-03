<script setup lang="ts">
import { computed } from 'vue'

const props = withDefaults(defineProps<{
  value: number
  label?: string
}>(), {
  label: 'Margem de Segurança',
})

const clamped = computed(() => Math.min(Math.max(props.value, -50), 80))

const status = computed(() => {
  const m = props.value
  if (m >= 30) return { label: 'Excelente margem', color: 'bg-revenue', text: 'text-revenue' }
  if (m >= 15) return { label: 'Boa margem', color: 'bg-emerald-500', text: 'text-emerald-500' }
  if (m >= 0) return { label: 'Margem positiva', color: 'bg-primary', text: 'text-primary' }
  if (m >= -15) return { label: 'Margem negativa', color: 'bg-amber-500', text: 'text-amber-500' }
  return { label: 'Risco alto', color: 'bg-destructive', text: 'text-destructive' }
})
</script>

<template>
  <div>
    <div class="mb-2 flex items-center justify-between">
      <span class="text-sm font-semibold text-foreground">{{ label }}</span>
      <span :class="`text-sm font-bold ${status.text}`">{{ value >= 0 ? '+' : '' }}{{ value.toFixed(1) }}%</span>
    </div>
    <div class="relative h-3 overflow-hidden rounded-full bg-muted">
      <div
        class="h-full rounded-full transition-all duration-500 ease-out"
        :class="status.color"
        :style="{ width: `${((clamped + 50) / 130) * 100}%` }"
      />
    </div>
    <div class="mt-1 flex justify-between text-xs text-muted-foreground">
      <span>-50%</span>
      <span>0%</span>
      <span>+80%</span>
    </div>
    <p class="mt-3 text-xs" :class="status.text">
      {{ status.label }}
      <template v-if="value > 0">
        — o ativo está sendo negociado
        {{ value >= 15 ? 'abaixo' : 'ligeiramente abaixo' }}
        do preço teto
      </template>
      <template v-else>
        — o ativo está sendo negociado acima do preço teto calculado
      </template>
    </p>
  </div>
</template>

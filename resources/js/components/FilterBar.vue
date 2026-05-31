<script setup lang="ts">
import { Button } from '@/components/ui/button';

interface Props {
  period: string;
  month: number;
  year: number;
  startDate?: string;
  endDate?: string;
}

interface Emits {
  (e: 'update:period', value: string): void;
  (e: 'update:month', value: number): void;
  (e: 'update:year', value: number): void;
  (e: 'update:startDate', value: string): void;
  (e: 'update:endDate', value: string): void;
  (e: 'apply'): void;
  (e: 'reset'): void;
}

defineProps<Props>();
const emit = defineEmits<Emits>();

const months = [
  'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
  'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro',
];
</script>

<template>
  <div
    class="flex flex-wrap items-end gap-4 rounded-xl border border-border bg-card p-5"
  >
    <div v-if="$slots.default">
      <slot />
    </div>
    <template v-else>
      <div class="flex flex-col gap-1">
        <label class="text-xs font-semibold text-muted-foreground">Período</label>
        <select
          :value="period"
          @change="emit('update:period', ($event.target as HTMLSelectElement).value)"
          class="h-9 rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
        >
          <option value="month">Mês</option>
          <option value="year">Ano</option>
          <option value="custom">Personalizado</option>
        </select>
      </div>

      <template v-if="period === 'month'">
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-muted-foreground">Mês</label>
          <select
            :value="month"
            @change="emit('update:month', Number(($event.target as HTMLSelectElement).value))"
            class="h-9 rounded-md border border-border bg-surface pl-3 pr-10 py-1 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          >
            <option v-for="(m, i) in months" :key="i" :value="i + 1">{{ m }}</option>
          </select>
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-muted-foreground">Ano</label>
          <input
            :value="year"
            @input="emit('update:year', Number(($event.target as HTMLInputElement).value))"
            type="number"
            class="h-9 w-[100px] rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          />
        </div>
      </template>

      <template v-if="period === 'year'">
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-muted-foreground">Ano</label>
          <input
            :value="year"
            @input="emit('update:year', Number(($event.target as HTMLInputElement).value))"
            type="number"
            class="h-9 w-[100px] rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          />
        </div>
      </template>

      <template v-if="period === 'custom'">
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-muted-foreground">De</label>
          <input
            :value="startDate"
            @input="emit('update:startDate', ($event.target as HTMLInputElement).value)"
            type="date"
            class="h-9 w-[160px] rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          />
        </div>
        <div class="flex flex-col gap-1">
          <label class="text-xs font-semibold text-muted-foreground">Até</label>
          <input
            :value="endDate"
            @input="emit('update:endDate', ($event.target as HTMLInputElement).value)"
            type="date"
            class="h-9 w-[160px] rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
          />
        </div>
      </template>
    </template>

    <div class="flex items-center gap-2">
      <Button size="sm" @click="emit('apply')">Aplicar</Button>
      <Button variant="secondary" size="sm" @click="emit('reset')">Limpar</Button>
    </div>
  </div>
</template>

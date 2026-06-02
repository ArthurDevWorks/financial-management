<script setup lang="ts">
import { Doughnut } from 'vue-chartjs';
import {
  Chart as ChartJS,
  ArcElement,
  Tooltip,
  Legend,
} from 'chart.js';

ChartJS.register(ArcElement, Tooltip, Legend);

interface DataPoint {
  category: string;
  value: number;
  count: number;
}

const props = defineProps<{
  data: DataPoint[];
  title?: string;
}>();

const colors = [
  'hsl(168 75% 42%)',
  'hsl(42 80% 52%)',
  'hsl(220 80% 55%)',
  'hsl(142 70% 50%)',
  'hsl(0 75% 55%)',
  'hsl(190 75% 45%)',
  'hsl(330 70% 55%)',
  'hsl(30 80% 55%)',
];

const chartData = {
  labels: props.data.map((d) => d.category),
  datasets: [
    {
      data: props.data.map((d) => d.value),
      backgroundColor: colors.slice(0, props.data.length),
      borderWidth: 0,
      hoverOffset: 8,
    },
  ],
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        usePointStyle: true,
        padding: 12,
        font: { family: 'Instrument Sans, sans-serif', size: 11 },
      },
    },
    tooltip: {
      backgroundColor: 'hsl(228 22% 9%)',
      titleColor: 'hsl(210 20% 96%)',
      bodyColor: 'hsl(210 20% 96%)',
      borderColor: 'hsl(228 15% 14%)',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (ctx: { parsed: { data: number }; label: string }) => {
          const total = ctx.parsed.data;
          const formatted = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(total);
          return ` ${ctx.label}: ${formatted}`;
        },
      },
    },
  },
};
</script>

<template>
  <div class="h-[280px]">
    <Doughnut v-if="data.length" :data="chartData" :options="chartOptions" />
    <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">
      Nenhum dado disponível
    </div>
  </div>
</template>

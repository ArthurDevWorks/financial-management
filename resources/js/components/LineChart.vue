<script setup lang="ts">
import { Line } from 'vue-chartjs';
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Filler,
} from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Filler);

interface DataPoint {
  month: string;
  revenue: number;
  expense: number;
  net: number;
}

const props = defineProps<{
  data: DataPoint[];
}>();

const chartData = {
  labels: props.data.map((d) => d.month),
  datasets: [
    {
      label: 'Receitas',
      data: props.data.map((d) => d.revenue),
      borderColor: 'hsl(142 70% 50%)',
      backgroundColor: 'hsl(142 70% 50% / 0.1)',
      fill: true,
      tension: 0.35,
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2,
    },
    {
      label: 'Despesas',
      data: props.data.map((d) => d.expense),
      borderColor: 'hsl(0 75% 55%)',
      backgroundColor: 'hsl(0 75% 55% / 0.1)',
      fill: true,
      tension: 0.35,
      pointRadius: 4,
      pointHoverRadius: 6,
      borderWidth: 2,
    },
  ],
};

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index' as const,
  },
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        usePointStyle: true,
        padding: 16,
        font: { family: 'Instrument Sans, sans-serif', size: 12 },
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
      displayColors: true,
    },
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { font: { family: 'Instrument Sans, sans-serif', size: 11 } },
    },
    y: {
      grid: { color: 'hsl(228 15% 14% / 0.4)' },
      ticks: {
        font: { family: 'Instrument Sans, sans-serif', size: 11 },
        callback: (value: number) =>
          new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL', maximumSignificantDigits: 3 }).format(value),
      },
    },
  },
};
</script>

<template>
  <div class="h-[300px]">
    <Line v-if="data.length" :data="chartData" :options="chartOptions" />
    <div v-else class="flex h-full items-center justify-center text-sm text-muted-foreground">
      Nenhum dado disponível
    </div>
  </div>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Plus, ChartNoAxesCombined } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  from: number;
  to: number;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
}

interface ValuationSummary {
  fair_value?: number;
  upside?: number;
  margin_of_safety?: number;
  current_price?: number;
}

interface Investment {
  id: number;
  name: string;
}

interface Valuation {
  id: number;
  investiment: Investment;
  calculated_at: string;
  summary: ValuationSummary | null;
}

defineProps<{
  valuations: {
    data: Valuation[];
    meta: PaginationMeta;
  };
}>();

const formatCurrency = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const createValuation = () => {
  router.visit('/valuations/create');
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader
        title="Valuations"
        description="Histórico de cálculos de valuation realizados"
      >
        <template #actions>
          <Button @click="createValuation">
            <Plus class="h-4 w-4" />
            Nova Valuation
          </Button>
        </template>
      </PageHeader>

      <SectionCard
        title="Cálculos Realizados"
        :description="`${valuations.meta?.total || valuations.data.length} valuation(ões) encontrado(s)`"
      >
        <div v-if="valuations.data.length" class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ativo
                </TableHead>
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Data do Cálculo
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Preço Justo
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Upside / Downside
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="valuation in valuations.data"
                :key="valuation.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <TableCell class="font-medium text-foreground">
                  {{ valuation.investiment.name }}
                </TableCell>
                <TableCell class="text-muted-foreground">
                  {{ formatDate(valuation.calculated_at) }}
                </TableCell>
                <TableCell class="text-right text-muted-foreground">
                  {{ formatCurrency(valuation.summary?.fair_value) }}
                </TableCell>
                <TableCell class="text-right font-semibold"
                  :class="(valuation.summary?.upside ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'"
                >
                  <span v-if="valuation.summary?.upside != null">
                    {{ (valuation.summary.upside >= 0 ? '+' : '') }}{{ (valuation.summary.upside).toFixed(2) }}%
                  </span>
                  <span v-else class="text-muted-foreground">---</span>
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>

          <PaginationLinks v-if="valuations.meta" :meta="valuations.meta" />
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <ChartNoAxesCombined class="mb-4 h-16 w-16 text-muted-foreground opacity-15" />
          <p class="font-medium text-muted-foreground">Nenhum valuation encontrado</p>
          <p class="mt-1 text-sm text-muted-foreground">Realize um cálculo de valuation para começar</p>
          <Button class="mt-6" @click="createValuation">
            <Plus class="h-4 w-4" />
            Nova Valuation
          </Button>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

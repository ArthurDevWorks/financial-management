<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Investment {
  id: number;
  name: string;
}

interface ValuationAssumptions {
  current_fcf?: number;
  payout?: number;
  roe?: number;
  base_growth_rate?: number;
  discount_rate?: number;
  terminal_growth_rate?: number;
  projection_years?: number;
  total_shares?: number;
  current_price_per_share?: number | null;
  growth_rates?: number[];
}

interface ProjectedCashFlow {
  year: number;
  growth_rate: number;
  projected_fcf: number;
  discount_factor: number;
  present_value: number;
}

interface ValuationSummary {
  present_value_of_cash_flows?: number;
  terminal_value?: number;
  terminal_present_value?: number;
  equity_value?: number;
  fair_value_per_share?: number;
  market_cap?: number | null;
  upside?: number | null;
  margin_of_safety?: number | null;
}

interface Valuation {
  id: number;
  investiment: Investment;
  assumptions: ValuationAssumptions;
  projected_cash_flows: ProjectedCashFlow[];
  summary: ValuationSummary;
  calculated_at: string;
}

const props = defineProps<{
  valuation: Valuation;
}>();

const formatCurrency = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatPercent = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
  return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const formatNumber = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
  return new Intl.NumberFormat('pt-BR').format(value);
};

const baseCalendarYear = new Date(props.valuation.calculated_at).getFullYear();

const cashFlowsWithCalendarYear = props.valuation.projected_cash_flows.map((cf) => ({
  ...cf,
  calendarYear: cf.year === 0 ? `${baseCalendarYear}` : `${baseCalendarYear + cf.year}`,
}));

const goBack = () => {
  router.visit('/valuations');
};

const goToEdit = () => {
  router.visit(`/investiments/${props.valuation.investiment.id}?valuation_id=${props.valuation.id}`);
};

const a = props.valuation.assumptions;
const s = props.valuation.summary;

const assumptionItems = [
  { label: 'FCF atual', value: formatCurrency(a.current_fcf) },
  { label: 'Taxa de desconto', value: `${a.discount_rate ?? 'N/A'}%` },
  { label: 'Payout', value: `${a.payout ?? 'N/A'}%` },
  { label: 'ROE', value: `${a.roe ?? 'N/A'}%` },
  { label: 'Crescimento base', value: `${a.base_growth_rate ?? 'N/A'}%` },
  { label: 'Crescimento na perpetuidade', value: `${a.terminal_growth_rate ?? 'N/A'}%` },
  { label: 'Anos de projeção', value: `${a.projection_years ?? 'N/A'}` },
  { label: 'Total de ações', value: formatNumber(a.total_shares) },
  { label: 'Preço atual por ação', value: formatCurrency(a.current_price_per_share) },
];

const summaryItems = [
  { label: 'Valor justo por ação', value: formatCurrency(s.fair_value_per_share), highlight: true },
  { label: 'Market cap', value: formatCurrency(s.market_cap) },
  { label: 'Upside / Downside', value: formatPercent(s.upside), positive: (s.upside ?? 0) >= 0 },
  { label: 'Margem de segurança', value: formatPercent(s.margin_of_safety), positive: (s.margin_of_safety ?? 0) >= 0 },
  { label: 'VP dos fluxos de caixa', value: formatCurrency(s.present_value_of_cash_flows) },
  { label: 'Valor terminal (VP)', value: formatCurrency(s.terminal_present_value) },
  { label: 'Valor do equity', value: formatCurrency(s.equity_value) },
];
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <div class="mb-2">
        <button
          type="button"
          class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Voltar
        </button>
      </div>

      <PageHeader
        :title="valuation.investiment.name"
        :description="`Valuation calculada em ${formatDate(valuation.calculated_at)}`"
      >
        <template #actions>
          <Button @click="goToEdit">
            <Pencil class="h-4 w-4" />
            Editar Premissas
          </Button>
        </template>
      </PageHeader>

      <div class="mt-6 grid gap-6 xl:grid-cols-2">
        <SectionCard title="Premissas da Análise" description="Parâmetros utilizados no cálculo">
          <div class="grid gap-3 sm:grid-cols-2">
            <div
              v-for="item in assumptionItems"
              :key="item.label"
              class="rounded-lg border border-border bg-surface px-4 py-3"
            >
              <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ item.label }}</p>
              <p class="mt-1 text-lg font-semibold text-foreground">{{ item.value }}</p>
            </div>
          </div>
        </SectionCard>

        <SectionCard title="Resumo do Valuation" description="Resultados do cálculo">
          <div class="grid gap-3">
            <div
              v-for="item in summaryItems"
              :key="item.label"
              class="rounded-lg border border-border px-4 py-3"
              :class="item.highlight
                ? 'border-primary/40 bg-primary/10'
                : 'bg-surface'"
            >
              <p class="text-xs font-medium uppercase tracking-wide text-muted-foreground">{{ item.label }}</p>
              <p
                class="mt-1 text-lg font-semibold"
                :class="item.highlight
                  ? 'text-primary'
                  : (item.positive !== undefined
                    ? (item.positive ? 'text-revenue' : 'text-destructive')
                    : 'text-foreground')"
              >
                {{ item.value }}
              </p>
            </div>
          </div>
        </SectionCard>
      </div>

      <SectionCard
        class="mt-6"
        title="Fluxos de Caixa Projetados"
        :description="`${valuation.projected_cash_flows.length} ano(s) projetado(s)`"
      >
        <div class="overflow-x-auto">
          <Table>
            <TableHeader>
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ano
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Taxa de Crescimento
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  FCF Projetado
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Fator de Desconto
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Valor Presente
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="cf in cashFlowsWithCalendarYear"
                :key="cf.year"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <TableCell class="font-medium text-foreground">
                  {{ cf.calendarYear }}
                </TableCell>
                <TableCell class="text-right text-muted-foreground">
                  <template v-if="cf.year === 0">—</template>
                  <template v-else>{{ cf.growth_rate }}%</template>
                </TableCell>
                <TableCell class="text-right text-muted-foreground">
                  {{ formatCurrency(cf.projected_fcf) }}
                </TableCell>
                <TableCell class="text-right text-muted-foreground">
                  {{ cf.year === 0 ? '1,0000' : cf.discount_factor.toFixed(4) }}
                </TableCell>
                <TableCell class="text-right font-semibold text-foreground">
                  {{ formatCurrency(cf.present_value) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

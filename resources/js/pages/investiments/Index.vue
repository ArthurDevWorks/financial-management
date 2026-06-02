<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import StatBadge from '@/components/StatBadge.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import CrudActions from '@/components/CrudActions.vue';
import SearchInput from '@/components/SearchInput.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Download, PieChart, PiggyBank, Plus, TrendingDown, TrendingUp, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  from: number;
  to: number;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
}

interface Investment {
  id: number;
  name: string;
  type: string;
  type_label: string;
  portfolio_class: string;
  quantity: number;
  average_price: number;
  current_balance: number;
  invested_amount: number;
  gain_loss: number;
  profitability_percentage: number;
}

interface DistributionItem {
  class: string;
  total: number;
  percentage: number;
}

interface PortfolioSummary {
  totalInvested: number;
  currentBalance: number;
  totalGainLoss: number;
  totalProfitability: number;
  distributionByClass: DistributionItem[];
}

const props = defineProps<{
  investiments: {
    data: Investment[];
    meta: PaginationMeta;
  };
  portfolioSummary?: PortfolioSummary;
  totalInvested?: number;
  totalCurrent?: number;
  totalProfitability?: number;
}>();

const emptyPortfolioSummary: PortfolioSummary = {
  totalInvested: 0,
  currentBalance: 0,
  totalGainLoss: 0,
  totalProfitability: 0,
  distributionByClass: [],
};

const portfolioSummary = computed(() => props.portfolioSummary ?? emptyPortfolioSummary);

const editInvestment = (investment: Investment) => {
  router.visit(`/investiments/${investment.id}/edit`);
};

const deleteInvestment = (investment: Investment) => {
  router.delete(`/investiments/${investment.id}`);
};

const createInvestment = () => {
  router.visit('/investiments/create');
};

const formatCurrency = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'R$ 0,00';

  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatQuantity = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return '0';

  return new Intl.NumberFormat('pt-BR', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 8,
  }).format(value);
};

const formatPercent = (value: number | null | undefined) => {
  const percent = value ?? 0;
  return `${percent >= 0 ? '+' : ''}${percent.toFixed(2)}%`;
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader title="Carteira de Investimentos" description="Acompanhe patrimônio, aportes e distribuição por classe de ativo">
        <template #actions>
          <Button @click="createInvestment">
            <Plus class="h-4 w-4" />
            Novo Investimento
          </Button>
        </template>
      </PageHeader>

      <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-4">
        <SummaryCard
          label="Patrimônio Investido"
          :value="formatCurrency(portfolioSummary.currentBalance)"
          variant="investment"
          :icon="Wallet"
          :trend="portfolioSummary.totalProfitability"
        />
        <SummaryCard
          label="Total Aportado"
          :value="formatCurrency(portfolioSummary.totalInvested)"
          variant="default"
          :icon="PiggyBank"
        />
        <SummaryCard
          label="Ganho/Perda Total"
          :value="formatCurrency(portfolioSummary.totalGainLoss)"
          :variant="portfolioSummary.totalGainLoss >= 0 ? 'profit' : 'expense'"
          :icon="portfolioSummary.totalGainLoss >= 0 ? TrendingUp : TrendingDown"
        />
        <SummaryCard
          label="Rentabilidade Total"
          :value="formatPercent(portfolioSummary.totalProfitability)"
          :variant="portfolioSummary.totalProfitability >= 0 ? 'profit' : 'expense'"
          :icon="TrendingUp"
          :trend="portfolioSummary.totalProfitability"
        />
      </div>

      <SectionCard title="Distribuição por Classe" description="Composição consolidada da carteira" class="mb-8">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
          <div
            v-for="item in portfolioSummary.distributionByClass"
            :key="item.class"
            class="rounded-xl border border-border bg-surface p-4"
          >
            <div class="mb-3 flex items-center justify-between gap-3">
              <div class="flex items-center gap-2">
                <PieChart class="h-4 w-4 text-primary" />
                <p class="font-semibold text-foreground">{{ item.class }}</p>
              </div>
              <span class="text-sm font-semibold text-primary">{{ item.percentage.toFixed(2) }}%</span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
              <div class="h-full rounded-full bg-primary" :style="{ width: `${Math.min(item.percentage, 100)}%` }" />
            </div>
            <p class="mt-2 text-sm text-muted-foreground">{{ formatCurrency(item.total) }}</p>
          </div>
        </div>
      </SectionCard>

      <DataTable
        title="Ativos da Carteira"
        :description="`${investiments.meta?.total || investiments.data.length} ativo(s) encontrado(s)`"
        :total="investiments.meta?.total"
        :empty="!investiments.data.length"
        empty-title="Nenhum investimento cadastrado"
        empty-description="Registre seus ativos para acompanhar patrimônio, saldo e rentabilidade"
        :empty-icon="PiggyBank"
      >
        <template #header-actions>
          <div class="flex items-center gap-2">
            <a href="/investiments/export">
              <Button variant="outline" size="icon" title="Exportar CSV">
                <Download class="h-4 w-4" />
              </Button>
            </a>
            <SearchInput placeholder="Buscar ativos..." route-name="/investiments" />
          </div>
        </template>

        <template #empty-actions>
          <Button class="mt-6" @click="createInvestment">
            <Plus class="h-4 w-4" />
            Novo Investimento
          </Button>
        </template>

        <template #head>
          <tr class="border-b border-border">
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Ativo
            </th>
            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Tipo
            </th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Quantidade
            </th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Preço Médio
            </th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Saldo
            </th>
            <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Rentabilidade
            </th>
            <th class="w-52 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
              Ações
            </th>
          </tr>
        </template>

        <template #body>
          <tr
            v-for="investment in investiments.data"
            :key="investment.id"
            class="border-b border-border transition-colors hover:bg-surface/50"
          >
            <td class="px-4 py-4">
              <p class="font-medium text-foreground">{{ investment.name }}</p>
              <p class="text-xs text-muted-foreground">Aportado: {{ formatCurrency(investment.invested_amount) }}</p>
            </td>
            <td class="px-4 py-4">
              <StatBadge variant="investment">
                {{ investment.type_label }}
              </StatBadge>
              <p class="mt-1 text-xs text-muted-foreground">{{ investment.portfolio_class }}</p>
            </td>
            <td class="px-4 py-4 text-right text-muted-foreground">
              {{ formatQuantity(investment.quantity) }}
            </td>
            <td class="px-4 py-4 text-right text-muted-foreground">
              {{ formatCurrency(investment.average_price) }}
            </td>
            <td class="px-4 py-4 text-right font-semibold text-primary">
              {{ formatCurrency(investment.current_balance) }}
            </td>
            <td
              class="px-4 py-4 text-right font-semibold"
              :class="investment.profitability_percentage >= 0 ? 'text-revenue' : 'text-destructive'"
            >
              {{ formatPercent(investment.profitability_percentage) }}
            </td>
            <td class="px-4 py-4">
              <CrudActions
                delete-confirm-message="Tem certeza que deseja excluir este investimento?"
                @edit="editInvestment(investment)"
                @delete="deleteInvestment(investment)"
              />
            </td>
          </tr>
        </template>

        <PaginationLinks v-if="investiments.meta" :meta="investiments.meta" />
      </DataTable>
    </div>
  </AppLayout>
</template>

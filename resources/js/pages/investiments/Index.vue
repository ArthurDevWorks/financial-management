<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatBadge from '@/components/StatBadge.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { PieChart, PiggyBank, Plus, TrendingDown, TrendingUp, Wallet } from 'lucide-vue-next';
import { computed } from 'vue';

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
  if (confirm(`Deseja excluir o investimento "${investment.name}"?`)) {
    router.delete(`/investiments/${investment.id}`);
  }
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

      <SectionCard title="Ativos da Carteira" :description="`${investiments.data.length} ativo(s) encontrado(s)`">
        <div v-if="investiments.data.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
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
            </thead>
            <tbody>
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
                  <div class="flex items-center justify-center gap-2">
                    <Button variant="secondary" size="sm" class="gap-1.5" @click="editInvestment(investment)">
                      Editar
                    </Button>
                    <Button variant="destructive" size="sm" class="gap-1.5" @click="deleteInvestment(investment)">
                      Excluir
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <PiggyBank class="mb-4 h-16 w-16 text-muted-foreground opacity-15" />
          <p class="font-medium text-muted-foreground">Nenhum investimento cadastrado</p>
          <p class="mt-1 text-sm text-muted-foreground">Registre seus ativos para acompanhar patrimônio, saldo e rentabilidade</p>
          <Button class="mt-6" @click="createInvestment">
            <Plus class="h-4 w-4" />
            Novo Investimento
          </Button>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import FilterBar from '@/components/FilterBar.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatBadge from '@/components/StatBadge.vue';
import { Button } from '@/components/ui/button';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import {
  TrendingUp,
  TrendingDown,
  DollarSign,
} from 'lucide-vue-next';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

interface Summary {
  totalBalance: number;
  totalInitialBalance: number;
  totalRevenue: number;
  totalExpense: number;
  totalInvestment: number;
  netBalance: number;
  totalProfitability: number;
}

interface Transaction {
  id: string;
  type: 'revenue' | 'expense';
  description: string;
  category: string;
  value: number;
  date: string;
  account: string;
}

interface MonthlyData {
  month: string;
  revenue: number;
  expense: number;
  net: number;
}

interface CategoryData {
  category: string;
  value: number;
  count: number;
}

interface AccountEvolution {
  account: string;
  bank: string;
  balance: number;
  initialBalance: number;
}

const props = defineProps<{
  period: string;
  month: number;
  year: number;
  startDate: string | null;
  endDate: string | null;
  summary: Summary;
  revenuesByCategory: CategoryData[];
  expensesByCategory: CategoryData[];
  recentTransactions: Transaction[];
  monthlyData: MonthlyData[];
  accountsEvolution: AccountEvolution[];
}>();

const selectedPeriod = ref(props.period);
const selectedMonth = ref(props.month);
const selectedYear = ref(props.year);
const customStartDate = ref(props.startDate || '');
const customEndDate = ref(props.endDate || '');

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR');
};

const applyFilter = () => {
  const params = new URLSearchParams();
  params.append('period', selectedPeriod.value);

  if (selectedPeriod.value === 'month') {
    params.append('month', String(selectedMonth.value));
    params.append('year', String(selectedYear.value));
  } else if (selectedPeriod.value === 'year') {
    params.append('year', String(selectedYear.value));
  } else if (selectedPeriod.value === 'custom') {
    params.append('start_date', customStartDate.value);
    params.append('end_date', customEndDate.value);
  }

  router.visit(`/dashboard?${params.toString()}`);
};

const resetFilter = () => {
  selectedPeriod.value = 'month';
  selectedMonth.value = new Date().getMonth() + 1;
  selectedYear.value = new Date().getFullYear();
  customStartDate.value = '';
  customEndDate.value = '';
  router.visit('/dashboard');
};

const topRevenueCategories = [...props.revenuesByCategory]
  .sort((a, b) => b.value - a.value)
  .slice(0, 5);
const topExpenseCategories = [...props.expensesByCategory]
  .sort((a, b) => b.value - a.value)
  .slice(0, 5);

const summaryCards = [
  {
    label: 'Saldo Total',
    value: formatCurrency(props.summary.totalBalance),
    variant: 'default' as const,
    icon: DollarSign,
    trend: props.summary.totalInitialBalance
      ? Number(
          (
            ((props.summary.totalBalance - props.summary.totalInitialBalance) /
              props.summary.totalInitialBalance) *
            100
          ).toFixed(1),
        )
      : undefined,
    trendLabel: 'vs período anterior',
  },
  {
    label: 'Receitas',
    value: formatCurrency(props.summary.totalRevenue),
    variant: 'revenue' as const,
    icon: TrendingUp,
  },
  {
    label: 'Despesas',
    value: formatCurrency(props.summary.totalExpense),
    variant: 'expense' as const,
    icon: TrendingDown,
  },
  {
    label: 'Resultado Líquido',
    value: formatCurrency(props.summary.netBalance),
    variant: (props.summary.netBalance >= 0 ? 'profit' : 'expense') as 'profit' | 'expense',
    icon: TrendingUp,
  },
];
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader title="Painel Financeiro" description="Visualize um resumo completo de suas finanças">
        <template #actions>
          <Button @click="applyFilter">Atualizar</Button>
        </template>
      </PageHeader>

      <!-- FILTER BAR -->
      <div class="mb-8">
        <FilterBar
          :period="selectedPeriod"
          :month="selectedMonth"
          :year="selectedYear"
          :start-date="customStartDate"
          :end-date="customEndDate"
          @update:period="selectedPeriod = $event"
          @update:month="selectedMonth = $event"
          @update:year="selectedYear = $event"
          @update:startDate="customStartDate = $event"
          @update:endDate="customEndDate = $event"
          @apply="applyFilter"
          @reset="resetFilter"
        />
      </div>

      <!-- SUMMARY CARDS -->
      <div class="mb-8 grid grid-cols-1 gap-5 md:grid-cols-2 lg:grid-cols-4">
        <SummaryCard
          v-for="(card, index) in summaryCards"
          :key="index"
          :label="card.label"
          :value="card.value"
          :variant="card.variant"
          :icon="card.icon"
          :trend="card.trend"
          :trend-label="card.trendLabel"
        />
      </div>

      <!-- RECEITAS E DESPESAS POR CATEGORIA -->
      <div class="mb-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <SectionCard title="Top Receitas" :description="`${topRevenueCategories.length} categoria(s)`">
          <div v-if="topRevenueCategories.length" class="space-y-1">
            <div
              v-for="category in topRevenueCategories"
              :key="category.category"
              class="flex items-center justify-between rounded-lg px-3 py-2.5 transition-colors hover:bg-surface/50"
            >
              <div class="min-w-0 flex-1">
                <p class="font-medium text-foreground">{{ category.category }}</p>
                <p class="text-xs text-muted-foreground">{{ category.count }} transação(ões)</p>
              </div>
              <p class="font-semibold text-revenue">{{ formatCurrency(category.value) }}</p>
            </div>
          </div>
          <div v-else class="py-8 text-center text-sm text-muted-foreground">
            Nenhuma receita neste período
          </div>
        </SectionCard>

        <SectionCard title="Top Despesas" :description="`${topExpenseCategories.length} categoria(s)`">
          <div v-if="topExpenseCategories.length" class="space-y-1">
            <div
              v-for="category in topExpenseCategories"
              :key="category.category"
              class="flex items-center justify-between rounded-lg px-3 py-2.5 transition-colors hover:bg-surface/50"
            >
              <div class="min-w-0 flex-1">
                <p class="font-medium text-foreground">{{ category.category }}</p>
                <p class="text-xs text-muted-foreground">{{ category.count }} transação(ões)</p>
              </div>
              <p class="font-semibold text-destructive">{{ formatCurrency(category.value) }}</p>
            </div>
          </div>
          <div v-else class="py-8 text-center text-sm text-muted-foreground">
            Nenhuma despesa neste período
          </div>
        </SectionCard>
      </div>

      <!-- GRÁFICO MENSAL -->
      <SectionCard title="Movimentação Mensal" :description="`${monthlyData.length} meses`" class="mb-8">
        <div class="space-y-5">
            <div v-for="data in monthlyData" :key="data.month" class="space-y-1.5">
              <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-foreground">{{ data.month }}</p>
                <p class="text-sm text-muted-foreground">{{ formatCurrency(data.net) }}</p>
              </div>
              <div class="flex h-12 gap-1 overflow-hidden rounded-lg bg-surface p-1">
                <div
                  v-if="data.revenue > 0"
                  class="h-full shrink-0 rounded bg-revenue transition-all"
                  :style="{
                    width:
                      Math.min(
                        (data.revenue / (data.revenue + data.expense)) * 100,
                        100,
                      ) + '%',
                  }"
                  :title="`Receita: ${formatCurrency(data.revenue)}`"
                />
                <div
                  v-if="data.expense > 0"
                  class="h-full shrink-0 rounded bg-destructive transition-all"
                  :style="{
                    width:
                      Math.min(
                        (data.expense / (data.revenue + data.expense)) * 100,
                        100,
                      ) + '%',
                  }"
                  :title="`Despesa: ${formatCurrency(data.expense)}`"
                />
                <div
                  v-if="data.revenue === 0 && data.expense === 0"
                  class="h-full w-full rounded bg-surface"
                />
              </div>
              <div class="flex justify-between text-xs text-muted-foreground">
                <span class="text-revenue">Receita {{ formatCurrency(data.revenue) }}</span>
                <span class="text-destructive">Despesa {{ formatCurrency(data.expense) }}</span>
              </div>
          </div>
        </div>
      </SectionCard>

      <!-- CONTAS BANCÁRIAS -->
      <SectionCard title="Resumo de Contas" :description="`${accountsEvolution.length} conta(s)`" class="mb-8">
        <Table>
          <TableHeader>
              <TableRow class="border-b border-border hover:bg-transparent">
              <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Banco
              </TableHead>
              <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Conta
              </TableHead>
              <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Saldo Inicial
              </TableHead>
              <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Saldo Atual
              </TableHead>
              <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                Variação
              </TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="account in accountsEvolution"
              :key="account.account"
              class="border-b border-border transition-colors hover:bg-surface/50"
            >
              <TableCell class="font-medium text-foreground">{{ account.bank }}</TableCell>
              <TableCell class="text-muted-foreground">{{ account.account }}</TableCell>
              <TableCell class="text-right text-muted-foreground">
                {{ formatCurrency(account.initialBalance) }}
              </TableCell>
              <TableCell class="text-right font-semibold text-primary">
                {{ formatCurrency(account.balance) }}
              </TableCell>
              <TableCell
                class="text-right font-semibold"
                :class="account.balance - account.initialBalance >= 0 ? 'text-revenue' : 'text-destructive'"
              >
                {{ formatCurrency(account.balance - account.initialBalance) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </SectionCard>

      <!-- TRANSAÇÕES RECENTES -->
      <SectionCard
        title="Transações Recentes"
        :description="`${recentTransactions.length} transação(ões)`"
      >
        <div v-if="recentTransactions.length">
          <Table>
            <TableHeader>
              <TableRow class="border-b border-border hover:bg-transparent">
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Data
                </TableHead>
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Descrição
                </TableHead>
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Categoria
                </TableHead>
                <TableHead class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Conta
                </TableHead>
                <TableHead class="text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Valor
                </TableHead>
              </TableRow>
            </TableHeader>
            <TableBody>
              <TableRow
                v-for="transaction in recentTransactions"
                :key="transaction.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <TableCell class="text-sm text-muted-foreground">
                  {{ formatDate(transaction.date) }}
                </TableCell>
                <TableCell class="font-medium text-foreground">
                  {{ transaction.description }}
                </TableCell>
                <TableCell>
                  <StatBadge :variant="transaction.type === 'revenue' ? 'revenue' : 'expense'">
                    {{ transaction.category }}
                  </StatBadge>
                </TableCell>
                <TableCell class="text-sm text-muted-foreground">
                  {{ transaction.account }}
                </TableCell>
                <TableCell
                  class="text-right font-semibold"
                  :class="transaction.type === 'revenue' ? 'text-revenue' : 'text-destructive'"
                >
                  {{ transaction.type === 'revenue' ? '+' : '-' }}
                  {{ formatCurrency(transaction.value) }}
                </TableCell>
              </TableRow>
            </TableBody>
          </Table>
        </div>
        <div v-else class="py-8 text-center text-sm text-muted-foreground">
          Nenhuma transação neste período
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

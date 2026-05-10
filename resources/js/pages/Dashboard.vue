<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { TrendingUp, TrendingDown, DollarSign, Calendar } from 'lucide-vue-next'
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'

interface Summary {
  totalBalance: number
  totalInitialBalance: number
  totalRevenue: number
  totalExpense: number
  totalInvestment: number
  netBalance: number
  totalProfitability: number
}

interface Transaction {
  id: string
  type: 'revenue' | 'expense'
  description: string
  category: string
  value: number
  date: string
  account: string
}

interface MonthlyData {
  month: string
  revenue: number
  expense: number
  net: number
}

interface CategoryData {
  category: string
  value: number
  count: number
}

interface AccountEvolution {
  account: string
  bank: string
  balance: number
  initialBalance: number
}

const props = defineProps<{
  period: string
  month: number
  year: number
  startDate: string | null
  endDate: string | null
  summary: Summary
  revenuesByCategory: CategoryData[]
  expensesByCategory: CategoryData[]
  recentTransactions: Transaction[]
  monthlyData: MonthlyData[]
  accountsEvolution: AccountEvolution[]
}>()

const selectedPeriod = ref(props.period)
const selectedMonth = ref(props.month)
const selectedYear = ref(props.year)
const customStartDate = ref(props.startDate || '')
const customEndDate = ref(props.endDate || '')

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}

const applyFilter = () => {
  const params = new URLSearchParams()
  params.append('period', selectedPeriod.value)
  
  if (selectedPeriod.value === 'month') {
    params.append('month', String(selectedMonth.value))
    params.append('year', String(selectedYear.value))
  } else if (selectedPeriod.value === 'year') {
    params.append('year', String(selectedYear.value))
  } else if (selectedPeriod.value === 'custom') {
    params.append('start_date', customStartDate.value)
    params.append('end_date', customEndDate.value)
  }
  
  router.visit(`/dashboard?${params.toString()}`)
}

const resetFilter = () => {
  router.visit('/dashboard')
}

const topRevenueCategories = props.revenuesByCategory.sort((a, b) => b.value - a.value).slice(0, 5)
const topExpenseCategories = props.expensesByCategory.sort((a, b) => b.value - a.value).slice(0, 5)

</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8">
      <h1 class="text-4xl font-bold text-white mb-2">Painel Financeiro</h1>
      <p class="text-slate-400">Visualize um resumo completo de suas finanças</p>
    </div>

    <!-- FILTROS -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 p-6 mb-8 shadow-lg">
      <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
        <Calendar class="h-5 w-5 text-cyan-400" />
        Filtrar por Período
      </h2>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Tipo de Período -->
        <div>
          <label class="block text-sm font-semibold text-slate-200 mb-2">Tipo</label>
          <select
            v-model="selectedPeriod"
            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500 text-sm"
          >
            <option value="month">Mês</option>
            <option value="year">Ano</option>
            <option value="custom">Período Personalizado</option>
          </select>
        </div>

        <!-- Mês -->
        <div v-if="selectedPeriod === 'month'">
          <label class="block text-sm font-semibold text-slate-200 mb-2">Mês</label>
          <select
            v-model.number="selectedMonth"
            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500 text-sm"
          >
            <option value="1">Janeiro</option>
            <option value="2">Fevereiro</option>
            <option value="3">Março</option>
            <option value="4">Abril</option>
            <option value="5">Maio</option>
            <option value="6">Junho</option>
            <option value="7">Julho</option>
            <option value="8">Agosto</option>
            <option value="9">Setembro</option>
            <option value="10">Outubro</option>
            <option value="11">Novembro</option>
            <option value="12">Dezembro</option>
          </select>
        </div>

        <!-- Ano -->
        <div v-if="selectedPeriod !== 'custom'">
          <label class="block text-sm font-semibold text-slate-200 mb-2">Ano</label>
          <input
            v-model.number="selectedYear"
            type="number"
            class="w-full px-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500 text-sm"
            min="2020"
          />
        </div>

        <!-- Datas Personalizadas -->
        <template v-if="selectedPeriod === 'custom'">
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-2">De</label>
            <input
              v-model="customStartDate"
              type="date"
              class="w-full px-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500 text-sm"
            />
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-200 mb-2">Até</label>
            <input
              v-model="customEndDate"
              type="date"
              class="w-full px-3 py-2 bg-slate-700 border border-slate-600 text-white rounded-lg focus:border-cyan-500 focus:ring-cyan-500 text-sm"
            />
          </div>
        </template>
      </div>

      <!-- Botões -->
      <div class="flex gap-3 mt-4">
        <Button
          class="bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold"
          @click="applyFilter"
        >
          Aplicar Filtro
        </Button>
        <Button
          variant="outline"
          class="bg-slate-700 hover:bg-slate-600 text-white border-slate-600"
          @click="resetFilter"
        >
          Limpar
        </Button>
      </div>
    </div>

    <!-- CARDS DE RESUMO -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <!-- Saldo Total -->
      <div class="rounded-lg border border-slate-700 bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-slate-400 text-sm font-medium">Saldo Total</p>
            <p class="text-2xl font-bold text-white mt-2">{{ formatCurrency(summary.totalBalance) }}</p>
          </div>
          <DollarSign class="h-10 w-10 text-cyan-400 opacity-20" />
        </div>
      </div>

      <!-- Receitas -->
      <div class="rounded-lg border border-green-700/30 bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-green-400 text-sm font-medium">Receitas</p>
            <p class="text-2xl font-bold text-green-400 mt-2">{{ formatCurrency(summary.totalRevenue) }}</p>
          </div>
          <TrendingUp class="h-10 w-10 text-green-400 opacity-20" />
        </div>
      </div>

      <!-- Despesas -->
      <div class="rounded-lg border border-red-700/30 bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-lg">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-red-400 text-sm font-medium">Despesas</p>
            <p class="text-2xl font-bold text-red-400 mt-2">{{ formatCurrency(summary.totalExpense) }}</p>
          </div>
          <TrendingDown class="h-10 w-10 text-red-400 opacity-20" />
        </div>
      </div>

      <!-- Líquido -->
      <div
        :class="[
          'rounded-lg border bg-gradient-to-br from-slate-800 to-slate-900 p-6 shadow-lg',
          summary.netBalance >= 0
            ? 'border-cyan-700/30'
            : 'border-orange-700/30'
        ]"
      >
        <div class="flex items-center justify-between">
          <div>
            <p :class="['text-sm font-medium', summary.netBalance >= 0 ? 'text-cyan-400' : 'text-orange-400']">
              Movimentação Líquida
            </p>
            <p :class="['text-2xl font-bold mt-2', summary.netBalance >= 0 ? 'text-cyan-400' : 'text-orange-400']">
              {{ formatCurrency(summary.netBalance) }}
            </p>
            <p class="mt-1 text-xs text-slate-400">
              Receitas - despesas do período
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- RECEITAS E DESPESAS POR CATEGORIA -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
      <!-- RECEITAS -->
      <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg">
        <div class="border-b border-slate-700 px-6 py-4">
          <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <TrendingUp class="h-5 w-5 text-green-400" />
            Top Receitas
          </h3>
        </div>
        <div class="p-6">
          <div v-if="topRevenueCategories.length" class="space-y-4">
            <div
              v-for="category in topRevenueCategories"
              :key="category.category"
              class="flex items-center justify-between p-3 rounded-lg bg-slate-700/50 hover:bg-slate-700 transition"
            >
              <div class="flex-1">
                <p class="text-white font-medium">{{ category.category }}</p>
                <p class="text-xs text-slate-400">{{ category.count }} transação(ões)</p>
              </div>
              <p class="text-green-400 font-semibold">{{ formatCurrency(category.value) }}</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-slate-400">
            Nenhuma receita neste período
          </div>
        </div>
      </div>

      <!-- DESPESAS -->
      <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg">
        <div class="border-b border-slate-700 px-6 py-4">
          <h3 class="text-lg font-semibold text-white flex items-center gap-2">
            <TrendingDown class="h-5 w-5 text-red-400" />
            Top Despesas
          </h3>
        </div>
        <div class="p-6">
          <div v-if="topExpenseCategories.length" class="space-y-4">
            <div
              v-for="category in topExpenseCategories"
              :key="category.category"
              class="flex items-center justify-between p-3 rounded-lg bg-slate-700/50 hover:bg-slate-700 transition"
            >
              <div class="flex-1">
                <p class="text-white font-medium">{{ category.category }}</p>
                <p class="text-xs text-slate-400">{{ category.count }} transação(ões)</p>
              </div>
              <p class="text-red-400 font-semibold">{{ formatCurrency(category.value) }}</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-slate-400">
            Nenhuma despesa neste período
          </div>
        </div>
      </div>
    </div>

    <!-- GRÁFICO MENSAL -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg mb-8">
      <div class="border-b border-slate-700 px-6 py-4">
        <h3 class="text-lg font-semibold text-white">Movimentação Mensal</h3>
      </div>
      <div class="p-6">
        <div class="space-y-4">
          <div v-for="data in monthlyData" :key="data.month" class="space-y-2">
            <div class="flex items-center justify-between mb-1">
              <p class="text-slate-200 font-medium">{{ data.month }}</p>
              <p class="text-slate-400 text-sm">{{ formatCurrency(data.net) }}</p>
            </div>
            <div class="h-12 bg-slate-700 rounded-lg overflow-hidden flex gap-1 p-1">
              <div
                v-if="data.revenue > 0"
                class="bg-green-500 rounded h-full flex-shrink-0"
                :style="{ width: Math.min((data.revenue / (data.revenue + data.expense)) * 100, 100) + '%' }"
                :title="`Receita: ${formatCurrency(data.revenue)}`"
              />
              <div
                v-if="data.expense > 0"
                class="bg-red-500 rounded h-full flex-shrink-0"
                :style="{ width: Math.min((data.expense / (data.revenue + data.expense)) * 100, 100) + '%' }"
                :title="`Despesa: ${formatCurrency(data.expense)}`"
              />
              <div v-if="data.revenue === 0 && data.expense === 0" class="bg-slate-600 rounded h-full w-full" />
            </div>
            <div class="flex justify-between text-xs text-slate-400">
              <span>📈 {{ formatCurrency(data.revenue) }}</span>
              <span>📉 {{ formatCurrency(data.expense) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTAS BANCÁRIAS -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg mb-8">
      <div class="border-b border-slate-700 px-6 py-4">
        <h3 class="text-lg font-semibold text-white">Resumo de Contas</h3>
      </div>
      <div class="p-6 overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Banco</TableHead>
              <TableHead class="text-slate-300 font-semibold">Conta</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Saldo Inicial</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Saldo Atual</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Variação</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="account in accountsEvolution"
              :key="account.account"
              class="hover:bg-slate-700/50 border-b border-slate-700 transition"
            >
              <TableCell class="font-semibold text-white">{{ account.bank }}</TableCell>
              <TableCell class="text-slate-300">{{ account.account }}</TableCell>
              <TableCell class="text-right text-slate-300">{{ formatCurrency(account.initialBalance) }}</TableCell>
              <TableCell class="text-right font-semibold text-cyan-400">{{ formatCurrency(account.balance) }}</TableCell>
              <TableCell
                :class="[
                  'text-right font-semibold',
                  account.balance - account.initialBalance >= 0 ? 'text-green-400' : 'text-red-400'
                ]"
              >
                {{ formatCurrency(account.balance - account.initialBalance) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- TRANSAÇÕES RECENTES -->
    <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg">
      <div class="border-b border-slate-700 px-6 py-4">
        <h3 class="text-lg font-semibold text-white">Transações Recentes</h3>
      </div>
      <div class="p-6 overflow-x-auto">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Data</TableHead>
              <TableHead class="text-slate-300 font-semibold">Descrição</TableHead>
              <TableHead class="text-slate-300 font-semibold">Categoria</TableHead>
              <TableHead class="text-slate-300 font-semibold">Conta</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Valor</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow
              v-for="transaction in recentTransactions"
              :key="transaction.id"
              class="hover:bg-slate-700/50 border-b border-slate-700 transition"
            >
              <TableCell class="text-slate-300 text-sm">{{ formatDate(transaction.date) }}</TableCell>
              <TableCell class="font-medium text-white">{{ transaction.description }}</TableCell>
              <TableCell>
                <span
                  :class="[
                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border',
                    transaction.type === 'revenue'
                      ? 'bg-green-500/20 text-green-400 border-green-500/50'
                      : 'bg-red-500/20 text-red-400 border-red-500/50'
                  ]"
                >
                  {{ transaction.category }}
                </span>
              </TableCell>
              <TableCell class="text-slate-400 text-sm">{{ transaction.account }}</TableCell>
              <TableCell
                :class="[
                  'text-right font-semibold',
                  transaction.type === 'revenue' ? 'text-green-400' : 'text-red-400'
                ]"
              >
                {{ transaction.type === 'revenue' ? '+' : '-' }} {{ formatCurrency(transaction.value) }}
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
        <div v-if="recentTransactions.length === 0" class="text-center py-8 text-slate-400">
          Nenhuma transação neste período
        </div>
      </div>
    </div>
  </AppLayout>
</template>

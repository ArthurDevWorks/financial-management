<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table'
import { Button } from '@/components/ui/button'
import { Pencil, Trash2, Plus, TrendingDown } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Expense {
  id: number
  name: string
  value: number
  dt_expense: string
  account: { id: number; name: string }
  category: { id: number; name: string }
}

defineProps<{
  expenses: {
    data: Expense[]
  }
}>()

const editExpense = (expense: Expense) => {
  router.visit(`/expenses/${expense.id}/edit`)
}

const deleteExpense = (expense: Expense) => {
  if (confirm(`Deseja excluir a despesa "${expense.name}"?`)) {
    router.delete(`/expenses/${expense.id}`)
  }
}

const createExpense = () => {
  router.visit('/expenses/create')
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatDate = (date: string) => {
  return new Date(date).toLocaleDateString('pt-BR')
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-3xl font-bold text-slate-900">
            Despesas
          </h1>
        </div>
        <p class="mt-1 text-slate-500">
          Controle seus gastos e despesas
        </p>
      </div>

      <Button class="gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800" @click="createExpense">
        <Plus class="h-4 w-4" />
        Nova Despesa
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-200 px-8 py-6 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-lg font-semibold text-slate-900">
          Despesas Cadastradas
        </h2>
        <p class="text-sm text-slate-500 mt-1">
          {{ expenses.data.length }} despesa(s) encontrada(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-200 hover:bg-transparent">
              <TableHead class="text-slate-600 font-semibold">Descrição</TableHead>
              <TableHead class="text-slate-600 font-semibold">Categoria</TableHead>
              <TableHead class="text-slate-600 font-semibold">Conta</TableHead>
              <TableHead class="text-slate-600 font-semibold">Data</TableHead>
              <TableHead class="text-right text-slate-600 font-semibold">Valor</TableHead>
              <TableHead class="text-right w-20 text-slate-600 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="expenses.data.length">
              <TableRow
                v-for="expense in expenses.data"
                :key="expense.id"
                class="hover:bg-emerald-50 border-b border-slate-200 transition"
              >
                <TableCell class="font-semibold text-slate-900 py-4">
                  {{ expense.name }}
                </TableCell>

                <TableCell class="py-4">
                  <span class="inline-flex items-center rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                    {{ expense.category.name }}
                  </span>
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ expense.account.name }}
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ formatDate(expense.dt_expense) }}
                </TableCell>

                <TableCell class="text-right font-semibold text-red-600 py-4">
                  - {{ formatCurrency(expense.value) }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                      @click="editExpense(expense)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      @click="deleteExpense(expense)"
                      title="Deletar"
                    >
                      <Trash2 class="h-4 w-4" />
                    </button>
                  </div>
                </TableCell>
              </TableRow>
            </template>

            <!-- SEM DADOS -->
            <template v-else>
              <TableRow>
                <TableCell
                  colspan="6"
                  class="py-12 text-center text-slate-500"
                >
                  <div class="flex flex-col items-center justify-center">
                    <TrendingDown class="h-12 w-12 text-slate-300 mb-3" />
                    <p class="font-medium">Nenhuma despesa cadastrada</p>
                    <p class="text-sm">Comece criando uma nova despesa</p>
                  </div>
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>

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
import { Pencil, Trash2, Plus, TrendingUp } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Investment {
  id: number
  name: string
  value: number
  type: string
  profitability: number
  dt_investment: string
  category_id: number
  categories: { id: number; name: string }
}

defineProps<{
  investiments: {
    data: Investment[]
  }
}>()

const editInvestment = (investment: Investment) => {
  router.visit(`/investiments/${investment.id}/edit`)
}

const deleteInvestment = (investment: Investment) => {
  if (confirm(`Deseja excluir o investimento?`)) {
    router.delete(`/investiments/${investment.id}`)
  }
}

const createInvestment = () => {
  router.visit('/investiments/create')
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
          <h1 class="text-3xl font-bold text-white">
            Investimentos
          </h1>
        </div>
        <p class="mt-1 text-slate-400">
          Controle e monitore seus investimentos
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createInvestment">
        <Plus class="h-4 w-4" />
        Novo Investimento
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Investimentos Cadastrados
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ investiments.data.length }} investimento(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Descrição</TableHead>
              <TableHead class="text-slate-300 font-semibold">Tipo</TableHead>
              <TableHead class="text-slate-300 font-semibold">Data</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Valor</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Rentabilidade</TableHead>
              <TableHead class="text-right w-20 text-slate-300 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="investiments.data.length">
              <TableRow
                v-for="investment in investiments.data"
                :key="investment.id"
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="font-semibold text-white py-4">
                  <span class="text-base">{{ investment.name }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span class="inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold bg-slate-600/30 text-slate-300 border border-slate-500/50">
                    {{ investment.categories?.name || `Tipo ${investment.type}` }}
                  </span>
                </TableCell>

                <TableCell class="text-slate-400 py-4">
                  {{ formatDate(investment.dt_investment) }}
                </TableCell>

                <TableCell class="text-right font-semibold text-cyan-400 py-4">
                  {{ formatCurrency(investment.value) }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <span :class="investment.profitability >= 0 ? 'text-green-400' : 'text-red-400'" class="font-semibold">
                    {{ investment.profitability }}%
                  </span>
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-700 rounded-lg transition"
                      @click="editInvestment(investment)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded-lg transition"
                      @click="deleteInvestment(investment)"
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
                  class="py-12 text-center text-slate-400"
                >
                  <div class="flex flex-col items-center justify-center">
                    <TrendingUp class="h-12 w-12 text-slate-600 mb-3" />
                    <p class="font-medium">Nenhum investimento cadastrado</p>
                    <p class="text-sm">Comece registrando seu primeiro investimento</p>
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


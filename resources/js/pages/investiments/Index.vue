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
import { Pencil, Trash2, Plus, TrendingUp, Calculator } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Investment {
  id: number
  name: string
  value: number
  type: number
  profitability: number
  dt_investment: string
  category?: { id: number; name: string }
}

defineProps<{
  investiments: {
    data: Investment[]
  }
}>()

const editInvestment = (investment: Investment) => {
  router.visit(`/investiments/${investment.id}/edit`)
}

const valuateInvestment = (investment: Investment) => {
  router.visit(`/investiments/${investment.id}`)
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
          Controle as posicoes atuais da sua carteira
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
              <TableHead class="text-slate-300 font-semibold">Ticker</TableHead>
              <TableHead class="text-slate-300 font-semibold">Tipo</TableHead>
              <TableHead class="text-slate-300 font-semibold">Cotação</TableHead>
              <TableHead class="w-[22rem] text-center text-slate-300 font-semibold">
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
                    {{ investment.category?.name || `Categoria ${investment.type}` }}
                  </span>
                </TableCell>

                <TableCell class="font-semibold text-cyan-400 py-4">
                  {{ formatCurrency(investment.value) }}
                </TableCell>

                <TableCell class="py-4">
                  <div class="flex w-full items-center justify-center gap-2">
                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-amber-500/40 bg-amber-500/10 px-2.5 py-1.5 text-xs font-medium text-amber-100 transition hover:border-amber-400/70 hover:bg-amber-500/20"
                      @click="valuateInvestment(investment)"
                      title="Calcular valuation"
                    >
                      <Calculator class="h-4 w-4" />
                      Valuation
                    </button>

                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-slate-600/70 bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-slate-200 transition hover:border-cyan-500/60 hover:bg-slate-700 hover:text-cyan-300"
                      @click="editInvestment(investment)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                      Editar
                    </button>

                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-red-500/40 bg-red-500/10 px-2.5 py-1.5 text-xs font-medium text-red-200 transition hover:border-red-400/70 hover:bg-red-500/20 hover:text-red-100"
                      @click="deleteInvestment(investment)"
                      title="Deletar"
                    >
                      <Trash2 class="h-4 w-4" />
                      Excluir
                    </button>
                  </div>
                </TableCell>
              </TableRow>
            </template>

            <!-- SEM DADOS -->
            <template v-else>
              <TableRow>
                <TableCell
                  colspan="4"
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

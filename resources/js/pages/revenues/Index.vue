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

interface Revenue {
  id: number
  name: string
  value: number
  dt_revenue: string
  account: { id: number; name: string }
  category: { id: number; name: string }
}

defineProps<{
  revenues: {
    data: Revenue[]
  }
}>()

const editRevenue = (revenue: Revenue) => {
  router.visit(`/revenues/${revenue.id}/edit`)
}

const deleteRevenue = (revenue: Revenue) => {
  if (confirm(`Deseja excluir a receita "${revenue.name}"?`)) {
    router.delete(`/revenues/${revenue.id}`)
  }
}

const createRevenue = () => {
  router.visit('/revenues/create')
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
            Receitas
          </h1>
        </div>
        <p class="mt-1 text-slate-500">
          Controle suas receitas e ganhos
        </p>
      </div>

      <Button class="gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800" @click="createRevenue">
        <Plus class="h-4 w-4" />
        Nova Receita
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-200 px-8 py-6 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-lg font-semibold text-slate-900">
          Receitas Cadastradas
        </h2>
        <p class="text-sm text-slate-500 mt-1">
          {{ revenues.data.length }} receita(s) encontrada(s)
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
            <template v-if="revenues.data.length">
              <TableRow
                v-for="revenue in revenues.data"
                :key="revenue.id"
                class="hover:bg-emerald-50 border-b border-slate-200 transition"
              >
                <TableCell class="font-semibold text-slate-900 py-4">
                  {{ revenue.name }}
                </TableCell>

                <TableCell class="py-4">
                  <span class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                    {{ revenue.category.name }}
                  </span>
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ revenue.account.name }}
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ formatDate(revenue.dt_revenue) }}
                </TableCell>

                <TableCell class="text-right font-semibold text-emerald-600 py-4">
                  + {{ formatCurrency(revenue.value) }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                      @click="editRevenue(revenue)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      @click="deleteRevenue(revenue)"
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
                    <TrendingUp class="h-12 w-12 text-slate-300 mb-3" />
                    <p class="font-medium">Nenhuma receita cadastrada</p>
                    <p class="text-sm">Comece criando uma nova receita</p>
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

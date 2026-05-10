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
import { Pencil, Trash2, Plus, ArrowRightLeft } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Release {
  id: number
  title: string
  amount: number
  date: string
  type: 'revenue' | 'expense'
  account: { id: number; account: string; bank?: { name: string } } | null
  category: { id: number; name: string } | null
}

defineProps<{
  releases: {
    data: Release[]
  }
}>()

const editRelease = (release: Release) => {
  router.visit(`/releases/${release.id}/edit`)
}

const deleteRelease = (release: Release) => {
  if (confirm(`Deseja excluir o lançamento "${release.title}"?`)) {
    router.delete(`/releases/${release.id}`)
  }
}

const createRelease = () => {
  router.visit('/releases/create')
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const formatDate = (date: string) => {
  if (!date) return ''

  const [ano, mes, dia] = date.split('-')

  if (!ano || !mes || !dia) {
    return date
  }

  return `${dia}/${mes}/${ano}`
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-3xl font-bold text-white">
            Lançamentos
          </h1>
        </div>
        <p class="mt-1 text-slate-400">
          Controle centralizado de suas receitas e despesas
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createRelease">
        <Plus class="h-4 w-4" />
        Novo Lançamento
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Lançamentos Cadastrados
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ releases?.data?.length || 0 }} lançamento(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Descrição</TableHead>
              <TableHead class="text-slate-300 font-semibold">Categoria</TableHead>
              <TableHead class="text-slate-300 font-semibold">Conta</TableHead>
              <TableHead class="text-slate-300 font-semibold">Data</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Valor</TableHead>
              <TableHead class="text-right w-20 text-slate-300 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="releases?.data?.length">
              <TableRow
                v-for="release in releases.data"
                :key="release.id"
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="font-semibold text-white py-4">
                  {{ release.title }}
                </TableCell>

                <TableCell class="py-4">
                  <span
                    :class="[
                      'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border',
                      release.type === 'revenue'
                        ? 'bg-green-500/20 text-green-400 border-green-500/50'
                        : 'bg-red-500/20 text-red-400 border-red-500/50'
                    ]"
                  >
                    {{ release.category?.name ?? 'Sem Categoria' }}
                  </span>
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ release.account?.bank ? `${release.account.bank.name} - ${release.account.account}` : (release.account?.account ?? 'Sem Conta') }}
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ formatDate(release.date) }}
                </TableCell>

                <TableCell
                  :class="[
                    'text-right font-semibold py-4',
                    release.type === 'revenue' ? 'text-green-400' : 'text-red-400'
                  ]"
                >
                  {{ release.type === 'revenue' ? '+' : '-' }} {{ formatCurrency(release.amount) }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-700 rounded-lg transition"
                      @click="editRelease(release)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded-lg transition"
                      @click="deleteRelease(release)"
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
                    <ArrowRightLeft class="h-12 w-12 text-slate-500 mb-3" />
                    <p class="font-medium">Nenhum lançamento cadastrado</p>
                    <p class="text-sm">Comece criando um novo lançamento financeiro</p>
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

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
import { Pencil, Trash2, Plus, Wallet } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Account {
  id: number
  agency: string
  account: string
  type: number
  total: number
  bank: { id: number; name: string }
}

defineProps<{
  accounts: {
    data: Account[]
  }
}>()

const editAccount = (account: Account) => {
  router.visit(`/accounts/${account.id}/edit`)
}

const deleteAccount = (account: Account) => {
  if (confirm(`Deseja excluir a conta bancária?`)) {
    router.delete(`/accounts/${account.id}`)
  }
}

const createAccount = () => {
  router.visit('/accounts/create')
}

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value)
}

const typeLabel: Record<number, string> = {
  1: 'Corrente',
  2: 'Poupança',
  3: 'Crédito',
  4: 'Investimento',
}

const typeColor: Record<number, string> = {
  1: 'bg-blue-500/20 text-blue-400 border border-blue-500/50',
  2: 'bg-green-500/20 text-green-400 border border-green-500/50',
  3: 'bg-orange-500/20 text-orange-400 border border-orange-500/50',
  4: 'bg-purple-500/20 text-purple-400 border border-purple-500/50',
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-3xl font-bold text-white">
            Contas
          </h1>
        </div>
        <p class="mt-1 text-slate-400">
          Gerencie suas contas bancárias e controle seus saldos
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createAccount">
        <Plus class="h-4 w-4" />
        Nova Conta
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Contas Cadastradas
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ accounts.data.length }} conta(s) encontrada(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Banco</TableHead>
              <TableHead class="text-slate-300 font-semibold">Agência</TableHead>
              <TableHead class="text-slate-300 font-semibold">Conta</TableHead>
              <TableHead class="text-slate-300 font-semibold">Tipo</TableHead>
              <TableHead class="text-right text-slate-300 font-semibold">Saldo Total</TableHead>
              <TableHead class="text-right w-20 text-slate-300 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="accounts.data.length">
              <TableRow
                v-for="account in accounts.data"
                :key="account.id"
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="font-semibold text-white py-4">
                  <span class="text-base">{{ account.bank.name }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span class="text-slate-300">{{ account.agency }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span class="text-slate-300 font-mono">{{ account.account }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span :class="['inline-flex items-center rounded-full px-4 py-2 text-xs font-semibold', typeColor[account.type] || 'bg-slate-500/20 text-slate-400 border border-slate-500/50']">
                    {{ typeLabel[account.type] || `Tipo ${account.type}` }}
                  </span>
                </TableCell>

                <TableCell class="text-right font-semibold text-cyan-400 py-4">
                  {{ formatCurrency(account.total) }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-400 hover:text-cyan-400 hover:bg-slate-700 rounded-lg transition"
                      @click="editAccount(account)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-700 rounded-lg transition"
                      @click="deleteAccount(account)"
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
                    <Wallet class="h-12 w-12 text-slate-600 mb-3" />
                    <p class="font-medium">Nenhuma conta cadastrada</p>
                    <p class="text-sm">Comece criando uma nova conta</p>
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


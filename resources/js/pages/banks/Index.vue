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
import { Pencil, Trash2, Plus, Landmark } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Bank {
  id: number
  name: string
  logo?: string
  accounts_count: number
}

defineProps<{
  banks: {
    data: Bank[]
  }
}>()

const editBank = (bank: Bank) => {
  router.visit(`/banks/${bank.id}/edit`)
}

const deleteBank = (bank: Bank) => {
  if (confirm(`Deseja excluir o banco "${bank.name}"?`)) {
    router.delete(`/banks/${bank.id}`)
  }
}

const createBank = () => {
  router.visit('/banks/create')
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg">
            <Landmark class="h-6 w-6 text-white" />
          </div>
          <h1 class="text-3xl font-bold text-slate-900">
            Bancos
          </h1>
        </div>
        <p class="mt-1 text-slate-500 ml-11">
          Gerencie os bancos cadastrados no sistema
        </p>
      </div>

      <Button class="gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800" @click="createBank">
        <Plus class="h-4 w-4" />
        Novo Banco
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-200 px-8 py-6 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-lg font-semibold text-slate-900">
          Bancos Cadastrados
        </h2>
        <p class="text-sm text-slate-500 mt-1">
          {{ banks.data.length }} banco(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-200 hover:bg-transparent">
              <TableHead class="w-20 text-slate-600 font-semibold">Logo</TableHead>
              <TableHead class="text-slate-600 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-600 font-semibold">Contas Vinculadas</TableHead>
              <TableHead class="text-right w-32 text-slate-600 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="banks.data.length">
              <TableRow
                v-for="bank in banks.data"
                :key="bank.id"
                class="hover:bg-emerald-50 border-b border-slate-200 transition"
              >
                <TableCell class="text-3xl py-4 font-semibold">
                  {{ bank.logo ?? '🏦' }}
                </TableCell>

                <TableCell class="font-semibold text-slate-900 py-4">
                  <span class="text-base">{{ bank.name }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span
                    class="inline-flex items-center rounded-full bg-emerald-100 px-4 py-2 text-xs font-semibold text-emerald-700\"
                  >
                    {{ bank.accounts_count }} conta(s)
                  </span>
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                      @click="editBank(bank)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      @click="deleteBank(bank)"
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
                  colspan="4"
                  class="py-12 text-center text-slate-500"
                >
                  <div class="flex flex-col items-center justify-center">
                    <Landmark class="h-12 w-12 text-slate-300 mb-3" />
                    <p class="font-medium">Nenhum banco cadastrado</p>
                    <p class="text-sm">Comece criando um novo banco</p>
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


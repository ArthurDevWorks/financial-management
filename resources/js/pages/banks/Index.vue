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
import { Pencil, Trash2, Plus } from 'lucide-vue-next'
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
        <h1 class="text-3xl font-bold text-slate-900">
          Bancos
        </h1>
        <p class="mt-1 text-slate-500">
          Gerencie os bancos cadastrados no sistema
        </p>
      </div>

      <Button class="gap-2" @click="createBank">
        <Plus class="h-4 w-4" />
        Novo Banco
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-xl border bg-white">
      <!-- CARD HEADER -->
      <div class="border-b px-6 py-4">
        <h2 class="text-lg font-semibold text-slate-900">
          Bancos Cadastrados
        </h2>
        <p class="text-sm text-slate-500">
          {{ banks.data.length }} banco(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-6 py-4">
        <Table>
          <TableHeader>
            <TableRow>
              <TableHead class="w-20">Logo</TableHead>
              <TableHead>Nome</TableHead>
              <TableHead>Contas Vinculadas</TableHead>
              <TableHead class="text-right w-32">
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
                class="hover:bg-slate-50"
              >
                <TableCell class="text-2xl">
                  {{ bank.logo ?? '🏦' }}
                </TableCell>

                <TableCell class="font-medium text-slate-900">
                  {{ bank.name }}
                </TableCell>

                <TableCell>
                  <span
                    class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-medium text-emerald-700"
                  >
                    {{ bank.accounts_count }} conta(s)
                  </span>
                </TableCell>

                <TableCell class="text-right">
                  <div class="flex justify-end gap-3">
                    <button
                      class="text-slate-600 hover:text-slate-900"
                      @click="editBank(bank)"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="text-red-500 hover:text-red-600"
                      @click="deleteBank(bank)"
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
                  class="py-10 text-center text-slate-500"
                >
                  Nenhum banco cadastrado
                </TableCell>
              </TableRow>
            </template>
          </TableBody>
        </Table>
      </div>
    </div>
  </AppLayout>
</template>

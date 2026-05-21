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
  logo_url?: string
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

const logoSrc = (bank: Bank) => {
  if (bank.logo_url){
    return bank.logo_url;
  }
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-white">
          Bancos
        </h1>
        <p class="text-slate-400 mt-1">
          Gerencie os bancos cadastrados no sistema
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createBank">
        <Plus class="h-4 w-4 "/>
        Novo Banco
      </Button>
    </div>

    <div class="rounded-lg border border-slate-700 bg-slate-800 shadow-lg overflow-hidden">
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Bancos Cadastrados
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ banks.data.length }} banco(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="w-20 text-slate-300 font-semibold">Logo</TableHead>
              <TableHead class="text-slate-300 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-300 font-semibold">Contas Vinculadas</TableHead>
              <TableHead class="text-center w-52 text-slate-300 font-semibold">
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
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="py-4">
                  <div class="flex items-center">
                    <img
                      :src="logoSrc(bank)"
                      :alt="`Logo ${bank.name}`"
                      class="h-8 w-8 rounded-md object-contain"
                    />
                  </div>
                </TableCell>

                <TableCell class="font-semibold text-white py-4">
                  <span class="text-base">{{ bank.name }}</span>
                </TableCell>

                <TableCell class="py-4">
                  <span class="inline-flex items-center rounded-full bg-cyan-500/20 px-4 py-2 text-xs font-semibold text-cyan-400 border border-cyan-500/50">
                    {{ bank.accounts_count }} conta(s)
                  </span>
                </TableCell>

                <TableCell class="py-4">
                  <div class="flex w-full items-center justify-center gap-2">
                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-slate-600/70 bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-slate-200 transition hover:border-cyan-500/60 hover:bg-slate-700 hover:text-cyan-300"
                      @click="editBank(bank)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                      Editar
                    </button>

                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-red-500/40 bg-red-500/10 px-2.5 py-1.5 text-xs font-medium text-red-200 transition hover:border-red-400/70 hover:bg-red-500/20 hover:text-red-100"
                      @click="deleteBank(bank)"
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
                    <Landmark class="h-12 w-12 text-slate-600 mb-3" />
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

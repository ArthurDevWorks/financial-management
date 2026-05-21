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
import { Pencil, Trash2, Plus, Tags } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface Category {
  id: number
  name: string
  type: string
}

defineProps<{
  categories: {
    data: Category[]
  }
}>()

const typeLabel: Record<string, string> = {
  receita: 'Receita',
  despesa: 'Despesa',
  investimento: 'Investimento',
}

const typeColor: Record<string, string> = {
  receita: 'bg-green-500/20 text-green-400 border border-green-500/50',
  despesa: 'bg-red-500/20 text-red-400 border border-red-500/50',
  investimento: 'bg-purple-500/20 text-purple-400 border border-purple-500/50',
}

const editCategory = (category: Category) => {
  router.visit(`/categories/${category.id}/edit`)
}

const deleteCategory = (category: Category) => {
  if (confirm(`Deseja excluir a categoria "${category.name}"?`)) {
    router.delete(`/categories/${category.id}`)
  }
}

const createCategory = () => {
  router.visit('/categories/create')
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <h1 class="text-3xl font-bold text-white">
            Categorias
          </h1>
        </div>
        <p class="mt-1 text-slate-400">
          Gerencie as categorias de receitas e despesas
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createCategory">
        <Plus class="h-4 w-4" />
        Nova Categoria
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Categorias Cadastradas
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ categories.data.length }} categoria(s) encontrada(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-300 font-semibold">Tipo</TableHead>
              <TableHead class="text-center w-52 text-slate-300 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="categories.data.length">
              <TableRow
                v-for="category in categories.data"
                :key="category.id"
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="font-semibold text-white py-4">
                  {{ category.name }}
                </TableCell>

                <TableCell class="py-4">
                  <span :class="`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold border ${typeColor[category.type]}`">
                    {{ typeLabel[category.type] }}
                  </span>
                </TableCell>

                <TableCell class="py-4">
                  <div class="flex w-full items-center justify-center gap-2">
                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-slate-600/70 bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-slate-200 transition hover:border-cyan-500/60 hover:bg-slate-700 hover:text-cyan-300"
                      @click="editCategory(category)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                      Editar
                    </button>

                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-red-500/40 bg-red-500/10 px-2.5 py-1.5 text-xs font-medium text-red-200 transition hover:border-red-400/70 hover:bg-red-500/20 hover:text-red-100"
                      @click="deleteCategory(category)"
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
                  colspan="3"
                  class="py-12 text-center text-slate-400"
                >
                  <div class="flex flex-col items-center justify-center">
                    <Tags class="h-12 w-12 text-slate-500 mb-3" />
                    <p class="font-medium">Nenhuma categoria cadastrada</p>
                    <p class="text-sm">Comece criando uma nova categoria</p>
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

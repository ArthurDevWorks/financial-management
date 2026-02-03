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
  revenue: 'Receita',
  expense: 'Despesa',
  investment: 'Investimento',
}

const typeColor: Record<string, string> = {
  revenue: 'bg-emerald-100 text-emerald-700',
  expense: 'bg-red-100 text-red-700',
  investment: 'bg-amber-100 text-amber-700',
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
          <h1 class="text-3xl font-bold text-slate-900">
            Categorias
          </h1>
        </div>
        <p class="mt-1 text-slate-500">
          Gerencie as categorias de receitas e despesas
        </p>
      </div>

      <Button class="gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800" @click="createCategory">
        <Plus class="h-4 w-4" />
        Nova Categoria
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-200 px-8 py-6 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-lg font-semibold text-slate-900">
          Categorias Cadastradas
        </h2>
        <p class="text-sm text-slate-500 mt-1">
          {{ categories.data.length }} categoria(s) encontrada(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-200 hover:bg-transparent">
              <TableHead class="text-slate-600 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-600 font-semibold">Tipo</TableHead>
              <TableHead class="text-right w-20 text-slate-600 font-semibold">
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
                class="hover:bg-emerald-50 border-b border-slate-200 transition"
              >
                <TableCell class="font-semibold text-slate-900 py-4">
                  {{ category.name }}
                </TableCell>

                <TableCell class="py-4">
                  <span :class="`inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold ${typeColor[category.type]}`">
                    {{ typeLabel[category.type] }}
                  </span>
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                      @click="editCategory(category)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      @click="deleteCategory(category)"
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
                  colspan="3"
                  class="py-12 text-center text-slate-500"
                >
                  <div class="flex flex-col items-center justify-center">
                    <Tags class="h-12 w-12 text-slate-300 mb-3" />
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

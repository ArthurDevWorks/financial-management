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
import { Pencil, Trash2, Plus, Users } from 'lucide-vue-next'
import { router } from '@inertiajs/vue3'

interface User {
  id: number
  name: string
  email: string
}

defineProps<{
  users: {
    data: User[]
  }
}>()

const editUser = (user: User) => {
  router.visit(`/users/${user.id}/edit`)
}

const deleteUser = (user: User) => {
  if (confirm(`Deseja excluir o usuário "${user.name}"?`)) {
    router.delete(`/users/${user.id}`)
  }
}

const createUser = () => {
  router.visit('/users/create')
}
</script>

<template>
  <AppLayout>
    <!-- PAGE HEADER -->
    <div class="mb-8 flex items-center justify-between">
      <div>
        <div class="flex items-center gap-3 mb-2">
          <div class="p-2 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-lg">
            <Users class="h-6 w-6 text-white" />
          </div>
          <h1 class="text-3xl font-bold text-slate-900">
            Usuários
          </h1>
        </div>
        <p class="mt-1 text-slate-500 ml-11">
          Gerencie os usuários do sistema
        </p>
      </div>

      <Button class="gap-2 bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800" @click="createUser">
        <Plus class="h-4 w-4" />
        Novo Usuário
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-200 bg-white shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-200 px-8 py-6 bg-gradient-to-r from-slate-50 to-white">
        <h2 class="text-lg font-semibold text-slate-900">
          Usuários Cadastrados
        </h2>
        <p class="text-sm text-slate-500 mt-1">
          {{ users.data.length }} usuário(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-200 hover:bg-transparent">
              <TableHead class="text-slate-600 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-600 font-semibold">Email</TableHead>
              <TableHead class="text-right w-20 text-slate-600 font-semibold">
                Ações
              </TableHead>
            </TableRow>
          </TableHeader>

          <TableBody>
            <!-- COM DADOS -->
            <template v-if="users.data.length">
              <TableRow
                v-for="user in users.data"
                :key="user.id"
                class="hover:bg-emerald-50 border-b border-slate-200 transition"
              >
                <TableCell class="font-semibold text-slate-900 py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white font-semibold text-sm">
                      {{ user.name[0].toUpperCase() }}
                    </div>
                    {{ user.name }}
                  </div>
                </TableCell>

                <TableCell class="text-slate-600 py-4">
                  {{ user.email }}
                </TableCell>

                <TableCell class="text-right py-4">
                  <div class="flex justify-end gap-2">
                    <button
                      class="p-2 text-slate-600 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition"
                      @click="editUser(user)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                    </button>

                    <button
                      class="p-2 text-red-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition"
                      @click="deleteUser(user)"
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
                    <Users class="h-12 w-12 text-slate-300 mb-3" />
                    <p class="font-medium">Nenhum usuário cadastrado</p>
                    <p class="text-sm">Comece criando um novo usuário</p>
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

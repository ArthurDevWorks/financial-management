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
          <div class="p-2 bg-cyan-500 rounded-lg">
            <Users class="h-6 w-6 text-slate-900" />
          </div>
          <h1 class="text-3xl font-bold text-white">
            Usuários
          </h1>
        </div>
        <p class="mt-1 text-slate-400 ml-11">
          Gerencie os usuários do sistema
        </p>
      </div>

      <Button class="gap-2 bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold" @click="createUser">
        <Plus class="h-4 w-4" />
        Novo Usuário
      </Button>
    </div>

    <!-- CARD -->
    <div class="rounded-2xl border border-slate-700 bg-slate-800 shadow-sm overflow-hidden">
      <!-- CARD HEADER -->
      <div class="border-b border-slate-700 px-8 py-6 bg-slate-800">
        <h2 class="text-lg font-semibold text-white">
          Usuários Cadastrados
        </h2>
        <p class="text-sm text-slate-400 mt-1">
          {{ users.data.length }} usuário(s) encontrado(s)
        </p>
      </div>

      <!-- TABLE -->
      <div class="px-8 py-6">
        <Table>
          <TableHeader>
            <TableRow class="border-b border-slate-700 hover:bg-transparent">
              <TableHead class="text-slate-300 font-semibold">Nome</TableHead>
              <TableHead class="text-slate-300 font-semibold">Email</TableHead>
              <TableHead class="text-center w-52 text-slate-300 font-semibold">
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
                class="hover:bg-slate-700/50 border-b border-slate-700 transition"
              >
                <TableCell class="font-semibold text-white py-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-cyan-500 flex items-center justify-center text-slate-900 font-semibold text-sm">
                      {{ user.name[0].toUpperCase() }}
                    </div>
                    {{ user.name }}
                  </div>
                </TableCell>

                <TableCell class="text-slate-300 py-4">
                  {{ user.email }}
                </TableCell>

                <TableCell class="py-4">
                  <div class="flex w-full items-center justify-center gap-2">
                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-slate-600/70 bg-slate-800 px-2.5 py-1.5 text-xs font-medium text-slate-200 transition hover:border-cyan-500/60 hover:bg-slate-700 hover:text-cyan-300"
                      @click="editUser(user)"
                      title="Editar"
                    >
                      <Pencil class="h-4 w-4" />
                      Editar
                    </button>

                    <button
                      class="inline-flex items-center gap-1.5 rounded-md border border-red-500/40 bg-red-500/10 px-2.5 py-1.5 text-xs font-medium text-red-200 transition hover:border-red-400/70 hover:bg-red-500/20 hover:text-red-100"
                      @click="deleteUser(user)"
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
                    <Users class="h-12 w-12 text-slate-500 mb-3" />
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

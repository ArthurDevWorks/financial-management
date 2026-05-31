<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Plus, Users } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface User {
  id: number;
  name: string;
  email: string;
}

defineProps<{
  users: {
    data: User[];
  };
}>();

const editUser = (user: User) => {
  router.visit(`/users/${user.id}/edit`);
};

const deleteUser = (user: User) => {
  if (confirm(`Deseja excluir o usuário "${user.name}"?`)) {
    router.delete(`/users/${user.id}`);
  }
};

const createUser = () => {
  router.visit('/users/create');
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader
        title="Usuários"
        description="Gerencie os usuários do sistema"
      >
        <template #actions>
          <Button @click="createUser">
            <Plus class="h-4 w-4" />
            Novo Usuário
          </Button>
        </template>
      </PageHeader>

      <SectionCard
        title="Usuários Cadastrados"
        :description="`${users.data.length} usuário(s) encontrado(s)`"
      >
        <div v-if="users.data.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Nome
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Email
                </th>
                <th class="w-52 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="user in users.data"
                :key="user.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <td class="px-4 py-4 font-medium text-foreground">
                  {{ user.name }}
                </td>
                <td class="px-4 py-4 text-muted-foreground">
                  {{ user.email }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Button variant="secondary" size="sm" @click="editUser(user)">
                      Editar
                    </Button>
                    <Button variant="destructive" size="sm" @click="deleteUser(user)">
                      Excluir
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <Users class="mb-4 h-16 w-16 text-muted-foreground opacity-15" />
          <p class="font-medium text-muted-foreground">Nenhum usuário cadastrado</p>
          <Button class="mt-6" @click="createUser">
            <Plus class="h-4 w-4" />
            Novo Usuário
          </Button>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

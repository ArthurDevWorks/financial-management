<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Plus, Landmark } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Bank {
  id: number;
  name: string;
  logo_url?: string;
  accounts_count: number;
}

defineProps<{
  banks: {
    data: Bank[];
  };
}>();

const editBank = (bank: Bank) => {
  router.visit(`/banks/${bank.id}/edit`);
};

const deleteBank = (bank: Bank) => {
  if (confirm(`Deseja excluir o banco "${bank.name}"?`)) {
    router.delete(`/banks/${bank.id}`);
  }
};

const createBank = () => {
  router.visit('/banks/create');
};

const logoSrc = (bank: Bank) => bank.logo_url || undefined;
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader
        title="Bancos"
        description="Gerencie os bancos cadastrados no sistema"
      >
        <template #actions>
          <Button @click="createBank">
            <Plus class="h-4 w-4" />
            Novo Banco
          </Button>
        </template>
      </PageHeader>

      <SectionCard
        title="Bancos Cadastrados"
        :description="`${banks.data.length} banco(s) encontrado(s)`"
      >
        <div v-if="banks.data.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border">
                <th class="w-20 px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Logo
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Nome
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Contas Vinculadas
                </th>
                <th class="w-52 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="bank in banks.data"
                :key="bank.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <td class="px-4 py-4">
                  <img
                    v-if="logoSrc(bank)"
                    :src="logoSrc(bank)"
                    :alt="`Logo ${bank.name}`"
                    class="h-8 w-8 rounded-md object-contain"
                  />
                  <div v-else class="flex h-8 w-8 items-center justify-center rounded-md bg-primary/10 text-primary">
                    <Landmark class="h-4 w-4" />
                  </div>
                </td>
                <td class="px-4 py-4 font-medium text-foreground">
                  {{ bank.name }}
                </td>
                <td class="px-4 py-4">
                  <span class="inline-flex items-center rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary">
                    {{ bank.accounts_count }} conta(s)
                  </span>
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Button variant="secondary" size="sm" class="gap-1.5" @click="editBank(bank)">
                      Editar
                    </Button>
                    <Button variant="destructive" size="sm" class="gap-1.5" @click="deleteBank(bank)">
                      Excluir
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <Landmark class="mb-4 h-16 w-16 text-muted-foreground opacity-15" />
          <p class="font-medium text-muted-foreground">Nenhum banco cadastrado</p>
          <p class="mt-1 text-sm text-muted-foreground">Comece criando um novo banco</p>
          <Button class="mt-6" @click="createBank">
            <Plus class="h-4 w-4" />
            Novo Banco
          </Button>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

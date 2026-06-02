<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import DataTable from '@/components/DataTable.vue';
import CrudActions from '@/components/CrudActions.vue';
import SearchInput from '@/components/SearchInput.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Plus, Landmark, Download, Building2 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface PaginationMeta {
  current_page: number;
  last_page: number;
  from: number;
  to: number;
  total: number;
  links: { url: string | null; label: string; active: boolean }[];
}

interface Bank {
  id: number;
  name: string;
  logo_url?: string;
  accounts_count: number;
}

defineProps<{
  banks: {
    data: Bank[];
    meta: PaginationMeta;
  };
}>();

const editBank = (bank: Bank) => {
  router.visit(`/banks/${bank.id}/edit`);
};

const deleteBank = (bank: Bank) => {
  router.delete(`/banks/${bank.id}`);
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

      <DataTable
        title="Bancos Cadastrados"
        :description="`${banks.meta?.total || banks.data.length} banco(s) encontrado(s)`"
        :total="banks.meta?.total"
        :empty="!banks.data.length"
        empty-title="Nenhum banco cadastrado"
        empty-description="Comece criando um novo banco"
        :empty-icon="Building2"
      >
        <template #header-actions>
          <div class="flex items-center gap-2">
            <a href="/banks/export">
              <Button variant="outline" size="icon" title="Exportar CSV">
                <Download class="h-4 w-4" />
              </Button>
            </a>
            <SearchInput placeholder="Buscar bancos..." route-name="/banks" />
          </div>
        </template>

        <template #empty-actions>
          <Button class="mt-6" @click="createBank">
            <Plus class="h-4 w-4" />
            Novo Banco
          </Button>
        </template>

        <template #head>
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
        </template>

        <template #body>
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
              <CrudActions
                delete-confirm-message="Tem certeza que deseja excluir este banco?"
                @edit="editBank(bank)"
                @delete="deleteBank(bank)"
              />
            </td>
          </tr>
        </template>

        <PaginationLinks v-if="banks.meta" :meta="banks.meta" />
      </DataTable>
    </div>
  </AppLayout>
</template>

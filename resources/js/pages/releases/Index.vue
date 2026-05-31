<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatBadge from '@/components/StatBadge.vue';
import CrudActions from '@/components/CrudActions.vue';
import EmptyState from '@/components/EmptyState.vue';
import { Button } from '@/components/ui/button';
import { Plus, ArrowRightLeft } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Release {
  id: number;
  title: string;
  amount: number;
  date: string;
  type: 'revenue' | 'expense';
  account: { id: number; account: string; bank?: { name: string } } | null;
  category: { id: number; name: string } | null;
}

defineProps<{
  releases: {
    data: Release[];
  };
}>();

const editRelease = (release: Release) => {
  router.visit(`/releases/${release.id}/edit`);
};

const deleteRelease = (release: Release) => {
  if (confirm(`Deseja excluir o lançamento "${release.title}"?`)) {
    router.delete(`/releases/${release.id}`);
  }
};

const createRelease = () => {
  router.visit('/releases/create');
};

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};

const formatDate = (date: string) => {
  if (!date) return '';
  const [ano, mes, dia] = date.split('-');
  if (!ano || !mes || !dia) return date;
  return `${dia}/${mes}/${ano}`;
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader
        title="Lançamentos"
        description="Controle centralizado de suas receitas e despesas"
      >
        <template #actions>
          <Button @click="createRelease">
            <Plus class="h-4 w-4" />
            Novo Lançamento
          </Button>
        </template>
      </PageHeader>

      <SectionCard
        title="Lançamentos Cadastrados"
        :description="`${releases?.data?.length || 0} lançamento(s) encontrado(s)`"
      >
        <div v-if="releases?.data?.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Descrição
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Categoria
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Conta
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Data
                </th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Valor
                </th>
                <th class="px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="release in releases.data"
                :key="release.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <td class="px-4 py-4 font-medium text-foreground">
                  {{ release.title }}
                </td>
                <td class="px-4 py-4">
                  <StatBadge :variant="release.type === 'revenue' ? 'revenue' : 'expense'">
                    {{ release.category?.name ?? 'Sem Categoria' }}
                  </StatBadge>
                </td>
                <td class="px-4 py-4 text-muted-foreground">
                  {{
                    release.account?.bank
                      ? `${release.account.bank.name} - ${release.account.account}`
                      : release.account?.account ?? 'Sem Conta'
                  }}
                </td>
                <td class="px-4 py-4 text-muted-foreground">
                  {{ formatDate(release.date) }}
                </td>
                <td
                  class="px-4 py-4 text-right font-semibold"
                  :class="release.type === 'revenue' ? 'text-revenue' : 'text-destructive'"
                >
                  {{ release.type === 'revenue' ? '+' : '-' }}
                  {{ formatCurrency(release.amount) }}
                </td>
                <td class="px-4 py-4">
                  <CrudActions
                    @edit="editRelease(release)"
                    @delete="deleteRelease(release)"
                  />
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else>
          <EmptyState
            :icon="ArrowRightLeft"
            title="Nenhum lançamento cadastrado"
            description="Comece criando um novo lançamento financeiro"
          >
            <template #actions>
              <Button @click="createRelease">
                <Plus class="h-4 w-4" />
                Novo Lançamento
              </Button>
            </template>
          </EmptyState>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

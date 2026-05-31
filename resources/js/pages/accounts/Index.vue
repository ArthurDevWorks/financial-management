<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import StatBadge from '@/components/StatBadge.vue';
import { Button } from '@/components/ui/button';
import { Plus, Wallet } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

interface Account {
  id: number;
  account: string;
  total: number;
  current_balance: number;
  type: string;
  bank: { id: number; name: string };
}

defineProps<{
  accounts: {
    data: Account[];
  };
}>();

const editAccount = (account: Account) => {
  router.visit(`/accounts/${account.id}/edit`);
};

const deleteAccount = (account: Account) => {
  if (confirm(`Deseja excluir a conta "${account.account}"?`)) {
    router.delete(`/accounts/${account.id}`);
  }
};

const createAccount = () => {
  router.visit('/accounts/create');
};

const formatCurrency = (value: number | null | undefined) => {
  if (value === null || value === undefined || Number.isNaN(value)) return 'R$ 0,00';
  return new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  }).format(value);
};
</script>

<template>
  <AppLayout>
    <div class="p-8">
      <PageHeader
        title="Contas"
        description="Gerencie suas contas bancárias"
      >
        <template #actions>
          <Button @click="createAccount">
            <Plus class="h-4 w-4" />
            Nova Conta
          </Button>
        </template>
      </PageHeader>

      <SectionCard
        title="Contas Cadastradas"
        :description="`${accounts.data.length} conta(s) encontrada(s)`"
      >
        <div v-if="accounts.data.length" class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b border-border">
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Banco
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Conta
                </th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Tipo
                </th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Saldo Inicial
                </th>
                <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Saldo Atual
                </th>
                <th class="w-52 px-4 py-3 text-center text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                  Ações
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="account in accounts.data"
                :key="account.id"
                class="border-b border-border transition-colors hover:bg-surface/50"
              >
                <td class="px-4 py-4 font-medium text-foreground">
                  {{ account.bank?.name }}
                </td>
                <td class="px-4 py-4 text-foreground">
                  {{ account.account }}
                </td>
                <td class="px-4 py-4">
                  <StatBadge variant="default">
                    {{ account.type }}
                  </StatBadge>
                </td>
                <td class="px-4 py-4 text-right text-muted-foreground">
                  {{ formatCurrency(account.total) }}
                </td>
                <td
                  class="px-4 py-4 text-right font-semibold"
                  :class="account.current_balance >= 0 ? 'text-primary' : 'text-destructive'"
                >
                  {{ formatCurrency(account.current_balance) }}
                </td>
                <td class="px-4 py-4">
                  <div class="flex items-center justify-center gap-2">
                    <Button variant="secondary" size="sm" class="gap-1.5" @click="editAccount(account)">
                      Editar
                    </Button>
                    <Button variant="destructive" size="sm" class="gap-1.5" @click="deleteAccount(account)">
                      Excluir
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-else class="flex flex-col items-center justify-center py-16 text-center">
          <Wallet class="mb-4 h-16 w-16 text-muted-foreground opacity-15" />
          <p class="font-medium text-muted-foreground">Nenhuma conta cadastrada</p>
          <p class="mt-1 text-sm text-muted-foreground">Crie uma conta bancária para começar</p>
          <Button class="mt-6" @click="createAccount">
            <Plus class="h-4 w-4" />
            Nova Conta
          </Button>
        </div>
      </SectionCard>
    </div>
  </AppLayout>
</template>

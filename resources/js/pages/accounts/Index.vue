<script setup lang="ts">
import CrudActions from '@/components/CrudActions.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchInput from '@/components/SearchInput.vue';
import StatBadge from '@/components/StatBadge.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Building2, Download, Plus } from 'lucide-vue-next';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

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
        meta: PaginationMeta;
    };
}>();

const editAccount = (account: Account) => {
    router.visit(`/accounts/${account.id}/edit`);
};

const deleteAccount = (account: Account) => {
    router.delete(`/accounts/${account.id}`);
};

const createAccount = () => {
    router.visit('/accounts/create');
};

const formatCurrency = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value))
        return '0,00';
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

            <DataTable
                title="Contas Cadastradas"
                :description="`${accounts.meta?.total || accounts.data.length} conta(s) encontrada(s)`"
                :total="accounts.meta?.total"
                :empty="!accounts.data.length"
                empty-title="Nenhuma conta cadastrada"
                empty-description="Crie uma conta bancária para começar"
                :empty-icon="Building2"
            >
                <template #header-actions>
                    <div class="flex items-center gap-2">
                        <a href="/accounts/export">
                            <Button
                                variant="outline"
                                size="icon"
                                title="Exportar XLSX"
                            >
                                <Download class="h-4 w-4" />
                            </Button>
                        </a>
                        <SearchInput
                            placeholder="Buscar contas..."
                            route-name="/accounts"
                        />
                    </div>
                </template>

                <template #empty-actions>
                    <Button class="mt-6" @click="createAccount">
                        <Plus class="h-4 w-4" />
                        Nova Conta
                    </Button>
                </template>

                <template #head>
                    <tr class="border-b border-border">
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Banco
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Conta
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Tipo
                        </th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Saldo Inicial
                        </th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Saldo Atual
                        </th>
                        <th
                            class="w-52 px-4 py-3 text-center text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Ações
                        </th>
                    </tr>
                </template>

                <template #body>
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
                            :class="
                                account.current_balance >= 0
                                    ? 'text-primary'
                                    : 'text-destructive'
                            "
                        >
                            {{ formatCurrency(account.current_balance) }}
                        </td>
                        <td class="px-4 py-4">
                            <CrudActions
                                delete-confirm-message="Tem certeza que deseja excluir esta conta?"
                                @edit="editAccount(account)"
                                @delete="deleteAccount(account)"
                            />
                        </td>
                    </tr>
                </template>

                <PaginationLinks v-if="accounts.meta" :meta="accounts.meta" />
            </DataTable>
        </div>
    </AppLayout>
</template>

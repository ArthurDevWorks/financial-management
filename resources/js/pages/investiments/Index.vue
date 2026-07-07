<script setup lang="ts">
import CrudActions from '@/components/CrudActions.vue';
import DataTable from '@/components/DataTable.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SearchInput from '@/components/SearchInput.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Investment {
    id: number;
    name: string;
    type_label: string;
    current_balance: number;
    logo_url: string | null;
}

defineProps<{
    investiments: {
        data: Investment[];
        meta: PaginationMeta;
    };
}>();

const editInvestment = (investment: Investment) => {
    router.visit(`/investiments/${investment.id}/edit`);
};

const deleteInvestment = (investment: Investment) => {
    router.delete(`/investiments/${investment.id}`);
};

const createInvestment = () => {
    router.visit('/investiments/create');
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
            <PageHeader title="Carteira de Investimentos">
                <template #actions>
                    <Button @click="createInvestment">
                        Novo Investimento
                    </Button>
                </template>
            </PageHeader>

            <DataTable
                title="Ativos da Carteira"
                :description="`${investiments.meta?.total || investiments.data.length} ativo(s) encontrado(s)`"
                :total="investiments.meta?.total"
                :empty="!investiments.data.length"
                empty-title="Nenhum investimento cadastrado"
                empty-description="Registre seus ativos para acompanhar seu saldo"
            >
                <template #header-actions>
                    <div class="flex items-center gap-2">
                        <SearchInput
                            placeholder="Buscar ativos..."
                            route-name="/investiments"
                        />
                    </div>
                </template>

                <template #empty-actions>
                    <Button class="mt-6" @click="createInvestment">
                        Novo Investimento
                    </Button>
                </template>

                <template #head>
                    <tr class="border-b border-border">
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Ativo
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Categoria
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Valor da Cotação
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
                        v-for="investment in investiments.data"
                        :key="investment.id"
                        class="border-b border-border transition-colors hover:bg-surface/50"
                    >
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-3">
                                <img
                                    v-if="investment.logo_url"
                                    :src="investment.logo_url"
                                    :alt="investment.name"
                                    class="h-8 w-8 rounded-full object-contain"
                                />
                                <div
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-muted text-xs font-bold text-muted-foreground"
                                >
                                    {{ investment.name.charAt(0) }}
                                </div>
                                <p class="font-medium text-foreground">
                                    {{ investment.name }}
                                </p>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            {{ investment.type_label }}
                        </td>
                        <td
                            class="px-4 py-4 text-left font-semibold text-primary"
                        >
                            {{ formatCurrency(investment.current_balance) }}
                        </td>
                        <td class="px-4 py-4">
                            <CrudActions
                                delete-confirm-message="Tem certeza que deseja excluir este investimento?"
                                @edit="editInvestment(investment)"
                                @delete="deleteInvestment(investment)"
                            />
                        </td>
                    </tr>
                </template>

                <PaginationLinks
                    v-if="investiments.meta"
                    :meta="investiments.meta"
                />
            </DataTable>
        </div>
    </AppLayout>
</template>

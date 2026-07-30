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
import { ArrowRightLeft, Download, Plus } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Release {
    id: number;
    title: string;
    amount: number;
    date: string;
    type: 'revenue' | 'expense';
    account: { id: number; account: string; bank?: { name: string } } | null;
    category: { id: number; name: string } | null;
    payment_method: string | null;
    status: string;
    installment_number: number | null;
    total_installments: number | null;
    recurrence_id: number | null;
    parent: { id: number } | null;
}

defineProps<{
    releases: {
        data: Release[];
        meta: PaginationMeta;
    };
    filters?: {
        search?: string;
    };
}>();

const paymentMethodLabels: Record<string, string> = {
    cash: 'Dinheiro',
    credit_card: 'Cartão de Crédito',
    debit_card: 'Cartão de Débito',
    pix: 'Pix',
};

const statusLabels: Record<string, string> = {
    paid: 'Pago',
    pending: 'Previsto',
    canceled: 'Cancelado',
};

const statusColors: Record<string, string> = {
    paid: 'bg-green-500/20 text-green-400 border border-green-500/50',
    pending: 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/50',
    canceled: 'bg-red-500/20 text-red-400 border border-red-500/50',
};

const editRelease = (release: Release) => {
    router.visit(`/releases/${release.id}/edit`);
};

const deleteRelease = (release: Release) => {
    router.delete(`/releases/${release.id}`);
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

            <DataTable
                title="Lançamentos Cadastrados"
                :description="`${releases?.meta?.total || 0} lançamento(s) encontrado(s)`"
                :total="releases?.meta?.total"
                :empty="!releases?.data?.length"
                empty-title="Nenhum lançamento cadastrado"
                empty-description="Comece criando um novo lançamento financeiro"
                :empty-icon="ArrowRightLeft"
            >
                <template #header-actions>
                    <div class="flex items-center gap-2">
                        <a href="/releases/export">
                            <Button
                                variant="outline"
                                size="icon"
                                title="Exportar CSV"
                            >
                                <Download class="h-4 w-4" />
                            </Button>
                        </a>
                        <SearchInput
                            placeholder="Buscar lançamentos..."
                            route-name="/releases"
                        />
                    </div>
                </template>

                <template #empty-actions>
                    <Button class="mt-6" @click="createRelease">
                        <Plus class="h-4 w-4" />
                        Novo Lançamento
                    </Button>
                </template>

                <template #head>
                    <tr class="border-b border-border">
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Descrição
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Categoria
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Conta
                        </th>
                        <th
                            class="px-4 py-3 text-center text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Forma de Pagamento
                        </th>
                        <th
                            class="px-4 py-3 text-center text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Status
                        </th>
                        <th
                            class="px-4 py-3 text-left text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Data
                        </th>
                        <th
                            class="px-4 py-3 text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Valor
                        </th>
                        <th
                            class="px-4 py-3 text-center text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >
                            Ações
                        </th>
                    </tr>
                </template>

                <template #body>
                    <tr
                        v-for="release in releases.data"
                        :key="release.id"
                        class="border-b border-border transition-colors hover:bg-surface/50"
                    >
                        <td class="px-4 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-medium text-foreground">
                                    {{ release.title }}
                                </span>
                                <span
                                    v-if="release.installment_number"
                                    class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                                >
                                    {{ release.installment_number }}/{{ release.total_installments }}
                                </span>
                                <span
                                    v-if="release.recurrence_id"
                                    class="inline-flex items-center rounded-full bg-purple-500/10 px-2 py-0.5 text-[10px] font-medium text-purple-400"
                                >
                                    Recorrente
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-4">
                            <StatBadge
                                :variant="
                                    release.type === 'revenue'
                                        ? 'revenue'
                                        : 'expense'
                                "
                            >
                                {{ release.category?.name ?? 'Sem Categoria' }}
                            </StatBadge>
                        </td>
                        <td class="px-4 py-4 text-muted-foreground">
                            {{
                                release.account?.bank
                                    ? `${release.account.bank.name} - ${release.account.account}`
                                    : (release.account?.account ?? 'Sem Conta')
                            }}
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span
                                v-if="release.payment_method"
                                class="text-xs text-muted-foreground"
                            >
                                {{ paymentMethodLabels[release.payment_method] ?? release.payment_method }}
                            </span>
                            <span v-else class="text-xs text-muted-foreground/50">—</span>
                        </td>
                        <td class="px-4 py-4 text-center">
                            <span
                                class="inline-block rounded-full px-2.5 py-0.5 text-[11px] font-medium"
                                :class="statusColors[release.status] || 'bg-surface text-muted-foreground border border-border'"
                            >
                                {{ statusLabels[release.status] ?? release.status }}
                            </span>
                        </td>
                        <td class="px-4 py-4 text-muted-foreground">
                            {{ formatDate(release.date) }}
                        </td>
                        <td
                            class="px-4 py-4 text-right font-semibold"
                            :class="
                                release.type === 'revenue'
                                    ? 'text-revenue'
                                    : 'text-destructive'
                            "
                        >
                            {{ release.type === 'revenue' ? '+' : '-' }}
                            {{ formatCurrency(release.amount) }}
                        </td>
                        <td class="px-4 py-4">
                            <CrudActions
                                delete-confirm-message="Tem certeza que deseja excluir este lançamento?"
                                @edit="editRelease(release)"
                                @delete="deleteRelease(release)"
                            />
                        </td>
                    </tr>
                </template>

                <PaginationLinks v-if="releases.meta" :meta="releases.meta" />
            </DataTable>
        </div>
    </AppLayout>
</template>

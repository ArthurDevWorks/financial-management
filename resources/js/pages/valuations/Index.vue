<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ChartNoAxesCombined, Plus } from 'lucide-vue-next';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface ValuationSummary {
    id: number;
    calculated_at: string;
    fair_value_per_share?: number | null;
    upside?: number | null;
    margin_of_safety?: number | null;
}

interface Investment {
    id: number;
    name: string;
}

interface Valuation {
    investiment: Investment;
    dcf: ValuationSummary | null;
    preco_teto: ValuationSummary | null;
}

defineProps<{
    valuations: {
        data: Valuation[];
        meta: PaginationMeta;
    };
}>();

const formatCurrency = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value))
        return '0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatPercent = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value))
        return '---';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};

const createValuation = () => {
    router.visit('/valuations/create');
};

const openValuation = (valuation: ValuationSummary | null) => {
    if (valuation) {
        router.visit(`/valuations/${valuation.id}`);
    }
};
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <PageHeader
                title="Valuations"
                description="Histórico de cálculos de valuation realizados"
            >
                <template #actions>
                    <Button @click="createValuation">
                        <Plus class="h-4 w-4" />
                        Nova Valuation
                    </Button>
                </template>
            </PageHeader>

            <SectionCard
                title="Cálculos por Ativo"
                :description="`${valuations.meta?.total || valuations.data.length} ativo(s) com valuation`"
            >
                <div v-if="valuations.data.length" class="overflow-x-auto">
                    <Table>
                        <TableHeader>
                            <TableRow
                                class="border-b border-border hover:bg-transparent"
                            >
                                <TableHead
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Ativo
                                </TableHead>
                                <TableHead
                                    class="text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Margem DCF
                                </TableHead>
                                <TableHead
                                    class="text-right text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Margem Preço Teto
                                </TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="valuation in valuations.data"
                                :key="valuation.investiment.id"
                                class="border-b border-border transition-colors hover:bg-surface/50"
                            >
                                <TableCell class="font-medium text-foreground">
                                    {{ valuation.investiment.name }}
                                </TableCell>
                                <TableCell class="text-right">
                                    <button
                                        v-if="valuation.dcf"
                                        type="button"
                                        class="text-right"
                                        @click="openValuation(valuation.dcf)"
                                    >
                                        <span
                                            class="block font-semibold"
                                            :class="
                                                (valuation.dcf
                                                    .margin_of_safety ?? 0) >= 0
                                                    ? 'text-revenue'
                                                    : 'text-destructive'
                                            "
                                        >
                                            {{
                                                formatPercent(
                                                    valuation.dcf
                                                        .margin_of_safety,
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="block text-xs text-muted-foreground"
                                        >
                                            {{
                                                formatCurrency(
                                                    valuation.dcf
                                                        .fair_value_per_share,
                                                )
                                            }}
                                            ·
                                            {{
                                                formatDate(
                                                    valuation.dcf.calculated_at,
                                                )
                                            }}
                                        </span>
                                    </button>
                                    <span v-else class="text-muted-foreground"
                                        >---</span
                                    >
                                </TableCell>
                                <TableCell class="text-right">
                                    <button
                                        v-if="valuation.preco_teto"
                                        type="button"
                                        class="text-right"
                                        @click="
                                            openValuation(valuation.preco_teto)
                                        "
                                    >
                                        <span
                                            class="block font-semibold"
                                            :class="
                                                (valuation.preco_teto
                                                    .margin_of_safety ?? 0) >= 0
                                                    ? 'text-revenue'
                                                    : 'text-destructive'
                                            "
                                        >
                                            {{
                                                formatPercent(
                                                    valuation.preco_teto
                                                        .margin_of_safety,
                                                )
                                            }}
                                        </span>
                                        <span
                                            class="block text-xs text-muted-foreground"
                                        >
                                            {{
                                                formatCurrency(
                                                    valuation.preco_teto
                                                        .fair_value_per_share,
                                                )
                                            }}
                                            ·
                                            {{
                                                formatDate(
                                                    valuation.preco_teto
                                                        .calculated_at,
                                                )
                                            }}
                                        </span>
                                    </button>
                                    <span v-else class="text-muted-foreground"
                                        >---</span
                                    >
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <PaginationLinks
                        v-if="valuations.meta"
                        :meta="valuations.meta"
                    />
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <ChartNoAxesCombined
                        class="mb-4 h-16 w-16 text-muted-foreground opacity-15"
                    />
                    <p class="font-medium text-muted-foreground">
                        Nenhum ativo com valuation encontrado
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Realize um cálculo de valuation para começar
                    </p>
                    <Button class="mt-6" @click="createValuation">
                        <Plus class="h-4 w-4" />
                        Nova Valuation
                    </Button>
                </div>
            </SectionCard>
        </div>
    </AppLayout>
</template>

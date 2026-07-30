<script setup lang="ts">
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, Pencil } from 'lucide-vue-next';
import { computed } from 'vue';

interface ValuationAsset {
    id: number;
    ticker: string;
    name: string;
    logo_url?: string | null;
    current_price?: number | null;
    dividends_per_share?: number | null;
    net_income?: number | null;
    total_shares?: number | null;
    free_cash_flow?: number | null;
    asset_type: string;
}

interface Valuation {
    id: number;
    asset: ValuationAsset;
    method: 'dcf' | 'preco_teto' | 'gordon';
    method_label: string;
    assumptions: Record<string, any>;
    calculated_at: string;
}

const props = defineProps<{
    valuation: Valuation;
}>();

const formatCurrency = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatPercent = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatNumber = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value)) return 'N/A';
    return new Intl.NumberFormat('pt-BR').format(value);
};

const goBack = () => {
    router.visit('/valuations');
};

const goToEdit = () => {
    if (props.valuation.method === 'preco_teto') {
        router.visit(`/preco-teto?valuation_id=${props.valuation.id}`);
        return;
    }
    if (props.valuation.method === 'gordon') {
        router.visit(`/gordon?valuation_id=${props.valuation.id}`);
        return;
    }
    router.visit(`/valuations?valuation_id=${props.valuation.id}`);
};

const a = computed(() => props.valuation.assumptions ?? {});

const assumptionItems = computed(() => {
    const isGordon = props.valuation.method === 'gordon';
    const isPrecoTeto = props.valuation.method === 'preco_teto';

    if (isGordon) {
        return [
            { label: 'DPS atual', value: formatCurrency(a.value.dps) },
            { label: 'Taxa de desconto (Ke)', value: `${a.value.discount_rate ?? 'N/A'}%` },
            { label: 'Cresc. perpetuidade (g)', value: `${a.value.growth_perpetuity ?? 'N/A'}%` },
            { label: 'Preço atual', value: formatCurrency(a.value.current_price) },
            { label: 'Anos de projeção', value: `${a.value.projection_years ?? 'N/A'}` },
        ];
    }
    if (isPrecoTeto) {
        return [
            { label: 'Dividend yield desejado', value: `${a.value.desired_yield ?? 'N/A'}%` },
            { label: 'Payout projetado', value: `${a.value.projected_payout ?? 'N/A'}%` },
            { label: 'Lucro líquido projetado', value: formatCurrency(a.value.projected_net_income) },
            { label: 'Quantidade de ações', value: formatNumber(a.value.total_shares) },
            { label: 'Crescimento projetado', value: `${a.value.projected_growth_rate ?? 'N/A'}%` },
            { label: 'Preço atual por ação', value: formatCurrency(a.value.current_price_per_share) },
        ];
    }
    return [
        { label: 'FCF atual', value: formatCurrency(a.value.current_fcf) },
        { label: 'Taxa de desconto', value: `${a.value.discount_rate ?? 'N/A'}%` },
        { label: 'Payout', value: `${a.value.payout ?? 'N/A'}%` },
        { label: 'ROE', value: `${a.value.roe ?? 'N/A'}%` },
        { label: 'Crescimento na perpetuidade', value: `${a.value.terminal_growth_rate ?? 'N/A'}%` },
        { label: 'Anos de projeção', value: `${a.value.projection_years ?? 'N/A'}` },
        { label: 'Total de ações', value: formatNumber(a.value.total_shares) },
        { label: 'Preço atual por ação', value: formatCurrency(a.value.current_price_per_share) },
    ];
});
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <div class="mb-2">
                <button
                    type="button"
                    class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="goBack"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Voltar
                </button>
            </div>

            <div class="mb-8 flex items-start justify-between gap-4">
                <div class="flex items-center gap-3">
                    <img
                        :src="valuation.asset.logo_url || '/images/default-logo.svg'"
                        :alt="valuation.asset.ticker"
                        class="h-10 w-10 rounded-full object-contain"
                        @error="($event.target as HTMLImageElement).src = '/images/default-logo.svg'"
                    />
                    <div class="min-w-0">
                        <h1 class="text-2xl font-bold tracking-tight text-foreground">
                            {{ valuation.asset.name }}
                        </h1>
                        <p class="mt-1 text-sm text-muted-foreground">
                            {{ valuation.asset.ticker }} · {{ valuation.method_label }} · {{ formatDate(valuation.calculated_at) }}
                        </p>
                    </div>
                </div>
                <div class="flex shrink-0 items-center gap-3">
                    <Button @click="goToEdit">
                        <Pencil class="h-4 w-4" />
                        Editar Premissas
                    </Button>
                </div>
            </div>

            <div class="mt-6 grid gap-6 xl:grid-cols-2">
                <SectionCard
                    title="Dados do Ativo"
                    description="Informações atuais do ativo na tabela Assets"
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div class="rounded-lg border border-border bg-surface px-4 py-3">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Cotação Atual</p>
                            <p class="mt-1 text-lg font-semibold text-foreground">{{ formatCurrency(valuation.asset.current_price) }}</p>
                        </div>
                        <div v-if="valuation.asset.dividends_per_share !== null" class="rounded-lg border border-border bg-surface px-4 py-3">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Dividendos por Ação</p>
                            <p class="mt-1 text-lg font-semibold text-foreground">{{ formatCurrency(valuation.asset.dividends_per_share) }}</p>
                        </div>
                        <div v-if="valuation.asset.net_income !== null" class="rounded-lg border border-border bg-surface px-4 py-3">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Lucro Líquido</p>
                            <p class="mt-1 text-lg font-semibold text-foreground">{{ formatCurrency(valuation.asset.net_income) }}</p>
                        </div>
                        <div v-if="valuation.asset.total_shares !== null" class="rounded-lg border border-border bg-surface px-4 py-3">
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Total de Ações</p>
                            <p class="mt-1 text-lg font-semibold text-foreground">{{ formatNumber(valuation.asset.total_shares) }}</p>
                        </div>
                    </div>
                </SectionCard>

                <SectionCard
                    title="Premissas da Análise"
                    description="Parâmetros utilizados no cálculo"
                >
                    <div class="grid gap-3 sm:grid-cols-2">
                        <div
                            v-for="item in assumptionItems"
                            :key="item.label"
                            class="rounded-lg border border-border bg-surface px-4 py-3"
                        >
                            <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                {{ item.label }}
                            </p>
                            <p class="mt-1 text-lg font-semibold text-foreground">
                                {{ item.value }}
                            </p>
                        </div>
                    </div>
                </SectionCard>
            </div>
        </div>
    </AppLayout>
</template>

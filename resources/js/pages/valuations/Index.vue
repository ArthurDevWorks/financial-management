<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ChartNoAxesCombined, Plus, TrendingUp, Calculator } from 'lucide-vue-next';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface ValuationResult {
    id: number;
    method: string;
    method_label: string;
    calculated_at: string;
    fair_value?: number | null;
    margin_of_safety?: number | null;
    upside?: number | null;
}

interface Valuation {
    id: number;
    ticker: string;
    name: string;
    logo_url?: string | null;
    current_price?: number | null;
    asset_type: string;
    valuation_count: number;
    latest_calculated_at?: string | null;
    valuations: ValuationResult[];
}

defineProps<{
    valuations: {
        data: Valuation[];
        meta: PaginationMeta;
    };
}>();

const formatCurrency = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value)) return '---';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatDate = (date: string | null | undefined) => {
    if (!date) return '---';
    return new Date(date).toLocaleDateString('pt-BR');
};

const formatPercent = (value: number | null | undefined) => {
    if (value === null || value === undefined || Number.isNaN(value)) return '---';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};

const assetTypeLabel = (type: string) => (type === 'fii' ? 'FII' : 'Ação');

const openValuation = (valuation: ValuationResult) => {
    router.visit(`/valuations/${valuation.id}`);
};

const methodIcon = (method: string) => {
    if (method === 'dcf') return Calculator;
    if (method === 'preco_teto') return ChartNoAxesCombined;
    return TrendingUp;
};

const marginTone = (margin: number | null | undefined) => {
    if (margin === null || margin === undefined || Number.isNaN(margin)) {
        return { text: 'text-muted-foreground', bg: 'bg-muted', bar: 'bg-muted' };
    }
    if (margin >= 30) return { text: 'text-revenue', bg: 'bg-revenue/10', bar: 'bg-revenue' };
    if (margin >= 15) return { text: 'text-emerald-500', bg: 'bg-emerald-500/10', bar: 'bg-emerald-500' };
    if (margin >= 0) return { text: 'text-primary', bg: 'bg-primary/10', bar: 'bg-primary' };
    if (margin >= -15) return { text: 'text-amber-500', bg: 'bg-amber-500/10', bar: 'bg-amber-500' };
    return { text: 'text-destructive', bg: 'bg-destructive/10', bar: 'bg-destructive' };
};

const gaugeWidth = (margin: number | null | undefined) => {
    if (margin === null || margin === undefined || Number.isNaN(margin)) return '0%';
    return `${Math.min(Math.max(((margin + 50) / 130) * 100, 2), 100)}%`;
};
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <PageHeader
                title="Valuations"
                description="Histórico de cálculos de valuation realizados"
            >
            </PageHeader>

            <SectionCard
                class="mt-6"
                title="Valuations Salvas"
                :description="`${valuations.meta?.total || valuations.data.length} ativo(s)`"
            >
                <div v-if="valuations.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="assetGroup in valuations.data"
                        :key="assetGroup.id"
                        class="flex flex-col overflow-hidden rounded-xl border border-border bg-card transition-all hover:border-primary/30 hover:shadow-sm"
                    >
                        <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                            <img
                                :src="assetGroup.logo_url || '/images/default-logo.svg'"
                                :alt="assetGroup.ticker"
                                class="h-9 w-9 rounded-full object-contain"
                                @error="($event.target as HTMLImageElement).src = '/images/default-logo.svg'"
                            />
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="truncate text-sm font-semibold text-foreground">
                                        {{ assetGroup.name }}
                                    </p>
                                    <span class="rounded bg-surface px-1.5 py-0.5 text-[10px] font-semibold tracking-wide text-muted-foreground uppercase">
                                        {{ assetTypeLabel(assetGroup.asset_type) }}
                                    </span>
                                </div>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    {{ assetGroup.ticker }} · {{ assetGroup.valuation_count }} cálculo(s) · {{ formatDate(assetGroup.latest_calculated_at) }}
                                </p>
                            </div>
                        </div>

                        <div class="grid flex-1 gap-4 p-5">
                            <button
                                v-for="valuation in assetGroup.valuations"
                                :key="valuation.id"
                                type="button"
                                class="group/block cursor-pointer rounded-xl border border-border bg-surface/50 p-4 text-left transition-all hover:border-primary/40 hover:bg-surface"
                                @click="openValuation(valuation)"
                            >
                                <div class="flex items-center justify-between gap-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                        <component :is="methodIcon(valuation.method)" class="h-3.5 w-3.5 text-primary" />
                                        {{ valuation.method_label }}
                                    </span>
                                    <span
                                        class="rounded-full px-2.5 py-1 text-xs font-bold"
                                        :class="marginTone(valuation.margin_of_safety).bg + ' ' + marginTone(valuation.margin_of_safety).text"
                                    >
                                        {{ formatPercent(valuation.margin_of_safety) }}
                                    </span>
                                </div>

                                <div class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                    <div>
                                        <p class="text-xs tracking-wide text-muted-foreground uppercase">Cotação atual</p>
                                        <p class="mt-0.5 font-semibold text-foreground">{{ formatCurrency(assetGroup.current_price) }}</p>
                                    </div>
                                    <div>
                                        <p class="text-xs tracking-wide text-muted-foreground uppercase">Cotação justa</p>
                                        <p class="mt-0.5 font-semibold text-foreground">{{ formatCurrency(valuation.fair_value) }}</p>
                                    </div>
                                </div>

                                <div
                                    class="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-muted"
                                    role="img"
                                    aria-label="Margem de segurança"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-500 ease-out"
                                        :class="marginTone(valuation.margin_of_safety).bar"
                                        :style="{ width: gaugeWidth(valuation.margin_of_safety) }"
                                    />
                                </div>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    v-else
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <ChartNoAxesCombined
                        class="mb-4 h-16 w-16 text-muted-foreground opacity-15"
                    />
                    <p class="font-medium text-muted-foreground">
                        Nenhuma valuation salva
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        Realize um cálculo de valuation para começar
                    </p>
                    <Button class="mt-6" @click="createValuation">
                        <Plus class="h-4 w-4" />
                        Nova Valuation
                    </Button>
                </div>

                <template v-if="valuations.data.length && valuations.meta">
                    <PaginationLinks
                        class="mt-6"
                        :meta="valuations.meta"
                    />
                </template>
            </SectionCard>
        </div>
    </AppLayout>
</template>

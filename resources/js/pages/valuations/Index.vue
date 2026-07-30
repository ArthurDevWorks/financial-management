<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ChartNoAxesCombined, Plus, TrendingUp, TrendingDown, Calculator } from 'lucide-vue-next';
import { computed } from 'vue';

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

interface Valuation {
    id: number;
    asset: {
        id: number;
        ticker: string;
        name: string;
        logo_url?: string | null;
        current_price?: number | null;
        asset_type: string;
    };
    method: string;
    method_label: string;
    calculated_at: string;
}

const props = defineProps<{
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

const createValuation = () => {
    router.visit('/valuations/create');
};

const openValuation = (valuation: Valuation) => {
    router.visit(`/valuations/${valuation.id}`);
};

const methodIcon = (method: string) => {
    if (method === 'dcf') return Calculator;
    if (method === 'preco_teto') return ChartNoAxesCombined;
    return TrendingUp;
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
                class="mt-6"
                title="Valuations Salvas"
                :description="`${valuations.meta?.total || valuations.data.length} registro(s)`"
            >
                <div v-if="valuations.data.length" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    <div
                        v-for="valuation in valuations.data"
                        :key="valuation.id"
                        class="group cursor-pointer rounded-xl border border-border bg-card transition-all hover:border-primary/30 hover:shadow-sm"
                        @click="openValuation(valuation)"
                        role="button"
                        tabindex="0"
                        @keydown.enter="openValuation(valuation)"
                    >
                        <div class="flex items-center gap-3 border-b border-border px-5 py-4">
                            <img
                                :src="valuation.asset.logo_url || '/images/default-logo.svg'"
                                :alt="valuation.asset.ticker"
                                class="h-9 w-9 rounded-full object-contain"
                                @error="($event.target as HTMLImageElement).src = '/images/default-logo.svg'"
                            />
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm font-semibold text-foreground">
                                    {{ valuation.asset.name }}
                                </p>
                                <p class="text-xs text-muted-foreground">{{ valuation.asset.ticker }}</p>
                            </div>
                        </div>

                        <div class="px-5 py-3.5">
                            <div class="flex items-center justify-between">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    <component :is="methodIcon(valuation.method)" class="h-3.5 w-3.5" />
                                    {{ valuation.method_label }}
                                </span>
                                <span class="text-xs text-muted-foreground">{{ formatDate(valuation.calculated_at) }}</span>
                            </div>
                            <div class="mt-2 text-sm text-muted-foreground">
                                Cotação: <strong class="text-foreground">{{ formatCurrency(valuation.asset.current_price) }}</strong>
                            </div>
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

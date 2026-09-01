<script setup lang="ts">
import AssetDataCard from '@/components/AssetDataCard.vue';
import CurrencyInput from '@/components/CurrencyInput.vue';
import MarginGauge from '@/components/MarginGauge.vue';
import NumberInput from '@/components/NumberInput.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDcf } from '@/composables/useDcf';
import { usePrecoTeto } from '@/composables/usePrecoTeto';
import AppLayout from '@/layouts/AppLayout.vue';
import { cn } from '@/lib/utils';
import { router } from '@inertiajs/vue3';
import {
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    LineElement,
    PointElement,
    Title,
    Tooltip,
} from 'chart.js';
import { ArrowLeft, Save } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
);

interface Asset {
    id: number;
    ticker: string;
    name: string | null;
    current_price: number | null;
    market_cap: number | null;
    enterprise_value: number | null;
    dividend_yield: number | null;
    price_to_earnings: number | null;
    price_to_book: number | null;
    roe: number | null;
    profit_margin: number | null;
    net_debt_to_ebitda: number | null;
    free_cash_flow: number | null;
    revenue: number | null;
    net_income: number | null;
    dividends_per_share: number | null;
    earnings_per_share: number | null;
    book_value_per_share: number | null;
    total_shares: number | null;
    payout: number | null;
    asset_type: string;
    logo_url: string | null;
}

interface ExistingValuation {
    id: number;
    assumptions: Record<string, any>;
}

const props = defineProps<{
    asset: Asset;
    existingValuations?: Record<string, ExistingValuation>;
    valuationId?: string;
}>();

function resolveInitialTab(): string {
    if (props.valuationId && props.existingValuations) {
        for (const [method, data] of Object.entries(props.existingValuations)) {
            if (data.id?.toString() === props.valuationId) {
                return method === 'preco_teto' ? 'preco-teto' : method;
            }
        }
    }
    return props.asset.asset_type === 'fii' ? 'gordon' : 'preco-teto';
}

const activeTab = ref(resolveInitialTab());

function toNumber(value: unknown): number | null {
    if (value === null || value === undefined) return null;
    const num = typeof value === 'string' ? parseFloat(value) : Number(value);
    return isNaN(num) ? null : num;
}

function formatCurrency(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(num);
}

function formatPercent(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return (num >= 0 ? '+' : '') + num.toFixed(2) + '%';
}

// ─── Gordon Model ────────────────────────────────────────
const gordonData = props.existingValuations?.gordon?.assumptions;
const dps = ref(gordonData?.dps ?? props.asset.dividends_per_share ?? 0);
const discountRate = ref(gordonData?.discount_rate ?? 13);
const riskPremium = ref(gordonData?.risk_premium ?? 4);
const growthPerpetuity = ref(gordonData?.growth_perpetuity ?? 3.0);
const currentPrice = ref(
    gordonData?.current_price ?? props.asset.current_price ?? 0,
);

const gordonEffectiveKe = computed(
    () => discountRate.value + riskPremium.value,
);

const gordonFairPrice = computed(() => {
    const ke = gordonEffectiveKe.value / 100;
    const g = growthPerpetuity.value / 100;
    if (ke <= g || !dps.value) return null;
    return (dps.value * (1 + g)) / (ke - g);
});

const gordonUpside = computed(() => {
    if (!gordonFairPrice.value || !currentPrice.value) return null;
    return (
        ((gordonFairPrice.value - currentPrice.value) / currentPrice.value) *
        100
    );
});

const gordonReturn = computed(() => {
    if (!dps.value || !currentPrice.value) return null;
    return (dps.value / currentPrice.value) * 100 + growthPerpetuity.value;
});

const gordonMargin = computed(() => {
    const fp = gordonFairPrice.value;
    const cp = currentPrice.value;
    if (!fp || !cp) return 0;
    return ((fp - cp) / cp) * 100;
});

const growthRates = ref<number[]>(
    gordonData?.growth_rates ?? [8.0, 7.0, 6.0, 5.0, 4.0],
);

// ─── Preço Teto ──────────────────────────────────
const ptData = props.existingValuations?.preco_teto?.assumptions;
const ptDesiredYield = ref(ptData?.desired_yield ?? 6);
const ptNetIncome = ref(
    ptData?.projected_net_income ?? props.asset.net_income ?? 0,
);
const ptTotalShares = ref(
    ptData?.total_shares ?? props.asset.total_shares ?? 0,
);
const ptRoe = ref(ptData?.roe ?? props.asset.roe ?? 0);
const ptPayout = ref(ptData?.payout ?? props.asset.payout ?? 50);
const ptGrowthRate = ref(ptData?.projected_growth_rate ?? 5);
const ptCurrentPrice = ref(
    ptData?.current_price_per_share ?? props.asset.current_price ?? 0,
);

const ptDefaultGrowthRate = computed(() => {
    const roe = Number(ptRoe.value) || 0;
    const payout = Number(ptPayout.value) || 0;
    return Math.round((1 - payout / 100) * roe * 100) / 100;
});

const hasSavedPtGrowthRate = !!ptData?.projected_growth_rate;

watch(
    [ptRoe, ptPayout],
    () => {
        ptGrowthRate.value = ptDefaultGrowthRate.value;
    },
    { immediate: !hasSavedPtGrowthRate },
);

const {
    precoTeto: ptPrecoTeto,
    lpaProjetado: ptLpa,
    dpaProjetado: ptDpa,
    yieldProjetado: ptYield,
    margemSeguranca: ptMargem,
} = usePrecoTeto({
    desiredYield: ptDesiredYield,
    projectedPayout: ptPayout,
    projectedNetIncome: ptNetIncome,
    totalShares: ptTotalShares,
    projectedGrowthRate: ptGrowthRate,
    currentPrice: ptCurrentPrice,
});

// ─── DCF ─────────────────────────────────────────
const dcfData = props.existingValuations?.dcf?.assumptions;
const dcfFcf = ref(dcfData?.current_fcf ?? props.asset.free_cash_flow ?? 0);
const dcfRoe = ref(dcfData?.roe ?? props.asset.roe ?? 0);
const dcfPayout = ref(dcfData?.payout ?? props.asset.payout ?? 0);
const dcfDiscountRate = ref(dcfData?.discount_rate ?? 12.5);
const dcfTerminalGrowth = ref(dcfData?.terminal_growth_rate ?? 3.0);
const dcfProjectionYears = ref(dcfData?.projection_years ?? 5);
const dcfTotalShares = ref(
    dcfData?.total_shares ?? props.asset.total_shares ?? 0,
);
const dcfCurrentPrice = ref(
    dcfData?.current_price_per_share ?? props.asset.current_price ?? 0,
);

const dcfDefaultGrowthRate = computed(() => {
    const roe = Number(dcfRoe.value) || 0;
    const payout = Number(dcfPayout.value) || 0;
    return Math.round((1 - payout / 100) * roe * 100) / 100;
});

const hasSavedDcfGrowthRates = !!dcfData?.growth_rates?.length;

const buildDefaultGrowthRates = (n: number) => {
    return Array.from({ length: n }, () => dcfDefaultGrowthRate.value);
};

const dcfGrowthRates = ref<number[]>(
    dcfData?.growth_rates?.slice(0, dcfProjectionYears.value) ??
        buildDefaultGrowthRates(dcfProjectionYears.value),
);

watch(dcfProjectionYears, (newLen, oldLen) => {
    if (newLen > oldLen) {
        while (dcfGrowthRates.value.length < newLen) {
            dcfGrowthRates.value.push(dcfDefaultGrowthRate.value);
        }
    } else {
        dcfGrowthRates.value = dcfGrowthRates.value.slice(0, newLen);
    }
});

watch(
    [dcfRoe, dcfPayout],
    () => {
        dcfGrowthRates.value = dcfGrowthRates.value.map(
            () => dcfDefaultGrowthRate.value,
        );
    },
    { immediate: !hasSavedDcfGrowthRates },
);

const {
    fairPrice: dcfFairPrice,
    upside: dcfUpside,
    marginOfSafety: dcfMarginOfSafety,
    enterpriseValue: dcfEnterpriseValue,
    terminalValue: dcfTerminalValue,
    pvTerminal: dcfPvTerminal,
    projectedFcfs: dcfProjectedFcfs,
} = useDcf({
    freeCashFlow: dcfFcf,
    growthRates: dcfGrowthRates,
    discountRate: dcfDiscountRate,
    terminalGrowthRate: dcfTerminalGrowth,
    projectionYears: dcfProjectionYears,
    totalShares: dcfTotalShares,
    currentPrice: dcfCurrentPrice,
    netDebt: ref(0),
});

// ─── Save functions ─────────────────────────────────
function savePrecoTeto() {
    const data = {
        asset_id: props.asset.id,
        desired_yield: ptDesiredYield.value,
        projected_net_income: ptNetIncome.value,
        total_shares: ptTotalShares.value,
        projected_growth_rate: ptGrowthRate.value,
        current_price_per_share: ptCurrentPrice.value,
        roe: ptRoe.value,
        payout: ptPayout.value,
    };
    const existing = props.existingValuations?.preco_teto;
    if (existing) {
        router.put(`/preco-teto/${existing.id}`, data);
    } else {
        router.post('/preco-teto', data);
    }
}

function saveDcf() {
    const data = {
        asset_id: props.asset.id,
        current_fcf: dcfFcf.value,
        roe: dcfRoe.value,
        payout: dcfPayout.value,
        discount_rate: dcfDiscountRate.value,
        terminal_growth_rate: dcfTerminalGrowth.value,
        projection_years: dcfProjectionYears.value,
        total_shares: dcfTotalShares.value,
        current_price_per_share: dcfCurrentPrice.value,
        growth_rates: dcfGrowthRates.value,
    };
    const existing = props.existingValuations?.dcf;
    if (existing) {
        router.put(`/dcf/${existing.id}`, data);
    } else {
        router.post('/dcf', data);
    }
}

function saveGordon() {
    const data = {
        asset_id: props.asset.id,
        dps: dps.value,
        discount_rate: discountRate.value,
        risk_premium: riskPremium.value,
        growth_perpetuity: growthPerpetuity.value,
        current_price: currentPrice.value,
        projection_years: 5,
        growth_rates: growthRates.value,
    };
    const existing = props.existingValuations?.gordon;
    if (existing) {
        router.put(`/gordon/${existing.id}`, data);
    } else {
        router.post('/gordon', data);
    }
}
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a
                        href="/screening"
                        class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Voltar
                    </a>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight">
                        <span class="text-primary">V</span>aluation
                        <span class="text-lg font-normal text-muted-foreground"
                            >— {{ asset.ticker }} {{ asset.name }}</span
                        >
                    </h1>
                </div>
            </div>

            <!-- ─── DADOS DO ATIVO (read-only) ─────────────── -->
            <AssetDataCard :asset="asset" />

            <!-- Tab Bar -->
            <div class="mb-6">
                <div
                    class="inline-flex h-10 items-center justify-center rounded-xl bg-surface p-1 text-muted-foreground"
                >
                    <button
                        v-for="tab in [
                            { value: 'preco-teto', label: 'Preço Teto' },
                            { value: 'dcf', label: 'DCF' },
                            { value: 'gordon', label: 'Gordon' },
                        ]"
                        :key="tab.value"
                        role="tab"
                        :class="
                            cn(
                                'inline-flex items-center justify-center rounded-lg px-3 py-1.5 text-sm font-medium whitespace-nowrap ring-offset-background transition-all',
                                'focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none',
                                activeTab === tab.value
                                    ? 'bg-card text-foreground shadow-sm'
                                    : 'hover:text-foreground',
                            )
                        "
                        @click="activeTab = tab.value"
                    >
                        <span
                            :class="{ 'text-primary': tab.value === activeTab }"
                            >{{ tab.label }}</span
                        >
                    </button>
                </div>
            </div>

            <!-- ─── PRECO TETO TAB ─────────────────────────────── -->
            <div
                v-if="activeTab === 'preco-teto'"
                class="grid grid-cols-1 gap-6 lg:grid-cols-3"
            >
                <div
                    class="rounded-xl border border-border bg-card p-5 lg:col-span-1"
                >
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do
                        Investidor
                    </h3>
                    <p class="mb-4 text-xs text-muted-foreground">
                        Defina suas expectativas para projetar o preço teto
                    </p>
                    <div class="space-y-4">
                        <div>
                            <Label>Dividend Yield Desejado (%)</Label>
                            <Input
                                v-model.number="ptDesiredYield"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Lucro Líquido (R$)</Label>
                            <CurrencyInput
                                v-model="ptNetIncome"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Total de Ações</Label>
                            <NumberInput
                                v-model.number="ptTotalShares"
                                :precision="0"
                            />
                        </div>
                        <div>
                            <Label>ROE (%)</Label>
                            <Input
                                v-model.number="ptRoe"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Payout (%)</Label>
                            <Input
                                v-model.number="ptPayout"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label class="flex items-center justify-between">
                                <span>Crescimento</span>
                            </Label>
                            <Input
                                v-model.number="ptGrowthRate"
                                type="number"
                                step="0.1"
                                class="mt-1"
                            />
                            <p
                                class="mt-1 text-xs text-muted-foreground"
                            >
                                Taxa de Crescimento Esperada:
                                <span
                                    class="font-medium text-foreground"
                                    >{{ ptDefaultGrowthRate }}%</span
                                >
                                (ROE × (1 − Payout%))
                            </p>
                        </div>
                        <div>
                            <Label>Preço Atual (R$)</Label>
                            <CurrencyInput
                                v-model="ptCurrentPrice"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                    </div>
                    <div class="mt-6">
                        <Button class="w-full gap-2" @click="savePrecoTeto">
                            <Save class="h-4 w-4" />
                            Salvar Valuation
                        </Button>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do
                            Preço Teto
                        </h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div
                                class="overflow-hidden rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Preço Teto
                                </p>
                                <p class="mt-1 text-2xl font-bold text-primary">
                                    {{ formatCurrency(ptPrecoTeto) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Yield Projetado
                                </p>
                                <p class="mt-1 text-xl font-bold text-revenue">
                                    {{ formatPercent(ptYield) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Margem
                                </p>
                                <p
                                    class="mt-1 text-xl font-bold"
                                    :class="
                                        (ptMargem ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatPercent(ptMargem) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    LPA Projetado
                                </p>
                                <p class="mt-1 text-lg font-bold">
                                    {{ formatCurrency(ptLpa) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    DPA Projetado
                                </p>
                                <p class="mt-1 text-lg font-bold">
                                    {{ formatCurrency(ptDpa) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <MarginGauge
                            :value="ptMargem"
                            label="Margem de Segurança"
                        />
                    </div>
                </div>
            </div>

            <!-- ─── DCF TAB ────────────────────────────────────────── -->
            <div
                v-if="activeTab === 'dcf'"
                class="grid grid-cols-1 gap-6 lg:grid-cols-3"
            >
                <div
                    class="rounded-xl border border-border bg-card p-5 lg:col-span-1"
                >
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do
                        Investidor
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <Label>Lucro Líquido (R$)</Label>
                            <CurrencyInput
                                v-model="dcfFcf"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>ROE (%)</Label>
                            <Input
                                v-model.number="dcfRoe"
                                type="number"
                                step="0.1"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Payout (%)</Label>
                            <Input
                                v-model.number="dcfPayout"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Taxa de Desconto (%)</Label>
                            <Input
                                v-model.number="dcfDiscountRate"
                                type="number"
                                step="0.1"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Crescimento Perpetuidade (%)</Label>
                            <Input
                                v-model.number="dcfTerminalGrowth"
                                type="number"
                                step="0.1"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Anos de Projeção</Label>
                            <Input
                                v-model.number="dcfProjectionYears"
                                type="number"
                                step="1"
                                min="1"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Total de Ações</Label>
                            <NumberInput
                                v-model.number="dcfTotalShares"
                                :precision="0"
                            />
                        </div>
                        <div>
                            <Label>Preço Atual (R$)</Label>
                            <CurrencyInput
                                v-model="dcfCurrentPrice"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                    </div>
                    <div class="mt-6">
                        <Button class="w-full gap-2" @click="saveDcf">
                            <Save class="h-4 w-4" />
                            Salvar Valuation
                        </Button>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do DCF
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="overflow-hidden rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Preço Justo
                                </p>
                                <p class="mt-1 text-2xl font-bold text-primary">
                                    {{ formatCurrency(dcfFairPrice) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Upside
                                </p>
                                <p
                                    class="mt-1 text-xl font-bold"
                                    :class="
                                        (dcfUpside ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatPercent(dcfUpside) }}
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Margem
                                </p>
                                <p
                                    class="mt-1 text-xl font-bold"
                                    :class="
                                        (dcfMarginOfSafety ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatPercent(dcfMarginOfSafety) }}
                                </p>
                            </div>
                            <div
                                class="overflow-hidden rounded-lg border border-border bg-surface p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Market Cap
                                </p>
                                <p class="mt-1 text-xl font-bold">
                                    {{ formatCurrency(dcfEnterpriseValue) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <MarginGauge
                            :value="dcfMarginOfSafety"
                            label="Margem de Segurança"
                        />
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-1 text-sm font-semibold">
                            Projeção
                            <span class="text-primary">Lucro Líquido</span>
                        </h3>
                        <div
                            class="mb-4 mt-3 flex items-center gap-3 rounded-lg border border-primary/20 bg-primary/5 px-4 py-3"
                        >
                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-primary/10"
                            >
                                <span
                                    class="text-sm font-bold text-primary"
                                >%</span>
                            </div>
                            <div>
                                <p
                                    class="text-xs font-semibold uppercase tracking-wider text-muted-foreground"
                                >
                                    Taxa de Crescimento Esperada
                                </p>
                                <p class="text-lg font-bold text-primary">
                                    {{ dcfDefaultGrowthRate }}%
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                    >{{ `ROE (${dcfRoe}%) × (1 − Payout (${dcfPayout}%))` }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr
                                        class="border-b-2 border-border text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        <th class="pb-3 pl-3 text-left">Ano</th>
                                        <th class="ml-3 flex pb-3 text-start">
                                            Lucro Líquido
                                        </th>
                                        <th class="pb-3 text-right">
                                            <div>Crescimento</div>
                                            <div
                                                class="normal-case tracking-normal text-muted-foreground/70"
                                            >
                                                Taxa Esperada
                                            </div>
                                        </th>
                                        <th class="pr-3 pb-3 text-right">
                                            VPL
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="border-b border-border/50 bg-primary/5"
                                    >
                                        <td
                                            class="py-2.5 pl-3 font-semibold text-foreground"
                                        >
                                            {{ new Date().getFullYear() }}
                                        </td>
                                        <td class="py-2.5">
                                            <div class="justify-begin flex">
                                                <CurrencyInput
                                                    v-model="dcfFcf"
                                                    placeholder="0,00"
                                                    class="h-8 w-45 text-xs"
                                                />
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 text-center text-xs font-medium text-muted-foreground"
                                        >
                                            Base
                                        </td>
                                        <td
                                            class="py-2.5 pr-3 text-right text-muted-foreground"
                                        >
                                            —
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(proj, i) in dcfProjectedFcfs"
                                        :key="proj.year"
                                        class="border-b border-border/30 transition-colors hover:bg-surface/50"
                                    >
                                        <td
                                            class="py-2.5 pl-3 text-muted-foreground"
                                        >
                                            {{ proj.year }}
                                        </td>
                                        <td
                                            class="ml-3 flex py-2.5 font-medium text-foreground"
                                        >
                                            {{ formatCurrency(proj.fcf) }}
                                        </td>
                                        <td class="py-2.5">
                                            <div
                                                class="flex items-center justify-end gap-1"
                                            >
                                                <Input
                                                    v-model.number="
                                                        dcfGrowthRates[i]
                                                    "
                                                    type="number"
                                                    step="0.1"
                                                    class="h-8 w-20 text-center text-xs font-medium"
                                                />
                                                <span
                                                    class="text-xs text-muted-foreground"
                                                    >%</span
                                                >
                                            </div>
                                        </td>
                                        <td
                                            class="py-2.5 pr-3 text-right font-medium text-primary"
                                        >
                                            {{ formatCurrency(proj.pv) }}
                                        </td>
                                    </tr>
                                    <tr
                                        class="border-t-2 border-primary/30 bg-primary/5"
                                    >
                                        <td
                                            class="py-3 pl-3 font-semibold text-primary"
                                        >
                                            Pérpetuo
                                        </td>
                                        <td
                                            class="py-3 text-right font-semibold text-primary"
                                        >
                                            {{
                                                formatCurrency(dcfTerminalValue)
                                            }}
                                        </td>
                                        <td
                                            class="py-3 text-center text-xs text-muted-foreground"
                                        ></td>
                                        <td
                                            class="py-3 pr-3 text-right font-semibold text-primary"
                                        >
                                            {{ formatCurrency(dcfPvTerminal) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── GORDON TAB ─────────────────────────────────────── -->
            <div
                v-if="activeTab === 'gordon'"
                class="grid grid-cols-1 gap-6 lg:grid-cols-3"
            >
                <div
                    class="rounded-xl border border-border bg-card p-5 lg:col-span-1"
                >
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do
                        Investidor
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <Label>Dividendo Anual Esperado (R$)</Label>
                            <CurrencyInput
                                v-model="dps"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Taxa de Desconto — Tesouro IPCA (%)</Label>
                            <Input
                                v-model.number="discountRate"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Prêmio de Risco (%)</Label>
                            <Input
                                v-model.number="riskPremium"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                            <p class="mt-1 text-xs text-muted-foreground">
                                Exigência adicional sobre o Tesouro IPCA
                            </p>
                        </div>
                        <div>
                            <Label>Crescimento (%)</Label>
                            <Input
                                v-model.number="growthPerpetuity"
                                type="number"
                                step="0.1"
                                min="0"
                                class="mt-1"
                            />
                        </div>
                        <div>
                            <Label>Preço Atual (R$)</Label>
                            <CurrencyInput
                                v-model="currentPrice"
                                placeholder="0,00"
                                class="mt-1"
                            />
                        </div>
                    </div>
                    <div class="mt-6">
                        <Button class="w-full gap-2" @click="saveGordon">
                            <Save class="h-4 w-4" />
                            Salvar Valuation
                        </Button>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do
                            Gordon
                        </h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="overflow-hidden rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Preço Teto
                                </p>
                                <p class="mt-1 text-2xl font-bold text-primary">
                                    {{ formatCurrency(gordonFairPrice) }}
                                </p>
                                <div v-if="gordonUpside !== null" class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-0.5 text-xs font-semibold"
                                        :class="
                                            gordonUpside >= 0
                                                ? 'text-revenue'
                                                : 'text-destructive'
                                        "
                                    >
                                        {{ gordonUpside >= 0 ? '+' : ''
                                        }}{{ gordonUpside.toFixed(1) }}% vs.
                                        preço atual
                                    </span>
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Retorno Esperado
                                </p>
                                <p class="mt-1 text-xl font-bold text-revenue">
                                    {{ formatPercent(gordonReturn) }}
                                </p>
                                <p class="text-xs text-muted-foreground"></p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-surface p-3 text-center"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Margem
                                </p>
                                <p
                                    class="mt-1 text-xl font-bold"
                                    :class="
                                        (gordonMargin ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatPercent(gordonMargin) }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Growth Table -->
                    <!-- <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            Crescimento <span class="text-primary">Anual</span>
                        </h3>
                        <div class="grid grid-cols-5 gap-2 sm:grid-cols-5">
                            <div v-for="(_, i) in 5" :key="i">
                                <Label class="text-xs text-muted-foreground">{{ new Date().getFullYear() + i + 1 }}</Label>
                                <div class="mt-1 flex items-center gap-1">
                                    <Input v-model.number="growthRates[i]" type="number" step="0.1" class="h-8 text-xs" />
                                    <span class="text-xs text-muted-foreground">%</span>
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 border-t border-border pt-3 text-xs text-muted-foreground">
                            <p>
                                VP dividendos projetados:
                                <span class="font-semibold text-foreground">{{ formatCurrency(projectedDividends.years.reduce((s, y) => s + y.pv, 0)) }}</span>
                            </p>
                            <p>
                                VP perpetuidade:
                                <span class="font-semibold text-primary">{{ formatCurrency(projectedDividends.terminalPv) }}</span>
                            </p>
                        </div>
                    </div> -->
                </div>
            </div>
        </div>
    </AppLayout>
</template>

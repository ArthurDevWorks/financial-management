<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import MarginGauge from '@/components/MarginGauge.vue';
import AssetDataCard from '@/components/AssetDataCard.vue';
import { cn } from '@/lib/utils';
import { Line as ChartLine } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement,
    Title, Tooltip, Legend,
} from 'chart.js';
import { ref, computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';
import { usePrecoTeto } from '@/composables/usePrecoTeto';
import { useDcf } from '@/composables/useDcf';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

interface Asset {
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

const props = defineProps<{
    asset: Asset;
}>();

const activeTab = ref(props.asset.asset_type === 'fii' ? 'gordon' : 'preco-teto');

function toNumber(value: unknown): number | null {
    if (value === null || value === undefined) return null;
    const num = typeof value === 'string' ? parseFloat(value) : Number(value);
    return isNaN(num) ? null : num;
}

function formatCurrency(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(num);
}

function formatPercent(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return (num >= 0 ? '+' : '') + num.toFixed(2) + '%';
}

// ─── Gordon Model ────────────────────────────────────────
const dps = ref(props.asset.dividends_per_share ?? 0);
const discountRate = ref(12.5);
const growthPerpetuity = ref(3.0);
const currentPrice = ref(props.asset.current_price ?? 0);

const gordonFairPrice = computed(() => {
    const ke = discountRate.value / 100;
    const g = growthPerpetuity.value / 100;
    if (ke <= g || !dps.value) return null;
    return dps.value / (ke - g);
});

const gordonUpside = computed(() => {
    if (!gordonFairPrice.value || !currentPrice.value) return null;
    return ((gordonFairPrice.value - currentPrice.value) / currentPrice.value) * 100;
});

const gordonReturn = computed(() => {
    if (!dps.value || !currentPrice.value) return null;
    return (dps.value / currentPrice.value) * 100 + growthPerpetuity.value;
});

const gordonMargin = computed(() => {
    const fp = gordonFairPrice.value;
    const cp = currentPrice.value;
    if (!fp || !cp) return 0;
    return (1 - cp / fp) * 100;
});

const growthRates = ref([8.0, 7.0, 6.0, 5.0, 4.0]);

const projectedDividends = computed(() => {
    let d = dps.value;
    const result: { year: number; growth: number; dps: number; pv: number }[] = [];
    const ke = discountRate.value / 100;

    growthRates.value.forEach((g, i) => {
        const gr = g / 100;
        const nextDps = d * (1 + gr);
        const pv = nextDps / Math.pow(1 + ke, i + 1);
        result.push({ year: i + 1, growth: g, dps: nextDps, pv });
        d = nextDps;
    });

    const terminalGrowth = growthPerpetuity.value / 100;
    const terminalDps = d * (1 + terminalGrowth);
    const terminalPv = terminalDps / (ke - terminalGrowth) / Math.pow(1 + ke, 5);

    return { years: result, terminalDps, terminalPv };
});

const dpsSens = computed(() => dps.value || 0);

const sensitivityData = computed(() => {
    const keValues = [10, 10.5, 11, 11.5, 12, 12.5, 13, 13.5, 14, 14.5, 15];
    const gValues = [2.0, 2.5, 3.0, 3.5, 4.0];
    const colors = [
        'hsla(168, 75%, 42%, 0.85)',
        'hsla(168, 75%, 42%, 0.6)',
        'hsla(42, 80%, 52%, 0.85)',
        'hsla(42, 80%, 52%, 0.6)',
        'hsla(220, 80%, 55%, 0.85)',
    ];

    return {
        labels: keValues.map(v => v.toFixed(1) + '%'),
        datasets: [
            ...gValues.map((g, i) => ({
                label: `g = ${g.toFixed(1)}%`,
                data: keValues.map(ke => {
                    const k = ke / 100;
                    const gr = g / 100;
                    if (k <= gr || !dpsSens.value) return null;
                    return dpsSens.value / (k - gr);
                }),
                borderColor: colors[i],
                backgroundColor: colors[i].replace('0.85', '0.05'),
                fill: false,
                tension: 0.3,
                pointRadius: 2,
                borderWidth: 2,
            })),
            {
                label: 'Preço atual',
                data: keValues.map(() => currentPrice.value),
                borderColor: 'hsla(0, 75%, 55%, 0.5)',
                borderDash: [6, 4] as number[],
                fill: false,
                pointRadius: 0,
                borderWidth: 1.5,
            },
        ],
    };
});

const sensitivityOptions = {
    responsive: true,
    maintainAspectRatio: false,
    interaction: { mode: 'index' as const, intersect: false },
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: { boxWidth: 12, usePointStyle: true, padding: 12, font: { size: 10 } },
        },
        tooltip: {
            backgroundColor: 'hsl(228, 22%, 9%)',
            borderColor: 'hsl(228, 15%, 14%)',
            borderWidth: 1,
            padding: 8,
            callbacks: {
                label: (ctx: { parsed: { y: number | null }; dataset: { label: string } }) => {
                    if (ctx.parsed.y === null) return ctx.dataset.label + ': N/A';
                    return ctx.dataset.label + ': R$ ' + ctx.parsed.y.toFixed(2);
                },
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            title: { display: true, text: 'Taxa de Desconto (Ke)', font: { size: 11 } },
            ticks: { font: { size: 10 } },
        },
        y: {
            grid: { color: 'hsla(228, 15%, 14%, 0.3)' },
            title: { display: true, text: 'Preço Justo (R$)', font: { size: 11 } },
            ticks: { callback: (v: number) => 'R$' + v.toFixed(0), font: { size: 10 } },
        },
    },
};

// ─── Preço Teto ──────────────────────────────────
const ptDesiredYield = ref(8);
const ptProjectedPayout = ref(props.asset.payout ?? 50);
const ptNetIncome = ref(props.asset.net_income ?? 0);
const ptTotalShares = ref(props.asset.total_shares ?? 0);
const ptGrowthRate = ref(5);
const ptCurrentPrice = ref(props.asset.current_price ?? 0);

const {
    precoTeto: ptPrecoTeto,
    lpaProjetado: ptLpa,
    dpaProjetado: ptDpa,
    yieldProjetado: ptYield,
    margemSeguranca: ptMargem,
    podeCalcular: ptPodeCalcular,
} = usePrecoTeto({
    desiredYield: ptDesiredYield,
    projectedPayout: ptProjectedPayout,
    projectedNetIncome: ptNetIncome,
    totalShares: ptTotalShares,
    projectedGrowthRate: ptGrowthRate,
    currentPrice: ptCurrentPrice,
});

// ─── DCF ─────────────────────────────────────────
const dcfFcf = ref(props.asset.free_cash_flow ?? 0);
const dcfRoe = ref(props.asset.roe ?? 0);
const dcfPayout = ref(props.asset.payout ?? 0);
const dcfDiscountRate = ref(12.5);
const dcfTerminalGrowth = ref(3.0);
const dcfProjectionYears = ref(5);
const dcfTotalShares = ref(props.asset.total_shares ?? 0);
const dcfCurrentPrice = ref(props.asset.current_price ?? 0);

const {
    fairPrice: dcfFairPrice,
    upside: dcfUpside,
    marginOfSafety: dcfMarginOfSafety,
    enterpriseValue: dcfEnterpriseValue,
    terminalValue: dcfTerminalValue,
    pvTerminal: dcfPvTerminal,
    growthRate: dcfGrowthRate,
    projectedFcfs: dcfProjectedFcfs,
    podeCalcular: dcfPodeCalcular,
} = useDcf({
    freeCashFlow: dcfFcf,
    roe: dcfRoe,
    payout: dcfPayout,
    discountRate: dcfDiscountRate,
    terminalGrowthRate: dcfTerminalGrowth,
    projectionYears: dcfProjectionYears,
    totalShares: dcfTotalShares,
    currentPrice: dcfCurrentPrice,
    netDebt: ref(0),
});
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <a href="/screening" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                        <ArrowLeft class="h-4 w-4" />
                        Voltar
                    </a>
                    <h1 class="mt-2 text-2xl font-bold tracking-tight">
                        <span class="text-primary">V</span>aluation
                        <span class="text-lg font-normal text-muted-foreground">— {{ asset.ticker }} {{ asset.name }}</span>
                    </h1>
                </div>
            </div>

            <!-- ─── DADOS DO ATIVO (read-only) ─────────────── -->
            <AssetDataCard :asset="asset" />

            <!-- Tab Bar -->
            <div class="mb-6">
                <div class="inline-flex h-10 items-center justify-center rounded-xl bg-surface p-1 text-muted-foreground">
                    <button
                        v-for="tab in [
                            { value: 'preco-teto', label: 'Preço Teto' },
                            { value: 'dcf', label: 'DCF' },
                            { value: 'gordon', label: 'Gordon' },
                        ]"
                        :key="tab.value"
                        role="tab"
                        :class="cn(
                            'inline-flex items-center justify-center whitespace-nowrap rounded-lg px-3 py-1.5 text-sm font-medium ring-offset-background transition-all',
                            'focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2',
                            activeTab === tab.value
                                ? 'bg-card text-foreground shadow-sm'
                                : 'hover:text-foreground'
                        )"
                        @click="activeTab = tab.value"
                    >
                        <span :class="{ 'text-primary': tab.value === activeTab }">{{ tab.label }}</span>
                    </button>
                </div>
            </div>

            <!-- ─── PRECO TETO TAB ─────────────────────────────── -->
            <div v-if="activeTab === 'preco-teto'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 lg:col-span-1">
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do Investidor
                    </h3>
                    <p class="mb-4 text-xs text-muted-foreground">
                        Defina suas expectativas para projetar o preço teto
                    </p>
                    <div class="space-y-4">
                        <div>
                            <Label>Dividend Yield Desejado (%)</Label>
                            <Input v-model.number="ptDesiredYield" type="number" step="0.1" min="0" class="mt-1" />
                            <p class="mt-0.5 text-xs text-muted-foreground">Retorno mínimo em dividendos que você espera</p>
                        </div>
                        <div>
                            <Label>Payout Projetado (%)</Label>
                            <Input v-model.number="ptProjectedPayout" type="number" step="0.1" min="0" class="mt-1" />
                            <p class="mt-0.5 text-xs text-muted-foreground">% do lucro distribuído como dividendos</p>
                        </div>
                        <div>
                            <Label>Lucro Líquido Projetado (R$)</Label>
                            <Input v-model.number="ptNetIncome" type="number" step="0.01" class="mt-1" />
                        </div>
                        <div>
                            <Label>Qtd. de Ações</Label>
                            <Input v-model.number="ptTotalShares" type="number" step="1" min="0" class="mt-1" />
                        </div>
                        <div>
                            <Label class="flex items-center justify-between">
                                <span>Crescimento (%)</span>
                                <span class="text-xs text-muted-foreground">Opcional — 0 = sem crescimento</span>
                            </Label>
                            <Input v-model.number="ptGrowthRate" type="number" step="0.1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Preço Atual (R$)</Label>
                            <Input v-model.number="ptCurrentPrice" type="number" step="0.01" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do Preço Teto
                        </h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Preço Teto</p>
                                <p class="mt-1 text-3xl font-bold text-primary">{{ formatCurrency(ptPrecoTeto) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Yield Projetado</p>
                                <p class="mt-1 text-xl font-bold text-revenue">{{ formatPercent(ptYield) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Margem</p>
                                <p class="mt-1 text-xl font-bold" :class="(ptMargem ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(ptMargem) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">LPA Projetado</p>
                                <p class="mt-1 text-lg font-bold">{{ formatCurrency(ptLpa) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">DPA Projetado</p>
                                <p class="mt-1 text-lg font-bold">{{ formatCurrency(ptDpa) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <MarginGauge :value="ptMargem" label="Margem de Segurança" />
                    </div>
                </div>
            </div>

            <!-- ─── DCF TAB ────────────────────────────────────────── -->
            <div v-if="activeTab === 'dcf'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 lg:col-span-1">
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do Investidor
                    </h3>
                    <p class="mb-4 text-xs text-muted-foreground">
                        Configure os parâmetros para o fluxo de caixa descontado
                    </p>
                    <div class="space-y-4">
                        <div>
                            <Label>FCF Atual (R$)</Label>
                            <Input v-model.number="dcfFcf" type="number" step="0.01" class="mt-1" />
                            <p class="mt-0.5 text-xs text-muted-foreground">Free Cash Flow — base para projeção dos fluxos futuros</p>
                        </div>
                        <div>
                            <Label>ROE (%)</Label>
                            <Input v-model.number="dcfRoe" type="number" step="0.1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Payout (%)</Label>
                            <Input v-model.number="dcfPayout" type="number" step="0.1" min="0" class="mt-1" />
                        </div>
                        <div>
                            <Label>Taxa de Desconto Ke (%)</Label>
                            <Input v-model.number="dcfDiscountRate" type="number" step="0.1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Cresc. Perpetuidade g (%)</Label>
                            <Input v-model.number="dcfTerminalGrowth" type="number" step="0.1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Anos de Projeção</Label>
                            <Input v-model.number="dcfProjectionYears" type="number" step="1" min="1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Total de Ações</Label>
                            <Input v-model.number="dcfTotalShares" type="number" step="1" min="0" class="mt-1" />
                        </div>
                        <div>
                            <Label>Preço Atual (R$)</Label>
                            <Input v-model.number="dcfCurrentPrice" type="number" step="0.01" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do DCF
                        </h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Preço Justo</p>
                                <p class="mt-1 text-3xl font-bold text-primary">{{ formatCurrency(dcfFairPrice) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Upside</p>
                                <p class="mt-1 text-xl font-bold" :class="(dcfUpside ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(dcfUpside) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Margem</p>
                                <p class="mt-1 text-xl font-bold" :class="(dcfMarginOfSafety ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(dcfMarginOfSafety) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Enterprise Value</p>
                                <p class="mt-1 text-lg font-bold">{{ formatCurrency(dcfEnterpriseValue) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Valor Terminal</p>
                                <p class="mt-1 text-lg font-bold text-primary">{{ formatCurrency(dcfTerminalValue) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <MarginGauge :value="dcfMarginOfSafety" label="Margem de Segurança" />
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            Projeção <span class="text-primary">FCF</span>
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border text-xs font-semibold text-muted-foreground">
                                        <th class="pb-2 text-left">Ano</th>
                                        <th class="pb-2 text-right">FCF Projetado</th>
                                        <th class="pb-2 text-right">Valor Presente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="proj in dcfProjectedFcfs" :key="proj.year" class="border-b border-border/50">
                                        <td class="py-2 text-muted-foreground">Ano {{ proj.year }}</td>
                                        <td class="py-2 text-right font-medium">{{ formatCurrency(proj.fcf) }}</td>
                                        <td class="py-2 text-right font-medium text-primary">{{ formatCurrency(proj.pv) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3 space-y-1 text-xs text-muted-foreground">
                            <p>VP Perpetuidade: <span class="font-semibold text-primary">{{ formatCurrency(dcfPvTerminal) }}</span></p>
                            <p>Cresc. projetado (g): <span class="font-semibold text-foreground">{{ formatPercent(dcfGrowthRate * 100) }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ─── GORDON TAB ─────────────────────────────────────── -->
            <div v-if="activeTab === 'gordon'" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="rounded-xl border border-border bg-card p-5 lg:col-span-1">
                    <h3 class="mb-4 text-sm font-semibold">
                        <span class="text-primary">Premissas</span> do Investidor
                    </h3>
                    <p class="mb-4 text-xs text-muted-foreground">
                        Informe o dividendo pago e suas expectativas de crescimento
                    </p>
                    <div class="space-y-4">
                        <div class="rounded-lg border border-primary/20 bg-primary/5 p-3">
                            <p class="text-xs text-muted-foreground">DY pago (12m)</p>
                            <p class="text-lg font-bold text-primary">{{ formatPercent(asset.dividend_yield) }}</p>
                        </div>
                        <div>
                            <Label>DPS atual (R$)</Label>
                            <Input v-model.number="dps" type="number" step="0.01" class="mt-1" />
                            <p class="mt-0.5 text-xs text-muted-foreground">Dividendos por ação nos últimos 12 meses</p>
                        </div>
                        <div>
                            <Label>Taxa de desconto (Ke %)</Label>
                            <Input v-model.number="discountRate" type="number" step="0.1" class="mt-1" />
                            <p class="mt-0.5 text-xs text-muted-foreground">Sugestão: taxa do Tesouro IPCA + prêmio de risco</p>
                        </div>
                        <div>
                            <Label class="flex items-center justify-between">
                                <span>Cresc. perpetuidade (g %)</span>
                                <span class="text-xs text-muted-foreground">0 = sem crescimento</span>
                            </Label>
                            <Input v-model.number="growthPerpetuity" type="number" step="0.1" class="mt-1" />
                        </div>
                        <div>
                            <Label>Preço atual (R$)</Label>
                            <Input v-model.number="currentPrice" type="number" step="0.01" class="mt-1" />
                        </div>
                    </div>
                </div>

                <div class="space-y-6 lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            <span class="text-primary">Resultados</span> do Gordon
                        </h3>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
                            <div class="rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Preço Justo</p>
                                <p class="mt-1 text-3xl font-bold text-primary">{{ formatCurrency(gordonFairPrice) }}</p>
                                <div v-if="gordonUpside !== null" class="mt-1">
                                    <span class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-0.5 text-xs font-semibold" :class="gordonUpside >= 0 ? 'text-revenue' : 'text-destructive'">
                                        {{ gordonUpside >= 0 ? '+' : '' }}{{ gordonUpside.toFixed(1) }}% vs. preço atual
                                    </span>
                                </div>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Retorno Esperado</p>
                                <p class="mt-1 text-xl font-bold text-revenue">{{ formatPercent(gordonReturn) }}</p>
                                <p class="text-xs text-muted-foreground">a.a. (DY + g)</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Margem</p>
                                <p class="mt-1 text-xl font-bold" :class="(gordonMargin ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(gordonMargin) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-3 text-center sm:col-span-2">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Comparação: Tesouro IPCA</p>
                                <p class="mt-1 text-lg font-bold">
                                    {{ gordonFairPrice ? formatCurrency(gordonFairPrice) : '—' }}
                                    <span class="text-sm font-normal text-muted-foreground">vs. IPCA+{{ (discountRate - growthPerpetuity).toFixed(1) }}%</span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-xl border border-border bg-card p-5">
                        <MarginGauge :value="gordonMargin" label="Margem de Segurança" />
                    </div>

                    <!-- Growth Table -->
                    <div class="rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">
                            Crescimento <span class="text-primary">Anual</span>
                        </h3>
                        <p class="mb-4 text-xs text-muted-foreground">Ajuste a taxa de crescimento esperada para cada ano</p>
                        <div class="grid grid-cols-5 gap-2 sm:grid-cols-5">
                            <div v-for="(_, i) in 5" :key="i">
                                <Label class="text-xs text-muted-foreground">Ano {{ i + 1 }}</Label>
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
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

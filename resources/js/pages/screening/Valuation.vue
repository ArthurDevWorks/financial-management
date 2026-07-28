<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { cn } from '@/lib/utils';
import { Line as ChartLine } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement,
    Title, Tooltip, Legend,
} from 'chart.js';
import { ref, computed } from 'vue';
import { ArrowLeft, Calculator, RefreshCw } from 'lucide-vue-next';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend);

interface Asset {
    ticker: string;
    name: string | null;
    current_price: number | null;
    market_cap: number | null;
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
}

const props = defineProps<{
    asset: Asset;
}>();

// Active tab
const activeTab = ref('gordon');

// Gordon Model inputs
const dps = ref(props.asset.dividends_per_share ?? 0);
const discountRate = ref(12.5);
const growthPerpetuity = ref(3.0);
const currentPrice = ref(props.asset.current_price ?? 0);

// Gordon Model calculation: P = DPS / (Ke - g)
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

// Growth rates for multi-year Gordon
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

    // Terminal value (perpetuity)
    const terminalGrowth = growthPerpetuity.value / 100;
    const terminalDps = d * (1 + terminalGrowth);
    const terminalPv = terminalDps / (ke - terminalGrowth) / Math.pow(1 + ke, 5);

    return { years: result, terminalDps, terminalPv };
});

// Sensitivity analysis (fallback DPS para o gráfico)
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
    return num.toFixed(2) + '%';
}

function formatDecimal(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return num.toFixed(2);
}

function calculateDCF() {
    // Placeholder — DCF será integrado com o DcfValuationService
}
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
                <Button variant="outline" size="sm" @click="calculateDCF">
                    <Calculator class="h-4 w-4" />
                    Calcular DCF
                </Button>
            </div>

            <!-- Quick Asset Info -->
            <div class="mb-6 flex flex-wrap gap-4 rounded-xl border border-border bg-card p-4">
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-muted-foreground">Preço:</span>
                    <span class="font-semibold">{{ formatCurrency(asset.current_price) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-muted-foreground">DY:</span>
                    <span class="font-semibold text-revenue">{{ formatPercent(asset.dividend_yield) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-muted-foreground">P/L:</span>
                    <span class="font-semibold">{{ formatDecimal(asset.price_to_earnings) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-muted-foreground">ROE:</span>
                    <span class="font-semibold text-revenue">{{ formatPercent(asset.roe) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm">
                    <span class="text-muted-foreground">DPS:</span>
                    <span class="font-semibold">{{ formatCurrency(asset.dividends_per_share) }}</span>
                </div>
            </div>

            <!-- Tab Bar: DCF | Preço Teto | Gordon -->
            <div class="mb-6">
                <div class="inline-flex h-10 items-center justify-center rounded-xl bg-surface p-1 text-muted-foreground">
                    <button
                        v-for="tab in [{ value: 'dcf', label: 'DCF' }, { value: 'preco-teto', label: 'Preço Teto' }, { value: 'gordon', label: 'Gordon' }]"
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
                        <span :class="{ 'text-primary': tab.value === 'gordon' }">{{ tab.label }}</span>
                    </button>
                </div>
            </div>

            <!-- DCF Tab Content -->
            <div v-if="activeTab === 'dcf'" class="rounded-xl border border-border bg-card p-6">
                <p class="text-sm text-muted-foreground">
                    O cálculo DCF será exibido aqui. Utilize a página
                    <a href="/valuations" class="font-medium text-primary hover:underline">Valuations</a>
                    para realizar valuation DCF completo com o serviço existente.
                </p>
            </div>

            <!-- Preço Teto Tab Content -->
            <div v-else-if="activeTab === 'preco-teto'" class="rounded-xl border border-border bg-card p-6">
                <p class="text-sm text-muted-foreground">
                    O cálculo de Preço Teto será exibido aqui. Utilize a página
                    <a href="/preco-teto" class="font-medium text-primary hover:underline">Preço Teto</a>
                    para calcular com o serviço existente.
                </p>
            </div>

            <!-- Gordon Tab Content -->
            <div v-else>
                    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                        <!-- Inputs -->
                        <div class="rounded-xl border border-border bg-card p-5">
                            <h3 class="mb-4 text-sm font-semibold">Premissas do <span class="text-primary">Gordon</span></h3>
                            <div class="space-y-4">
                                <div>
                                    <Label>DPS atual (R$)</Label>
                                    <Input v-model.number="dps" type="number" step="0.01" class="mt-1" />
                                </div>
                                <div>
                                    <Label>Taxa de desconto (Ke %)</Label>
                                    <Input v-model.number="discountRate" type="number" step="0.1" class="mt-1" />
                                </div>
                                <div>
                                    <Label>Cresc. perpetuidade (g %)</Label>
                                    <Input v-model.number="growthPerpetuity" type="number" step="0.1" class="mt-1" />
                                </div>
                                <div>
                                    <Label>Preço atual (R$)</Label>
                                    <Input v-model.number="currentPrice" type="number" step="0.01" class="mt-1" />
                                </div>
                            </div>
                        </div>

                        <!-- Results -->
                        <div class="rounded-xl border border-border bg-card p-5">
                            <h3 class="mb-4 text-sm font-semibold">Resultados do <span class="text-primary">Gordon</span></h3>
                            <div class="space-y-4">
                                <div class="rounded-lg border border-primary/30 bg-card p-4 text-center">
                                    <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Preço Justo</p>
                                    <p class="mt-1 text-3xl font-bold text-primary">{{ formatCurrency(gordonFairPrice) }}</p>
                                    <div v-if="gordonUpside !== null" class="mt-1">
                                        <span class="inline-block rounded-full bg-primary/10 px-3 py-0.5 text-xs font-semibold" :class="gordonUpside >= 0 ? 'text-revenue' : 'text-destructive'">
                                            {{ gordonUpside >= 0 ? '+' : '' }}{{ gordonUpside.toFixed(1) }}% vs. preço atual
                                        </span>
                                    </div>
                                </div>
                                <div class="rounded-lg border border-border bg-surface p-3 text-center">
                                    <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Retorno Esperado</p>
                                    <p class="mt-1 text-xl font-bold text-revenue">{{ formatPercent(gordonReturn) }}</p>
                                    <p class="text-xs text-muted-foreground">a.a. com crescimento {{ growthPerpetuity }}%</p>
                                </div>
                            </div>
                        </div>

                        <!-- Growth Table -->
                        <div class="rounded-xl border border-border bg-card p-5">
                            <h3 class="mb-4 text-sm font-semibold">Crescimento <span class="text-primary">Anual</span></h3>
                            <div class="space-y-2">
                                <div v-for="(_, i) in 5" :key="i" class="flex items-center gap-2">
                                    <span class="w-12 text-xs text-muted-foreground">Ano {{ i + 1 }}</span>
                                    <Input v-model.number="growthRates[i]" type="number" step="0.1" class="h-8 text-xs" />
                                    <span class="text-xs text-muted-foreground">%</span>
                                </div>
                                <div class="mt-3 border-t border-border pt-3">
                                    <p class="text-xs text-muted-foreground">
                                        VP dividendos projetados:
                                        <span class="font-semibold text-foreground">{{ formatCurrency(projectedDividends.years.reduce((s, y) => s + y.pv, 0)) }}</span>
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        VP perpetuidade:
                                        <span class="font-semibold text-primary">{{ formatCurrency(projectedDividends.terminalPv) }}</span>
                                    </p>
                                </div>
                                <Button class="mt-2 w-full" size="sm" variant="outline" @click="calculateDCF">
                                    <RefreshCw class="h-3 w-3" />
                                    Recalcular
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Sensitivity Chart -->
                    <div class="mt-6 rounded-xl border border-border bg-card p-5">
                        <h3 class="mb-4 text-sm font-semibold">Análise de <span class="text-primary">Sensibilidade</span></h3>
                        <p class="mb-4 text-xs text-muted-foreground">Preço justo variando a taxa de desconto (Ke) e o crescimento perpetuidade (g)</p>
                        <div class="h-[280px]">
                            <ChartLine :data="sensitivityData" :options="sensitivityOptions" />
                        </div>
                    </div>
            </div>
        </div>
    </AppLayout>
</template>

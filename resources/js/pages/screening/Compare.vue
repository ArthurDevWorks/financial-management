<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import { Radar as ChartRadar, Bar as ChartBar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    RadialLinearScale, CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';
import { ArrowLeft, Plus } from 'lucide-vue-next';
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

ChartJS.register(RadialLinearScale, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler);

interface Asset {
    id: number;
    ticker: string;
    name: string | null;
    current_price: number | null;
    market_cap: number | null;
    dividend_yield: number | null;
    price_to_earnings: number | null;
    price_to_book: number | null;
    ev_to_ebitda: number | null;
    roe: number | null;
    profit_margin: number | null;
    net_debt_to_ebitda: number | null;
    payout: number | null;
    volume_avg_30d: number | null;
}

const props = defineProps<{
    assets: Record<string, Asset>;
    tickers: string[];
}>();

const assetList = props.tickers.map(t => props.assets[t]).filter(Boolean);
const colspanCount = computed(() => 1 + assetList.length);

function toNumber(value: unknown): number | null {
    if (value === null || value === undefined) return null;
    const num = typeof value === 'string' ? parseFloat(value) : Number(value);
    return isNaN(num) ? null : num;
}

function formatCurrency(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency', currency: 'BRL',
        minimumFractionDigits: 2,
    }).format(num);
}

function formatPercent(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return num.toFixed(2) + '%';
}

function formatRatio(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return num.toFixed(2);
}

// Determine which value is "better" for each indicator
function bestValue(items: (number | null | undefined)[], higherIsBetter: boolean): number | null {
    const valid = items.filter((v): v is number => v !== null && v !== undefined);
    if (!valid.length) return null;
    return higherIsBetter ? Math.max(...valid) : Math.min(...valid);
}

function isBest(value: number | null | undefined, indicator: string): boolean {
    const values = assetList.map(a => {
        switch (indicator) {
            case 'dividend_yield':
            case 'roe':
            case 'profit_margin': return a?.[indicator] ?? null;
            case 'price_to_earnings':
            case 'price_to_book':
            case 'ev_to_ebitda':
            case 'net_debt_to_ebitda': return a?.[indicator] ?? null;
            default: return null;
        }
    });
    const higherIsBetter = ['dividend_yield', 'roe', 'profit_margin'].includes(indicator);
    const best = bestValue(values, higherIsBetter);
    if (best === null || value === null || value === undefined) return false;
    return Math.abs(value - best) < 0.001;
}

// Radar Chart — scores normalizados a partir de indicadores reais
const score = (value: number | null | undefined, inverse = false): number => {
    if (value === null || value === undefined || value < 0) return 0;
    const clamped = Math.min(value, 100);
    return inverse ? Math.max(0, 10 - clamped / 10) : clamped / 10;
};

const radarLabels = ['Dividend Yield', 'ROE', 'Margem Líq.', 'Saúde Financ.', 'Valuação'];
const teal = 'hsla(168, 75%, 42%, 0.8)';
const gold = 'hsla(42, 80%, 52%, 0.8)';
const colors = [teal, gold, 'hsla(220, 80%, 55%, 0.8)', 'hsla(142, 70%, 50%, 0.8)', 'hsla(0, 70%, 50%, 0.8)'];

const radarChartData = {
    labels: radarLabels,
    datasets: assetList.map((a, i) => ({
        label: a.ticker,
        data: [
            score(a.dividend_yield),
            score(a.roe),
            score(a.profit_margin),
            score(a.net_debt_to_ebitda, true), // inverse: menor = melhor
            score(a.price_to_earnings, true),  // inverse: menor = melhor
        ],
        borderColor: colors[i % colors.length],
        backgroundColor: colors[i % colors.length].replace('0.8', '0.08'),
        pointBackgroundColor: colors[i % colors.length],
        borderWidth: 2,
    })),
};

const radarChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' as const, labels: { boxWidth: 12, usePointStyle: true, padding: 16, font: { size: 11 } } },
    },
    scales: {
        r: {
            beginAtZero: true,
            max: 10,
            ticks: { stepSize: 2, font: { size: 10 }, backdropColor: 'transparent' },
            grid: { color: 'hsla(228, 15%, 14%, 0.5)' },
            angleLines: { color: 'hsla(228, 15%, 14%, 0.5)' },
            pointLabels: { font: { size: 11, weight: '500' as const } },
        },
    },
};

// Bar Chart
const barChartData = {
    labels: ['P/L', 'P/VP', 'EV/EBITDA', 'Dív./EBITDA', 'Payout'],
    datasets: assetList.map((a, i) => ({
        label: a.ticker,
        data: [
            a.price_to_earnings ?? 0,
            a.price_to_book ?? 0,
            a.ev_to_ebitda ?? 0,
            a.net_debt_to_ebitda ?? 0,
                        a.payout ?? 0,
        ],
        backgroundColor: colors[i % colors.length].replace('0.8', '0.7'),
        borderRadius: 4,
    })),
};

const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { position: 'bottom' as const, labels: { boxWidth: 12, usePointStyle: true, padding: 16, font: { size: 11 } } },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: { grid: { color: 'hsla(228, 15%, 14%, 0.3)' }, ticks: { font: { size: 11 } } },
    },
};

function formatMarketCap(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    if (num >= 1e9) return `R$ ${(num / 1e9).toFixed(1)}B`;
    if (num >= 1e6) return `R$ ${(num / 1e6).toFixed(1)}M`;
    return formatCurrency(num);
}
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <PageHeader title="Comparar Ativos" description="Análise lado a lado de indicadores fundamentalistas">
                <template #actions>
                    <Button variant="outline" size="sm" @click="router.visit('/screening')">
                        <ArrowLeft class="h-4 w-4" />
                        Voltar
                    </Button>
                </template>
            </PageHeader>

            <!-- Asset Pills -->
            <div class="mb-6 flex flex-wrap gap-3">
                <div
                    v-for="a in assetList"
                    :key="a.ticker"
                    class="flex flex-1 items-center gap-3 rounded-lg border border-border bg-card p-4 transition-all hover:border-border/60"
                >
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg border border-border bg-surface font-bold text-primary">
                        {{ a.ticker.charAt(0) }}
                    </div>
                    <div class="flex-1">
                        <p class="text-sm font-semibold">{{ a.name || a.ticker }}</p>
                        <p class="font-mono text-xs text-muted-foreground">{{ a.ticker }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-base font-bold">{{ formatCurrency(a.current_price) }}</p>
                    </div>
                </div>
                <button class="flex flex-1 items-center justify-center gap-2 rounded-lg border border-dashed border-border p-4 text-sm text-muted-foreground transition-all hover:border-primary hover:text-primary hover:bg-primary/5">
                    <Plus class="h-4 w-4" />
                    Adicionar ativo
                </button>
            </div>

            <!-- Comparison Table -->
            <div class="mb-6 overflow-hidden rounded-xl border border-border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border bg-black/10">
                                <th class="w-44 px-4 py-3 text-left text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">Indicador</th>
                                <th v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center text-[11px] font-semibold tracking-wider text-muted-foreground uppercase">
                                    {{ a.ticker }}
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Section: Preço -->
                            <tr class="border-b border-border bg-black/5">
                                <td :colspan="colspanCount" class="px-4 py-2 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Preço & Mercado</td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Preço atual</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm">{{ formatCurrency(a.current_price) }}</td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Valor de mercado</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm">{{ formatMarketCap(a.market_cap) }}</td>
                            </tr>

                            <!-- Section: Valuation -->
                            <tr class="border-b border-border bg-black/5">
                                <td :colspan="colspanCount" class="px-4 py-2 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Valuation</td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Dividend Yield</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.dividend_yield, 'dividend_yield') ? 'text-revenue' : ''">
                                    {{ formatPercent(a.dividend_yield) }}
                                    <span v-if="isBest(a.dividend_yield, 'dividend_yield')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">P/L</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.price_to_earnings, 'price_to_earnings') ? 'text-revenue' : ''">
                                    {{ formatRatio(a.price_to_earnings) }}
                                    <span v-if="isBest(a.price_to_earnings, 'price_to_earnings')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">P/VP</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.price_to_book, 'price_to_book') ? 'text-revenue' : ''">
                                    {{ formatRatio(a.price_to_book) }}
                                    <span v-if="isBest(a.price_to_book, 'price_to_book')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">EV/EBITDA</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.ev_to_ebitda, 'ev_to_ebitda') ? 'text-revenue' : ''">
                                    {{ formatRatio(a.ev_to_ebitda) }}
                                    <span v-if="isBest(a.ev_to_ebitda, 'ev_to_ebitda')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>

                            <!-- Section: Rentabilidade -->
                            <tr class="border-b border-border bg-black/5">
                                <td :colspan="colspanCount" class="px-4 py-2 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Rentabilidade</td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">ROE</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.roe, 'roe') ? 'text-revenue' : ''">
                                    {{ formatPercent(a.roe) }}
                                    <span v-if="isBest(a.roe, 'roe')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Margem Líquida</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.profit_margin, 'profit_margin') ? 'text-revenue' : ''">
                                    {{ formatPercent(a.profit_margin) }}
                                    <span v-if="isBest(a.profit_margin, 'profit_margin')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>

                            <!-- Section: Saúde Financeira -->
                            <tr class="border-b border-border bg-black/5">
                                <td :colspan="colspanCount" class="px-4 py-2 text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Saúde Financeira</td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Dív. Líq./EBITDA</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.net_debt_to_ebitda, 'net_debt_to_ebitda') ? 'text-revenue' : ''">
                                    {{ formatRatio(a.net_debt_to_ebitda) }}
                                    <span v-if="isBest(a.net_debt_to_ebitda, 'net_debt_to_ebitda')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                            <tr class="border-b border-border transition-colors hover:bg-primary/5">
                                <td class="px-4 py-3 text-sm font-medium text-foreground">Payout</td>
                                <td v-for="a in assetList" :key="a.ticker" class="px-4 py-3 text-center font-mono text-sm" :class="isBest(a.payout, 'payout') ? 'text-revenue' : ''">
                                    {{ formatPercent(a.payout) }}
                                    <span v-if="isBest(a.payout, 'payout')" class="ml-1 rounded-full bg-revenue/10 px-1.5 py-0.5 text-[9px] font-bold text-revenue uppercase">✔</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Charts -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-border bg-card p-5">
                    <h3 class="mb-3 text-sm font-semibold">Comparativo <span class="text-primary">Radar</span></h3>
                    <div class="h-[280px]">
                        <ChartRadar :data="radarChartData" :options="radarChartOptions" />
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-5">
                    <h3 class="mb-3 text-sm font-semibold">Indicadores <span class="text-primary">Lado a Lado</span></h3>
                    <div class="h-[280px]">
                        <ChartBar :data="barChartData" :options="barChartOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

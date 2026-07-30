<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import PageHeader from '@/components/PageHeader.vue';
import { Line as ChartLine, Bar as ChartBar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale, LinearScale, PointElement, LineElement, BarElement,
    Title, Tooltip, Legend, Filler,
} from 'chart.js';
import { computed } from 'vue';
import { Heart, ArrowLeft, BarChart3 } from 'lucide-vue-next';
import { router } from '@inertiajs/vue3';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend, Filler);

interface Asset {
    id: number;
    ticker: string;
    name: string | null;
    sector: string | null;
    subsector: string | null;
    segment: string | null;
    current_price: number | null;
    market_cap: number | null;
    enterprise_value: number | null;
    volume_avg_30d: number | null;
    dividend_yield: number | null;
    price_to_earnings: number | null;
    price_to_book: number | null;
    ev_to_ebitda: number | null;
    price_to_sales: number | null;
    roe: number | null;
    roa: number | null;
    profit_margin: number | null;
    ebitda_margin: number | null;
    gross_margin: number | null;
    debt_to_ebitda: number | null;
    net_debt_to_ebitda: number | null;
    current_liquidity: number | null;
    payout: number | null;
    logo_url: string | null;
    net_income: number | null;
    revenue: number | null;
    free_cash_flow: number | null;
    dividends_per_share: number | null;
    earnings_per_share: number | null;
    book_value_per_share: number | null;
    total_shares: number | null;
    asset_type: string;
    long_business_summary: string | null;
    website: string | null;
    full_time_employees: number | null;
    fetched_at: string | null;
}

interface DividendRecord {
    date: string | null;
    value: number;
    type: string;
}

interface HistoricalPrice {
    date: number | null;
    close: number;
    open?: number;
    high?: number;
    low?: number;
    volume?: number;
}

const props = defineProps<{
    asset: Asset;
    isFavorite: boolean;
    dividends?: DividendRecord[];
    historicalPrices?: HistoricalPrice[];
}>();

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
        minimumFractionDigits: 2, maximumFractionDigits: 2,
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

function formatMarketCap(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    if (num >= 1e12) return `R$ ${(num / 1e12).toFixed(2)}T`;
    if (num >= 1e9) return `R$ ${(num / 1e9).toFixed(1)}B`;
    if (num >= 1e6) return `R$ ${(num / 1e6).toFixed(1)}M`;
    return formatCurrency(num);
}

const hasPriceData = computed(() => props.historicalPrices && props.historicalPrices.length > 0);
const hasDividends = computed(() => props.dividends && props.dividends.length > 0);

function formatTimestamp(ts: number | null): string {
    if (!ts) return '';
    return new Date(ts * 1000).toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' });
}

const dividendLabels = computed(() => {
    if (!hasDividends.value) return [];
    return props.dividends!.map((d) => {
        if (!d.date) return '';
        return new Date(d.date).toLocaleDateString('pt-BR', { month: 'short', year: '2-digit' });
    });
});

const dividendValues = computed(() => {
    if (!hasDividends.value) return [];
    return props.dividends!.map((d) => d.value);
});

const priceChartLabels = computed(() => {
    if (hasPriceData.value) {
        return props.historicalPrices!.map((p) => formatTimestamp(p.date));
    }
    return ['Preço atual'];
});

const priceChartValues = computed(() => {
    if (hasPriceData.value) {
        return props.historicalPrices!.map((p) => p.close);
    }
    return props.asset.current_price !== null ? [props.asset.current_price] : [0];
});

const priceChartData = computed(() => ({
    labels: priceChartLabels.value,
    datasets: [
        {
            label: props.asset.ticker,
            data: priceChartValues.value,
            borderColor: 'hsl(168, 75%, 42%)',
            backgroundColor: 'hsla(168, 75%, 42%, 0.08)',
            fill: true,
            tension: 0.35,
            pointRadius: hasPriceData.value ? 2 : 4,
            pointHitRadius: 8,
            borderWidth: 2.5,
        },
    ],
}));

const priceChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'hsl(228, 22%, 9%)',
            borderColor: 'hsl(228, 15%, 14%)',
            borderWidth: 1,
            padding: 10,
            callbacks: {
                label: (ctx: { parsed: { y: number | null } }) => ctx.parsed.y !== null ? `R$ ${ctx.parsed.y.toFixed(2)}` : '',
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { maxTicksLimit: 7, font: { size: 11 } },
        },
        y: {
            grid: { color: 'hsla(228, 15%, 14%, 0.3)' },
            ticks: { callback: (v: number) => 'R$ ' + v.toFixed(0), font: { size: 11 } },
        },
    },
};

const dividendChartData = computed(() => ({
    labels: dividendLabels.value.length > 0 ? dividendLabels.value : ['Sem dados'],
    datasets: [
        {
            label: 'Dividendos (R$/ação)',
            data: dividendValues.value.length > 0 ? dividendValues.value : [0],
            backgroundColor: 'hsla(142, 70%, 50%, 0.7)',
            hoverBackgroundColor: 'hsla(142, 70%, 50%, 0.9)',
            borderRadius: 4,
            borderSkipped: false,
        },
    ],
}));

const dividendChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: 'hsl(228, 22%, 9%)',
            borderColor: 'hsl(228, 15%, 14%)',
            borderWidth: 1,
            padding: 10,
            callbacks: {
                label: (ctx: { parsed: { y: number } }) => `R$ ${ctx.parsed.y.toFixed(2)}/ação`,
            },
        },
    },
    scales: {
        x: { grid: { display: false }, ticks: { font: { size: 11 } } },
        y: {
            grid: { color: 'hsla(228, 15%, 14%, 0.3)' },
            ticks: { callback: (v: number) => 'R$ ' + v.toFixed(1), font: { size: 11 } },
        },
    },
};

const sectors = [
    { key: 'Valuation', items: [
        { label: 'P/L', value: formatRatio(props.asset.price_to_earnings) },
        { label: 'P/VP', value: formatRatio(props.asset.price_to_book) },
        { label: 'EV/EBITDA', value: formatRatio(props.asset.ev_to_ebitda) },
        { label: 'P/Sales', value: formatRatio(props.asset.price_to_sales) },
        { label: 'DY', value: formatPercent(props.asset.dividend_yield), highlight: true },
        { label: 'Payout', value: formatPercent(props.asset.payout) },
    ]},
    { key: 'Rentabilidade', items: [
        { label: 'ROE', value: formatPercent(props.asset.roe), highlight: true },
        { label: 'ROA', value: formatPercent(props.asset.roa) },
        { label: 'Margem Líquida', value: formatPercent(props.asset.profit_margin), highlight: true },
        { label: 'Margem EBITDA', value: formatPercent(props.asset.ebitda_margin) },
        { label: 'Margem Bruta', value: formatPercent(props.asset.gross_margin) },
    ]},
    { key: 'Saúde Financeira', items: [
        { label: 'Dívida/EBITDA', value: formatRatio(props.asset.debt_to_ebitda) },
        { label: 'Dívida Líquida/EBITDA', value: formatRatio(props.asset.net_debt_to_ebitda), highlight: true },
        { label: 'Liquidez Corrente', value: formatRatio(props.asset.current_liquidity) },
    ]},
    { key: 'Mercado', items: [
        { label: 'Valor de Mercado', value: formatMarketCap(props.asset.market_cap) },
        { label: 'Enterprise Value', value: formatMarketCap(props.asset.enterprise_value) },
        { label: 'Volume Médio (30d)', value: formatCurrency(props.asset.volume_avg_30d) },
        { label: 'Ações Totais', value: formatNumber(props.asset.total_shares) !== '—' ? (toNumber(props.asset.total_shares)! / 1e9).toFixed(2) + 'B' : '—' },
    ]},
];

function toggleFavorite() {
    router.post('/screening/favorite', {
        ticker: props.asset.ticker,
        asset_type: props.asset.asset_type,
    }, { preserveState: true, preserveScroll: true });
}

function formatNumber(value: unknown, decimals = 2): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return num.toFixed(decimals);
}
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <!-- Back + Actions -->
            <div class="mb-6 flex items-center justify-between">
                <a href="/screening" class="inline-flex items-center gap-1.5 text-sm text-muted-foreground transition-colors hover:text-foreground">
                    <ArrowLeft class="h-4 w-4" />
                    Voltar ao Screening
                </a>
                <div class="flex items-center gap-2">
                    <Button variant="outline" size="sm" @click="router.visit(`/screening/${asset.ticker}/valuation`)">
                        <BarChart3 class="h-4 w-4" />
                        Valuation
                    </Button>
                    <Button variant="outline" size="icon" :class="{ 'text-accent': isFavorite }" @click="toggleFavorite">
                        <Heart class="h-4 w-4" :fill="isFavorite ? 'currentColor' : 'none'" />
                    </Button>
                </div>
            </div>

            <!-- Asset Header -->
            <div class="mb-6 rounded-xl border border-border bg-card p-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <img :src="asset.logo_url || '/images/default-logo.svg'" :alt="asset.ticker" class="h-14 w-14 rounded-xl border border-border object-contain" @error="($event.target as HTMLImageElement).src = '/images/default-logo.svg'" />
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">{{ asset.name || asset.ticker }}</h1>
                            <div class="mt-1 flex flex-wrap items-center gap-2">
                                <span class="rounded-md bg-primary/10 px-2.5 py-0.5 font-mono text-sm font-bold text-primary">{{ asset.ticker }}</span>
                                <span v-if="asset.sector" class="rounded-full bg-investment/10 px-2.5 py-0.5 text-xs font-medium text-investment">{{ asset.sector }}</span>
                                <span v-if="asset.asset_type === 'fii'" class="rounded-full bg-investment/10 px-2.5 py-0.5 text-xs font-medium text-investment">FII</span>
                                <span v-else-if="asset.asset_type === 'bdr'" class="rounded-full bg-accent/10 px-2.5 py-0.5 text-xs font-medium text-accent">BDR</span>
                                <span v-else-if="asset.asset_type === 'etf'" class="rounded-full bg-chart-3/10 px-2.5 py-0.5 text-xs font-medium text-chart-3">ETF</span>
                                <span v-else class="rounded-full bg-revenue/10 px-2.5 py-0.5 text-xs font-medium text-revenue">Ação</span>
                            </div>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold tracking-tight">{{ formatCurrency(asset.current_price) }}</p>
                        <p class="text-sm text-muted-foreground">último fechamento</p>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="mb-6 grid grid-cols-2 gap-3 lg:grid-cols-6">
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">DY</p>
                    <p class="mt-0.5 text-lg font-bold text-revenue">{{ formatPercent(asset.dividend_yield) }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">P/L</p>
                    <p class="mt-0.5 text-lg font-bold">{{ formatRatio(asset.price_to_earnings) }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">P/VP</p>
                    <p class="mt-0.5 text-lg font-bold">{{ formatRatio(asset.price_to_book) }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">ROE</p>
                    <p class="mt-0.5 text-lg font-bold text-revenue">{{ formatPercent(asset.roe) }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Margem</p>
                    <p class="mt-0.5 text-lg font-bold text-revenue">{{ formatPercent(asset.profit_margin) }}</p>
                </div>
                <div class="rounded-xl border border-border bg-card p-3 text-center transition-all hover:border-border/60">
                    <p class="text-[10px] font-semibold tracking-wider text-muted-foreground uppercase">Dív./EBITDA</p>
                    <p class="mt-0.5 text-lg font-bold" :class="(asset.net_debt_to_ebitda ?? 999) < 2 ? 'text-revenue' : 'text-destructive'">{{ formatRatio(asset.net_debt_to_ebitda) }}</p>
                </div>
            </div>

            <!-- Company Description -->
            <div v-if="asset.long_business_summary" class="mb-6 rounded-xl border border-border bg-card p-5">
                <h3 class="mb-3 text-sm font-semibold">Sobre a <span class="text-primary">Empresa</span></h3>
                <p class="text-sm leading-relaxed text-muted-foreground">{{ asset.long_business_summary }}</p>
                <div v-if="asset.website || asset.full_time_employees" class="mt-4 flex flex-wrap gap-6 text-sm text-muted-foreground">
                    <span v-if="asset.website">
                        🌐 <a :href="asset.website" target="_blank" rel="noopener noreferrer" class="text-primary hover:underline">{{ asset.website }}</a>
                    </span>
                    <span v-if="asset.full_time_employees">
                        👥 {{ asset.full_time_employees.toLocaleString('pt-BR') }} funcionários
                    </span>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-xl border border-border bg-card p-5">
                    <h3 class="mb-3 text-sm font-semibold">Preço <span class="text-primary">Histórico</span></h3>
                    <div class="h-[220px]">
                        <ChartLine :data="priceChartData" :options="priceChartOptions" />
                    </div>
                </div>
                <div class="rounded-xl border border-border bg-card p-5">
                    <h3 class="mb-3 text-sm font-semibold">Dividendos <span class="text-primary">Mensais</span></h3>
                    <div class="h-[220px]">
                        <ChartBar :data="dividendChartData" :options="dividendChartOptions" />
                    </div>
                </div>
            </div>

            <!-- Indicators by Category -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div v-for="section in sectors" :key="section.key" class="rounded-xl border border-border bg-card p-5">
                    <h3 class="mb-3 text-xs font-semibold tracking-wider text-muted-foreground uppercase">{{ section.key }}</h3>
                    <div class="space-y-2">
                        <div v-for="item in section.items" :key="item.label" class="flex items-center justify-between border-b border-border pb-1.5 text-sm last:border-0 last:pb-0">
                            <span class="text-muted-foreground">{{ item.label }}</span>
                            <span class="font-semibold" :class="{ 'text-revenue': item.highlight }">{{ item.value }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

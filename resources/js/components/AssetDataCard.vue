<script setup lang="ts">
import { computed } from 'vue';
import {
    Percent,
    TrendingUp,
    Layers,
    PieChart,
    Globe,
    Coins,
    Activity,
    Briefcase
} from 'lucide-vue-next';

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
    logo_url?: string | null;
    sector?: string | null;
    industry?: string | null;
}

const props = defineProps<{
    asset: Asset;
}>();

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

function formatShares(value: unknown): string {
    const num = toNumber(value);
    if (num === null) return '—';
    return num.toLocaleString('pt-BR');
}

const indicators = computed(() => [
    {
        label: 'DY (12m)',
        value: formatPercent(props.asset.dividend_yield),
        icon: Percent,
        variant: 'success'
    },
    {
        label: 'P/VP',
        value: formatDecimal(props.asset.price_to_book),
        icon: Layers,
        variant: 'neutral'
    },
    {
        label: 'ROE',
        value: formatPercent(props.asset.roe),
        icon: TrendingUp,
        variant: 'success'
    },
    {
        label: 'Payout',
        value: formatPercent(props.asset.payout),
        icon: PieChart,
        variant: 'neutral'
    },
    {
        label: 'Market Cap',
        value: formatCurrency(props.asset.market_cap),
        icon: Globe,
        variant: 'neutral',
        wide: true
    },
    {
        label: 'Total Ações',
        value: formatShares(props.asset.total_shares),
        icon: Activity,
        variant: 'neutral'
    },
    {
        label: 'Lucro Líquido',
        value: formatCurrency(props.asset.net_income),
        icon: Coins,
        variant: 'neutral'
    }
]);
</script>

<template>
    <div class="mb-6 rounded-2xl border border-border bg-gradient-to-b from-card to-card/90 shadow-sm overflow-hidden">
        <!-- Card Header with Asset Logo and Core Details -->
        <div class="border-b border-border/60 bg-surface/10 px-6 py-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div v-if="asset.logo_url" class="h-14 w-14 shrink-0 rounded-xl border border-border bg-white p-2.5 flex items-center justify-center shadow-xs">
                    <img :src="asset.logo_url" :alt="asset.ticker" class="max-h-full max-w-full object-contain" />
                </div>
                <div v-else class="h-14 w-14 shrink-0 rounded-xl bg-primary/10 text-primary border border-primary/20 flex items-center justify-center font-bold text-xl shadow-xs">
                    {{ asset.ticker.substring(0, 2) }}
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-2xl font-black tracking-tight text-foreground">{{ asset.ticker }}</span>
                        <span class="rounded-md bg-primary/10 px-2 py-0.5 text-xs font-semibold text-primary uppercase tracking-wider">
                            {{ asset.asset_type === 'fii' ? 'FII' : 'Ação' }}
                        </span>
                    </div>
                    <h2 class="text-sm font-medium text-muted-foreground truncate mt-0.5">{{ asset.name }}</h2>
                </div>
            </div>

            <!-- Sector & Big Price -->
            <div class="flex items-center justify-between md:justify-end gap-6 border-t border-border/40 pt-3 md:border-t-0 md:pt-0">
                <div v-if="asset.sector" class="hidden sm:flex items-center gap-1.5 text-xs text-muted-foreground bg-surface px-3 py-1.5 rounded-lg border border-border/40">
                    <Briefcase class="h-3.5 w-3.5" />
                    <span>{{ asset.sector }}</span>
                </div>
                <div class="text-right">
                    <p class="text-xs text-muted-foreground font-medium">Preço Atual</p>
                    <p class="text-2xl font-black text-foreground tracking-tight mt-0.5">
                        {{ formatCurrency(asset.current_price) }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Indicators Grid -->
        <div class="p-6">
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                <!-- Highlight Indicator Card -->
                <div 
                    v-for="ind in indicators" 
                    :key="ind.label"
                    class="group relative overflow-hidden rounded-xl border border-border/50 bg-surface/30 p-4 transition-all duration-300 hover:-translate-y-1 hover:border-primary/20 hover:shadow-xs hover:bg-surface/50"
                    :class="{ 'lg:col-span-2': ind.wide }"
                >
                    <!-- Small Background Sparkle for hover -->
                    <div class="absolute -right-3 -bottom-3 h-12 w-12 rounded-full opacity-0 group-hover:opacity-10 transition-opacity duration-300"
                        :class="[
                            ind.variant === 'success' ? 'bg-revenue' :
                            ind.variant === 'danger' ? 'bg-destructive' : 'bg-primary'
                        ]"
                    />

                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-muted-foreground truncate">{{ ind.label }}</span>
                        <component 
                            :is="ind.icon" 
                            class="h-4 w-4 shrink-0 transition-transform group-hover:scale-110" 
                            :class="[
                                ind.variant === 'success' ? 'text-revenue' :
                                ind.variant === 'danger' ? 'text-destructive' : 'text-primary'
                            ]"
                        />
                    </div>
                    <p class="mt-2 text-base font-extrabold tracking-tight"
                       :class="[
                           ind.variant === 'success' ? 'text-revenue' :
                           ind.variant === 'danger' ? 'text-destructive' : 'text-foreground'
                       ]"
                    >
                        {{ ind.value }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>

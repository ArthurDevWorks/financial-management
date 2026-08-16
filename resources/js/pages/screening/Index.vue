<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import { useToast } from '@/composables/useToast';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { Loader2, Search, SlidersHorizontal, Star, X } from 'lucide-vue-next';
import { nextTick, onMounted, onUnmounted, ref } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string | null;
    sector: string | null;
    current_price: number | null;
    market_cap: number | null;
    dividend_yield: number | null;
    price_to_earnings: number | null;
    price_to_book: number | null;
    roe: number | null;
    profit_margin: number | null;
    asset_type: string;
    volume_avg_30d: number | null;
    logo_url: string | null;
}

interface Pagination {
    data: Asset[];
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    assets: Pagination;
    sectors: string[];
    savedFilters: {
        id: number;
        name: string;
        criteria: Record<string, unknown>;
    }[];
    stats: {
        total: number;
        avg_dy: number | null;
        avg_pe: number | null;
        avg_roe: number | null;
    };
    favorites: string[];
    filters: Record<string, string | undefined>;
}>();

const allAssets = ref<Asset[]>([...props.assets.data]);
const currentPage = ref(props.assets.current_page);
const lastPage = ref(props.assets.last_page);
const total = ref(props.assets.total);
const isLoading = ref(false);
const sentinelRef = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

function buildQueryString(): string {
    const params = new URLSearchParams();
    Object.entries(localFilters.value).forEach(([key, val]) => {
        if (val) params.set(key, val);
    });
    return params.toString();
}

async function loadMore() {
    if (isLoading.value || currentPage.value >= lastPage.value) return;
    isLoading.value = true;
    try {
        const qs = buildQueryString();
        const sep = qs ? '&' : '';
        const url = `/screening/json?${qs}${sep}page=${currentPage.value + 1}`;
        const res = await fetch(url);
        const json = await res.json();
        allAssets.value.push(...json.data);
        currentPage.value = json.current_page;
        lastPage.value = json.last_page;
        total.value = json.total;
    } catch {
        useToast().error('Erro ao carregar mais resultados.');
    } finally {
        isLoading.value = false;
    }
}

onMounted(async () => {
    await nextTick();
    observer = new IntersectionObserver(
        (entries) => {
            if (entries[0].isIntersecting) loadMore();
        },
        { rootMargin: '200px' },
    );
    if (sentinelRef.value) observer.observe(sentinelRef.value);
});

onUnmounted(() => {
    observer?.disconnect();
});

// Reactive filters
const localFilters = ref<Record<string, string>>({
    asset_type: props.filters.asset_type || '',
    sector: props.filters.sector || '',
    dy_min: props.filters.dy_min || '',
    pe_max: props.filters.pe_max || '',
    roe_min: props.filters.roe_min || '',
    pvp_min: props.filters.pvp_min || '',
    pvp_max: props.filters.pvp_max || '',
    net_debt_to_ebitda_min: props.filters.net_debt_to_ebitda_min || '',
    net_debt_to_ebitda_max: props.filters.net_debt_to_ebitda_max || '',
    liq_min: props.filters.liq_min || '',
    search: props.filters.search || '',
});

const selectedForCompare = ref<string[]>([]);
const showFilters = ref(true);

const assetTypes = [
    { value: '', label: 'Todos' },
    { value: 'stock', label: 'Ações' },
    { value: 'fii', label: 'FIIs' },
    { value: 'bdr', label: 'BDRs' },
];

function applyFilters() {
    const params: Record<string, string> = {};
    Object.entries(localFilters.value).forEach(([key, val]) => {
        if (val) params[key] = val;
    });
    router.get('/screening', params, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            allAssets.value = [...props.assets.data];
            currentPage.value = props.assets.current_page;
            lastPage.value = props.assets.last_page;
            total.value = props.assets.total;
        },
    });
}

function clearFilters() {
    localFilters.value = {
        asset_type: '',
        sector: '',
        dy_min: '',
        pe_max: '',
        roe_min: '',
        pvp_min: '',
        pvp_max: '',
        net_debt_to_ebitda_min: '',
        net_debt_to_ebitda_max: '',
        liq_min: '',
        search: '',
    };
    router.get(
        '/screening',
        {},
        {
            preserveState: true,
            onSuccess: () => {
                allAssets.value = [...props.assets.data];
                currentPage.value = props.assets.current_page;
                lastPage.value = props.assets.last_page;
                total.value = props.assets.total;
            },
        },
    );
}

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
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
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

function toggleCompare(ticker: string) {
    const idx = selectedForCompare.value.indexOf(ticker);
    if (idx >= 0) selectedForCompare.value.splice(idx, 1);
    else if (selectedForCompare.value.length < 5)
        selectedForCompare.value.push(ticker);
}

function applySavedFilter(filter: {
    id: number;
    criteria: Record<string, unknown>;
}) {
    const criteria = filter.criteria as Record<string, string>;
    Object.keys(criteria).forEach((key) => {
        if (key in localFilters.value) {
            (localFilters.value as Record<string, string>)[key] = criteria[key];
        }
    });
    applyFilters();
}

let searchTimeout: ReturnType<typeof setTimeout> | null = null;
function onSearchInput() {
    if (searchTimeout) clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => applyFilters(), 400);
}
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <PageHeader
                title="Screening de Ativos"
                description="Encontre oportunidades com filtros fundamentalistas"
            >
                <template #actions>
                    <Button
                        variant="outline"
                        size="sm"
                        @click="showFilters = !showFilters"
                    >
                        <SlidersHorizontal class="h-4 w-4" />
                        Filtros
                    </Button>
                    <!-- <Button variant="outline" size="sm">
                        <Download class="h-4 w-4" />
                        Exportar
                    </Button> -->
                </template>
            </PageHeader>

            <!-- Search Bar -->
            <div class="relative mb-4">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="localFilters.search"
                    placeholder="Buscar por ticker ou nome..."
                    class="h-10 pl-10"
                    @input="onSearchInput"
                />
                <button
                    v-if="localFilters.search"
                    class="absolute top-1/2 right-3 -translate-y-1/2 text-muted-foreground hover:text-foreground"
                    @click="
                        localFilters.search = '';
                        applyFilters();
                    "
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Filter Bar -->
            <div
                v-if="showFilters"
                class="mb-6 rounded-xl border border-border bg-card p-5 transition-all"
            >
                <div class="mb-3 flex items-center justify-between">
                    <span
                        class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                        >Filtros</span
                    >
                    <button
                        class="text-xs text-muted-foreground hover:text-foreground"
                        @click="clearFilters"
                    >
                        Limpar
                    </button>
                </div>
                <div
                    class="grid grid-cols-2 gap-3 md:grid-cols-4 lg:grid-cols-7"
                >
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >Tipo</label
                        >
                        <select
                            v-model="localFilters.asset_type"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        >
                            <option
                                v-for="t in assetTypes"
                                :key="t.value"
                                :value="t.value"
                            >
                                {{ t.label }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >Setor</label
                        >
                        <select
                            v-model="localFilters.sector"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option v-for="s in sectors" :key="s" :value="s">
                                {{ s }}
                            </option>
                        </select>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >DY mínimo</label
                        >
                        <div class="flex items-center gap-1">
                            <input
                                v-model="localFilters.dy_min"
                                type="number"
                                step="0.1"
                                placeholder="0"
                                class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                                @change="applyFilters"
                            />
                            <span class="text-xs text-muted-foreground">%</span>
                        </div>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >P/L máx.</label
                        >
                        <input
                            v-model="localFilters.pe_max"
                            type="number"
                            step="0.1"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >ROE mínimo</label
                        >
                        <div class="flex items-center gap-1">
                            <input
                                v-model="localFilters.roe_min"
                                type="number"
                                step="0.1"
                                placeholder="0"
                                class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                                @change="applyFilters"
                            />
                            <span class="text-xs text-muted-foreground">%</span>
                        </div>
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >P/VP mín.</label
                        >
                        <input
                            v-model="localFilters.pvp_min"
                            type="number"
                            step="0.1"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >P/VP máx.</label
                        >
                        <input
                            v-model="localFilters.pvp_max"
                            type="number"
                            step="0.1"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >Div. L&iacute;q./EBITDA m&iacute;n.</label
                        >
                        <input
                            v-model="localFilters.net_debt_to_ebitda_min"
                            type="number"
                            step="0.1"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >Div. L&iacute;q./EBITDA m&aacute;x.</label
                        >
                        <input
                            v-model="localFilters.net_debt_to_ebitda_max"
                            type="number"
                            step="0.1"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                    <div>
                        <label
                            class="mb-1 block text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >Liq. (R$)</label
                        >
                        <input
                            v-model="localFilters.liq_min"
                            type="number"
                            placeholder="0"
                            class="h-9 w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            @change="applyFilters"
                        />
                    </div>
                </div>

                <!-- Saved Filters -->
                <div
                    v-if="savedFilters.length"
                    class="mt-4 flex flex-wrap items-center gap-2"
                >
                    <span
                        class="text-[11px] font-semibold text-muted-foreground"
                        >Salvos:</span
                    >
                    <button
                        v-for="f in savedFilters"
                        :key="f.id"
                        class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-xs font-medium text-primary transition-all hover:bg-primary/20"
                        @click="applySavedFilter(f)"
                    >
                        <Star class="h-3 w-3" />
                        {{ f.name }}
                    </button>
                </div>
            </div>

            <!-- Results Table -->
            <div
                class="overflow-hidden rounded-xl border border-border bg-card"
            >
                <div class="max-h-[70vh] overflow-y-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-border">
                                <th
                                    class="sticky top-0 z-10 w-10 bg-card px-4 py-3 text-left"
                                ></th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-left text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Ticker
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-left text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Empresa
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Preço
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    DY
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    P/L
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    P/VP
                                </th>
                                <th
                                    class="sticky top-0 z-10 bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    ROE
                                </th>
                                <th
                                    class="sticky top-0 z-10 hidden bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase lg:table-cell"
                                >
                                    Margem Líq.
                                </th>
                                <th
                                    class="sticky top-0 z-10 hidden bg-card px-4 py-3 text-right text-[11px] font-semibold tracking-wider text-muted-foreground uppercase md:table-cell"
                                >
                                    Valor Mercado
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="asset in allAssets"
                                :key="asset.id"
                                class="border-b border-border transition-colors hover:bg-primary/5"
                            >
                                <td class="px-4 py-3">
                                    <Checkbox
                                        :checked="
                                            selectedForCompare.includes(
                                                asset.ticker,
                                            )
                                        "
                                        @update:checked="
                                            toggleCompare(asset.ticker)
                                        "
                                    />
                                </td>
                                <td class="px-4 py-3">
                                    <a
                                        :href="`/screening/${asset.ticker}`"
                                        class="group flex items-center gap-2"
                                    >
                                        <img
                                            :src="
                                                asset.logo_url ||
                                                '/images/default-logo.svg'
                                            "
                                            :alt="asset.ticker"
                                            class="h-6 w-6 rounded-full object-contain"
                                            @error="
                                                (
                                                    $event.target as HTMLImageElement
                                                ).src =
                                                    '/images/default-logo.svg'
                                            "
                                        />
                                        <span
                                            class="font-mono text-sm font-bold text-primary"
                                            >{{ asset.ticker }}</span
                                        >
                                        <span
                                            v-if="asset.asset_type === 'fii'"
                                            class="rounded-full bg-investment/10 px-2 py-0.5 text-[10px] font-semibold text-investment"
                                            >FII</span
                                        >
                                    </a>
                                </td>
                                <td
                                    class="max-w-[200px] truncate px-4 py-3 text-sm text-muted-foreground"
                                >
                                    {{ asset.name || '—' }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono text-sm font-semibold"
                                >
                                    {{ formatCurrency(asset.current_price) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono text-sm font-semibold text-revenue"
                                >
                                    {{ formatPercent(asset.dividend_yield) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono text-sm"
                                >
                                    {{ formatRatio(asset.price_to_earnings) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono text-sm"
                                >
                                    {{ formatRatio(asset.price_to_book) }}
                                </td>
                                <td
                                    class="px-4 py-3 text-right font-mono text-sm font-semibold text-revenue"
                                >
                                    {{ formatPercent(asset.roe) }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-right font-mono text-sm lg:table-cell"
                                    :class="
                                        toNumber(asset.profit_margin) !== null
                                            ? toNumber(asset.profit_margin)! >= 15
                                                ? 'text-revenue font-semibold'
                                                : toNumber(asset.profit_margin)! >= 0
                                                  ? 'text-foreground'
                                                  : 'text-destructive'
                                            : 'text-muted-foreground'
                                    "
                                >
                                    {{ formatPercent(asset.profit_margin) }}
                                </td>
                                <td
                                    class="hidden px-4 py-3 text-right font-mono text-sm text-muted-foreground md:table-cell"
                                >
                                    {{ formatMarketCap(asset.market_cap) }}
                                </td>
                            </tr>
                            <!-- Empty state -->
                            <tr v-if="!allAssets.length && !isLoading">
                                <td colspan="9" class="px-4 py-16 text-center">
                                    <Search
                                        class="mx-auto mb-3 h-10 w-10 text-muted-foreground/30"
                                    />
                                    <p
                                        class="text-base font-semibold text-foreground"
                                    >
                                        Nenhum ativo encontrado
                                    </p>
                                    <p
                                        class="mt-1 text-sm text-muted-foreground"
                                    >
                                        Tente ajustar os filtros ou buscar por
                                        outro termo.
                                    </p>
                                    <Button
                                        variant="outline"
                                        class="mt-4"
                                        @click="clearFilters"
                                        >Limpar Filtros</Button
                                    >
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Infinite scroll sentinel -->
                    <div ref="sentinelRef" class="h-1" />

                    <!-- Loading indicator -->
                    <div
                        v-if="isLoading"
                        class="flex items-center justify-center gap-2 py-6"
                    >
                        <Loader2 class="h-5 w-5 animate-spin text-primary" />
                        <span class="text-sm text-muted-foreground"
                            >Carregando mais ativos...</span
                        >
                    </div>

                    <!-- End of list -->
                    <div
                        v-if="
                            !isLoading &&
                            currentPage >= lastPage &&
                            allAssets.length
                        "
                        class="py-6 text-center"
                    >
                        <p class="text-xs text-muted-foreground">
                            Todos os {{ total }} ativos carregados
                        </p>
                    </div>
                </div>

                <!-- Footer with count -->
                <div
                    v-if="allAssets.length"
                    class="flex items-center justify-between border-t border-border px-4 py-3"
                >
                    <p class="text-xs text-muted-foreground">
                        Mostrando {{ allAssets.length }} de {{ total }}
                    </p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

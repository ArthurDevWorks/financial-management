<script setup lang="ts">
import StatBadge from '@/components/StatBadge.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, CreditCard, Receipt } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Period {
    start: string;
    end: string;
    due: string;
}

interface ReleaseCategory {
    id: number;
    name: string;
}

interface Release {
    id: number;
    title: string;
    amount: number;
    date: string;
    type: 'revenue' | 'expense';
    category: ReleaseCategory | null;
    installment_number: number | null;
    total_installments: number | null;
}

interface MonthItem {
    month: number;
    year: number;
    label: string;
}

interface CardInfo {
    id: number;
    name: string;
    color: string;
    limit: number;
    closing_day: number;
    due_day: number;
    bank: { name: string } | null;
}

const props = defineProps<{
    card: CardInfo;
    period: Period;
    releases: Release[];
    total: number;
    month: number;
    year: number;
    months: MonthItem[];
    current_month: number;
    current_year: number;
}>();

const selectedMonthYear = ref(`${props.year}-${String(props.month).padStart(2, '0')}`);

const goToMonth = () => {
    const [y, m] = selectedMonthYear.value.split('-');
    router.visit(`/credit-cards/${props.card.id}/invoices/${y}/${parseInt(m)}`);
};

const goToCard = () => {
    router.visit('/credit-cards');
};

const isCurrentInvoice = computed(
    () => props.month === props.current_month && props.year === props.current_year,
);

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
};

const usagePercent = computed(() => {
    if (!props.card.limit) return 0;
    return Math.min(100, Math.round((props.total / props.card.limit) * 100));
});

const usageColor = computed(() => {
    if (usagePercent.value >= 90) return 'bg-destructive';
    if (usagePercent.value >= 70) return 'bg-accent';
    return 'bg-primary';
});

// Gastos por categoria
const byCategory = computed(() => {
    const map: Record<string, { name: string; total: number }> = {};
    for (const r of props.releases) {
        if (r.type !== 'expense') continue;
        const name = r.category?.name ?? 'Sem categoria';
        if (!map[name]) map[name] = { name, total: 0 };
        map[name].total += Number(r.amount);
    }
    return Object.values(map).sort((a, b) => b.total - a.total);
});
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <!-- Back -->
            <Button variant="ghost" size="sm" class="mb-4" @click="goToCard">
                <ArrowLeft class="h-4 w-4" />
                Cartões
            </Button>

            <!-- Header -->
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <div
                        class="flex h-12 w-12 items-center justify-center rounded-2xl"
                        :style="{ backgroundColor: card.color + '22', color: card.color }"
                    >
                        <CreditCard class="h-6 w-6" />
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-foreground">{{ card.name }}</h1>
                        <p class="text-sm text-muted-foreground">
                            {{ card.bank?.name ?? 'Sem banco' }} •
                            Fecha dia {{ card.closing_day }} •
                            Vence dia {{ card.due_day }}
                        </p>
                    </div>
                </div>

                <!-- Month selector -->
                <div class="flex items-center gap-2">
                    <CalendarDays class="h-4 w-4 text-muted-foreground" />
                    <select
                        v-model="selectedMonthYear"
                        @change="goToMonth"
                        class="h-9 rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                    >
                        <option
                            v-for="m in months"
                            :key="`${m.year}-${m.month}`"
                            :value="`${m.year}-${String(m.month).padStart(2, '0')}`"
                        >
                            {{ m.label }}
                        </option>
                    </select>
                </div>
            </div>

            <!-- Invoice summary -->
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <!-- Total fatura -->
                <div class="col-span-2 rounded-2xl border border-border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Total da Fatura
                            </p>
                            <p class="mt-1 text-4xl font-bold text-foreground">
                                {{ formatCurrency(total) }}
                            </p>
                        </div>
                        <span
                            v-if="isCurrentInvoice"
                            class="rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary"
                        >
                            Fatura Aberta
                        </span>
                        <span
                            v-else
                            class="rounded-full bg-surface px-3 py-1 text-xs font-semibold text-muted-foreground"
                        >
                            Fatura Fechada
                        </span>
                    </div>

                    <!-- Barra de progresso -->
                    <div class="mt-4">
                        <div class="mb-1 flex justify-between text-xs text-muted-foreground">
                            <span>{{ usagePercent }}% do limite de {{ formatCurrency(card.limit) }}</span>
                            <span>{{ formatCurrency(card.limit - total) }} disponível</span>
                        </div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-border">
                            <div
                                class="h-full rounded-full transition-all duration-700"
                                :class="usageColor"
                                :style="{ width: usagePercent + '%' }"
                            />
                        </div>
                    </div>

                    <!-- Período -->
                    <div class="mt-4 flex gap-6 text-xs text-muted-foreground">
                        <span>
                            Período:
                            <strong class="text-foreground">
                                {{ formatDate(period.start) }} a {{ formatDate(period.end) }}
                            </strong>
                        </span>
                        <span>
                            Vencimento:
                            <strong class="text-foreground">{{ formatDate(period.due) }}</strong>
                        </span>
                    </div>
                </div>

                <!-- Gastos por categoria -->
                <div class="rounded-2xl border border-border bg-card p-5">
                    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                        Por Categoria
                    </p>
                    <div v-if="byCategory.length" class="space-y-3">
                        <div
                            v-for="cat in byCategory.slice(0, 5)"
                            :key="cat.name"
                            class="flex items-center justify-between text-sm"
                        >
                            <span class="text-foreground truncate max-w-[120px]">{{ cat.name }}</span>
                            <span class="font-semibold text-destructive">
                                {{ formatCurrency(cat.total) }}
                            </span>
                        </div>
                    </div>
                    <p v-else class="text-xs text-muted-foreground">
                        Nenhum gasto neste período
                    </p>
                </div>
            </div>

            <!-- Releases list -->
            <div class="mt-6 rounded-2xl border border-border bg-card">
                <div class="flex items-center gap-2 border-b border-border px-6 py-4">
                    <Receipt class="h-4 w-4 text-muted-foreground" />
                    <h2 class="font-semibold text-foreground">
                        Lançamentos da Fatura
                    </h2>
                    <span class="ml-auto text-xs text-muted-foreground">
                        {{ releases.length }} lançamento(s)
                    </span>
                </div>

                <!-- Empty state -->
                <div
                    v-if="!releases.length"
                    class="flex flex-col items-center justify-center py-16 text-center"
                >
                    <Receipt class="mb-3 h-10 w-10 text-muted-foreground/40" />
                    <p class="text-sm font-medium text-foreground">
                        Nenhum lançamento neste período
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Período: {{ formatDate(period.start) }} a {{ formatDate(period.end) }}
                    </p>
                </div>

                <!-- Table -->
                <table v-else class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-border">
                            <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Descrição
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Categoria
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Data
                            </th>
                            <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-muted-foreground">
                                Valor
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="release in releases"
                            :key="release.id"
                            class="border-b border-border transition-colors hover:bg-surface/50 last:border-0"
                        >
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-foreground">{{ release.title }}</span>
                                    <span
                                        v-if="release.installment_number"
                                        class="inline-flex items-center rounded-full bg-primary/10 px-2 py-0.5 text-[10px] font-medium text-primary"
                                    >
                                        {{ release.installment_number }}/{{ release.total_installments }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <StatBadge :variant="release.type === 'revenue' ? 'revenue' : 'expense'">
                                    {{ release.category?.name ?? 'Sem Categoria' }}
                                </StatBadge>
                            </td>
                            <td class="px-4 py-4 text-muted-foreground">
                                {{ formatDate(release.date) }}
                            </td>
                            <td
                                class="px-6 py-4 text-right font-semibold"
                                :class="release.type === 'revenue' ? 'text-revenue' : 'text-destructive'"
                            >
                                {{ release.type === 'revenue' ? '+' : '-' }}
                                {{ formatCurrency(release.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>

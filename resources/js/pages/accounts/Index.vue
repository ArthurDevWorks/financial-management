<script setup lang="ts">
import ConfirmDialog from '@/components/ConfirmDialog.vue';
import PageHeader from '@/components/PageHeader.vue';
import PaginationLinks from '@/components/PaginationLinks.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import {
    ArrowDownRight,
    ArrowUpRight,
    Briefcase,
    Building2,
    CreditCard,
    Download,
    Edit3,
    PiggyBank,
    Plus,
    Search,
    Trash2,
    TrendingUp,
    Wallet,
    X,
} from 'lucide-vue-next';
import { ref } from 'vue';

interface Account {
    id: number;
    account: string;
    total: number;
    current_balance: number;
    type: string;
    revenue_sum: number;
    expense_sum: number;
    bank: {
        id: number;
        name: string;
        logo_url?: string | null;
    };
    agency?: string;
}

interface Stats {
    total_balance: number;
    avg_balance: number;
    total_count: number;
    total_revenues: number;
    total_expenses: number;
    count_by_type: Record<string, number>;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    accounts: {
        data: Account[];
        meta: PaginationMeta;
    };
    stats: Stats;
}>();

const search = ref('');
const showDeleteDialog = ref(false);
const accountToDelete = ref<Account | null>(null);

// Account type label map (matches AccountType enum)
const accountTypeLabels: Record<string, string> = {
    corrente: 'Conta Corrente',
    poupanca: 'Poupança',
    digital: 'Conta Digital',
    investimento: 'Conta de Investimento',
    salario: 'Conta Salário',
};

// Account type icon map
const accountTypeIcons: Record<string, any> = {
    corrente: CreditCard,
    poupanca: PiggyBank,
    digital: Building2,
    investimento: TrendingUp,
    salario: Briefcase,
};

// Account type color map
const accountTypeColors: Record<string, { badge: string; dot: string }> = {
    corrente: {
        badge: 'bg-primary/10 text-primary border-primary/20',
        dot: 'bg-primary',
    },
    poupanca: {
        badge: 'bg-revenue/10 text-revenue border-revenue/20',
        dot: 'bg-revenue',
    },
    digital: {
        badge: 'bg-investment/10 text-investment border-investment/20',
        dot: 'bg-investment',
    },
    investimento: {
        badge: 'bg-accent/10 text-accent border-accent/20',
        dot: 'bg-accent',
    },
    salario: {
        badge: 'bg-surface/50 text-muted-foreground border-border',
        dot: 'bg-muted-foreground',
    },
};

function getBankInitials(name: string): string {
    return name
        .split(' ')
        .map((w) => w.charAt(0))
        .slice(0, 2)
        .join('')
        .toUpperCase();
}

function getBankColor(name: string): string {
    const colors = [
        'bg-primary/15 text-primary',
        'bg-revenue/15 text-revenue',
        'bg-investment/15 text-investment',
        'bg-accent/15 text-accent',
        'bg-chart-3/15 text-chart-3',
        'bg-chart-4/15 text-chart-4',
    ];
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = (hash << 5) - hash + name.charCodeAt(i);
    }
    return colors[Math.abs(hash) % colors.length];
}

function formatCurrency(value: number | null | undefined): string {
    if (value === null || value === undefined || Number.isNaN(value))
        return 'R$ 0,00';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

function formatCompactCurrency(value: number | null | undefined): string {
    if (value === null || value === undefined || Number.isNaN(value))
        return 'R$ 0,00';
    // Abrevia apenas para bilhões+; abaixo disso mostra valor exato com centavos
    if (Math.abs(value) >= 1e9) {
        return `R$ ${(value / 1e9).toFixed(2)}B`;
    }
    if (Math.abs(value) >= 1e6) {
        return `R$ ${(value / 1e6).toFixed(2)}M`;
    }
    return formatCurrency(value);
}

function onSearch() {
    router.get('/accounts', search.value ? { search: search.value } : {}, {
        preserveState: true,
        preserveScroll: true,
    });
}

function clearSearch() {
    search.value = '';
    router.get('/accounts', {}, { preserveState: true });
}

function goToCreate() {
    router.visit('/accounts/create');
}

function goToEdit(account: Account) {
    router.visit(`/accounts/${account.id}/edit`);
}

function deleteAccount() {
    if (!accountToDelete.value) return;
    router.delete(`/accounts/${accountToDelete.value.id}`);
    accountToDelete.value = null;
}

// Search with enter key
function onSearchKeydown(e: KeyboardEvent) {
    if (e.key === 'Enter') onSearch();
}
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <!-- Header -->
            <PageHeader
                title="Contas"
                description="Gerencie suas contas bancárias e acompanhe seu saldo"
            >
                <template #actions>
                    <Button
                        variant="outline"
                        size="sm"
                        as="a"
                        href="/accounts/export"
                        target="_blank"
                    >
                        <Download class="h-4 w-4" />
                        Exportar
                    </Button>
                    <Button size="sm" @click="goToCreate">
                        <Plus class="h-4 w-4" />
                        Nova Conta
                    </Button>
                </template>
            </PageHeader>

            <!-- Search Bar -->
            <div class="relative mb-6">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    v-model="search"
                    placeholder="Buscar por banco ou número da conta... (Enter para buscar)"
                    class="h-10 pr-10 pl-10"
                    @keydown="onSearchKeydown"
                />
                <button
                    v-if="search"
                    class="absolute top-1/2 right-3 -translate-y-1/2 rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-surface hover:text-foreground"
                    :title="'Limpar busca'"
                    @click="clearSearch"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>

            <!-- Stats Row -->
            <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
                <div
                    class="animate-fade-in-up rounded-xl border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Saldo Total
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold tracking-tight"
                                :class="
                                    stats.total_balance >= 0
                                        ? 'text-revenue'
                                        : 'text-destructive'
                                "
                            >
                                {{ formatCompactCurrency(stats.total_balance) }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-primary/10 p-2 text-primary">
                            <Wallet class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ stats.total_count }}
                        {{ stats.total_count === 1 ? 'conta' : 'contas' }}
                    </p>
                </div>

                <div
                    class="animate-fade-in-up rounded-xl border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
                    style="animation-delay: 0.04s"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Saldo Médio
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold tracking-tight"
                                :class="
                                    stats.avg_balance >= 0
                                        ? 'text-primary'
                                        : 'text-destructive'
                                "
                            >
                                {{ formatCompactCurrency(stats.avg_balance) }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-investment/10 p-2 text-investment"
                        >
                            <TrendingUp class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">por conta</p>
                </div>

                <div
                    class="animate-fade-in-up rounded-xl border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
                    style="animation-delay: 0.08s"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Receitas
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold tracking-tight text-revenue"
                            >
                                {{
                                    formatCompactCurrency(stats.total_revenues)
                                }}
                            </p>
                        </div>
                        <div class="rounded-lg bg-revenue/10 p-2 text-revenue">
                            <ArrowUpRight class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        total lançado
                    </p>
                </div>

                <div
                    class="animate-fade-in-up rounded-xl border border-border bg-card p-4 transition-all hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
                    style="animation-delay: 0.12s"
                >
                    <div class="flex items-start justify-between">
                        <div>
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Despesas
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold tracking-tight text-destructive"
                            >
                                {{
                                    formatCompactCurrency(stats.total_expenses)
                                }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-destructive/10 p-2 text-destructive"
                        >
                            <ArrowDownRight class="h-5 w-5" />
                        </div>
                    </div>
                    <p class="mt-1 text-xs text-muted-foreground">
                        total lançado
                    </p>
                </div>
            </div>

            <!-- Account Cards -->
            <div
                v-if="accounts.data.length"
                class="grid grid-cols-1 gap-4 lg:grid-cols-2"
            >
                <div
                    v-for="(account, i) in accounts.data"
                    :key="account.id"
                    class="animate-fade-in-up group rounded-xl border border-border bg-card p-5 transition-all hover:-translate-y-0.5 hover:border-border/60 hover:shadow-md"
                    :style="{ animationDelay: `${i * 0.04}s` }"
                >
                    <div class="flex items-start gap-4">
                        <!-- Bank Logo -->
                        <img
                            v-if="account.bank?.logo_url"
                            :src="account.bank.logo_url"
                            :alt="`Logo ${account.bank.name}`"
                            class="h-12 w-12 shrink-0 rounded-xl object-contain"
                        />
                        <!-- Bank Avatar fallback -->
                        <div
                            v-else
                            class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl text-sm font-bold tracking-wide"
                            :class="getBankColor(account.bank?.name || '')"
                        >
                            {{ getBankInitials(account.bank?.name || '') }}
                        </div>

                        <!-- Account Info -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3
                                        class="text-base leading-tight font-semibold text-foreground"
                                    >
                                        {{ account.bank?.name || 'Banco' }}
                                    </h3>
                                    <p
                                        class="mt-0.5 text-sm text-muted-foreground"
                                    >
                                        {{
                                            account.agency
                                                ? `${account.agency} / `
                                                : ''
                                        }}{{ account.account }}
                                    </p>
                                </div>

                                <!-- Actions (always visible on mobile, on hover on desktop) -->
                                <div
                                    class="flex shrink-0 gap-1 opacity-100 transition-opacity lg:opacity-0 lg:group-hover:opacity-100"
                                >
                                    <button
                                        class="rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-investment/10 hover:text-investment"
                                        title="Editar"
                                        @click="goToEdit(account)"
                                    >
                                        <Edit3 class="h-4 w-4" />
                                    </button>
                                    <button
                                        class="rounded-md p-1.5 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                        title="Excluir"
                                        @click="accountToDelete = account; showDeleteDialog = true"
                                    >
                                        <Trash2 class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <div class="mt-3 flex items-center gap-2">
                                <!-- Type Badge -->
                                <span
                                    class="inline-flex items-center gap-1 rounded-md border px-2 py-0.5 text-[11px] font-semibold"
                                    :class="
                                        accountTypeColors[account.type]
                                            ?.badge ||
                                        'border-border bg-surface/50 text-muted-foreground'
                                    "
                                >
                                    <component
                                        :is="
                                            accountTypeIcons[account.type] ||
                                            Building2
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{
                                        accountTypeLabels[account.type] ||
                                        account.type
                                    }}
                                </span>

                                <!-- Revenue/Expense indicator -->
                                <span
                                    v-if="
                                        account.revenue_sum > 0 ||
                                        account.expense_sum > 0
                                    "
                                    class="flex items-center gap-2 text-[11px] text-muted-foreground"
                                >
                                    <span
                                        v-if="account.revenue_sum > 0"
                                        class="flex items-center gap-0.5 text-revenue"
                                    >
                                        <ArrowUpRight class="h-3 w-3" />
                                        {{
                                            formatCompactCurrency(
                                                account.revenue_sum,
                                            )
                                        }}
                                    </span>
                                    <span
                                        v-if="account.expense_sum > 0"
                                        class="flex items-center gap-0.5 text-destructive"
                                    >
                                        <ArrowDownRight class="h-3 w-3" />
                                        {{
                                            formatCompactCurrency(
                                                account.expense_sum,
                                            )
                                        }}
                                    </span>
                                </span>
                            </div>

                            <!-- Balance -->
                            <div
                                class="mt-4 flex items-center justify-between border-t border-border pt-3"
                            >
                                <div>
                                    <p
                                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Saldo Atual
                                    </p>
                                    <p
                                        class="mt-0.5 text-xl font-bold tracking-tight"
                                        :class="
                                            account.current_balance >= 0
                                                ? 'text-revenue'
                                                : 'text-destructive'
                                        "
                                    >
                                        {{
                                            formatCurrency(
                                                account.current_balance,
                                            )
                                        }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <p
                                        class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                                    >
                                        Saldo Inicial
                                    </p>
                                    <p
                                        class="mt-0.5 text-sm font-medium text-muted-foreground"
                                    >
                                        {{ formatCurrency(account.total) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div
                v-else
                class="rounded-xl border border-border bg-card py-16 text-center"
            >
                <Wallet
                    class="mx-auto mb-4 h-12 w-12 text-muted-foreground/30"
                />
                <p class="text-base font-semibold text-foreground">
                    Nenhuma conta encontrada
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{
                        search
                            ? 'Nenhum resultado para sua busca.'
                            : 'Crie uma conta bancária para começar.'
                    }}
                </p>
                <Button class="mt-6" @click="goToCreate" v-if="!search">
                    <Plus class="h-4 w-4" />
                    Nova Conta
                </Button>
                <Button
                    variant="outline"
                    class="mt-6"
                    @click="clearSearch"
                    v-else
                >
                    <X class="h-4 w-4" />
                    Limpar Busca
                </Button>
            </div>

            <!-- Pagination -->
            <PaginationLinks
                v-if="accounts.meta && accounts.data.length"
                :meta="accounts.meta"
                class="mt-6"
            />
        </div>

        <ConfirmDialog
            v-model:open="showDeleteDialog"
            :title="`Tem certeza que deseja excluir a conta \u201c${accountToDelete?.account}\u201d?`"
            description="Esta ação não pode ser desfeita. Todos os lançamentos vinculados a esta conta perderão a referência."
            confirm-label="Sim, excluir"
            variant="destructive"
            @confirm="deleteAccount"
            @cancel="accountToDelete = null"
        />
    </AppLayout>
</template>

<script setup lang="ts">
import CrudActions from '@/components/CrudActions.vue';
import PageHeader from '@/components/PageHeader.vue';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { CreditCard, Plus } from 'lucide-vue-next';

interface Invoice {
    total: number;
    month: number;
    year: number;
    period: { start: string; end: string; due: string };
}

interface Card {
    id: number;
    name: string;
    color: string;
    limit: number;
    closing_day: number;
    due_day: number;
    bank: { name: string } | null;
    current_invoice: Invoice;
}

defineProps<{ cards: Card[] }>();

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);

const monthName = (month: number, year: number) => {
    const date = new Date(year, month - 1, 1);
    return date.toLocaleDateString('pt-BR', { month: 'long', year: 'numeric' });
};

const usagePercent = (total: number, limit: number) => {
    if (!limit) return 0;
    return Math.min(100, Math.round((total / limit) * 100));
};

const usageColor = (percent: number) => {
    if (percent >= 90) return 'bg-destructive';
    if (percent >= 70) return 'bg-accent';
    return 'bg-primary';
};

const goToInvoice = (card: Card) => {
    const { month, year } = card.current_invoice;
    router.visit(`/credit-cards/${card.id}/invoices/${year}/${month}`);
};

const editCard = (card: Card) => {
    router.visit(`/credit-cards/${card.id}/edit`);
};

const deleteCard = (card: Card) => {
    router.delete(`/credit-cards/${card.id}`);
};

const createCard = () => {
    router.visit('/credit-cards/create');
};

const formatDate = (dateStr: string) => {
    if (!dateStr) return '';
    const [y, m, d] = dateStr.split('-');
    return `${d}/${m}/${y}`;
};
</script>

<template>
    <AppLayout>
        <div class="p-8">
            <PageHeader
                title="Cartões de Crédito"
                description="Acompanhe seus cartões e faturas abertas"
            >
                <template #actions>
                    <Button @click="createCard">
                        <Plus class="h-4 w-4" />
                        Novo Cartão
                    </Button>
                </template>
            </PageHeader>

            <!-- Empty state -->
            <div
                v-if="!cards.length"
                class="mt-8 flex flex-col items-center justify-center rounded-xl border border-dashed border-border bg-card py-20 text-center"
            >
                <CreditCard class="mb-4 h-12 w-12 text-muted-foreground/40" />
                <p class="text-base font-semibold text-foreground">
                    Nenhum cartão cadastrado
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Cadastre seu primeiro cartão de crédito para controlar suas
                    faturas
                </p>
                <Button class="mt-6" @click="createCard">
                    <Plus class="h-4 w-4" />
                    Novo Cartão
                </Button>
            </div>

            <!-- Cards grid -->
            <div v-else class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="card in cards"
                    :key="card.id"
                    class="group relative flex flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-sm transition-all hover:border-border/80 hover:shadow-lg"
                >
                    <!-- Color stripe -->
                    <div
                        class="h-1.5 w-full"
                        :style="{ backgroundColor: card.color }"
                    />

                    <div class="flex flex-1 flex-col p-5">
                        <!-- Header -->
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-xl"
                                    :style="{
                                        backgroundColor: card.color + '22',
                                        color: card.color,
                                    }"
                                >
                                    <CreditCard class="h-5 w-5" />
                                </div>
                                <div>
                                    <p
                                        class="leading-tight font-semibold text-foreground"
                                    >
                                        {{ card.name }}
                                    </p>
                                    <p
                                        class="mt-0.5 text-xs text-muted-foreground"
                                    >
                                        {{ card.bank?.name ?? 'Sem banco' }}
                                    </p>
                                </div>
                            </div>
                            <CrudActions
                                delete-confirm-message="Tem certeza que deseja remover este cartão? Os lançamentos vinculados não serão excluídos."
                                @edit="editCard(card)"
                                @delete="deleteCard(card)"
                            />
                        </div>

                        <!-- Fatura atual -->
                        <div class="mt-5 rounded-xl bg-surface p-4">
                            <p
                                class="text-[11px] font-semibold tracking-wider text-muted-foreground uppercase"
                            >
                                Fatura
                                {{
                                    monthName(
                                        card.current_invoice.month,
                                        card.current_invoice.year,
                                    )
                                }}
                            </p>
                            <p class="mt-1 text-2xl font-bold text-foreground">
                                {{ formatCurrency(card.current_invoice.total) }}
                            </p>

                            <!-- Progress bar -->
                            <div class="mt-3">
                                <div
                                    class="mb-1 flex justify-between text-[11px] text-muted-foreground"
                                >
                                    <span
                                        >{{
                                            usagePercent(
                                                card.current_invoice.total,
                                                card.limit,
                                            )
                                        }}% do limite</span
                                    >
                                    <span>{{
                                        formatCurrency(card.limit)
                                    }}</span>
                                </div>
                                <div
                                    class="h-1.5 w-full overflow-hidden rounded-full bg-border"
                                >
                                    <div
                                        class="h-full rounded-full transition-all duration-500"
                                        :class="
                                            usageColor(
                                                usagePercent(
                                                    card.current_invoice.total,
                                                    card.limit,
                                                ),
                                            )
                                        "
                                        :style="{
                                            width:
                                                usagePercent(
                                                    card.current_invoice.total,
                                                    card.limit,
                                                ) + '%',
                                        }"
                                    />
                                </div>
                            </div>

                            <!-- Datas -->
                            <div
                                class="mt-3 flex gap-4 text-[11px] text-muted-foreground"
                            >
                                <span>
                                    Fechamento
                                    <strong class="text-foreground">
                                        {{
                                            formatDate(
                                                card.current_invoice.period.end,
                                            )
                                        }}
                                    </strong>
                                </span>
                                <span>
                                    Vencimento
                                    <strong class="text-foreground">
                                        {{
                                            formatDate(
                                                card.current_invoice.period.due,
                                            )
                                        }}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <Button
                            variant="outline"
                            size="sm"
                            class="mt-4 w-full"
                            @click="goToInvoice(card)"
                        >
                            Ver Fatura Completa
                        </Button>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

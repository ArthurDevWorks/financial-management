<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useDcf } from '@/composables/useDcf';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Calculator } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string;
    current_price?: number | null;
    free_cash_flow?: number | null;
    roe?: number | null;
    payout?: number | null;
    total_shares?: number | null;
    logo_url?: string | null;
    asset_type?: string;
}

interface DcfAssumptions {
    current_fcf?: number | string | null;
    roe?: number | string | null;
    payout?: number | string | null;
    discount_rate?: number | string | null;
    terminal_growth_rate?: number | string | null;
    projection_years?: number | string | null;
    total_shares?: number | string | null;
    current_price_per_share?: number | string | null;
    growth_rates?: number[];
}

interface DcfValuation {
    id: number;
    method: string;
    assumptions: DcfAssumptions;
    summary: Record<string, number | null>;
    calculated_at: string;
}

const props = defineProps<{
    asset: Asset | null;
    assets: Asset[];
    valuation: DcfValuation | null;
    defaultAssumptions?: Record<string, unknown> | null;
}>();

const defaults = (props.valuation?.assumptions ??
    props.defaultAssumptions ??
    {}) as DcfAssumptions;

const form = useForm<{
    asset_id: string;
    current_fcf: number | string;
    roe: number | string;
    payout: number | string;
    discount_rate: number | string;
    terminal_growth_rate: number | string;
    projection_years: number | string;
    total_shares: number | string;
    current_price_per_share: number | string;
    growth_rates: number[];
}>({
    asset_id: props.asset?.id?.toString() ?? '',
    current_fcf: defaults.current_fcf ?? props.asset?.free_cash_flow ?? '',
    roe: defaults.roe ?? props.asset?.roe ?? 15,
    payout: defaults.payout ?? props.asset?.payout ?? 50,
    discount_rate: defaults.discount_rate ?? 12.5,
    terminal_growth_rate: defaults.terminal_growth_rate ?? 3,
    projection_years: defaults.projection_years ?? 5,
    total_shares: defaults.total_shares ?? props.asset?.total_shares ?? '',
    current_price_per_share:
        defaults.current_price_per_share ?? props.asset?.current_price ?? '',
    growth_rates: defaults.growth_rates ?? Array(5).fill(0),
});

const isEditing = computed(() => !!props.valuation);

const projectionYears = computed(() => {
    const years = Math.max(3, Math.min(15, Number(form.projection_years) || 5));
    return years;
});

watch(
    projectionYears,
    (newLen, oldLen) => {
        if (newLen > oldLen) {
            while (form.growth_rates.length < newLen) {
                form.growth_rates.push(defaultGrowthRate.value);
            }
        } else {
            form.growth_rates = form.growth_rates.slice(0, newLen);
        }
    },
    { immediate: false },
);

const fcfBase = computed(() => Number(form.current_fcf) || 0);
const totalShares = computed(() => Number(form.total_shares) || 0);
const discountRate = computed(() => Number(form.discount_rate) || 0);
const terminalGrowthRate = computed(
    () => Number(form.terminal_growth_rate) || 0,
);
const currentPrice = computed(() => Number(form.current_price_per_share) || 0);

const defaultGrowthRate = computed(() => {
    const roe = Number(form.roe) || 0;
    const payout = Number(form.payout) || 0;
    return Math.round((1 - payout / 100) * roe * 100) / 100;
});

const hasSavedGrowthRates = !!defaults.growth_rates?.length;

watch(
    [() => form.roe, () => form.payout],
    () => {
        form.growth_rates = form.growth_rates.map(() => defaultGrowthRate.value);
    },
    { immediate: !hasSavedGrowthRates },
);

const { fairPrice, upside, marginOfSafety, projectedFcfs } = useDcf({
    freeCashFlow: fcfBase,
    growthRates: computed(() => form.growth_rates.map(Number)),
    discountRate,
    terminalGrowthRate,
    projectionYears,
    totalShares,
    currentPrice,
    netDebt: ref(0),
});

const goBack = () => router.visit('/valuations');

const submit = () => {
    const payload = {
        asset_id: Number(form.asset_id),
        current_fcf: Number(form.current_fcf),
        roe: Number(form.roe),
        payout: Number(form.payout),
        discount_rate: Number(form.discount_rate),
        terminal_growth_rate: Number(form.terminal_growth_rate),
        projection_years: Number(form.projection_years),
        total_shares: Number(form.total_shares),
        current_price_per_share: Number(form.current_price_per_share) || null,
        growth_rates: form.growth_rates
            .slice(0, projectionYears.value)
            .map(Number),
    };

    if (isEditing.value) {
        form.put(`/dcf/${props.valuation!.id}`, {
            ...payload,
        });
    } else {
        form.post('/dcf', {
            ...payload,
        });
    }
};

function formatCurrency(value: number | null | undefined) {
    if (value === null || value === undefined || !Number.isFinite(value))
        return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

function formatPercent(value: number | null | undefined) {
    if (value === null || value === undefined || !Number.isFinite(value))
        return '—';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
}

const currentYear = new Date().getFullYear();
</script>

<template>
    <AppLayout>
        <div class="p-6 lg:p-8">
            <div class="mb-2">
                <button
                    type="button"
                    class="-ml-2 inline-flex items-center gap-1 text-sm text-muted-foreground transition-colors hover:text-foreground"
                    @click="goBack"
                >
                    <ArrowLeft class="h-4 w-4" />
                    Voltar
                </button>
            </div>

            <PageHeader
                :title="isEditing ? 'Editar DCF' : 'Fluxo de Caixa Descontado'"
                description="Valor intrínseco com base no fluxo de caixa livre projetado"
            />

            <div class="mt-6 grid gap-6 xl:grid-cols-3">
                <!-- Left: Premissas -->
                <div class="space-y-6 xl:col-span-1">
                    <SectionCard title="Ativo">
                        <div>
                            <Label>Selecione o Ativo</Label>
                            <select
                                v-model="form.asset_id"
                                class="mt-1 h-[42px] w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                                :disabled="isEditing"
                            >
                                <option value="">Selecione...</option>
                                <option
                                    v-for="a in assets"
                                    :key="a.id"
                                    :value="a.id"
                                >
                                    {{ a.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.asset_id" />
                        </div>
                    </SectionCard>

                    <SectionCard title="Premissas">
                        <div class="space-y-4">
                            <div>
                                <Label>FCF Base (R$)</Label>
                                <Input
                                    v-model="form.current_fcf"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.current_fcf"
                                />
                            </div>
                            <div>
                                <Label>Total de Ações</Label>
                                <Input
                                    v-model="form.total_shares"
                                    type="number"
                                    step="1"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.total_shares"
                                />
                            </div>
                            <div>
                                <Label>Preço Atual (R$)</Label>
                                <Input
                                    v-model="form.current_price_per_share"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="
                                        form.errors.current_price_per_share
                                    "
                                />
                            </div>
                            <div>
                                <Label>ROE (%)</Label>
                                <Input
                                    v-model="form.roe"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    class="mt-1"
                                />
                                <InputError :message="form.errors.roe" />
                            </div>
                            <div>
                                <Label>Payout (%)</Label>
                                <Input
                                    v-model="form.payout"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="100"
                                    class="mt-1"
                                />
                                <InputError :message="form.errors.payout" />
                            </div>
                            <div>
                                <Label>Taxa de Desconto (Ke, %)</Label>
                                <Input
                                    v-model="form.discount_rate"
                                    type="number"
                                    step="0.1"
                                    min="0.01"
                                    max="100"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.discount_rate"
                                />
                            </div>
                            <div>
                                <Label>Crescimento Perpetuidade (%)</Label>
                                <Input
                                    v-model="form.terminal_growth_rate"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.terminal_growth_rate"
                                />
                            </div>
                            <div>
                                <Label>Anos de Projeção</Label>
                                <Input
                                    v-model="form.projection_years"
                                    type="number"
                                    step="1"
                                    min="3"
                                    max="15"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.projection_years"
                                />
                            </div>
                        </div>
                    </SectionCard>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="goBack"
                            >Cancelar</Button
                        >
                        <Button
                            @click="submit"
                            :disabled="
                                form.processing ||
                                !form.asset_id ||
                                fcfBase <= 0 ||
                                totalShares <= 0
                            "
                        >
                            <Calculator class="h-4 w-4" />
                            {{ isEditing ? 'Atualizar' : 'Calcular' }}
                        </Button>
                    </div>
                </div>

                <!-- Right: Results -->
                <div class="space-y-6 xl:col-span-2">
                    <!-- Projeção de FCF -->
                    <SectionCard title="Projeção de Fluxo de Caixa">
                        <div
                            class="mb-4 flex items-center gap-3 rounded-lg border border-primary/20 bg-primary/5 px-4 py-3"
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
                                    {{ defaultGrowthRate }}%
                                    <span
                                        class="text-xs font-normal text-muted-foreground"
                                    >{{ `ROE (${form.roe}%) × (1 − Payout (${form.payout}%))` }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                        >
                                            Ano
                                        </th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                        >
                                            <div>Crescimento (%)</div>
                                            <div
                                                class="normal-case tracking-normal text-muted-foreground/70"
                                            >
                                                Taxa Esperada
                                            </div>
                                        </th>
                                        <th
                                            class="px-4 py-2 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase"
                                        >
                                            FCF Projetado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        class="border-b border-border bg-muted/30"
                                    >
                                        <td
                                            class="px-4 py-2 font-medium text-foreground"
                                        >
                                            Base
                                        </td>
                                        <td
                                            class="px-4 py-2 text-muted-foreground"
                                        >
                                            —
                                        </td>
                                        <td
                                            class="px-4 py-2 text-right font-medium text-foreground"
                                        >
                                            {{ formatCurrency(fcfBase) }}
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(item, index) in projectedFcfs"
                                        :key="index"
                                        class="border-b border-border last:border-0"
                                    >
                                        <td class="px-4 py-2 text-foreground">
                                            {{ currentYear + index + 1 }}
                                        </td>
                                        <td class="px-4 py-2">
                                            <Input
                                                v-model.number="
                                                    form.growth_rates[index]
                                                "
                                                type="number"
                                                step="0.1"
                                                class="h-8 w-24 text-right text-sm"
                                            />
                                        </td>
                                        <td
                                            class="px-4 py-2 text-right font-medium text-foreground"
                                        >
                                            {{ formatCurrency(item.fcf) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </SectionCard>

                    <SectionCard title="Resultados do DCF">
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="overflow-hidden rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Valor Intrínseco / Ação
                                </p>
                                <p class="mt-1 text-2xl font-bold text-primary">
                                    {{ formatCurrency(fairPrice) }}
                                </p>
                                <div
                                    v-if="
                                        upside !== null &&
                                        Number.isFinite(upside)
                                    "
                                    class="mt-1"
                                >
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full px-3 py-0.5 text-xs font-semibold"
                                        :class="
                                            upside >= 0
                                                ? 'bg-revenue/10 text-revenue'
                                                : 'bg-expense/10 text-expense'
                                        "
                                    >
                                        {{ formatPercent(upside) }} vs. preço
                                        atual
                                    </span>
                                </div>
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
                                        (marginOfSafety ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-expense'
                                    "
                                >
                                    {{ formatPercent(marginOfSafety) }}
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
                                        (upside ?? 0) >= 0
                                            ? 'text-revenue'
                                            : 'text-expense'
                                    "
                                >
                                    {{ formatPercent(upside) }}
                                </p>
                            </div>
                        </div>
                    </SectionCard>
                </div>
            </div>

            <div
                v-if="!assets.length"
                class="mt-6 rounded-xl border border-border bg-card p-6 text-center"
            >
                <p class="text-sm text-muted-foreground">
                    Nenhum ativo cadastrado. Adicione um ativo primeiro para
                    usar esta ferramenta.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

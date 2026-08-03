<script setup lang="ts">
import CurrencyInput from '@/components/CurrencyInput.vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { useDcf } from '@/composables/useDcf';
import { router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calculator,
    ShieldCheck,
    TrendingUp,
    Wallet,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string;
    current_price?: number | string | null;
    net_income?: number | string | null;
    total_shares?: number | string | null;
    fcf?: number | string | null;
    logo_url?: string | null;
    asset_type?: string;
}

interface DcfAssumptions {
    discount_rate?: number | string;
    perpetuity_growth_rate?: number | string;
    projection_years?: number;
    growth_rates?: number[];
    fcf_base?: number | string;
    current_price?: number | string;
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
    defaultAssumptions?: DcfAssumptions | null;
}>();

const selectedId = ref(props.asset?.id?.toString() ?? '');

const assumptions = props.valuation?.assumptions ?? props.defaultAssumptions ?? {};

const form = useForm({
    asset_id: props.asset?.id?.toString() ?? '',
    discount_rate: assumptions.discount_rate?.toString() ?? '10',
    perpetuity_growth_rate: assumptions.perpetuity_growth_rate?.toString() ?? '3',
    projection_years: (assumptions.projection_years ?? 5).toString(),
    fcf_base: assumptions.fcf_base?.toString() ?? '',
    current_price: assumptions.current_price?.toString() ?? '',
});

const dcfProjectionYears = ref(
    assumptions.projection_years ?? 5,
);

const dcfGrowthRates = ref<number[]>(
    assumptions.growth_rates?.slice(0, dcfProjectionYears.value) ??
    Array(dcfProjectionYears.value).fill(5),
);

watch(dcfProjectionYears, (newLen, oldLen) => {
    if (newLen > oldLen) {
        while (dcfGrowthRates.value.length < newLen) {
            dcfGrowthRates.value.push(5);
        }
    } else {
        dcfGrowthRates.value = dcfGrowthRates.value.slice(0, newLen);
    }
});

const fcfBase = computed(() => {
    const val = parseFloat(form.fcf_base);
    return isNaN(val) || val < 0 ? 0 : val;
});

const discountRate = computed(() => {
    const val = parseFloat(form.discount_rate);
    return isNaN(val) || val < 0 ? 0 : val;
});

const perpetuityGrowthRate = computed(() => {
    const val = parseFloat(form.perpetuity_growth_rate);
    return isNaN(val) || val < 0 ? 0 : val;
});

const currentPrice = computed(() => {
    const val = parseFloat(form.current_price);
    return isNaN(val) || val < 0 ? 0 : val;
});

const { fairValue, marginOfSafety, projectedFcfs } = useDcf({
    freeCashFlow: fcfBase,
    growthRates: dcfGrowthRates,
    discountRate,
    terminalGrowthRate: perpetuityGrowthRate,
    projectionYears: dcfProjectionYears,
    totalShares: ref(1),
    currentPrice,
    netDebt: ref(0),
});

const upside = computed(() => {
    const price = parseFloat(form.current_price);
    if (isNaN(price) || price <= 0 || fairValue.value <= 0) return null;
    return ((fairValue.value - price) / price) * 100;
});

watch(selectedId, (id) => {
    if (id) {
        router.visit(`/dcf?asset_id=${id}`, {
            preserveState: true,
            replace: true,
        });
    } else {
        router.visit('/dcf', { preserveState: true, replace: true });
    }
});

watch(
    () => [props.asset?.id, props.asset?.current_price, props.asset?.fcf] as const,
    ([id, currentPrice, fcf]) => {
        form.asset_id = id?.toString() ?? '';

        if (!props.valuation) {
            form.current_price = currentPrice?.toString() ?? '';
            form.fcf_base = fcf?.toString() ?? '';
        }
    },
);

const parseNumber = (value: string | number) => {
    if (typeof value === 'number') return Number.isFinite(value) ? value : 0;
    const numeric = value.trim().replace(/[^\d,.-]/g, '');
    const lastComma = numeric.lastIndexOf(',');
    const lastDot = numeric.lastIndexOf('.');
    let normalized = numeric;
    if (lastComma >= 0 && lastDot >= 0) {
        normalized = lastComma > lastDot
            ? numeric.replace(/\./g, '').replace(',', '.')
            : numeric.replace(/,/g, '');
    } else if (lastComma >= 0) {
        normalized = numeric.replace(/\./g, '').replace(',', '.');
    } else if ((numeric.match(/\./g) ?? []).length > 1) {
        normalized = numeric.replace(/\./g, '');
    }
    const parsed = parseFloat(normalized);
    return Number.isFinite(parsed) ? parsed : 0;
};

const formatCurrency = (value: number) =>
    new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);

const formatPercent = (value: number) => `${value.toFixed(2)}%`;

const currentYear = new Date().getFullYear();

const goBack = () => router.visit('/valuations/create');

const submit = () => {
    if (props.valuation) {
        form.put(`/dcf/${props.valuation.id}`);
        return;
    }
    form.post('/dcf');
};
</script>

<template>
    <AppLayout>
        <div class="p-8">
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
                title="Fluxo de Caixa Descontado"
                description="Calcule o valor intrínseco com base no fluxo de caixa projetado"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Wallet class="h-4 w-4 text-muted-foreground" />
                        <select
                            v-model="selectedId"
                            :disabled="!!valuation"
                            class="h-9 rounded-md border border-border bg-surface py-1 pr-8 pl-3 text-sm text-foreground transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="">Selecione um ativo...</option>
                            <option v-for="item in assets" :key="item.id" :value="item.id">
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                </template>
            </PageHeader>

            <form class="grid grid-cols-1 gap-6 lg:grid-cols-3" @submit.prevent="submit">
                <!-- Inputs -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card">
                        <div class="border-b border-border px-6 py-4">
                            <h3 class="text-base font-semibold text-foreground">
                                Parâmetros do Cálculo
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Preencha os campos para calcular o valor intrínseco
                            </p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Taxa de Desconto (%)</Label>
                                    </div>
                                    <Input
                                        v-model="form.discount_rate"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        placeholder="Ex: 10"
                                        class="mt-1.5"
                                    />
                                    <InputError :message="form.errors.discount_rate" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Crescimento Perpetuidade (%)</Label>
                                    </div>
                                    <Input
                                        v-model="form.perpetuity_growth_rate"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        placeholder="Ex: 3"
                                        class="mt-1.5"
                                    />
                                    <InputError :message="form.errors.perpetuity_growth_rate" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Anos de Projeção</Label>
                                    </div>
                                    <Input
                                        v-model.number="dcfProjectionYears"
                                        type="number"
                                        min="1"
                                        max="20"
                                        placeholder="Ex: 5"
                                        class="mt-1.5"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>FCF Base (R$)</Label>
                                    </div>
                                    <CurrencyInput
                                        v-model="form.fcf_base"
                                        :error="form.errors.fcf_base"
                                        placeholder="0,00"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Preço Atual (R$)</Label>
                                    </div>
                                    <CurrencyInput
                                        v-model="form.current_price"
                                        :error="form.errors.current_price"
                                        placeholder="0,00"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabela de Projeção -->
                    <div class="mt-6 rounded-xl border border-border bg-card">
                        <div class="border-b border-border px-6 py-4">
                            <h3 class="text-base font-semibold text-foreground">
                                Projeção de Fluxo de Caixa
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Taxas de crescimento por ano e valores projetados
                            </p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-border">
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                            Ano
                                        </th>
                                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                            Crescimento (%)
                                        </th>
                                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-muted-foreground">
                                            FCF Projetado
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="border-b border-border bg-muted/30">
                                        <td class="px-4 py-2.5 font-medium text-foreground">
                                            Base
                                        </td>
                                        <td class="px-4 py-2.5 text-muted-foreground">—</td>
                                        <td class="px-4 py-2.5 text-right font-medium text-foreground">
                                            {{ fcfBase > 0 ? formatCurrency(fcfBase) : '—' }}
                                        </td>
                                    </tr>
                                    <tr
                                        v-for="(item, index) in projectedFcfs"
                                        :key="index"
                                        class="border-b border-border last:border-0"
                                    >
                                        <td class="px-4 py-2.5 text-foreground">
                                            {{ currentYear + index + 1 }}
                                        </td>
                                        <td class="px-4 py-2.5">
                                            <Input
                                                v-model.number="dcfGrowthRates[index]"
                                                type="number"
                                                step="0.1"
                                                class="h-8 w-24 text-right text-sm"
                                            />
                                        </td>
                                        <td class="px-4 py-2.5 text-right font-medium text-foreground">
                                            {{ formatCurrency(item.fcf) }}
                                        </td>
                                    </tr>
                                    <tr class="bg-muted/20 font-semibold">
                                        <td class="px-4 py-2.5 text-foreground" colspan="2">
                                            Total Projetado
                                        </td>
                                        <td class="px-4 py-2.5 text-right text-foreground">
                                            {{ formatCurrency(projectedFcfs.reduce((sum, item) => sum + item.fcf, 0)) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Upside badge -->
                    <div
                        v-if="upside !== null"
                        class="mt-6 rounded-xl border border-border bg-card p-6"
                    >
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-foreground">
                                Potencial de Valorização
                            </span>
                            <span
                                class="rounded-full px-3 py-1 text-xs font-bold"
                                :class="upside >= 0 ? 'bg-revenue/10 text-revenue' : 'bg-expense/10 text-expense'"
                            >
                                {{ upside >= 0 ? '+' : '' }}{{ formatPercent(upside) }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm text-muted-foreground">
                            {{ upside >= 0
                                ? 'O ativo está sendo negociado abaixo do valor intrínseco.'
                                : 'O ativo está sendo negociado acima do valor intrínseco.' }}
                        </p>
                    </div>
                </div>

                <!-- Results -->
                <div class="space-y-4">
                    <SummaryCard
                        label="Valor Intrínseco"
                        :value="fcfBase > 0 ? formatCurrency(fairValue) : '—'"
                        variant="investment"
                        :icon="Calculator"
                    />

                    <SummaryCard
                        label="Margem de Segurança"
                        :value="fcfBase > 0 && upside !== null ? formatPercent(marginOfSafety) : '—'"
                        :variant="marginOfSafety >= 0 ? 'revenue' : 'expense'"
                        :icon="ShieldCheck"
                        :trend="fcfBase > 0 && upside !== null ? parseFloat(marginOfSafety.toFixed(2)) : undefined"
                    />

                    <SummaryCard
                        label="Upside"
                        :value="upside !== null ? (upside >= 0 ? '+' : '') + formatPercent(upside) : '—'"
                        :variant="upside !== null && upside >= 0 ? 'profit' : 'expense'"
                        :icon="TrendingUp"
                        :trend="upside !== null ? parseFloat(upside.toFixed(2)) : undefined"
                    />

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing || fcfBase <= 0 || !form.asset_id"
                    >
                        {{
                            form.processing
                                ? 'Salvando...'
                                : valuation
                                  ? 'Atualizar valuation'
                                  : 'Salvar simulação'
                        }}
                    </Button>
                </div>
            </form>

            <div
                v-if="!assets.length"
                class="mt-6 rounded-xl border border-border bg-card p-6 text-center"
            >
                <p class="text-sm text-muted-foreground">
                    Nenhum ativo cadastrado. Adicione um ativo primeiro para usar esta ferramenta.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

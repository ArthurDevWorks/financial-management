<script setup lang="ts">
import CurrencyInput from '@/components/CurrencyInput.vue';
import InputError from '@/components/InputError.vue';
import NumberInput from '@/components/NumberInput.vue';
import PageHeader from '@/components/PageHeader.vue';
import SummaryCard from '@/components/SummaryCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Banknote,
    Calculator,
    PiggyBank,
    ShieldCheck,
    TrendingUp,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string;
    current_price?: number | string | null;
    net_income?: number | string | null;
    total_shares?: number | string | null;
    roe?: number | string | null;
    payout?: number | string | null;
    logo_url?: string | null;
    asset_type?: string;
}

interface PrecoTetoAssumptions {
    desired_yield?: number | string;
    projected_net_income?: number | string;
    total_shares?: number | string;
    projected_growth_rate?: number | string;
    current_price_per_share?: number | string;
    roe?: number | string;
    payout?: number | string;
}

interface PrecoTetoValuation {
    id: number;
    method: string;
    assumptions: PrecoTetoAssumptions;
    summary: Record<string, number | null>;
    calculated_at: string;
}

const props = defineProps<{
    asset: Asset | null;
    assets: Asset[];
    valuation: PrecoTetoValuation | null;
    defaultAssumptions?: PrecoTetoAssumptions | null;
}>();

const selectedId = ref(props.asset?.id?.toString() ?? '');

const toInputValue = (value: Asset['current_price'] | undefined) => {
    return value === null || value === undefined ? '' : value.toString();
};

const toFormValue = (value: string | number | null | undefined) => {
    return value === null || value === undefined ? '' : value.toString();
};

const assumptions =
    props.valuation?.assumptions ?? props.defaultAssumptions ?? {};

const form = useForm({
    asset_id: props.asset?.id?.toString() ?? '',
    desired_yield: toFormValue(assumptions.desired_yield),
    projected_net_income: toFormValue(assumptions.projected_net_income),
    total_shares: toFormValue(assumptions.total_shares),
    projected_growth_rate:
        toFormValue(assumptions.projected_growth_rate) || '0',
    current_price_per_share:
        toFormValue(assumptions.current_price_per_share) ||
        toInputValue(props.asset?.current_price),
    roe: toFormValue(assumptions.roe) ||
        toFormValue(props.asset?.roe) ||
        '0',
    payout: toFormValue(assumptions.payout) ||
        toFormValue(props.asset?.payout) ||
        '0',
});

const defaultGrowthRate = computed(() => {
    const roe = parseNumber(form.roe);
    const payout = parseNumber(form.payout);
    return Math.round((1 - payout / 100) * roe * 100) / 100;
});

const hasSavedGrowthRate = !!assumptions.projected_growth_rate;

watch(
    [() => form.roe, () => form.payout],
    () => {
        form.projected_growth_rate = defaultGrowthRate.value.toString();
    },
    { immediate: !hasSavedGrowthRate },
);

watch(selectedId, (id) => {
    if (id) {
        router.visit(`/preco-teto?asset_id=${id}`, {
            preserveState: true,
            replace: true,
        });
    } else {
        router.visit('/preco-teto', { preserveState: true, replace: true });
    }
});

watch(
    () => [props.asset?.id, props.asset?.current_price] as const,
    ([id, currentPrice]) => {
        form.asset_id = id?.toString() ?? '';

        if (!props.valuation) {
            form.current_price_per_share = toInputValue(currentPrice);
        }
    },
);

const parseNumber = (value: string | number) => {
    if (typeof value === 'number') {
        return Number.isFinite(value) ? value : 0;
    }

    const numeric = value.trim().replace(/[^\d,.-]/g, '');
    const lastComma = numeric.lastIndexOf(',');
    const lastDot = numeric.lastIndexOf('.');
    let normalized = numeric;

    if (lastComma >= 0 && lastDot >= 0) {
        normalized =
            lastComma > lastDot
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

const parseShareQuantity = (value: string | number) => {
    const str = typeof value === 'number' ? value.toString() : value;
    const parsed = parseInt(str.replace(/\D/g, ''), 10);

    return Number.isFinite(parsed) ? parsed : 0;
};

const percentToDecimal = (value: string | number) => parseNumber(value) / 100;

const lucroProjetado = computed(() => {
    const lucro = parseNumber(form.projected_net_income);

    if (lucro <= 0) return 0;

    return lucro * (1 + percentToDecimal(form.projected_growth_rate));
});

const lpaProjetado = computed(() => {
    const acoes = parseShareQuantity(form.total_shares);

    if (acoes <= 0 || lucroProjetado.value <= 0) return 0;

    return lucroProjetado.value / acoes;
});

const dpaProjetado = computed(() => {
    const payout = percentToDecimal(form.payout);

    if (payout <= 0 || lpaProjetado.value <= 0) return 0;

    return lpaProjetado.value * payout;
});

const precoTeto = computed(() => {
    const yieldDesejado = percentToDecimal(form.desired_yield);

    if (yieldDesejado <= 0 || dpaProjetado.value <= 0) return 0;

    return dpaProjetado.value / yieldDesejado;
});

const yieldProjetado = computed(() => {
    const preco = parseNumber(form.current_price_per_share);

    if (preco <= 0 || dpaProjetado.value <= 0) return 0;

    return (dpaProjetado.value / preco) * 100;
});

const margemSeguranca = computed(() => {
    const preco = parseNumber(form.current_price_per_share);

    if (preco <= 0 || precoTeto.value <= 0) return 0;

    return ((precoTeto.value - preco) / preco) * 100;
});

const margemGaugePercent = computed(() => {
    return Math.min(Math.max(margemSeguranca.value, -50), 80);
});

const margemStatus = computed(() => {
    const m = margemSeguranca.value;
    if (m >= 30)
        return {
            label: 'Excelente margem',
            color: 'bg-revenue',
            text: 'text-revenue',
        };
    if (m >= 15)
        return {
            label: 'Boa margem',
            color: 'bg-emerald-500',
            text: 'text-emerald-500',
        };
    if (m >= 0)
        return {
            label: 'Margem positiva',
            color: 'bg-primary',
            text: 'text-primary',
        };
    if (m >= -15)
        return {
            label: 'Margem negativa',
            color: 'bg-amber-500',
            text: 'text-amber-500',
        };
    return {
        label: 'Risco alto',
        color: 'bg-destructive',
        text: 'text-destructive',
    };
});

const podeCalcularPrecoTeto = computed(
    () =>
        percentToDecimal(form.desired_yield) > 0 &&
        percentToDecimal(form.payout) > 0 &&
        parseNumber(form.projected_net_income) > 0 &&
        lucroProjetado.value > 0 &&
        parseShareQuantity(form.total_shares) > 0,
);

const temPrecoAtual = computed(
    () => parseNumber(form.current_price_per_share) > 0,
);

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
};

const formatPercent = (value: number) => {
    return `${value.toFixed(2)}%`;
};

const goBack = () => {
    router.visit('/valuations/create');
};

const submit = () => {
    form.transform((data) => ({
        ...data,
        total_shares: parseShareQuantity(data.total_shares).toString(),
    }));

    if (props.valuation) {
        form.put(`/preco-teto/${props.valuation.id}`);
        return;
    }

    form.post('/preco-teto');
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
                title="Preço Teto Projetivo"
                description="Calcule o preço teto com base em lucro e dividendos projetados"
            >
                <template #actions>
                    <div class="flex items-center gap-2">
                        <Banknote class="h-4 w-4 text-muted-foreground" />
                        <select
                            v-model="selectedId"
                            :disabled="!!valuation"
                            class="h-9 rounded-md border border-border bg-surface py-1 pr-8 pl-3 text-sm text-foreground transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="">Selecione um ativo...</option>
                            <option
                                v-for="item in assets"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.name }}
                            </option>
                        </select>
                    </div>
                </template>
            </PageHeader>

            <form
                class="grid grid-cols-1 gap-6 lg:grid-cols-3"
                @submit.prevent="submit"
            >
                <!-- Inputs -->
                <div class="lg:col-span-2">
                    <div class="rounded-xl border border-border bg-card">
                        <div class="border-b border-border px-6 py-4">
                            <h3 class="text-base font-semibold text-foreground">
                                Parâmetros do Cálculo
                            </h3>
                            <p class="text-sm text-muted-foreground">
                                Preencha os campos para calcular o preço teto
                            </p>
                        </div>
                        <div class="p-6">
                            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label
                                            >Dividend Yield Desejado (%)</Label
                                        >
                                    </div>
                                    <Input
                                        v-model="form.desired_yield"
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        placeholder="Ex: 6"
                                        class="mt-1.5"
                                    />
                                    <InputError
                                        :message="form.errors.desired_yield"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Lucro Líquido (R$)</Label>
                                    </div>
                                    <CurrencyInput
                                        v-model="form.projected_net_income"
                                        :error="
                                            form.errors.projected_net_income
                                        "
                                        placeholder="0,00"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Total de Ações</Label>
                                    </div>
                                    <NumberInput
                                        v-model="form.total_shares"
                                        :precision="0"
                                        :error="form.errors.total_shares"
                                        placeholder="Ex: 13.822.910.028"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>ROE (%)</Label>
                                    </div>
                                    <Input
                                        v-model="form.roe"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        placeholder="Ex: 20"
                                        class="mt-1.5"
                                    />
                                    <InputError :message="form.errors.roe" />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>Payout (%)</Label>
                                    </div>
                                    <Input
                                        v-model="form.payout"
                                        type="number"
                                        step="0.1"
                                        min="0"
                                        max="100"
                                        placeholder="Ex: 50"
                                        class="mt-1.5"
                                    />
                                    <InputError
                                        :message="form.errors.payout"
                                    />
                                </div>
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <Label>
                                            <span>Crescimento</span>
                                        </Label>
                                    </div>
                                    <Input
                                        v-model="form.projected_growth_rate"
                                        type="number"
                                        step="0.1"
                                        placeholder="Ex: 5"
                                        class="mt-1.5"
                                    />
                                    <p
                                        class="mt-1 text-xs text-muted-foreground"
                                    >
                                        Taxa de Crescimento Esperada:
                                        <span
                                            class="font-medium text-foreground"
                                            >{{ defaultGrowthRate }}%</span
                                        >
                                        (ROE × (1 − Payout%))
                                    </p>
                                    <InputError
                                        :message="
                                            form.errors.projected_growth_rate
                                        "
                                    />
                                </div>
                                <div>
                                    <Label>Preço Atual (R$)</Label>
                                    <CurrencyInput
                                        v-model="form.current_price_per_share"
                                        :error="
                                            form.errors.current_price_per_share
                                        "
                                        placeholder="0,00"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Gauge -->
                    <div
                        v-if="podeCalcularPrecoTeto && temPrecoAtual"
                        class="mt-6 rounded-xl border border-border bg-card p-6"
                    >
                        <div class="mb-2 flex items-center justify-between">
                            <span class="text-sm font-semibold text-foreground"
                                >Margem de Segurança</span
                            >
                            <span
                                :class="`text-sm font-bold ${margemStatus.text}`"
                                >{{ formatPercent(margemSeguranca) }}</span
                            >
                        </div>
                        <div
                            class="relative h-3 overflow-hidden rounded-full bg-muted"
                        >
                            <div
                                class="h-full rounded-full transition-all duration-500 ease-out"
                                :class="margemStatus.color"
                                :style="{
                                    width: `${((margemGaugePercent + 50) / 130) * 100}%`,
                                }"
                            />
                        </div>
                        <div
                            class="mt-1 flex justify-between text-xs text-muted-foreground"
                        >
                            <span>-50%</span>
                            <span>0%</span>
                            <span>+80%</span>
                        </div>
                        <p class="mt-3 text-xs" :class="margemStatus.text">
                            {{ margemStatus.label }}
                            <template v-if="margemSeguranca > 0">
                                — o ativo está sendo negociado
                                {{
                                    margemSeguranca >= 15
                                        ? 'abaixo'
                                        : 'ligeiramente abaixo'
                                }}
                                do preço teto
                            </template>
                            <template v-else>
                                — o ativo está sendo negociado acima do preço
                                teto calculado
                            </template>
                        </p>
                    </div>
                </div>

                <!-- Results -->
                <div class="space-y-4">
                    <SummaryCard
                        label="Preço Teto"
                        :value="
                            podeCalcularPrecoTeto
                                ? formatCurrency(precoTeto)
                                : '—'
                        "
                        variant="investment"
                        :icon="Calculator"
                    />

                    <SummaryCard
                        label="Margem de Segurança"
                        :value="
                            podeCalcularPrecoTeto && temPrecoAtual
                                ? formatPercent(margemSeguranca)
                                : '—'
                        "
                        :variant="margemSeguranca >= 0 ? 'revenue' : 'expense'"
                        :icon="ShieldCheck"
                        :trend="
                            podeCalcularPrecoTeto && temPrecoAtual
                                ? parseFloat(margemSeguranca.toFixed(2))
                                : undefined
                        "
                    />

                    <SummaryCard
                        label="LPA Projetado"
                        :value="
                            podeCalcularPrecoTeto
                                ? formatCurrency(lpaProjetado)
                                : '—'
                        "
                        variant="default"
                        :icon="Banknote"
                    />

                    <SummaryCard
                        label="DPA Projetado"
                        :value="
                            podeCalcularPrecoTeto
                                ? formatCurrency(dpaProjetado)
                                : '—'
                        "
                        variant="default"
                        :icon="PiggyBank"
                    />

                    <SummaryCard
                        label="Yield Projetado"
                        :value="
                            podeCalcularPrecoTeto && temPrecoAtual
                                ? formatPercent(yieldProjetado)
                                : '—'
                        "
                        variant="profit"
                        :icon="TrendingUp"
                        :trend="
                            podeCalcularPrecoTeto && temPrecoAtual
                                ? parseFloat(yieldProjetado.toFixed(2))
                                : undefined
                        "
                    />

                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="
                            form.processing ||
                            !podeCalcularPrecoTeto ||
                            !form.asset_id
                        "
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
                    Nenhum ativo cadastrado. Adicione um ativo primeiro para
                    usar esta ferramenta.
                </p>
            </div>
        </div>
    </AppLayout>
</template>

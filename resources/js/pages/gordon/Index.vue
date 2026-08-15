<script setup lang="ts">
import AssetDataCard from '@/components/AssetDataCard.vue';
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Calculator } from 'lucide-vue-next';
import { computed } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string;
    current_price?: number | null;
    market_cap?: number | null;
    dividend_yield?: number | null;
    dividends_per_share?: number | null;
    price_to_book?: number | null;
    roe?: number | null;
    payout?: number | null;
    net_income?: number | null;
    total_shares?: number | null;
    free_cash_flow?: number | null;
    net_debt_to_ebitda?: number | null;
    logo_url?: string | null;
    asset_type?: string;
}

interface ValuationAssumptions {
    dps?: number;
    discount_rate?: number;
    risk_premium?: number;
    growth_perpetuity?: number;
    current_price?: number | null;
    projection_years?: number;
    growth_rates?: number[];
}

interface ValuationSummary {
    fair_price?: number | null;
    upside?: number | null;
    margin_of_safety?: number | null;
}

interface Valuation {
    id: number;
    method: string;
    assumptions: ValuationAssumptions;
    summary: ValuationSummary;
    calculated_at: string;
}

const props = defineProps<{
    asset: Asset | null;
    assets: Asset[];
    valuation: Valuation | null;
    defaultAssumptions?: Record<string, unknown> | null;
}>();

const defaults = props.valuation?.assumptions ?? props.defaultAssumptions ?? {};

const d = defaults as Record<string, unknown>;

const form = useForm<{
    asset_id: string;
    dps: number | string;
    discount_rate: number | string;
    risk_premium: number | string;
    growth_perpetuity: number | string;
    current_price: number | string;
    projection_years: number | string;
    growth_rates: number[];
}>({
    asset_id: props.asset?.id?.toString() ?? '',
    dps: (d.dps as number | string) ?? '',
    discount_rate: (d.discount_rate as number | string) ?? 13,
    risk_premium: (d.risk_premium as number | string) ?? 4,
    growth_perpetuity: (d.growth_perpetuity as number | string) ?? 3.0,
    current_price:
        (d.current_price as number | string) ??
        props.asset?.current_price ??
        '',
    projection_years: (d.projection_years as number | string) ?? 5,
    growth_rates: (d.growth_rates as number[]) ?? [8.0, 7.0, 6.0, 5.0, 4.0],
});

const effectiveKe = computed(
    () => Number(form.discount_rate) + Number(form.risk_premium),
);

const fairPrice = computed(() => {
    const dps = Number(form.dps);
    const ke = effectiveKe.value / 100;
    const g = Number(form.growth_perpetuity) / 100;
    if (!dps || ke <= g) return null;
    return (dps * (1 + g)) / (ke - g);
});

const upside = computed(() => {
    const fp = fairPrice.value;
    const cp = Number(form.current_price);
    if (!fp || !cp) return null;
    return ((fp - cp) / cp) * 100;
});

const marginOfSafety = computed(() => {
    const fp = fairPrice.value;
    const cp = Number(form.current_price);
    if (!fp || !cp) return null;
    return ((fp - cp) / cp) * 100;
});

const isEditing = computed(() => !!props.valuation);

const goBack = () => {
    router.visit('/valuations');
};

const submit = () => {
    const payload = {
        ...form.data(),
        asset_id: Number(form.asset_id),
        dps: Number(form.dps),
        discount_rate: Number(form.discount_rate),
        risk_premium: Number(form.risk_premium),
        growth_perpetuity: Number(form.growth_perpetuity),
        current_price: Number(form.current_price) || null,
        projection_years: Number(form.projection_years),
        growth_rates: form.growth_rates.map(Number),
    };

    if (isEditing.value) {
        form.put(`/gordon/${props.valuation!.id}`, {
            ...payload,
        });
    } else {
        form.post('/gordon', {
            ...payload,
        });
    }
};

function formatCurrency(value: number | null | undefined) {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL',
    }).format(value);
}

function formatPercent(value: number | null | undefined) {
    if (value === null || value === undefined) return '—';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
}
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
                :title="
                    isEditing ? 'Editar Gordon' : 'Modelo de Gordon Ajustado'
                "
                description="Valuation de ativos pelo modelo de desconto de dividendos"
            />

            <!-- ─── DADOS DO ATIVO (read-only) ─────────────── -->
            <AssetDataCard v-if="asset" :asset="asset" />

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

                    <SectionCard title="Premissas do Investidor">
                        <div class="space-y-4">
                            <div>
                                <Label>Dividendo Anual Esperado (R$)</Label>
                                <Input
                                    v-model="form.dps"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError :message="form.errors.dps" />
                            </div>
                            <div>
                                <Label
                                    >Taxa de Desconto — Tesouro IPCA (%)</Label
                                >
                                <Input
                                    v-model="form.discount_rate"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.discount_rate"
                                />
                            </div>
                            <div>
                                <Label>Prêmio de Risco (%)</Label>
                                <Input
                                    v-model="form.risk_premium"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    class="mt-1"
                                />
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Exigência adicional sobre o Tesouro IPCA
                                </p>
                                <InputError
                                    :message="form.errors.risk_premium"
                                />
                            </div>
                            <div>
                                <Label>Crescimento Perpetuidade (%)</Label>
                                <Input
                                    v-model="form.growth_perpetuity"
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.growth_perpetuity"
                                />
                            </div>
                            <div>
                                <Label>Preço Atual (R$)</Label>
                                <Input
                                    v-model="form.current_price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="mt-1"
                                />
                                <InputError
                                    :message="form.errors.current_price"
                                />
                            </div>
                        </div>
                    </SectionCard>

                    <!-- <SectionCard title="Crescimento Anual">
                        <p class="mb-3 text-xs text-muted-foreground">
                            Taxas de crescimento projetadas por ano
                        </p>
                        <div class="grid grid-cols-5 gap-2">
                            <div v-for="(_, i) in 5" :key="i">
                                <Label class="text-xs text-muted-foreground">{{ new Date().getFullYear() + i + 1 }}</Label>
                                <div class="mt-1 flex items-center gap-1">
                                    <Input v-model.number="form.growth_rates[i]" type="number" step="0.1" class="h-8 text-xs" />
                                    <span class="text-xs text-muted-foreground">%</span>
                                </div>
                            </div>
                        </div>
                    </SectionCard> -->

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="goBack"
                            >Cancelar</Button
                        >
                        <Button @click="submit" :disabled="form.processing">
                            <Calculator class="h-4 w-4" />
                            {{ isEditing ? 'Atualizar' : 'Calcular' }}
                        </Button>
                    </div>
                </div>

                <!-- Right: Results -->
                <div class="space-y-6 xl:col-span-2">
                    <SectionCard title="Resultados do Gordon">
                        <div class="grid grid-cols-2 gap-4">
                            <div
                                class="overflow-hidden rounded-lg border border-primary/30 bg-card p-4 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Preço Teto
                                </p>
                                <p class="mt-1 text-2xl font-bold text-primary">
                                    {{ formatCurrency(fairPrice) }}
                                </p>
                                <div v-if="upside !== null" class="mt-1">
                                    <span
                                        class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-0.5 text-xs font-semibold"
                                        :class="
                                            upside >= 0
                                                ? 'text-revenue'
                                                : 'text-destructive'
                                        "
                                    >
                                        {{ upside >= 0 ? '+' : ''
                                        }}{{ upside.toFixed(1) }}% vs. preço
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
                                            : 'text-destructive'
                                    "
                                >
                                    {{ formatPercent(marginOfSafety) }}
                                </p>
                            </div>
                            <div
                                class="overflow-hidden rounded-lg border border-border bg-surface p-3 text-center sm:col-span-2"
                            >
                                <p
                                    class="text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                >
                                    Ke Efetivo (IPCA + Prêmio)
                                </p>
                                <p class="mt-1 text-xl font-bold">
                                    {{ formatPercent(effectiveKe) }}
                                </p>
                            </div>
                        </div>
                    </SectionCard>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

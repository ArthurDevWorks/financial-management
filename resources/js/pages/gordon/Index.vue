<script setup lang="ts">
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ArrowLeft, Calculator, TrendingUp } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

interface Asset {
    id: number;
    ticker: string;
    name: string;
    current_price?: number | null;
    dividends_per_share?: number | null;
    logo_url?: string | null;
    asset_type?: string;
}

interface ValuationAssumptions {
    dps?: number;
    discount_rate?: number;
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

const form = useForm<{
    asset_id: string;
    dps: number | string;
    discount_rate: number | string;
    growth_perpetuity: number | string;
    current_price: number | string;
    projection_years: number | string;
    growth_rates: number[];
}>({
    asset_id: props.asset?.id?.toString() ?? '',
    dps: (defaults as Record<string, unknown>).dps ?? '',
    discount_rate: (defaults as Record<string, unknown>).discount_rate ?? 12.5,
    growth_perpetuity: (defaults as Record<string, unknown>).growth_perpetuity ?? 3.0,
    current_price: (defaults as Record<string, unknown>).current_price ?? props.asset?.current_price ?? '',
    projection_years: (defaults as Record<string, unknown>).projection_years ?? 5,
    growth_rates: (defaults as Record<string, unknown>).growth_rates as number[] ?? [8.0, 7.0, 6.0, 5.0, 4.0],
});

const fairPrice = computed(() => {
    const dps = Number(form.dps);
    const ke = Number(form.discount_rate) / 100;
    const g = Number(form.growth_perpetuity) / 100;
    if (!dps || ke <= g) return null;
    return dps / (ke - g);
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
    return (1 - cp / fp) * 100;
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
        growth_perpetuity: Number(form.growth_perpetuity),
        current_price: Number(form.current_price) || null,
        projection_years: Number(form.projection_years),
        growth_rates: form.growth_rates.map(Number),
    };

    if (isEditing.value) {
        form.put(`/gordon/${props.valuation!.id}`, {
            ...payload,
            onSuccess: () => router.visit(`/valuations/${props.valuation!.id}`),
        });
    } else {
        form.post('/gordon', {
            ...payload,
            onSuccess: () => { /* redirect handled by server */ },
        });
    }
};

const formatCurrency = (value: number | null | undefined) => {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency', currency: 'BRL',
    }).format(value);
};

const formatPercent = (value: number | null | undefined) => {
    if (value === null || value === undefined) return '—';
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};
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
                :title="isEditing ? 'Editar Gordon' : 'Gordon Growth Model'"
                description="Valuation de FIIs pelo modelo de desconto de dividendos"
            />

            <div class="mt-6 grid gap-6 xl:grid-cols-3">
                <!-- Left: Forms -->
                <div class="xl:col-span-2 space-y-6">
                    <SectionCard title="Ativo">
                        <div>
                            <Label>Selecione o Ativo</Label>
                            <select
                                v-model="form.asset_id"
                                class="mt-1 h-[42px] w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
                                :disabled="isEditing"
                            >
                                <option value="">Selecione...</option>
                                <option
                                    v-for="asset in assets"
                                    :key="asset.id"
                                    :value="asset.id"
                                >
                                    {{ asset.name }}
                                </option>
                            </select>
                            <InputError :message="form.errors.asset_id" />
                        </div>
                    </SectionCard>

                    <SectionCard title="Premissas do Gordon">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <Label>DPS atual (R$)</Label>
                                <Input v-model="form.dps" type="number" step="0.01" min="0" class="mt-1" />
                                <InputError :message="form.errors.dps" />
                            </div>
                            <div>
                                <Label>Taxa de desconto (Ke %)</Label>
                                <Input v-model="form.discount_rate" type="number" step="0.1" min="0" class="mt-1" />
                                <InputError :message="form.errors.discount_rate" />
                            </div>
                            <div>
                                <Label>Crescimento na perpetuidade (g %)</Label>
                                <Input v-model="form.growth_perpetuity" type="number" step="0.1" min="0" class="mt-1" />
                                <InputError :message="form.errors.growth_perpetuity" />
                            </div>
                            <div>
                                <Label>Preço atual (R$)</Label>
                                <Input v-model="form.current_price" type="number" step="0.01" min="0" class="mt-1" />
                                <InputError :message="form.errors.current_price" />
                            </div>
                            <div>
                                <Label>Anos de projeção</Label>
                                <Input v-model="form.projection_years" type="number" step="1" min="1" max="50" class="mt-1" />
                                <InputError :message="form.errors.projection_years" />
                            </div>
                        </div>
                    </SectionCard>

                    <SectionCard title="Crescimento Anual">
                        <div class="grid gap-3 sm:grid-cols-5">
                            <div v-for="(_, i) in Number(form.projection_years)" :key="i">
                                <Label>Ano {{ i + 1 }}</Label>
                                <div class="mt-1 flex items-center gap-1">
                                    <Input v-model="form.growth_rates[i]" type="number" step="0.1" class="h-9" />
                                    <span class="text-xs text-muted-foreground">%</span>
                                </div>
                            </div>
                        </div>
                    </SectionCard>

                    <div class="flex justify-end gap-3">
                        <Button type="button" variant="outline" @click="goBack">Cancelar</Button>
                        <Button @click="submit" :disabled="form.processing">
                            <Calculator class="h-4 w-4" />
                            {{ isEditing ? 'Atualizar' : 'Calcular' }}
                        </Button>
                    </div>
                </div>

                <!-- Right: Preview -->
                <div class="space-y-6">
                    <SectionCard title="Prévia do Resultado">
                        <div class="space-y-4">
                            <div class="rounded-lg border border-primary/30 bg-primary/10 p-4 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Preço Justo</p>
                                <p class="mt-1 text-3xl font-bold text-primary">{{ formatCurrency(fairPrice) }}</p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-4 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Upside</p>
                                <p class="mt-1 text-2xl font-bold" :class="(upside ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(upside) }}
                                </p>
                            </div>
                            <div class="rounded-lg border border-border bg-surface p-4 text-center">
                                <p class="text-xs font-semibold tracking-wider text-muted-foreground uppercase">Margem de Segurança</p>
                                <p class="mt-1 text-2xl font-bold" :class="(marginOfSafety ?? 0) >= 0 ? 'text-revenue' : 'text-destructive'">
                                    {{ formatPercent(marginOfSafety) }}
                                </p>
                            </div>
                            <div class="text-xs text-muted-foreground">
                                <p>Fórmula: <strong class="text-foreground">P = DPS / (Ke - g)</strong></p>
                                <p>Ke = {{ form.discount_rate }}% · g = {{ form.growth_perpetuity }}%</p>
                            </div>
                        </div>
                    </SectionCard>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import PageHeader from '@/components/PageHeader.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import {
    ArrowLeft,
    Calculator,
    ChartNoAxesCombined,
    TrendingUp,
} from 'lucide-vue-next';

interface AssetItem {
    id: number;
    ticker: string;
    name: string;
    logo_url?: string | null;
    asset_type: string;
    current_price?: number | null;
}

defineProps<{
    assets: AssetItem[];
}>();

type ValuationMethod = 'dcf' | 'preco_teto' | 'gordon';

const form = useForm<{
    asset_id: string;
    method: ValuationMethod | '';
}>({
    asset_id: '',
    method: '',
});

const submit = () => {
    if (form.method === 'dcf') {
        router.visit(`/valuations?asset_id=${form.asset_id}`);
    } else if (form.method === 'preco_teto') {
        router.visit(`/preco-teto?asset_id=${form.asset_id}`);
    } else if (form.method === 'gordon') {
        router.visit(`/gordon?asset_id=${form.asset_id}`);
    }
};

const goBack = () => {
    router.visit('/valuations');
};

const methods: {
    value: ValuationMethod;
    label: string;
    description: string;
    icon: any;
}[] = [
    {
        value: 'dcf',
        label: 'Fluxo de Caixa Descontado',
        description:
            'Calcula o valor presente dos fluxos de caixa futuros projetados, descontados por uma taxa que reflete o risco do ativo.',
        icon: Calculator,
    },
    {
        value: 'preco_teto',
        label: 'Preço Teto Projetivo',
        description:
            'Determina o preço máximo de compra com base em lucro, payout e dividend yield projetados.',
        icon: TrendingUp,
    },
    {
        value: 'gordon',
        label: 'Gordon Growth Model',
        description:
            'Valuation de FIIs pelo modelo de desconto de dividendos com crescimento perpétuo.',
        icon: TrendingUp,
    },
];
</script>

<template>
    <AppLayout>
        <div class="mx-auto max-w-3xl p-8">
            <div class="mb-8">
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
                    title="Nova Valuation"
                    description="Selecione o ativo e o método de cálculo"
                />
            </div>

            <div
                v-if="!assets.length"
                class="rounded-xl border border-border bg-card p-8 text-center"
            >
                <ChartNoAxesCombined
                    class="mx-auto mb-4 h-16 w-16 text-muted-foreground opacity-15"
                />
                <p class="font-medium text-foreground">
                    Nenhum ativo disponível
                </p>
                <p class="mt-1 text-sm text-muted-foreground">
                    Aguarde a sincronização de ativos para começar.
                </p>
            </div>

            <form v-else @submit.prevent="submit" class="space-y-6">
                <SectionCard title="Selecione o Ativo">
                    <div>
                        <Label required>Ativo</Label>
                        <select
                            v-model="form.asset_id"
                            required
                            class="mt-1.5 h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="" disabled>
                                Selecione um ativo
                            </option>
                            <option
                                v-for="item in assets"
                                :key="item.id"
                                :value="item.id"
                            >
                                {{ item.ticker }} - {{ item.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.asset_id" />
                    </div>
                </SectionCard>

                <SectionCard title="Método de Cálculo">
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <button
                            v-for="method in methods"
                            :key="method.value"
                            type="button"
                            class="flex flex-col items-start gap-3 rounded-lg border p-5 text-left transition-all"
                            :class="
                                form.method === method.value
                                    ? 'border-primary bg-primary/5 ring-1 ring-primary'
                                    : 'border-border hover:border-border/80 hover:bg-surface/50'
                            "
                            @click="form.method = method.value"
                        >
                            <component
                                :is="method.icon"
                                class="h-6 w-6"
                                :class="
                                    form.method === method.value
                                        ? 'text-primary'
                                        : 'text-muted-foreground'
                                "
                            />
                            <div>
                                <p
                                    class="font-semibold text-foreground"
                                    :class="
                                        form.method === method.value
                                            ? 'text-primary'
                                            : ''
                                    "
                                >
                                    {{ method.label }}
                                </p>
                                <p class="mt-1 text-sm text-muted-foreground">
                                    {{ method.description }}
                                </p>
                            </div>
                        </button>
                    </div>
                    <InputError :message="form.errors.method" />
                </SectionCard>

                <div class="flex items-center justify-end gap-3">
                    <Button type="button" variant="outline" @click="goBack">
                        Cancelar
                    </Button>
                    <Button
                        type="submit"
                        variant="default"
                        :disabled="!form.asset_id || !form.method"
                    >
                        Iniciar Cálculo
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

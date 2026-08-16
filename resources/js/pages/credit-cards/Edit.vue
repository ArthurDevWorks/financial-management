<script setup lang="ts">
import FormPageLayout from '@/components/FormPageLayout.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Bank {
    id: number;
    name: string;
}

interface CardData {
    id: number;
    name: string;
    bank_id: number | null;
    limit: number;
    closing_day: number;
    due_day: number;
    color: string;
}

const props = defineProps<{ card: CardData; banks: Bank[] }>();

const showUnsavedDialog = ref(false);

const CARD_COLORS = [
    '#22c9a2',
    '#f59e0b',
    '#6366f1',
    '#ec4899',
    '#10b981',
    '#3b82f6',
    '#8b5cf6',
    '#ef4444',
    '#f97316',
    '#06b6d4',
];

const form = useForm({
    name: props.card.name,
    bank_id: props.card.bank_id?.toString() ?? '',
    limit: props.card.limit.toString(),
    closing_day: props.card.closing_day.toString(),
    due_day: props.card.due_day.toString(),
    color: props.card.color,
});

const submit = () => {
    form.put(`/credit-cards/${props.card.id}`);
};

const goBack = () => {
    router.visit('/credit-cards');
};
</script>

<template>
    <AppLayout>
        <FormPageLayout
            title="Editar Cartão"
            description="Atualize as informações do cartão de crédito"
            :processing="form.processing"
            :dirty="form.isDirty"
            submit-label="Salvar Alterações"
            processing-label="Salvando..."
            v-model:showUnsavedDialog="showUnsavedDialog"
            @submit="submit"
            @cancel="goBack"
        >
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Nome do Cartão</Label>
                        <Input
                            v-model="form.name"
                            type="text"
                            placeholder="Ex: Nubank, Inter Gold..."
                        />
                        <InputError :message="form.errors.name" />
                    </div>
                    <div>
                        <Label>Banco / Emissor</Label>
                        <select
                            v-model="form.bank_id"
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="">Nenhum</option>
                            <option
                                v-for="bank in banks"
                                :key="bank.id"
                                :value="bank.id"
                            >
                                {{ bank.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.bank_id" />
                    </div>
                </div>

                <div>
                    <Label required>Limite do Cartão</Label>
                    <div class="relative">
                        <span
                            class="absolute top-1/2 left-3 -translate-y-1/2 text-sm text-muted-foreground"
                            >R$</span
                        >
                        <Input
                            v-model="form.limit"
                            type="number"
                            step="0.01"
                            min="0"
                            placeholder="0,00"
                            class="pl-8"
                        />
                    </div>
                    <InputError :message="form.errors.limit" />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Dia de Fechamento</Label>
                        <Input
                            v-model="form.closing_day"
                            type="number"
                            min="1"
                            max="28"
                            placeholder="Ex: 10"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Dia do mês em que a fatura fecha (1 a 28)
                        </p>
                        <InputError :message="form.errors.closing_day" />
                    </div>
                    <div>
                        <Label required>Dia de Vencimento</Label>
                        <Input
                            v-model="form.due_day"
                            type="number"
                            min="1"
                            max="28"
                            placeholder="Ex: 20"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            Dia do mês em que a fatura vence (1 a 28)
                        </p>
                        <InputError :message="form.errors.due_day" />
                    </div>
                </div>

                <div>
                    <Label>Cor do Cartão</Label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="color in CARD_COLORS"
                            :key="color"
                            type="button"
                            class="h-8 w-8 rounded-full transition-all hover:scale-110 focus:outline-none"
                            :class="[
                                form.color === color
                                    ? 'scale-110 ring-2 ring-offset-2 ring-offset-card'
                                    : '',
                            ]"
                            :style="{ backgroundColor: color }"
                            @click="form.color = color"
                            :title="color"
                        />
                    </div>
                    <InputError :message="form.errors.color" />
                </div>
            </div>
        </FormPageLayout>
    </AppLayout>
</template>

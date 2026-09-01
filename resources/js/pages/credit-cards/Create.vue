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

defineProps<{ banks: Bank[] }>();

const showUnsavedDialog = ref(false);

const CARD_COLORS = [
    '#22c9a2', // teal (primário Fidax)
    '#f59e0b', // amber/gold
    '#ec4899', // pink
    '#10b981', // emerald
    '#3b82f6', // blue
    '#8b5cf6', // violet
    '#ef4444', // red
    '#f97316', // orange
    '#06b6d4', // cyan
];

const form = useForm({
    name: '',
    bank_id: '',
    limit: '',
    closing_day: '',
    due_day: '',
    color: '#22c9a2',
});

const submit = () => {
    form.post('/credit-cards');
};

const goBack = () => {
    router.visit('/credit-cards');
};
</script>

<template>
    <AppLayout>
        <FormPageLayout
            title="Novo Cartão de Crédito"
            description="Cadastre um cartão para controlar suas faturas"
            :processing="form.processing"
            :dirty="form.isDirty"
            submit-label="Cadastrar Cartão"
            processing-label="Cadastrando..."
            v-model:showUnsavedDialog="showUnsavedDialog"
            @submit="submit"
            @cancel="goBack"
        >
            <div class="space-y-6">
                <!-- Nome e Banco -->
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
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:light] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20 dark:[color-scheme:dark]"
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

                <!-- Limite -->
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

                <!-- Dias de fechamento e vencimento -->
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

                <!-- Cor do cartão -->
                <div>
                    <Label>Cor do Cartão</Label>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="color in CARD_COLORS"
                            :key="color"
                            type="button"
                            class="h-8 w-8 rounded-full transition-all hover:scale-110 focus:outline-none"
                            :style="{ backgroundColor: color }"
                            :class="[
                                form.color === color
                                    ? 'scale-110 ring-2 ring-offset-2 ring-offset-card'
                                    : '',
                            ]"
                            :style-ring="
                                form.color === color
                                    ? `ring-color: ${color}`
                                    : ''
                            "
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

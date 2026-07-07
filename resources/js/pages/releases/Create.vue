<script setup lang="ts">
import CurrencyInput from '@/components/CurrencyInput.vue';
import FormPageLayout from '@/components/FormPageLayout.vue';
import InputError from '@/components/InputError.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface Account {
    id: number;
    account: string;
    bank?: { name: string };
}

interface Category {
    id: number;
    name: string;
    type: string;
}

const props = defineProps<{
    accounts: Account[];
    categories: Category[];
}>();

const showUnsavedDialog = ref(false);

const form = useForm({
    type: 'expense',
    account_id: '',
    category_id: '',
    title: '',
    amount: '',
    date: '',
    description: '',
});

const submit = () => {
    form.post('/releases');
};

const goBack = () => {
    router.visit('/releases');
};

const filteredCategories = computed(() => {
    const targetType = form.type === 'revenue' ? 'receita' : 'despesa';
    return props.categories.filter((c) => c.type === targetType);
});

watch(
    () => form.type,
    () => {
        form.category_id = '';
    },
);
</script>

<template>
    <AppLayout>
        <FormPageLayout
            title="Novo Lançamento"
            description="Cadastre uma receita ou despesa"
            :processing="form.processing"
            :dirty="form.isDirty"
            submit-label="Cadastrar Lançamento"
            processing-label="Cadastrando..."
            v-model:showUnsavedDialog="showUnsavedDialog"
            @submit="submit"
            @cancel="goBack"
        >
            <div class="space-y-6">
                <div>
                    <Label required>Tipo de Lançamento</Label>
                    <div class="mt-3 flex gap-4">
                        <label class="flex-1 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.type"
                                value="revenue"
                                class="peer sr-only"
                            />
                            <div
                                class="rounded-lg border border-border bg-surface p-4 text-center transition peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary hover:bg-secondary"
                            >
                                <span class="block font-semibold">Receita</span>
                            </div>
                        </label>
                        <label class="flex-1 cursor-pointer">
                            <input
                                type="radio"
                                v-model="form.type"
                                value="expense"
                                class="peer sr-only"
                            />
                            <div
                                class="rounded-lg border border-border bg-surface p-4 text-center transition peer-checked:border-destructive peer-checked:bg-destructive/10 peer-checked:text-destructive hover:bg-secondary"
                            >
                                <span class="block font-semibold">Despesa</span>
                            </div>
                        </label>
                    </div>
                    <InputError :message="form.errors.type" />
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Conta</Label>
                        <select
                            v-model="form.account_id"
                            required
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="" disabled>
                                Selecione uma conta
                            </option>
                            <option
                                v-for="account in accounts"
                                :key="account.id"
                                :value="account.id"
                            >
                                {{
                                    account.bank
                                        ? `${account.bank.name} - ${account.account}`
                                        : account.account
                                }}
                            </option>
                        </select>
                        <InputError :message="form.errors.account_id" />
                    </div>
                    <div>
                        <Label required>Categoria</Label>
                        <select
                            v-model="form.category_id"
                            required
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="" disabled>
                                Selecione uma categoria
                            </option>
                            <option
                                v-for="category in filteredCategories"
                                :key="category.id"
                                :value="category.id"
                            >
                                {{ category.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.category_id" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Descrição</Label>
                        <Input
                            v-model="form.title"
                            type="text"
                            placeholder="Título"
                        />
                        <InputError :message="form.errors.title" />
                    </div>
                    <div>
                        <Label required>Valor</Label>
                        <CurrencyInput
                            v-model="form.amount"
                            :error="form.errors.amount"
                            placeholder="0,00"
                        />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Data</Label>
                        <Input v-model="form.date" type="date" />
                        <InputError :message="form.errors.date" />
                    </div>
                    <div>
                        <Label>Observação</Label>
                        <Input
                            v-model="form.description"
                            type="text"
                            placeholder="Observação"
                        />
                        <InputError :message="form.errors.description" />
                    </div>
                </div>
            </div>
        </FormPageLayout>
    </AppLayout>
</template>

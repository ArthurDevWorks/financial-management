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

interface RecurrencePlan {
    id: number;
    frequency: string;
    end_date: string;
    active: boolean;
}

interface Release {
    id: number;
    type: 'revenue' | 'expense';
    account_id: number;
    category_id: number;
    title: string;
    amount: number;
    date: string;
    description: string | null;
    payment_method: string | null;
    credit_card_id: number | null;
    status: string;
    installment_number: number | null;
    total_installments: number | null;
    parent_id: number | null;
    recurrence_id: number | null;
    parent: Release | null;
    recurrencePlan: RecurrencePlan | null;
}

interface CreditCardOption {
    id: number;
    name: string;
    color: string;
    limit: number;
}

const props = defineProps<{
    release: Release;
    accounts: Account[];
    categories: Category[];
    paymentMethods: Record<string, string>;
    recurrenceFrequencies: Record<string, string>;
    releaseStatuses: Record<string, string>;
    creditCards: CreditCardOption[];
}>();

const showUnsavedDialog = ref(false);

const isInstallment = computed(() => !!props.release.installment_number);
const isRecurring = computed(() => !!props.release.recurrence_id);

const form = useForm({
    type: props.release.type,
    account_id: props.release.account_id?.toString() ?? '',
    category_id: props.release.category_id?.toString() ?? '',
    title: props.release.title,
    amount: props.release.amount?.toString() ?? '',
    date: props.release.date,
    description: props.release.description || '',
    payment_method: props.release.payment_method ?? '',
    credit_card_id: props.release.credit_card_id ?? ('' as string | number),
    status: props.release.status ?? 'paid',
});

const isCreditCard = computed(() => form.payment_method === 'credit_card');

const submit = () => {
    form.put(`/releases/${props.release.id}`);
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
    (newType, oldType) => {
        if (oldType) {
            form.category_id = '' as any;
        }
    },
);
</script>

<template>
    <AppLayout>
        <FormPageLayout
            title="Editar Lançamento"
            description="Atualize um lançamento"
            :processing="form.processing"
            :dirty="form.isDirty"
            submit-label="Salvar Alterações"
            processing-label="Salvando..."
            v-model:showUnsavedDialog="showUnsavedDialog"
            @submit="submit"
            @cancel="goBack"
        >
            <div class="space-y-6">
                <div v-if="isInstallment" class="rounded-lg border border-primary/30 bg-primary/5 p-4">
                    <p class="text-sm font-medium text-primary">
                        Parcela {{ props.release.installment_number }} de {{ props.release.total_installments }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        As demais parcelas não são afetadas por esta edição.
                    </p>
                </div>

                <div v-if="isRecurring && props.release.recurrencePlan" class="rounded-lg border border-purple-500/30 bg-purple-500/5 p-4">
                    <p class="text-sm font-medium text-purple-400">
                        Lançamento Recorrente • {{ props.release.recurrencePlan.active ? 'Ativo' : 'Inativo' }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        Frequência: {{ props.recurrenceFrequencies[props.release.recurrencePlan.frequency] ?? props.release.recurrencePlan.frequency }}
                        • Término: {{ props.release.recurrencePlan.end_date }}
                    </p>
                </div>

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

                <div class="border-t border-border pt-6">
                    <h3 class="mb-4 text-sm font-semibold text-foreground">
                        Forma de Pagamento e Status
                    </h3>

                    <div class="grid grid-cols-2 gap-6">
                        <div>
                            <Label>Forma de Pagamento</Label>
                            <select
                                v-model="form.payment_method"
                                class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            >
                                <option value="">Selecione</option>
                                <option
                                    v-for="(label, value) in props.paymentMethods"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.payment_method" />
                        </div>

                        <div>
                            <Label>Status</Label>
                            <select
                                v-model="form.status"
                                class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                            >
                                <option
                                    v-for="(label, value) in props.releaseStatuses"
                                    :key="value"
                                    :value="value"
                                >
                                    {{ label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.status" />
                        </div>
                    </div>

                    <!-- Cartão de crédito -->
                    <div v-if="isCreditCard" class="mt-4">
                        <Label required>Cartão de Crédito</Label>
                        <select
                            v-model="form.credit_card_id"
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:dark] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20"
                        >
                            <option value="" disabled>Selecione o cartão</option>
                            <option
                                v-for="card in props.creditCards"
                                :key="card.id"
                                :value="card.id"
                            >
                                {{ card.name }}
                            </option>
                        </select>
                        <InputError :message="form.errors.credit_card_id" />
                    </div>
                </div>
            </div>
        </FormPageLayout>
    </AppLayout>
</template>

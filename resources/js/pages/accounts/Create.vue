<script setup lang="ts">
import CurrencyInput from '@/components/CurrencyInput.vue';
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

defineProps<{
    banks: Bank[];
    accountTypes: Record<string, string>;
}>();

const showUnsavedDialog = ref(false);

const form = useForm({
    bank_id: '',
    type: '',
    agency: '',
    account: '',
    total: '',
});

const submit = () => {
    form.post('/accounts');
};

const goBack = () => {
    router.visit('/accounts');
};
</script>

<template>
    <AppLayout>
        <FormPageLayout
            title="Nova Conta"
            description="Cadastre uma conta"
            :processing="form.processing"
            :dirty="form.isDirty"
            submit-label="Cadastrar Conta"
            processing-label="Cadastrando..."
            v-model:showUnsavedDialog="showUnsavedDialog"
            @submit="submit"
            @cancel="goBack"
        >
            <div class="space-y-6">
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Banco</Label>
                        <select
                            v-model="form.bank_id"
                            required
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:light] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20 dark:[color-scheme:dark]"
                        >
                            <option value="" disabled>
                                Selecione um banco
                            </option>
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
                    <div>
                        <Label required>Tipo de Conta</Label>
                        <select
                            v-model="form.type"
                            required
                            class="h-9 w-full rounded-md border border-border bg-surface py-1 pr-10 pl-3 text-sm text-foreground [color-scheme:light] transition-all outline-none focus:border-ring focus:ring-[3px] focus:ring-primary/20 dark:[color-scheme:dark]"
                        >
                            <option value="" disabled>
                                Selecione o tipo de conta
                            </option>
                            <option
                                v-for="(label, value) in accountTypes"
                                :key="value"
                                :value="value"
                            >
                                {{ label }}
                            </option>
                        </select>
                        <InputError :message="form.errors.type" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <Label required>Agência</Label>
                        <Input
                            v-model="form.agency"
                            type="text"
                            placeholder="Agência"
                        />
                        <InputError :message="form.errors.agency" />
                    </div>
                    <div>
                        <Label required>Número da Conta</Label>
                        <Input
                            v-model="form.account"
                            type="text"
                            placeholder="Conta"
                        />
                        <InputError :message="form.errors.account" />
                    </div>
                </div>

                <div>
                    <Label required>Saldo Inicial</Label>
                    <CurrencyInput
                        v-model="form.total"
                        :error="form.errors.total"
                        placeholder="0,00"
                    />
                </div>
            </div>
        </FormPageLayout>
    </AppLayout>
</template>

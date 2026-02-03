<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { email } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Redefinir Senha"
        description="Digite seu email para receber um link de redefinição"
    >
        <Head title="Redefinir Senha" />

        <div
            v-if="status"
            class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-center text-sm font-medium text-emerald-700"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="email" class="text-slate-700 font-semibold">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="seu@email.com"
                        class="bg-slate-50 border-slate-200 text-slate-900"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button
                        class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-semibold h-11 rounded-lg"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="h-4 w-4 animate-spin mr-2"
                        />
                        {{ processing ? 'Enviando...' : 'Enviar Link de Redefinição' }}
                    </Button>
                </div>
            </Form>

            <div class="text-center text-sm text-slate-600">
                Lembrou a senha?
                <TextLink :href="login()" class="text-emerald-600 hover:text-emerald-700 font-semibold">Voltar para login</TextLink>
            </div>
        </div>
    </AuthLayout>
</template>

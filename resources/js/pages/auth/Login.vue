<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="Bem-vindo de volta"
        description="Acesse sua conta para gerenciar suas finanças"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-6 p-4 rounded-lg bg-cyan-500/20 border border-cyan-500/50 text-center text-sm font-medium text-cyan-300"
        >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2">
                    <Label for="email" class="text-slate-200 font-semibold">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="seu@email.com"
                        class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2">
                    <div class="flex items-center justify-between">
                        <Label for="password" class="text-slate-200 font-semibold">Senha</Label>
                        <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-cyan-400 hover:text-cyan-300 font-medium transition"
                            :tabindex="5"
                        >
                            Esqueceu a senha?
                        </TextLink>
                    </div>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Sua senha"
                        class="bg-slate-700 border-slate-600 text-white placeholder:text-slate-400 focus:border-cyan-500 focus:ring-cyan-500"
                    />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between">
                    <Label for="remember" class="flex items-center gap-3 cursor-pointer">
                        <Checkbox id="remember" name="remember" :tabindex="3" />
                        <span class="text-sm text-slate-200">Manter-me conectado</span>
                    </Label>
                </div>

                <Button
                    type="submit"
                    class="mt-4 w-full bg-cyan-500 hover:bg-cyan-600 text-slate-900 font-semibold h-11 rounded-lg transition-colors"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin mr-2"
                    />
                    {{ processing ? 'Acessando...' : 'Acessar Conta' }}
                </Button>
            </div>

            <div
                class="text-center text-sm text-slate-300"
                v-if="canRegister"
            >
                Não tem uma conta?
                <TextLink :href="register()" class="text-cyan-400 hover:text-cyan-300 font-semibold transition" :tabindex="5">Cadastrar-se</TextLink>
            </div>
        </Form>
    </AuthBase>
</template>

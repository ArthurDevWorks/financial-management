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
            class="mb-6 rounded-lg border border-primary/50 bg-primary/20 p-4 text-center text-sm font-medium text-primary"
        >
            {{ status }}
        </div>

        <div class="space-y-6">
            <Form v-bind="email.form()" v-slot="{ errors, processing }">
                <div class="grid gap-2">
                    <Label for="email">Email</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        autocomplete="off"
                        autofocus
                        placeholder="seu@email.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <div class="my-6 flex items-center justify-start">
                    <Button
                        variant="default"
                        class="w-full"
                        :disabled="processing"
                        data-test="email-password-reset-link-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{
                            processing
                                ? 'Enviando...'
                                : 'Enviar Link de Redefinição'
                        }}
                    </Button>
                </div>
            </Form>

            <div class="text-center text-sm text-muted-foreground">
                Lembrou a senha?
                <TextLink
                    :href="login()"
                    class="font-semibold text-primary hover:text-primary/80"
                    >Voltar para login</TextLink
                >
            </div>
        </div>
    </AuthLayout>
</template>

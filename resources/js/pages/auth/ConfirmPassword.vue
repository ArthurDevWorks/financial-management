<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { store } from '@/routes/password/confirm';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <AuthLayout
        title="Confirmar Senha"
        description="Esta é uma área segura. Por favor, confirme sua senha antes de continuar."
    >
        <Head title="Confirmar Senha" />

        <Form
            v-bind="store.form()"
            reset-on-success
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="space-y-6">
                <div class="grid gap-2">
                    <Label htmlFor="password" required>Senha</Label>
                    <Input
                        id="password"
                        type="password"
                        name="password"
                        required
                        autocomplete="current-password"
                        autofocus
                        placeholder="Digite sua senha"
                    />

                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center">
                    <Button
                        variant="default"
                        class="w-full"
                        :disabled="processing"
                        data-test="confirm-password-button"
                    >
                        <LoaderCircle
                            v-if="processing"
                            class="mr-2 h-4 w-4 animate-spin"
                        />
                        {{ processing ? 'Confirmando...' : 'Confirmar Senha' }}
                    </Button>
                </div>
            </div>
        </Form>
    </AuthLayout>
</template>

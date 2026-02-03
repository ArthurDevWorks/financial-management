<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout
        title="Verificar Email"
        description="Clique no link que enviamos para seu email para verificar sua conta."
    >
        <Head title="Verificação de Email" />

        <div
            v-if="status === 'verification-link-sent'"
            class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-center text-sm font-medium text-emerald-700"
        >
            Um novo link de verificação foi enviado para o email fornecido durante o cadastro.
        </div>

        <Form
            v-bind="send.form()"
            class="space-y-6 text-center"
            v-slot="{ processing }"
        >
            <Button 
                class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-semibold h-11 rounded-lg"
                :disabled="processing"
            >
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin mr-2" />
                {{ processing ? 'Enviando...' : 'Reenviar Email de Verificação' }}
            </Button>

            <TextLink
                :href="logout()"
                as="button"
                class="mx-auto block text-sm text-slate-600 hover:text-emerald-600 font-medium"
            >
                Sair
            </TextLink>
        </Form>
    </AuthLayout>
</template>

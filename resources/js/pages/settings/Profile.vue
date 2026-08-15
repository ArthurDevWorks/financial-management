<script setup lang="ts">
import ProfileController from '@/actions/App/Http/Controllers/Settings/ProfileController';
import { edit } from '@/routes/profile';
import { send } from '@/routes/verification';
import { Form, Head, Link, usePage } from '@inertiajs/vue3';

import DeleteUser from '@/components/DeleteUser.vue';
import { useToast } from '@/composables/useToast';
import InputError from '@/components/InputError.vue';
import SectionCard from '@/components/SectionCard.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Separator } from '@/components/ui/separator';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { type BreadcrumbItem } from '@/types';

interface Props {
    mustVerifyEmail: boolean;
    status?: string;
}

defineProps<Props>();

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configurações de perfil',
        href: edit().url,
    },
];

const page = usePage();
const user = page.props.auth.user;

const onSaved = () => {
    useToast().success('Perfil atualizado com sucesso.');
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configurações de perfil" />

        <SettingsLayout>
            <div class="flex flex-col space-y-6">
                <SectionCard
                    title="Informações do perfil"
                    description="Atualize seu nome e endereço de e-mail"
                >
                    <Form
                        v-bind="ProfileController.update.form()"
                        class="space-y-6"
                        :on-success="onSaved"
                        v-slot="{ errors, processing }"
                    >
                        <div class="grid gap-2">
                            <Label for="name" required>Nome</Label>
                            <Input
                                id="name"
                                class="mt-1 block w-full"
                                name="name"
                                :default-value="user.name"
                                required
                                autocomplete="name"
                                placeholder="Seu nome completo"
                            />
                            <InputError class="mt-2" :message="errors.name" />
                        </div>

                        <div class="grid gap-2">
                            <Label for="email" required>E-mail</Label>
                            <Input
                                id="email"
                                type="email"
                                class="mt-1 block w-full"
                                name="email"
                                :default-value="user.email"
                                required
                                autocomplete="username"
                                placeholder="seu@email.com"
                            />
                            <InputError class="mt-2" :message="errors.email" />
                        </div>

                        <div v-if="mustVerifyEmail && !user.email_verified_at">
                            <p class="-mt-4 text-sm text-muted-foreground">
                                Seu e-mail ainda não foi verificado.
                                <Link
                                    :href="send()"
                                    as="button"
                                    class="text-foreground underline decoration-border underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-border"
                                >
                                    Clique aqui para reenviar o e-mail de
                                    verificação.
                                </Link>
                            </p>

                            <div
                                v-if="status === 'verification-link-sent'"
                                class="mt-2 text-sm font-medium text-primary"
                            >
                                Um novo link de verificação foi enviado para seu
                                e-mail.
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <Button
                                :disabled="processing"
                                data-test="update-profile-button"
                                >Salvar</Button
                            >
                        </div>
                    </Form>
                </SectionCard>
            </div>

            <Separator class="my-6" />

            <DeleteUser />
        </SettingsLayout>
    </AppLayout>
</template>

<script setup lang="ts">
import SectionCard from '@/components/SectionCard.vue';
import TwoFactorRecoveryCodes from '@/components/TwoFactorRecoveryCodes.vue';
import TwoFactorSetupModal from '@/components/TwoFactorSetupModal.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useTwoFactorAuth } from '@/composables/useTwoFactorAuth';
import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { disable, enable, show } from '@/routes/two-factor';
import { BreadcrumbItem } from '@/types';
import { Form, Head } from '@inertiajs/vue3';
import { ShieldBan, ShieldCheck } from 'lucide-vue-next';
import { onUnmounted, ref } from 'vue';

interface Props {
    requiresConfirmation?: boolean;
    twoFactorEnabled?: boolean;
}

withDefaults(defineProps<Props>(), {
    requiresConfirmation: false,
    twoFactorEnabled: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Autenticação de dois fatores',
        href: show.url(),
    },
];

const { hasSetupData, clearTwoFactorAuthData } = useTwoFactorAuth();
const showSetupModal = ref<boolean>(false);

onUnmounted(() => {
    clearTwoFactorAuthData();
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Autenticação de dois fatores" />
        <SettingsLayout>
            <div class="space-y-6">
                <SectionCard
                    title="Autenticação de dois fatores"
                    description="Gerencie a autenticação de dois fatores da sua conta"
                >
                    <div
                        v-if="!twoFactorEnabled"
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge variant="destructive">Desativado</Badge>

                        <p class="text-muted-foreground">
                            Ao ativar a autenticação de dois fatores, você será
                            solicitado a informar um PIN seguro durante o login.
                            Este PIN pode ser obtido de um aplicativo compatível
                            com TOTP no seu celular.
                        </p>

                        <div>
                            <Button
                                v-if="hasSetupData"
                                @click="showSetupModal = true"
                            >
                                <ShieldCheck />Continuar Configuração
                            </Button>
                            <Form
                                v-else
                                v-bind="enable.form()"
                                @success="showSetupModal = true"
                                #default="{ processing }"
                            >
                                <Button type="submit" :disabled="processing">
                                    <ShieldCheck />Ativar 2FA</Button
                                ></Form
                            >
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex flex-col items-start justify-start space-y-4"
                    >
                        <Badge variant="default">Ativado</Badge>

                        <p class="text-muted-foreground">
                            Com a autenticação de dois fatores ativada, você
                            será solicitado a informar um PIN seguro e aleatório
                            durante o login, que pode ser obtido do aplicativo
                            compatível com TOTP no seu celular.
                        </p>

                        <TwoFactorRecoveryCodes />

                        <div class="relative inline">
                            <Form
                                v-bind="disable.form()"
                                #default="{ processing }"
                            >
                                <Button
                                    variant="destructive"
                                    type="submit"
                                    :disabled="processing"
                                >
                                    <ShieldBan />
                                    Desativar 2FA
                                </Button>
                            </Form>
                        </div>
                    </div>

                    <TwoFactorSetupModal
                        v-model:isOpen="showSetupModal"
                        :requiresConfirmation="requiresConfirmation"
                        :twoFactorEnabled="twoFactorEnabled"
                    />
                </SectionCard>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

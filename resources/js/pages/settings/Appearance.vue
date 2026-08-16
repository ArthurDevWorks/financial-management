<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

import AppearanceTabs from '@/components/AppearanceTabs.vue';
import SectionCard from '@/components/SectionCard.vue';
import { useTheme, type ThemeColor } from '@/composables/useTheme';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/AppLayout.vue';
import SettingsLayout from '@/layouts/settings/Layout.vue';
import { edit } from '@/routes/appearance';

const breadcrumbItems: BreadcrumbItem[] = [
    {
        title: 'Configurações de aparência',
        href: edit().url,
    },
];

const { currentTheme, themes, updateThemeColor } = useTheme();

const colorLabels: Record<ThemeColor, string> = {
    teal: 'Ciano',
    emerald: 'Esmeralda',
    blue: 'Azul',
    violet: 'Violeta',
    amber: 'Âmbar',
};

const colorSwatches: Record<ThemeColor, string> = {
    teal: 'hsl(188, 78%, 42%)',
    emerald: 'hsl(168, 65%, 35%)',
    blue: 'hsl(221.2, 83.2%, 53.3%)',
    violet: 'hsl(271, 75%, 60%)',
    amber: 'hsl(32, 88%, 52%)',
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">
        <Head title="Configurações de aparência" />

        <SettingsLayout>
            <div class="space-y-6">
                <SectionCard
                    title="Aparência"
                    description="Personalize a aparência da sua conta"
                >
                    <AppearanceTabs />
                </SectionCard>

                <SectionCard
                    title="Cor do tema"
                    description="Escolha a cor principal da interface"
                >
                    <div class="flex flex-wrap gap-3">
                        <button
                            v-for="color in themes"
                            :key="color"
                            @click="updateThemeColor(color)"
                            class="flex items-center gap-2 rounded-lg border-2 px-4 py-3 text-sm font-medium transition-all hover:opacity-90"
                            :class="
                                currentTheme === color
                                    ? 'border-ring shadow-sm'
                                    : 'border-transparent hover:border-border'
                            "
                        >
                            <span
                                class="inline-block h-5 w-5 rounded-full"
                                :style="{
                                    backgroundColor: colorSwatches[color],
                                }"
                            />
                            {{ colorLabels[color] }}
                        </button>
                    </div>
                </SectionCard>
            </div>
        </SettingsLayout>
    </AppLayout>
</template>

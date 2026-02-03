<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 flex items-center justify-center p-4 md:p-6">
        <div class="w-full max-w-sm">
            <!-- Card Container -->
            <div class="rounded-2xl border border-slate-200 bg-white shadow-lg overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-emerald-50 to-emerald-100/50 px-8 py-8 border-b border-slate-200">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex flex-col items-center gap-2 font-medium"
                        >
                            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 shadow-lg">
                                <AppLogoIcon
                                    class="size-6 fill-current text-white"
                                />
                            </div>
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-bold text-slate-900">{{ title }}</h1>
                            <p class="text-center text-sm text-slate-500">
                                {{ description }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="px-8 py-8">
                    <slot />
                </div>
            </div>

            <!-- Footer Link -->
            <div class="mt-6 text-center text-sm">
                <Link
                    :href="home()"
                    class="inline-flex items-center gap-2 text-slate-600 hover:text-emerald-600 font-medium transition"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Voltar para início
                </Link>
            </div>
        </div>
    </div>
</template>

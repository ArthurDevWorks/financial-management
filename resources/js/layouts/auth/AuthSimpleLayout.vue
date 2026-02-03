<script setup lang="ts">
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="min-h-screen bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4 md:p-6">
        <div class="w-full max-w-sm">
            <!-- Card Container -->
            <div class="rounded-xl border border-slate-700 bg-slate-800 shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="px-8 py-8 border-b border-slate-700 bg-slate-800">
                    <div class="flex flex-col items-center gap-4">
                        <Link
                            :href="home()"
                            class="flex items-center justify-center h-12 w-12 rounded-lg bg-cyan-500 hover:bg-cyan-600 transition-colors shadow-lg text-slate-900 font-bold text-lg"
                        >
                            FM
                        </Link>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-bold text-white">{{ title }}</h1>
                            <p class="text-center text-sm text-slate-400">
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
                    class="inline-flex items-center gap-2 text-slate-400 hover:text-cyan-400 font-medium transition"
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

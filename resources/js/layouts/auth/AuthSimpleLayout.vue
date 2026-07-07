<script setup lang="ts">
import { useFlashMessages } from '@/composables/useToast';
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';

defineProps<{
    title?: string;
    description?: string;
}>();

useFlashMessages();
</script>

<template>
    <div
        class="flex min-h-screen items-center justify-center bg-background p-4 md:p-6"
    >
        <Toaster
            richColors
            closeButton
            position="top-right"
            :toast-options="{
                class: '!font-sans',
            }"
        />
        <div class="w-full max-w-sm">
            <div
                class="overflow-hidden rounded-xl border border-border bg-card shadow-lg"
            >
                <div class="border-b border-border px-8 py-8">
                    <div class="flex flex-col items-center gap-4">
                        <svg
                            viewBox="0 0 32 40"
                            fill="none"
                            class="h-9 w-7 shrink-0"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <rect
                                x="3"
                                y="3"
                                width="7"
                                height="34"
                                rx="3.5"
                                class="fill-primary"
                            />
                            <rect
                                x="3"
                                y="3"
                                width="24"
                                height="7"
                                rx="3.5"
                                class="fill-primary"
                            />
                            <rect
                                x="3"
                                y="18"
                                width="18"
                                height="7"
                                rx="3.5"
                                class="fill-primary"
                            />
                        </svg>
                        <div class="space-y-2 text-center">
                            <h1 class="text-2xl font-bold text-foreground">
                                {{ title }}
                            </h1>
                            <p
                                class="text-center text-sm text-muted-foreground"
                            >
                                {{ description }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="px-8 py-8">
                    <slot />
                </div>
            </div>

            <div class="mt-6 text-center text-sm">
                <Link
                    :href="home()"
                    class="inline-flex items-center gap-2 font-medium text-muted-foreground transition hover:text-primary"
                >
                    <svg
                        class="h-4 w-4"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"
                        ></path>
                    </svg>
                    Voltar para início
                </Link>
            </div>
        </div>
    </div>
</template>

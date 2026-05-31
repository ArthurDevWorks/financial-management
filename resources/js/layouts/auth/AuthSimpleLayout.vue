<script setup lang="ts">
import { home } from '@/routes';
import { Link } from '@inertiajs/vue3';

defineProps<{
  title?: string;
  description?: string;
}>();
</script>

<template>
  <div class="flex min-h-screen items-center justify-center bg-background p-4 md:p-6">
    <div class="w-full max-w-sm">
      <div class="overflow-hidden rounded-xl border border-border bg-card shadow-lg">
        <div class="border-b border-border px-8 py-8">
          <div class="flex flex-col items-center gap-4">
            <Link
              :href="home()"
              class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary text-sm font-bold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]"
            >
              <span class="text-lg font-bold">F</span>
            </Link>
            <div class="space-y-2 text-center">
              <h1 class="text-2xl font-bold text-foreground">{{ title }}</h1>
              <p class="text-center text-sm text-muted-foreground">
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
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
          </svg>
          Voltar para início
        </Link>
      </div>
    </div>
  </div>
</template>

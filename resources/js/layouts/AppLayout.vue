<script setup lang="ts">
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarMobile from '@/components/AppSidebarMobile.vue';
import { Button } from '@/components/ui/button';
import { useFlashMessages } from '@/composables/useToast';
import type { BreadcrumbItemType } from '@/types';
import { router } from '@inertiajs/vue3';
import { Menu } from 'lucide-vue-next';
import { ref } from 'vue';
import { Toaster } from 'vue-sonner';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
    wide?: boolean;
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
    wide: false,
});

useFlashMessages();

const mobileSidebarOpen = ref(false);

const transitioning = ref(false);

router.on('start', () => {
    transitioning.value = true;
});

router.on('finish', () => {
    setTimeout(() => {
        transitioning.value = false;
    }, 50);
});
</script>

<template>
    <div class="flex h-screen bg-background">
        <!-- Desktop sidebar -->
        <AppSidebar class="hidden lg:flex" />

        <!-- Mobile sidebar overlay -->
        <AppSidebarMobile v-model:open="mobileSidebarOpen" />

        <main class="flex flex-1 flex-col overflow-y-auto">
            <!-- Mobile header -->
            <div
                class="sticky top-0 z-30 flex items-center gap-3 border-b border-border bg-background/80 px-4 py-3 backdrop-blur-md lg:hidden"
            >
                <Button
                    variant="ghost"
                    size="icon"
                    @click="mobileSidebarOpen = true"
                    aria-label="Abrir menu"
                >
                    <Menu class="h-5 w-5" />
                </Button>
                <span class="text-sm font-semibold text-foreground">Fidax</span>
            </div>

            <div
                class="animate-fade-in-up mx-auto w-full"
                :class="[
                    wide ? 'max-w-7xl' : 'max-w-6xl',
                    transitioning ? 'opacity-50' : '',
                ]"
            >
                <slot />
            </div>
        </main>

        <Toaster
            richColors
            closeButton
            position="top-right"
            :toast-options="{
                class: '!font-sans',
            }"
        />
    </div>
</template>

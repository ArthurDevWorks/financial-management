<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { router } from '@inertiajs/vue3';
import {
    ChevronLeft,
    ChevronRight,
    ChevronsLeft,
    ChevronsRight,
} from 'lucide-vue-next';

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    last_page: number;
    from: number;
    to: number;
    total: number;
    links: PaginationLink[];
}

defineProps<{
    meta: PaginationMeta;
}>();

function goToPage(url: string | null) {
    if (!url) return;
    router.visit(url, { preserveState: true, replace: true });
}
</script>

<template>
    <div
        v-if="meta.last_page > 1"
        class="flex items-center justify-between gap-4 border-t border-border pt-4"
    >
        <p class="text-xs text-muted-foreground">
            Mostrando {{ meta.from }}-{{ meta.to }} de {{ meta.total }}
        </p>
        <div class="flex items-center gap-1">
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8"
                :disabled="meta.current_page === 1"
                @click="goToPage(meta.links[0]?.url ?? null)"
            >
                <ChevronsLeft class="h-4 w-4" />
            </Button>
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8"
                :disabled="meta.current_page === 1"
                @click="
                    goToPage(meta.links[meta.current_page - 1]?.url ?? null)
                "
            >
                <ChevronLeft class="h-4 w-4" />
            </Button>
            <div class="flex items-center gap-1 px-1">
                <Button
                    v-for="(link, index) in meta.links.slice(1, -1)"
                    :key="index"
                    variant="ghost"
                    size="icon"
                    class="h-8 w-8 text-xs"
                    :class="
                        link.active
                            ? 'bg-primary/10 font-semibold text-primary'
                            : ''
                    "
                    @click="goToPage(link.url)"
                >
                    {{ link.label }}
                </Button>
            </div>
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8"
                :disabled="meta.current_page === meta.last_page"
                @click="
                    goToPage(meta.links[meta.current_page + 1]?.url ?? null)
                "
            >
                <ChevronRight class="h-4 w-4" />
            </Button>
            <Button
                variant="ghost"
                size="icon"
                class="h-8 w-8"
                :disabled="meta.current_page === meta.last_page"
                @click="
                    goToPage(meta.links[meta.links.length - 1]?.url ?? null)
                "
            >
                <ChevronsRight class="h-4 w-4" />
            </Button>
        </div>
    </div>
</template>

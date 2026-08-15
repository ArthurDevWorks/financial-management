<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
import { Sheet, SheetContent } from '@/components/ui/sheet';
import { footerNavItems, navSections } from '@/lib/navigation';
import { router } from '@inertiajs/vue3';

const open = defineModel<boolean>('open', { required: true });

function navigate(href: string, method: 'get' | 'post' = 'get') {
    open.value = false;
    if (method === 'post') {
        router.post(href);
    } else {
        router.visit(href);
    }
}
</script>

<template>
    <Sheet :open="open" @update:open="open = $event">
        <SheetContent
            side="left"
            class="flex w-64 flex-col border-r border-sidebar-border bg-sidebar p-0"
        >
            <div class="border-b border-sidebar-border px-5 py-5">
                <AppLogo />
            </div>

            <!-- MENU POR MÓDULOS -->
            <nav class="flex-1 overflow-y-auto px-3 py-5">
                <div
                    v-for="section in navSections"
                    :key="section.title"
                    class="mb-6 last:mb-0"
                >
                    <p
                        class="mb-2 px-2 pb-1 text-[0.6875rem] font-semibold tracking-[0.06em] text-muted-foreground uppercase"
                    >
                        {{ section.title }}
                    </p>

                    <div class="space-y-0.5">
                        <Button
                            v-for="item in section.items"
                            :key="item.href"
                            variant="ghost"
                            class="w-full justify-start gap-3 px-3 py-2 text-sm font-medium"
                            @click="navigate(item.href)"
                        >
                            <component
                                :is="item.icon"
                                class="h-[18px] w-[18px] shrink-0 text-muted-foreground"
                            />
                            {{ item.label }}
                        </Button>
                    </div>
                </div>
            </nav>

            <!-- FOOTER -->
            <div class="border-t border-sidebar-border px-3 py-3">
                <div class="space-y-0.5">
                    <Button
                        v-for="item in footerNavItems"
                        :key="item.href"
                        variant="ghost"
                        class="w-full justify-start gap-3 px-3 py-2 text-sm font-medium"
                        :class="
                            item.danger
                                ? 'text-destructive hover:bg-destructive/10'
                                : ''
                        "
                        @click="navigate(item.href, item.method)"
                    >
                        <component
                            :is="item.icon"
                            class="h-[18px] w-[18px] shrink-0"
                            :class="
                                item.danger
                                    ? 'text-destructive'
                                    : 'text-muted-foreground'
                            "
                        />
                        {{ item.label }}
                    </Button>
                </div>
            </div>
        </SheetContent>
    </Sheet>
</template>

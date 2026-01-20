<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import SidebarItem from '@/components/SidebarItem.vue'

import {
    Landmark,
    Wallet,
    TrendingUp,
    TrendingDown,
    Tags,
    PiggyBank,
    Settings,
    LogOut,
} from 'lucide-vue-next'

</script>

<template>
    <aside
        class="fixed left-0 top-0 z-40 flex h-screen w-64 flex-col bg-[#0F223A] text-white"
    >
        <!-- LOGO -->
        <div class="flex items-center gap-3 px-6 py-5 border-b border-white/10">
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-emerald-500">
                <PiggyBank class="h-6 w-6 text-white" />
            </div>

            <div>
                <h1 class="text-lg font-bold leading-none">
                    Fidax
                </h1>
                <div class="mt-1 text-sm text-white/60 leading-tight">
                    <p>Fé no caminho.</p>
                    <p>Paz no destino.</p>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <nav class="flex-1 overflow-y-auto px-4 py-6 space-y-2">
            <p class="px-2 text-xs font-semibold uppercase text-white/40">
                Menu Principal
            </p>

            <SidebarItem
                label="Bancos"
                :icon="Landmark"
                href="/banks"
            />

            <SidebarItem
                label="Contas"
                :icon="Wallet"
                href="/accounts"
            />

            <SidebarItem
                label="Receitas"
                :icon="TrendingUp"
                href="/revenues"
            />

            <SidebarItem
                label="Despesas"
                :icon="TrendingDown"
                href="/expenses"
            />

            <SidebarItem
                label="Categorias"
                :icon="Tags"
                href="/categories"
            />

            <SidebarItem
                label="Investimentos"
                :icon="PiggyBank"
                href="/investments"
            />
        </nav>

        <!-- FOOTER -->
        <div class="px-4 py-4 border-t border-white/10 space-y-2">
            <SidebarItem
                label="Configurações"
                :icon="Settings"
                href="/settings"
            />

            <SidebarItem
                label="Sair"
                :icon="LogOut"
                href="/logout"
                method="post"
                danger
            />
        </div>
    </aside>
</template>

<script setup lang="ts">
import AppLogo from '@/components/AppLogo.vue';
import SidebarItem from '@/components/SidebarItem.vue';

import {
  LayoutDashboard,
  Landmark,
  Wallet,
  ArrowRightLeft,
  Tags,
  PiggyBank,
  ChartNoAxesCombined,
  Settings,
  LogOut,
} from 'lucide-vue-next';
</script>

<template>
  <aside
    class="flex h-full w-64 flex-col border-r border-sidebar-border bg-sidebar"
  >
    <!-- LOGO -->
    <div class="border-b border-sidebar-border px-5 py-5">
      <AppLogo />
    </div>

    <!-- MENU -->
    <nav class="flex-1 overflow-y-auto px-3 py-5">
      <p
        class="mb-2 px-2 pb-1 text-[0.6875rem] font-semibold uppercase tracking-[0.06em] text-muted-foreground"
      >
        Financeiro
      </p>

      <div class="space-y-0.5">
        <SidebarItem
          label="Dashboard"
          :icon="LayoutDashboard"
          href="/dashboard"
        />
        <SidebarItem label="Bancos" :icon="Landmark" href="/banks" />
        <SidebarItem label="Contas" :icon="Wallet" href="/accounts" />
        <SidebarItem
          label="Lançamentos"
          :icon="ArrowRightLeft"
          href="/releases"
        />
        <SidebarItem label="Categorias" :icon="Tags" href="/categories" />
        <SidebarItem
          label="Investimentos"
          :icon="PiggyBank"
          href="/investiments"
        />
        <SidebarItem
          label="Valuations"
          :icon="ChartNoAxesCombined"
          href="/valuations"
        />
      </div>
    </nav>

    <!-- FOOTER -->
    <div class="border-t border-sidebar-border px-3 py-3">
      <div class="space-y-0.5">
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
    </div>
  </aside>
</template>

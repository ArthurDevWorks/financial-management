<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import {
  Sheet,
  SheetContent,
} from '@/components/ui/sheet';
import AppLogo from '@/components/AppLogo.vue';
import { Button } from '@/components/ui/button';
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
import type { FunctionalComponent } from 'vue';
import type { LucideProps } from 'lucide-vue-next';

const open = defineModel<boolean>('open', { required: true });

interface NavItem {
  label: string;
  icon: FunctionalComponent<LucideProps>;
  href: string;
  method?: 'get' | 'post';
  danger?: boolean;
}

const navItems: NavItem[] = [
  { label: 'Dashboard', icon: LayoutDashboard, href: '/dashboard' },
  { label: 'Bancos', icon: Landmark, href: '/banks' },
  { label: 'Contas', icon: Wallet, href: '/accounts' },
  { label: 'Lançamentos', icon: ArrowRightLeft, href: '/releases' },
  { label: 'Categorias', icon: Tags, href: '/categories' },
  { label: 'Investimentos', icon: PiggyBank, href: '/investiments' },
  { label: 'Valuations', icon: ChartNoAxesCombined, href: '/valuations' },
];

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
    <SheetContent side="left" class="flex w-64 flex-col border-r border-sidebar-border bg-sidebar p-0">
      <div class="border-b border-sidebar-border px-5 py-5">
        <AppLogo />
      </div>

      <nav class="flex-1 overflow-y-auto px-3 py-5">
        <p class="mb-2 px-2 pb-1 text-[0.6875rem] font-semibold uppercase tracking-[0.06em] text-muted-foreground">
          Financeiro
        </p>
        <div class="space-y-0.5">
          <Button
            v-for="item in navItems"
            :key="item.href"
            variant="ghost"
            class="w-full justify-start gap-3 px-3 py-2 text-sm font-medium"
            @click="navigate(item.href)"
          >
            <component :is="item.icon" class="h-[18px] w-[18px] shrink-0 text-muted-foreground" />
            {{ item.label }}
          </Button>
        </div>
      </nav>

      <div class="border-t border-sidebar-border px-3 py-3">
        <div class="space-y-0.5">
          <Button
            variant="ghost"
            class="w-full justify-start gap-3 px-3 py-2 text-sm font-medium"
            @click="navigate('/settings')"
          >
            <Settings class="h-[18px] w-[18px] shrink-0 text-muted-foreground" />
            Configurações
          </Button>
          <Button
            variant="ghost"
            class="w-full justify-start gap-3 px-3 py-2 text-sm font-medium text-destructive hover:bg-destructive/10"
            @click="navigate('/logout', 'post')"
          >
            <LogOut class="h-[18px] w-[18px] shrink-0 text-destructive" />
            Sair
          </Button>
        </div>
      </div>
    </SheetContent>
  </Sheet>
</template>

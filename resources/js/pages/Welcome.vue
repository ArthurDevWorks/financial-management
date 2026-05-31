<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import { Button } from '@/components/ui/button';
import {
  Banknote,
  TrendingUp,
  TrendingDown,
  PiggyBank,
  Tags,
  BarChart3,
  Infinity,
  ShieldCheck,
  Zap,
} from 'lucide-vue-next';

withDefaults(
  defineProps<{
    canRegister: boolean;
  }>(),
  {
    canRegister: true,
  },
);
</script>

<template>
  <Head title="Fidax - Gestão Financeira Inteligente">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:wght@400;500;600;700&display=swap" rel="stylesheet" />
  </Head>

  <div class="min-h-screen bg-background text-foreground">
    <!-- Navigation -->
    <nav class="fixed top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur-md">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <Link href="/" class="group flex items-center gap-3">
          <svg
          viewBox="0 0 32 40"
          fill="none"
          class="h-9 w-7 shrink-0"
          xmlns="http://www.w3.org/2000/svg"
           >
          <rect x="3" y="3" width="7" height="34" rx="3.5" class="fill-primary" />
          <rect x="3" y="3" width="24" height="7" rx="3.5" class="fill-primary" />
          <rect x="3" y="18" width="18" height="7" rx="3.5" class="fill-primary" />
        </svg>
          <span class="hidden text-xl font-bold sm:block">Fidax</span>
        </Link>

        <div class="flex items-center gap-4">
          <template v-if="$page.props.auth.user">
            <Button variant="glow" as-child>
              <Link :href="dashboard()">Dashboard</Link>
            </Button>
          </template>
          <template v-else>
            <Button variant="outline" as-child>
              <Link :href="login()">Entrar</Link>
            </Button>
            <Button v-if="canRegister" variant="glow" as-child>
              <Link :href="register()">Registrar</Link>
            </Button>
          </template>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <div class="px-4 pb-20 pt-32 sm:px-6 lg:px-8">
      <div class="mx-auto max-w-5xl text-center">
        <div class="mb-8 inline-block">
          <span class="rounded-full border border-primary/20 bg-primary/10 px-4 py-2 text-sm font-semibold text-primary">
            Gestão Financeira Completa
          </span>
        </div>

        <h1 class="text-balance bg-gradient-to-r from-primary to-[#0D8A6B] bg-clip-text pb-2 text-5xl font-bold text-transparent sm:text-6xl lg:text-7xl">
          Controle Total de Suas Finanças
        </h1>

        <p class="mx-auto mb-12 mt-6 max-w-3xl text-xl leading-relaxed text-muted-foreground sm:text-2xl">
          Gerencie contas bancárias, receitas, despesas, investimentos e categorias em um só lugar.
          Uma solução inteligente e intuitiva para sua saúde financeira.
        </p>

        <div class="mb-20 flex flex-col justify-center gap-4 sm:flex-row">
          <template v-if="!$page.props.auth.user">
            <Button variant="glow" size="lg" as-child class="px-8 py-6 text-lg">
              <Link :href="register()">Começar Agora</Link>
            </Button>
            <Button variant="outline" size="lg" as-child class="px-8 py-6 text-lg">
              <Link :href="login()">Já Tenho Conta</Link>
            </Button>
          </template>
          <template v-else>
            <Button variant="glow" size="lg" as-child class="px-8 py-6 text-lg">
              <Link :href="dashboard()">Ir ao Dashboard</Link>
            </Button>
          </template>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="mx-auto mb-20 max-w-6xl">
        <h2 class="mb-16 text-center text-4xl font-bold text-foreground">Recursos Principais</h2>

        <div class="grid gap-8 md:grid-cols-3">
          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-primary/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/20">
              <Banknote class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Contas Bancárias</h3>
            <p class="text-muted-foreground">Gerencie múltiplas contas de diferentes bancos em um único painel</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-revenue/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-revenue/10 text-revenue transition-colors group-hover:bg-revenue/20">
              <TrendingUp class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Receitas</h3>
            <p class="text-muted-foreground">Registre e categorize todas as suas fontes de renda</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-destructive/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-destructive/10 text-destructive transition-colors group-hover:bg-destructive/20">
              <TrendingDown class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Despesas</h3>
            <p class="text-muted-foreground">Controle seus gastos com categorização automática</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-investment/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-investment/10 text-investment transition-colors group-hover:bg-investment/20">
              <PiggyBank class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Investimentos</h3>
            <p class="text-muted-foreground">Acompanhe seus investimentos e rentabilidade</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-accent/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-accent/10 text-accent transition-colors group-hover:bg-accent/20">
              <Tags class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Categorias</h3>
            <p class="text-muted-foreground">Organize receitas e despesas com categorias personalizadas</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border-chart-3/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-chart-3/10 text-chart-3 transition-colors group-hover:bg-chart-3/20">
              <BarChart3 class="h-7 w-7" />
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Análises</h3>
            <p class="text-muted-foreground">Visualize gráficos e relatórios de sua situação financeira</p>
          </div>
        </div>
      </div>

      <!-- Stats Section -->
      <div class="mx-auto mb-20 max-w-5xl">
        <div class="grid gap-8 text-center md:grid-cols-3">
          <div class="rounded-2xl border border-border bg-card p-8">
            <Infinity class="mx-auto mb-2 h-8 w-8 text-primary" />
            <p class="text-muted-foreground">Contas Ilimitadas</p>
          </div>
          <div class="rounded-2xl border border-border bg-card p-8">
            <ShieldCheck class="mx-auto mb-2 h-8 w-8 text-primary" />
            <p class="text-muted-foreground">Segurança Garantida</p>
          </div>
          <div class="rounded-2xl border border-border bg-card p-8">
            <Zap class="mx-auto mb-2 h-8 w-8 text-primary" />
            <p class="text-muted-foreground">Rápido e Eficiente</p>
          </div>
        </div>
      </div>

      <!-- CTA Section -->
      <div class="mx-auto max-w-3xl border-t border-border py-20 text-center">
        <h2 class="mb-6 text-3xl font-bold sm:text-4xl">Pronto para Controlar Suas Finanças?</h2>
        <p class="mb-8 text-xl text-muted-foreground">
          Junte-se a milhares de pessoas que já estão usando Fidax para organizar suas finanças.
        </p>

        <template v-if="!$page.props.auth.user">
          <Button variant="glow" size="lg" as-child class="px-8 py-6 text-lg">
            <Link :href="register()">Criar Conta Agora</Link>
          </Button>
        </template>
      </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-border bg-background/50 py-12 backdrop-blur-md">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col items-center justify-between gap-6 md:flex-row">
          <div class="flex items-center gap-3">
            <AppLogoIcon class="h-6 w-5 fill-current text-primary" />
            <span class="text-sm font-semibold text-foreground">Fidax</span>
          </div>
          <div class="flex gap-6 text-sm text-muted-foreground">
            <Link href="/" class="transition-colors hover:text-foreground">Termos de Uso</Link>
            <Link href="/" class="transition-colors hover:text-foreground">Privacidade</Link>
            <Link href="/" class="transition-colors hover:text-foreground">Contato</Link>
          </div>
          <p class="text-sm text-muted-foreground">&copy; 2026 Fidax. Todos os direitos reservados.</p>
        </div>
      </div>
    </footer>
  </div>
</template>

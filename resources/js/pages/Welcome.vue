<script setup lang="ts">
import { dashboard, login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';

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
    <link rel="preconnect" href="https://rsms.me/" />
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
  </Head>

  <div class="min-h-screen bg-background text-foreground">
    <!-- Navigation -->
    <nav class="fixed top-0 z-50 w-full border-b border-border bg-background/80 backdrop-blur-md">
      <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">
        <Link href="/" class="group flex items-center gap-3">
          <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary font-bold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 group-hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]">
            <span class="text-lg">F</span>
          </div>
          <span class="hidden text-xl font-bold sm:block">Fidax</span>
        </Link>

        <div class="flex items-center gap-4">
          <template v-if="$page.props.auth.user">
            <Link
              :href="dashboard()"
              class="rounded-lg bg-primary px-6 py-2.5 font-semibold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]"
            >
              Dashboard
            </Link>
          </template>
          <template v-else>
            <Link
              :href="login()"
              class="rounded-lg border border-border px-6 py-2.5 font-semibold text-foreground transition-all duration-200 hover:border-[rgba(255,255,255,0.2)] hover:text-foreground"
            >
              Entrar
            </Link>
            <Link
              v-if="canRegister"
              :href="register()"
              class="rounded-lg bg-primary px-6 py-2.5 font-semibold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]"
            >
              Registrar
            </Link>
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
            <Link
              :href="register()"
              class="transform rounded-lg bg-primary px-8 py-4 text-lg font-bold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 hover:scale-105 hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]"
            >
              Começar Agora
            </Link>
            <Link
              :href="login()"
              class="transform rounded-lg border-2 border-border px-8 py-4 text-lg font-bold text-foreground transition-all duration-200 hover:border-primary hover:bg-primary/5"
            >
              Já Tenho Conta
            </Link>
          </template>
          <template v-else>
            <Link
              :href="dashboard()"
              class="transform rounded-lg bg-gradient-to-r from-primary to-[#0D8A6B] px-8 py-4 text-lg font-bold text-primary-foreground shadow-lg transition-all duration-200 hover:scale-105 hover:shadow-2xl"
            >
              Ir ao Dashboard
            </Link>
          </template>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="mx-auto mb-20 max-w-6xl">
        <h2 class="mb-16 text-center text-4xl font-bold text-foreground">Recursos Principais</h2>

        <div class="grid gap-8 md:grid-cols-3">
          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-primary/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Contas Bancárias</h3>
            <p class="text-muted-foreground">Gerencie múltiplas contas de diferentes bancos em um único painel</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-revenue/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-revenue/10 text-revenue transition-colors group-hover:bg-revenue/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Receitas</h3>
            <p class="text-muted-foreground">Registre e categorize todas as suas fontes de renda</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-destructive/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-destructive/10 text-destructive transition-colors group-hover:bg-destructive/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17H7m0 0H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v10.5m0 0v4.5m0-4.5h6a2 2 0 012 2v4.5m0 0V19a2 2 0 01-2 2h-6a2 2 0 01-2-2v-4.5" />
              </svg>
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Despesas</h3>
            <p class="text-muted-foreground">Controle seus gastos com categorização automática</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-investment/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-investment/10 text-investment transition-colors group-hover:bg-investment/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
              </svg>
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Investimentos</h3>
            <p class="text-muted-foreground">Acompanhe seus investimentos e rentabilidade</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-orange-500/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-orange-500/10 text-orange-400 transition-colors group-hover:bg-orange-500/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
              </svg>
            </div>
            <h3 class="mb-3 text-xl font-bold text-foreground">Categorias</h3>
            <p class="text-muted-foreground">Organize receitas e despesas com categorias personalizadas</p>
          </div>

          <div class="group rounded-2xl border border-border bg-card p-8 transition-all duration-200 hover:-translate-y-0.5 hover:border hover:border-indigo-500/30 hover:shadow-md">
            <div class="mb-6 flex h-14 w-14 items-center justify-center rounded-lg bg-indigo-500/10 text-indigo-400 transition-colors group-hover:bg-indigo-500/20">
              <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
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
            <div class="mb-2 text-4xl font-bold text-primary">∞</div>
            <p class="text-muted-foreground">Contas Ilimitadas</p>
          </div>
          <div class="rounded-2xl border border-border bg-card p-8">
            <div class="mb-2 text-4xl font-bold text-primary">🔒</div>
            <p class="text-muted-foreground">Segurança Garantida</p>
          </div>
          <div class="rounded-2xl border border-border bg-card p-8">
            <div class="mb-2 text-4xl font-bold text-primary">⚡</div>
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
          <Link
            :href="register()"
            class="inline-block transform rounded-lg bg-primary px-8 py-4 text-lg font-bold text-primary-foreground shadow-[0_0_16px_rgba(26,184,148,0.25)] transition-all duration-200 hover:scale-105 hover:shadow-[0_0_24px_rgba(26,184,148,0.4)]"
          >
            Criar Conta Agora
          </Link>
        </template>
      </div>
    </div>

    <!-- Footer -->
    <footer class="border-t border-border bg-background/50 py-12 backdrop-blur-md">
      <div class="mx-auto max-w-7xl px-4 text-center text-muted-foreground sm:px-6 lg:px-8">
        <p>&copy; 2026 Fidax. Todos os direitos reservados.</p>
      </div>
    </footer>
  </div>
</template>

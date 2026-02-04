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

import {
    PiggyBank,
} from 'lucide-vue-next'
</script>

<template>
    <Head title="Fidax - Gestão Financeira Inteligente">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    
    <div class="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white">
        <!-- Navigation -->
        <nav class="fixed top-0 w-full z-50 bg-slate-900/80 backdrop-blur-md border-b border-slate-700/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                <Link href="/" class="flex items-center gap-3 group">
                    <div class="flex items-center justify-center h-10 w-10 rounded-lg bg-cyan-500 group-hover:bg-cyan-600 transition-colors shadow-lg">
                       <PiggyBank class="h-6 w-6 text-white" />
                    </div>
                    <span class="text-xl font-bold hidden sm:block">Fidax</span>
                </Link>
                
                <div class="flex items-center gap-4">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="dashboard()"
                            class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 bg-cyan-500 hover:bg-cyan-600 text-slate-900 shadow-lg hover:shadow-xl"
                        >
                            Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="login()"
                            class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 text-slate-300 hover:text-white border border-slate-600 hover:border-slate-500"
                        >
                            Entrar
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="register()"
                            class="px-6 py-2.5 rounded-lg font-semibold transition-all duration-200 bg-cyan-500 hover:bg-cyan-600 text-slate-900 shadow-lg hover:shadow-xl"
                        >
                            Registrar
                        </Link>
                    </template>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <div class="pt-32 pb-20 px-4 sm:px-6 lg:px-8">
            <div class="max-w-5xl mx-auto text-center">
                <div class="mb-8 inline-block">
                    <span class="px-4 py-2 rounded-full bg-cyan-500/10 border border-cyan-500/30 text-cyan-400 text-sm font-semibold">
                        ✨ Gestão Financeira Completa
                    </span>
                </div>
                
                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold mb-6 bg-gradient-to-r from-cyan-400 to-blue-400 bg-clip-text text-transparent">
                    Controle Total de Suas Finanças
                </h1>
                
                <p class="text-xl sm:text-2xl text-slate-400 mb-12 max-w-3xl mx-auto leading-relaxed">
                    Gerencie contas bancárias, receitas, despesas, investimentos e categorias em um só lugar. 
                    Uma solução inteligente e intuitiva para sua saúde financeira.
                </p>
                
                <div class="flex flex-col sm:flex-row gap-4 justify-center mb-20">
                    <template v-if="!$page.props.auth.user">
                        <Link
                            :href="register()"
                            class="px-8 py-4 rounded-lg font-bold text-lg transition-all duration-200 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-slate-900 shadow-lg hover:shadow-2xl transform hover:scale-105"
                        >
                            Começar Agora
                        </Link>
                        <Link
                            :href="login()"
                            class="px-8 py-4 rounded-lg font-bold text-lg transition-all duration-200 border-2 border-slate-600 text-white hover:border-cyan-500 hover:bg-cyan-500/10"
                        >
                            Já Tenho Conta
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="dashboard()"
                            class="px-8 py-4 rounded-lg font-bold text-lg transition-all duration-200 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-slate-900 shadow-lg hover:shadow-2xl transform hover:scale-105"
                        >
                            Ir ao Dashboard
                        </Link>
                    </template>
                </div>
            </div>

            <!-- Features Grid -->
            <div class="max-w-6xl mx-auto mb-20">
                <h2 class="text-4xl font-bold text-center mb-16">Recursos Principais</h2>
                
                <div class="grid md:grid-cols-3 gap-8">
                    <!-- Feature 1 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-cyan-500/20 mb-6 group-hover:bg-cyan-500/30 transition-colors">
                            <svg class="w-7 h-7 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Contas Bancárias</h3>
                        <p class="text-slate-400">Gerencie múltiplas contas de diferentes bancos em um único painel</p>
                    </div>

                    <!-- Feature 2 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-green-500/20 mb-6 group-hover:bg-green-500/30 transition-colors">
                            <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Receitas</h3>
                        <p class="text-slate-400">Registre e categorize todas as suas fontes de renda</p>
                    </div>

                    <!-- Feature 3 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-red-500/20 mb-6 group-hover:bg-red-500/30 transition-colors">
                            <svg class="w-7 h-7 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17H7m0 0H5a2 2 0 01-2-2V5a2 2 0 012-2h6a2 2 0 012 2v10.5m0 0v4.5m0-4.5h6a2 2 0 012 2v4.5m0 0V19a2 2 0 01-2 2h-6a2 2 0 01-2-2v-4.5"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Despesas</h3>
                        <p class="text-slate-400">Controle seus gastos com categorização automática</p>
                    </div>

                    <!-- Feature 4 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-purple-500/20 mb-6 group-hover:bg-purple-500/30 transition-colors">
                            <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Investimentos</h3>
                        <p class="text-slate-400">Acompanhe seus investimentos e rentabilidade</p>
                    </div>

                    <!-- Feature 5 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-orange-500/20 mb-6 group-hover:bg-orange-500/30 transition-colors">
                            <svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Categorias</h3>
                        <p class="text-slate-400">Organize receitas e despesas com categorias personalizadas</p>
                    </div>

                    <!-- Feature 6 -->
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50 hover:bg-slate-800 transition-all duration-200 hover:border-cyan-500/30 group">
                        <div class="flex items-center justify-center h-14 w-14 rounded-lg bg-indigo-500/20 mb-6 group-hover:bg-indigo-500/30 transition-colors">
                            <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold mb-3">Análises</h3>
                        <p class="text-slate-400">Visualize gráficos e relatórios de sua situação financeira</p>
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="max-w-5xl mx-auto mb-20">
                <div class="grid md:grid-cols-3 gap-8 text-center">
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50">
                        <div class="text-4xl font-bold text-cyan-400 mb-2">∞</div>
                        <p class="text-slate-400">Contas Ilimitadas</p>
                    </div>
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50">
                        <div class="text-4xl font-bold text-cyan-400 mb-2">🔒</div>
                        <p class="text-slate-400">Segurança Garantida</p>
                    </div>
                    <div class="p-8 rounded-2xl border border-slate-700 bg-slate-800/50">
                        <div class="text-4xl font-bold text-cyan-400 mb-2">⚡</div>
                        <p class="text-slate-400">Rápido e Eficiente</p>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="max-w-3xl mx-auto text-center py-20 border-t border-slate-700">
                <h2 class="text-3xl sm:text-4xl font-bold mb-6">Pronto para Controlar Suas Finanças?</h2>
                <p class="text-xl text-slate-400 mb-8">
                    Junte-se a milhares de pessoas que já estão usando Fidax para organizar suas finanças.
                </p>
                
                <template v-if="!$page.props.auth.user">
                    <Link
                        :href="register()"
                        class="inline-block px-8 py-4 rounded-lg font-bold text-lg transition-all duration-200 bg-gradient-to-r from-cyan-500 to-blue-500 hover:from-cyan-600 hover:to-blue-600 text-slate-900 shadow-lg hover:shadow-2xl transform hover:scale-105"
                    >
                        Criar Conta Agora
                    </Link>
                </template>
            </div>
        </div>

        <!-- Footer -->
        <footer class="border-t border-slate-700 bg-slate-900/50 backdrop-blur-md py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-slate-400">
                <p>&copy; 2026 Fidax. Todos os direitos reservados.</p>
            </div>
        </footer>
    </div>
</template>

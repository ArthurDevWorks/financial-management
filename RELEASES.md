# Releases

| Release | Versão | Status | Data |
|---|---|---|---|
| Gestão Financeira | v1.0.0 | ✅ Lançada | 2026-07-07 |
| Módulo Screening | v2.0.0 | 🚧 Em desenvolvimento | Prevista |
| Valuation Avançado | v2.1.0 | 🚧 Em planejamento | Prevista |
| Automação e Infraestrutura | v3.0.0 | 📋 Planejada | Prevista |
| Financeiro Avançado | v3.1.0 | 📋 Planejada | Prevista |

---

## v1.0.0 — Gestão Financeira (Lançada)

**Data:** 2026-07-07

### O que foi entregue
- Dashboard financeiro com resumo de saldo, receitas, despesas
- Gráficos (doughnut por categoria, linha de evolução)
- CRUD completo de bancos, contas, categorias, lançamentos
- CRUD de investimentos com cotação automática via brapi.dev
- Valuation DCF (Fluxo de Caixa Descontado)
- Valuation Preço Teto Projetivo
- Exportação CSV em todas as listagens
- Autenticação completa (registro, login, 2FA, verificação de email)
- Design system Banco Premium (tokens semânticos, dark mode)
- Tema claro/escuro

### Pacotes instalados
- Laravel 12 + Inertia.js v2 + Vue 3 + TypeScript
- Tailwind CSS v4 + shadcn-vue (Reka UI)
- Chart.js + vue-chartjs
- Laravel Fortify + Sanctum
- brapi.dev API integration

---

## v2.0.0 — Módulo Screening (Em desenvolvimento)

**Status:** Issues criadas, implementação em andamento

### Escopo
- Cache de indicadores fundamentalistas no banco local
- Tela de screening com filtros combináveis
- Página detalhada de cada ativo com indicadores e gráficos
- Comparação side-by-side de ativos
- Favoritar ativos
- Templates de filtros salvos
- Integração com brapi.dev (statistics, financial-data, profile)
- Scraping complementar

---

## v2.1.0 — Valuation Avançado (Em planejamento)

### Escopo
- Modelo de Gordon para FIIs (perpetuidade fixa em 3%)
- Auto-preenchimento de premissas com dados em cache
- Página unificada com indicadores + valuation
- Atualização automática de cotação e dados de mercado

---

## v3.0.0 — Automação e Infraestrutura (Planejada)

### Escopo
- Jobs de sincronização de indicadores
- Comandos Artisan para manutenção
- Schedule automático
- Scraping estruturado
- Sistema de e-mails

---

## v3.1.0 — Financeiro Avançado (Planejada)

### Escopo
- Cartões de crédito e gerenciamento de faturas
- Lançamentos recorrentes e parcelados
- Planejamento financeiro mensal e anual
- Metas e objetivos financeiros

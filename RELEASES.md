# Releases

| Release | Versão | Status | Data |
|---|---|---|---|
| Gestão Financeira | v1.0.0 | ✅ Lançada | 2026-07-07 |
| Módulo Screening | v2.0.0 | ✅ Concluída | 2026-08-15 |
| Valuation Avançado | v2.1.0 | ✅ Concluída | 2026-08-15 |
| Automação e Infraestrutura | v3.0.0 | 🚧 Parcial | Prevista |
| Financeiro Avançado | v3.1.0 | 🚧 Parcial | Prevista |

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

## v2.0.0 — Módulo Screening (Concluída)

**Data:** 2026-08-15

### O que foi entregue
- Cache de indicadores fundamentalistas (asset_indicators, fii_indicators)
- Tabela consolidada de ativos com 40+ campos
- Tela de screening com 10+ filtros combináveis
- Página detalhada de cada ativo com gráficos de preço e dividendos
- Comparação side-by-side de até 5 ativos (radar + bar chart)
- Favoritar ativos (AssetFavorite)
- Templates de filtros salvos (ScreeningFilter)
- Scraping complementar (StatusInvest, Fundamentus, Investidor10)
- Normalização de setores e nomes (SectorMapper, NameNormalizer, FiiSegmentMapper)
- BrapiService expandido (quotes, completeQuote, historical, FII indicators)
- AssetSyncService (sincronização multi-fonte, 807 linhas)

---

## v2.1.0 — Valuation Avançado (Concluída)

**Data:** 2026-08-15

### O que foi entregue
- Modelo de Gordon para FIIs (fórmula P = D0*(1+g)/(ke-g))
- Auto-preenchimento de premissas com dados do cache
- Página unificada com todos os 3 métodos de valuation
- Campo ROE/Payout editáveis no Preço Teto
- Taxa de crescimento esperada calculada (ROE × (1 - Payout%))
- Margem de segurança unificada em todos os métodos
- Composables useDcf.ts e usePrecoTeto.ts
- MarginGauge component para visualização

---

## v3.0.0 — Automação e Infraestrutura (Parcial)

### O que foi entregue
- Artisan commands: assets:sync, releases:generate-recurring, assets:normalize-sectors
- Schedule configurado: assets:sync 3x/dias úteis, releases:generate-recurring diário
- Scraping de fontes complementares (StatusInvest, Fundamentus, Investidor10)
- Jobs de sincronização (SyncAssetIndicatorsJob)

### Pendente
- Sistema de e-mails (notificações, recovery)
- Fila de jobs para processamento assíncrono

---

## v3.1.0 — Financeiro Avançado (Parcial)

### O que foi entregue
- Cartões de crédito com CRUD completo
- Faturas mensais com navegação de 15 meses
- Lançamentos recorrentes (5 frequências)
- Lançamentos parcelados com distribuição automática
- Métodos de pagamento (dinheiro, cartão crédito, cartão débito, pix)
- Status de lançamentos (previsto, pago, cancelado)
- Dashboard aprimorado com filtering por período

### Pendente
- Planejamento financeiro mensal/anual
- Metas e objetivos financeiros
- Orçamento por categoria
- Registrar pagamento total ou parcial de fatura

# Roadmap do Produto

## Visão Geral

O Fidax é uma plataforma financeira que evolui em 4 eixos principais:

1. **Gestão Financeira** — Controle de finanças pessoais (bancos, contas, receitas, despesas)
2. **Screening** — Busca inteligente de ativos com filtros fundamentalistas
3. **Valuation** — Cálculo de valuation com DCF, Preço Teto e Gordon
4. **Automação** — Jobs, scraping, sincronização de dados

---

## Release 1 — Gestão Financeira ✅ (v1.0.0)

**Status:** Concluída  
**Data:** 2026-07-07  
**Objetivo:** Núcleo de gestão financeira pessoal com cadastro de ativos e cotação automática.

**Entregues:**
- Dashboard financeiro com gráficos e indicadores
- CRUD de bancos, contas, categorias, lançamentos
- CRUD de investimentos com cotação via brapi.dev
- Valuation DCF (Fluxo de Caixa Descontado)
- Valuation Preço Teto Projetivo
- Autenticação com Fortify (2FA, email verification)

---

## Release 2 — Módulo Screening ✅ (v2.0.0)

**Status:** Concluída  
**Objetivo:** Busca e descoberta de ativos com filtros fundamentalistas, cache de dados e comparação.

**Entregues:**
- Cache de indicadores fundamentalistas (asset_indicators, fii_indicators)
- Tabela consolidada de ativos (assets) com 40+ campos
- Filtros por DY, P/L, ROE, P/VP, liquidez, setor, endividamento
- Templates de filtros salvos (screening_filters)
- Favoritar ativos (asset_favorites)
- Página principal de screening com scroll infinito
- Página detalhada do ativo com gráficos de preço e dividendos
- Comparação side-by-side de até 5 ativos (radar + bar chart)
- Scraping complementar (StatusInvest, Fundamentus, Investidor10)
- Normalização de setores e nomes (SectorMapper, NameNormalizer, FiiSegmentMapper)
- BrapiService expandido (quotes, completeQuote, historical prices/dividends, FII indicators)
- AssetSyncService (sincronização multi-fonte, 807 linhas)

---

## Release 3 — Valuation Avançado ✅ (v2.1.0)

**Status:** Concluída  
**Objetivo:** Expandir modelos de valuation com Gordon para FIIs e automatizar premissas.

**Entregues:**
- Modelo de Gordon para FIIs (fórmula P = D0*(1+g)/(ke-g))
- Auto-preenchimento de premissas com dados do cache (ROE, payout, DPS, setor)
- Página unificada screening/Valuation.vue com todos os 3 métodos
- Campo ROE/Payout editáveis no Preço Teto com taxa de crescimento calculada
- Margem de segurança unificada (base no preço) em todos os métodos
- Composables useDcf.ts e usePrecoTeto.ts com cálculos em tempo real
- MarginGauge component para visualização da margem de segurança
- Atualização automática de cotação, ROE, número de papéis via AssetSyncService

---

## Release 4 — Automação e Infraestrutura 🚧

**Status:** Parcialmente concluída  
**Objetivo:** Automatizar processos e expandir fontes de dados.

**Entregues:**
- Artisan commands: assets:sync, releases:generate-recurring, assets:normalize-sectors
- Schedule configurado: assets:sync 3x/dias úteis, releases:generate-recurring diário
- Scraping de fontes complementares (StatusInvest, Fundamentus, Investidor10)
- Jobs de sincronização (SyncAssetIndicatorsJob)

**Pendente:**
- Sistema de e-mails (notificações, recovery)
- Fila dejobs para processamento assíncrono

---

## Release 5 — Financeiro Avançado 🚧

**Status:** Parcialmente concluída  
**Objetivo:** Expandir controle financeiro com cartões, faturas, planejamento e metas.

**Entregues:**
- Cartões de crédito com CRUD completo (limite, dia fechamento, dia vencimento)
- Faturas mensais com navegação de 15 meses
- Lançamentos recorrentes (RecurrencePlan) com 5 frequências
- Lançamentos parcelados com distribuição automática
- Métodos de pagamento (dinheiro, cartão crédito, cartão débito, pix)
- Status de lançamentos (previsto, pago, cancelado)
- Dashboard aprimorado com filtering por período e trend de 6 meses

**Pendente:**
- Planejamento financeiro mensal/anual
- Metas e objetivos financeiros
- Orçamento por categoria
- Associar compras/parcelas explicitamente às faturas (UI)
- Registrar pagamento total ou parcial de fatura

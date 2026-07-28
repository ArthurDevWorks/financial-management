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
**Objetivo:** Núcleo de gestão financeira pessoal com cadastro de ativos e cotação automática.

**Entregues:**
- Dashboard financeiro com gráficos e indicadores
- CRUD de bancos, contas, categorias, lançamentos
- CRUD de investimentos com cotação via brapi.dev
- Valuation DCF (Fluxo de Caixa Descontado)
- Valuation Preço Teto Projetivo
- Autenticação com Fortify (2FA, email verification)

---

## Release 2 — Módulo Screening 🚧

**Status:** Em planejamento  
**Objetivo:** Permitir busca e descoberta de ativos com filtros fundamentalistas.

**Inclui:**
- Cache de indicadores fundamentalistas (ações e FIIs)
- Filtros por DY, P/L, ROE, P/VP, liquidez, setor, preço
- Templates de filtros salvos
- Página detalhada de cada ativo com indicadores
- Comparação side-by-side de ativos
- Favoritar ativos
- Scraping complementar para dados não cobertos pela brapi

---

## Release 3 — Valuation Avançado

**Status:** Em planejamento  
**Objetivo:** Expandir os modelos de valuation e automatizar premissas.

**Inclui:**
- Modelo de Gordon para FIIs (perpetuidade fixa em 3%)
- Auto-preenchimento de premissas com dados da brapi
- Página unificada (indicadores + valuation)
- Atualização automática de cotação, ROE, número de papéis

---

## Release 4 — Automação e Infraestrutura

**Status:** Em planejamento  
**Objetivo:** Automatizar processos e expandir fontes de dados.

**Inclui:**
- SyncAssetIndicatorsJob (sincronização periódica)
- Artisan commands para manutenção
- Schedule diário de atualização
- Scraping de fontes complementares
- Sistema de e-mails (notificações, recovery)

---

## Release 5 — Financeiro Avançado

**Status:** Em planejamento  
**Objetivo:** Expandir controle financeiro com cartões, faturas, planejamento e metas.

**Inclui:**
- Cartões de crédito e faturas
- Lançamentos recorrentes e parcelados
- Planejamento financeiro mensal/anual
- Metas e objetivos financeiros
- Orçamento por categoria

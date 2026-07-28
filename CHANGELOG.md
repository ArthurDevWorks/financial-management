# Changelog

Todas as mudanças notáveis no Fidax serão documentadas aqui.

O formato é baseado em [Keep a Changelog](https://keepachangelog.com/),
e este projeto adere ao [Semantic Versioning](https://semver.org/).

---

## [1.0.0] — 2026-07-07

### Adicionado
- Dashboard financeiro com resumo de saldo, receitas, despesas e gráficos
- CRUD completo de bancos com upload de logo
- CRUD completo de contas bancárias
- CRUD completo de categorias (receita/despesa/investimento)
- CRUD completo de lançamentos financeiros
- CRUD completo de investimentos com tipos (ações, FIIs, ETFs, cripto, renda fixa)
- Integração com brapi.dev para cotação automática de ativos
- Valuation DCF (Fluxo de Caixa Descontado) com projeção por anos
- Valuation Preço Teto Projetivo
- Histórico de valuations persistido
- Exportação CSV em bancos, contas, categorias e lançamentos
- Autenticação com Laravel Fortify (registro, login, 2FA, verificação de email)
- Perfil de usuário com configurações de aparência
- Design system Banco Premium (tokens semânticos, dark/light mode)
- Tema escuro como padrão
- Artisan command `investiments:fetch-prices` para atualização em lote

### Técnico
- Laravel 12 + Vue 3 + Inertia.js v2 + TypeScript
- Tailwind CSS v4 + shadcn-vue (Reka UI)
- Chart.js com vue-chartjs
- SSR habilitado
- 41 testes (Feature + Unit)

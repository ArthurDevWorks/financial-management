O Fidax é uma plataforma financeira multiusuário voltada para diferentes perfis de usuários:

- Pessoas que estão começando a organizar a vida financeira
- Usuários focados em controle financeiro pessoal
- Pessoas que desejam planejamento financeiro
- Investidores iniciantes
- Investidores avançados

O projeto começa como um gerenciador financeiro pessoal e evolui para uma plataforma de investimentos e research. A trajetória prevista é semelhante a soluções como:

- Investidor10
- Status Invest
- Kinvo
- Trademap

O produto já teve a Release 1 colocada em produção com gerenciamento financeiro e cálculo de valuations. O foco atual é documentar a entrega, estabilizar operação pós-produção e preparar a Release 2 de planejamento financeiro e lançamentos avançados.

---

# OBJETIVOS PRINCIPAIS

A plataforma deve permitir:

## Gestão Financeira

- Controle de receitas
- Controle de despesas
- Controle de contas bancárias
- Controle de cartões
- Controle de faturas
- Controle de transferências
- Controle de investimentos
- Controle de patrimônio

## Planejamento Financeiro

- Metas financeiras
- Sonhos financeiros
- Planejamento de aposentadoria
- Reserva de emergência
- Planejamento mensal
- Planejamento anual

## Investimentos

- Cadastro de ativos com ticker
- Integração com API de cotação
- Atualização de preço, logo e metadados do ativo
- Valuation e preço teto usando preço atual
- Consolidação de carteira
- Acompanhamento de rentabilidade
- Dashboard de performance
- Dividendos
- Proventos
- Renda fixa
- Ações
- ETFs
- Fundos imobiliários
- Criptomoedas
- Stocks internacionais
- Filtros fundamentalistas para encontrar oportunidades
- Central de documentos e relatórios de ativos e FIIs
- Integração futura com APIs financeiras

## Inteligência Artificial

- Insights financeiros
- Sugestões inteligentes
- Análise de comportamento financeiro
- Alertas automáticos
- Recomendações financeiras
- Identificação de padrões
- Educação financeira personalizada

## Pesquisa e Discovery

- Filtros por P/VP, DY, P/L, liquidez, setor e preço
- Shortlist de ações e FIIs com base em tese do usuário
- Comparação de ativos
- Leitura de relatórios e documentos para suportar análise

---

# ROADMAP DE RELEASES

## Release 1 — Gestão Financeira ✅ (v1.0.0)

### Status
Entregue em produção.

### Objetivo
Núcleo de gestão financeira pessoal com cadastro de ativos e cotação automática.

### Entregues
- Dashboard financeiro com gráficos e indicadores
- CRUD de bancos, contas, categorias, lançamentos
- CRUD de investimentos com cotação via brapi.dev
- Valuation DCF (Fluxo de Caixa Descontado)
- Valuation Preço Teto Projetivo
- Autenticação completa (Fortify, 2FA, email verification)
- Design system Banco Premium

---

## Release 2 — Módulo Screening 🚧

### Objetivo
Permitir busca e descoberta de ativos com filtros fundamentalistas, cache de indicadores no banco local, comparação de ativos e favoritos.

### Entregas
- Cache de indicadores fundamentalistas (asset_indicators)
- Cache de indicadores de FIIs (fii_indicators)
- Templates de filtros salvos (screening_filters)
- Favoritar ativos (asset_favorites)
- Estender BrapiService (statistics, financial-data, profile, dividends)
- AssetIndicatorService (sincronização de indicadores)
- ScreeningService (lógica de filtros)
- ScrapingService (dados complementares)
- Página principal de screening (filtros + resultados)
- Página detalhada do ativo (indicadores, gráficos, dividendos)
- Comparação side-by-side de ativos
- Jobs de sincronização (SyncAssetIndicatorsJob)
- Artisan command screening:sync

---

## Release 3 — Valuation Avançado 🚧

### Objetivo
Expandir os modelos de valuation com Gordon para FIIs e automatizar carregamento de premissas.

### Entregas
- GordonValuationService (modelo de Gordon com perpetuidade fixa em 3%)
- Auto-preenchimento de premissas com dados do cache
- Página unificada (indicadores do ativo + valuation na mesma tela)
- Atualização automática de cotação, ROE, número de papéis
- Melhorias no frontend de DCF e Preço Teto

---

## Release 4 — Automação e Infraestrutura

### Objetivo
Automatizar sincronização de dados e expandir fontes com scraping.

### Entregas
- SyncAssetIndicatorsJob agendado
- Schedule diário de atualização
- Scraping estruturado de fontes complementares
- Sistema de e-mails (notificações, recovery)

---

## Release 5 — Financeiro Avançado

### Objetivo
Expandir controle financeiro com cartões, faturas, planejamento e metas.

### Entregas
- Cartões de crédito e faturas
- Lançamentos recorrentes e parcelados
- Planejamento financeiro mensal e anual
- Metas e objetivos financeiros
- Orçamento por categoria

# STACK TECNOLÓGICA

A documentação deve considerar obrigatoriamente:

## Backend

- PHP 8+
- Laravel 12+
- API REST
- Arquitetura modular
- Service Layer
- Repository Pattern
- SOLID
- Clean Architecture
- Clean Code

## Frontend

- Vue.js
- Starter Kit Laravel + Vue
- Componentização
- Composition API
- Pinia
- Vue Router

## Banco de Dados

- MySQL

---

# DIRETRIZES DO PRODUTO

- Menos fricção na entrada de dados.
- Dados atualizados com o mínimo de intervenção manual.
- Decisões baseadas em informação consolidada.
- Crescimento por releases incrementais.
- UX consistente com a stack atual do projeto.
- Integrações externas devem falhar com segurança e sem quebrar a navegação.

---

# 7. Common Patterns and Examples

## Form Pages (Create/Edit) Pattern

All CRUD create/edit pages follow this structure:

### Page Header
```vue
<Button variant="ghost" size="sm" class="mb-4" @click="goBack">
  <ArrowLeft class="h-4 w-4" />
  Voltar
</Button>

<PageHeader title="Título" description="Descrição" />
```

### Form Card
```vue
<div class="rounded-xl border border-border bg-card p-8">
  <form @submit.prevent="submit" class="space-y-6">
    <div>
      <Label>Nome do Campo</Label>
      <Input ... />
      <InputError :message="form.errors.field" />
    </div>
    <!-- divider before buttons -->
    <div class="flex justify-end gap-3 pt-6 border-t border-border">
      <Button type="button" variant="outline" @click="goBack">Cancelar</Button>
      <Button type="submit" variant="default" :disabled="form.processing">Salvar</Button>
    </div>
  </form>
</div>
```

### Select Element Styling
```vue
<select
  v-model="..."
  class="h-[42px] w-full rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20 [color-scheme:dark]"
>
  <option value="">Selecione...</option>
</select>
```

### Radio Card Selection (Revenue/Expense)
```vue
<label class="flex-1 cursor-pointer">
  <input type="radio" v-model="form.type" value="revenue" class="peer sr-only" />
  <div class="rounded-lg border border-border bg-surface p-4 text-center hover:bg-secondary peer-checked:border-primary peer-checked:bg-primary/10 peer-checked:text-primary transition">
    <span class="font-semibold block">Receita</span>
    <span class="text-xs text-muted-foreground mt-1 block">Entrada de dinheiro</span>
  </div>
</label>
```

### File Upload Area
```vue
<label
  for="logo-input"
  class="flex items-center justify-center w-full px-4 py-8 border-2 border-dashed border-border rounded-xl cursor-pointer hover:border-primary hover:bg-primary/5 transition"
>
  <div class="text-center">
    <Upload class="h-8 w-8 text-muted-foreground mx-auto mb-2" />
    <p class="text-foreground font-semibold">Clique para selecionar</p>
  </div>
</label>
```

### R$ Prefix Input
```vue
<div class="relative">
  <span class="absolute left-4 top-1/2 -translate-y-1/2 text-muted-foreground">R$</span>
  <Input v-model="form.value" type="number" class="pl-8" />
</div>
```

---

# 8. Project-Specific Rules

## Design System — Fidax (Jul/2026)

> ⚠️ **Fase de Exploração:** Os protótipos em `prototypes/` são explorações de design. As melhorias visuais (spacing, micro-interações, glassmorphism, gráficos) ainda **não** foram aplicadas ao código Vue/Tailwind do projeto. Quando forem convertidas, devem ser aplicadas globalmente, não isoladamente.

### Identidade Visual

O Fidax usa uma identidade dark premium com teal como cor primária e gold como acento. A paleta é consistente entre light/dark modes.

**Paleta (dark mode):**
- **Primária:** Teal `hsl(168,75%,42%)` — cor principal, usada em botões, links, ativos
- **Destaque (accent):** Gold/amber `hsl(42,80%,52%)` — usado com moderação para favoritos e badges
- **Revenue:** Verde `hsl(142,70%,50%)` — valores positivos
- **Destructive:** Vermelho `hsl(0,75%,55%)` — valores negativos
- **Investment:** Azul `hsl(220,80%,55%)` — badges de FIIs/informação
- **Background:** `hsl(228,28%,5%)` — quase preto azulado
- **Card:** `hsl(228,22%,9%)` — superfície de cartões
- **Surface:** `hsl(228,20%,12%)` — inputs, selects
- **Sidebar:** `hsl(228,25%,6%)` — navy escuro

**Tipografia:**
- **UI/Corpo:** `Instrument Sans` (pesos 400, 500, 600, 700) — via fonts.bunny.net
- **Dados financeiros:** `Instrument Sans` (weight 600) — sem fonte monoespaçada dedicada

**Logo:** Marca composta por 3 retângulos geométricos arredondados em teal + wordmark "Fidax" em bold. Sem serifa, sem ouro.

### Design System Tokens (vigentes — legado Banco Premium)
- **Background:** `bg-background` (screen bg)
- **Card:** `bg-card` (--card: hsl(228 22% 9%))
- **Surface:** `bg-surface` (--surface: hsl(228 20% 12%))
- **Secondary:** `bg-secondary` (--secondary: hsl(228 18% 14%))
- **Text:** `text-foreground` (white), `text-muted-foreground` (muted)
- **Border:** `border-border` (--border: hsl(228 15% 14%))
- **Primary:** `text-primary` / `bg-primary` (teal hsl(168 75% 42%)) ⚠️ migrar para gold
- **Destructive:** `text-destructive` / `bg-destructive/10`
- **Revenue:** `text-revenue` / `bg-revenue/10`
- **Investment:** `text-investment` / `bg-investment/10`
- **Accent (gold):** `text-accent` / `bg-accent/10`
- **Radius:** `rounded-xl` for cards, `rounded-lg` for small elements
- **Focus ring:** `focus:border-ring focus:ring-[3px] focus:ring-primary/20`
- **Buttons:** shadcn-vue `<Button variant="default|outline|secondary|destructive" size="sm">` ONLY — no custom classes
- **Labels:** shadcn-vue `<Label>` component for form labels
- **Page headers:** `<PageHeader>` component with title/description
- **Back buttons:** `<Button variant="ghost" size="sm">` with ArrowLeft icon
- **Form cards:** `rounded-xl border border-border bg-card p-8`
- **Dividers:** `border-border`
- **Input/Select:** `h-[42px] rounded-md border border-border bg-surface px-3 text-sm text-foreground outline-none transition-all focus:border-ring focus:ring-[3px] focus:ring-primary/20`
- **Select dark color scheme:** `[color-scheme:dark]` to prevent blank dropdowns
- **No custom button text colors:** Buttons should not have `text-slate-900` or similar
- **No hardcoded color classes:** No `bg-cyan-*`, `text-cyan-*`, `border-slate-*`, `bg-slate-*`, `text-slate-*`

### Components Available
- `PageHeader` — accepts `title` and `description` props, has `#actions` slot
- `Label` — from `@/components/ui/label`, used for form labels
- `InputError` — from `@/components/InputError.vue`, shows validation errors
- `Button` — from `@/components/ui/button`, use `variant` and `size` props only
- `Input` — from `@/components/ui/input`, no custom color classes

---

# 10. Lessons Learned

### 2026-07-02 - PRD e Roadmap do Produto

**Contexto:** Atualização do `PROJECT_CONTEXT.md` com base no novo PRD do Fidax.
**Discovery:** O produto tem uma direção mais clara em releases: primeiro finanças pessoais com ações e cotação, depois consolidação de carteira, screener fundamentalista, central documental e camadas inteligentes.
**Solution:** O contexto do projeto agora deixa explícito o foco da Release 1, o uso de API de cotação para valuation e o horizonte de produto para discovery e research.
**Source:** PRD atualizado do produto

### 2026-05-30 - Design Tokens: Hardcoded Colors Removal

**Context:** Refactoring all CRUD create/edit forms and auth pages from hardcoded Tailwind color classes to "Banco Premium" design system tokens.
**Discovery:** Many pages had inconsistent color usage — `bg-slate-700`/`border-slate-600`/`text-cyan-400` patterns were repeated across 16 files. The correct approach is to use semantic tokens (`bg-surface`, `border-border`, `text-primary`) that map to CSS variables.
**Solution:** Replaced all hardcoded `slate`, `cyan`, `green`, `red` color classes with design tokens in all 16 form/auth pages. Key changes:
- Page headers → `<PageHeader>` component with ghost back button
- Form cards → `rounded-xl border border-border bg-card p-8`
- Labels → `<Label>` component
- Inputs/selects → `bg-surface border-border` with `focus:border-ring focus:ring-primary/20`
- Buttons → `variant="default|outline"` only, no custom classes
- Dividers → `border-border`
- Select elements → added `[color-scheme:dark]` to prevent blank dropdowns
- Auth alerts → `bg-primary/20 border-primary/50 text-primary`
**Source:** User instruction (refactoring task)

### 2026-05-30 - Test Environment: pdo_sqlite Extension Required

**Context:** Running PHPUnit/Pest tests for this Laravel+Vue project.
**Discovery:** The test configuration (`phpunit.xml`) uses `DB_CONNECTION=sqlite` with `DB_DATABASE=:memory:`, but the `pdo_sqlite` PHP extension may not be installed in all environments.
**Solution:** Either install the extension (`sudo apt install php8.3-sqlite`) or override `phpunit.xml` to use MySQL with a dedicated test database (`fidax_testing`). The project `.env` has MySQL credentials available.
**Note:** No coverage driver (Xdebug/PCOV) was available in this environment — `sudo apt install php8.3-xdebug` resolves this.
**Source:** Tester agent (test execution)

### 2026-05-30 - Review Fixes: Missed Hardcoded Colors in Auth/Settings/Welcome Pages

**Context:** Code review of the Design System Premium refactoring found 3 files still containing hardcoded color classes.
**Discovery:** During the initial batch refactoring, `ForgotPassword.vue` was completely missed (still had 6 instances of `slate`/`cyan` hardcoded), `Profile.vue` had partial hardcoded `neutral`/`cyan` classes, and `Welcome.vue` used `emerald`/`red`/`purple` instead of semantic `revenue`/`destructive`/`investment` tokens.
**Solution:** 
- `ForgotPassword.vue`: Status alert → `bg-primary/20 border-primary/50 text-primary`; Label → removed custom class (inherits correctly); Input → removed custom class (shadcn Input provides default styling); Button → `variant="default"` only; TextLink → `text-primary hover:text-primary/80`
- `Profile.vue`: Link decoration → `decoration-border`; verification alert → `text-primary`; success message → `text-muted-foreground`
- `Welcome.vue`: Receitas card → `border-revenue/30 bg-revenue/10 text-revenue`; Despesas card → `border-destructive/30 bg-destructive/10 text-destructive`; Investimentos card → `border-investment/30 bg-investment/10 text-investment`
**Source:** Reviewer agent (code review fix)

### 2026-05-30 - Test Execution: 100% Pass After Frontend-Only Token Refactoring

**Context:** Testing the Design System Premium fixes — 3 Vue files (ForgotPassword.vue, Profile.vue, Welcome.vue) had hardcoded colors replaced with design tokens.
**Discovery:** All 41 backend tests pass with 132 assertions. These are pure frontend refactoring changes (Vue templates + CSS tokens) that do not affect backend PHP logic. No regression introduced.
**Solution:** Standard `php artisan test` workflow continues to validate backend integrity after frontend-only changes. Coverage driver (Xdebug/PCOV) remains unavailable in this environment.
**Source:** Tester agent (test execution)

### 2026-05-30 - Review Approval: All Fixes Verified, Decorative Colors Remain

**Context:** Final review of the Design System Premium CHANGES_REQUESTED fixes.
**Discovery:** All 3 requested files (ForgotPassword.vue, Profile.vue, Welcome.vue) had their hardcoded colors correctly replaced with design tokens. Welcome.vue still has 2 decorative feature grid cards (Categorias with `orange-500`, Análises with `indigo-500`) using hardcoded colors — but these were NOT in the original review scope and no semantic tokens exist for general decorative marketing accents.
**Solution:** The 2 remaining hardcoded color instances in Welcome.vue are decorative marketing features on the landing page, not application UI elements. The design system does not define tokens for these. If desired, consider adding general-purpose accent tokens (e.g., `--accent-warm`, `--accent-cool`) or converting to chart-based tokens for full token purity. Not blocking.
**Source:** Reviewer agent (code review final approval)

### 2026-07-07 - Release 1 em Produção e Backlog Pós-Produção

**Contexto:** O usuário confirmou que a Release 1 já foi colocada em produção com gerenciamento financeiro e cálculo de valuations.
**Discovery:** As próximas prioridades não são finalizar a Release 1, mas documentar a entrega, estabilizar operação real e abrir milestones/issues para evolução do produto.
**Solution:** O roadmap passa a tratar Release 1 como entregue, adiciona Release 1.1 para operação/localização/infraestrutura e define Release 2 como planejamento financeiro e lançamentos avançados.
**Source:** User clarification (backlog and milestones request)

### 2026-08-04 - Definição Unificada de Margem de Segurança nos Valuations

**Contexto:** Durante a revisão de QA do merge `dev → main`, o cálculo de margem de segurança estava inconsistente entre os métodos de valuation: Preço Teto usava base na cotação atual, enquanto DCF, Gordon e o index de Valuations (`ValuationController::computeSummary`) usavam base no valor justo (fórmula de Graham). O teste unitário de Preço Teto apontava a divergência.

**Discovery:** A "margem de segurança" pode ser calculada sobre duas bases, com significados diferentes:
- **Graham** `(valor justo − preço) / valor justo` — mede tolerância a erro na estimativa, limitada a 100%. Responde "quanto meu valor justo pode estar errado antes de eu perder dinheiro".
- **Base no preço** `(valor justo − preço) / preço` — mede o desconto relativo ao que se paga hoje; matematicamente idêntica ao upside (ilimitada).

**Solution:** Decisão do usuário: unificar TODOS os métodos (Preço Teto, DCF, Gordon, index de Valuations e composables `usePrecoTeto`/`useDcf`) na definição **base no preço** (= upside). Arquivos ajustados: `PrecoTetoProjetivoValuationService`, `DcfValuationService`, `GordonValuationService`, `ValuationController`, `useDcf.ts`, `gordon/Index.vue`; teste de Preço Teto atualizado (teto 5,0 / preço 2,5 → `margin_of_safety = 100.0`). Consequência aceita: "Margem de Segurança" e "Upside" exibem o mesmo valor em todas as telas.

**Source:** Decisão do usuário (revisão de QA do merge `dev → main`)

### 2026-08-04 - Gordon: dps é trailing (D0), fair price deve crescer um período

**Contexto:** Revisão do modelo Gordon com premissas reais (dps=9,57, discount_rate=10,54, risk_premium=1, growth=2, preço=100,82): o app retornava preço teto 100,31 enquanto a base de referência dava 102,36 (margem 1,53%).

**Discovery:** A fórmula do app era `P = D0 / (ke − g)`, tratando o DPS digitado como dividendo do próximo ano (D1). Porém o usuário informa o DPS como **soma dos últimos 12 meses = trailing (D0)**. O Gordon clássico exige `P = D1 / (ke − g)` com `D1 = D0 × (1 + g)`. Verificação numérica: `9,57 × 1,02 / (0,1154 − 0,02) = 102,32` ≈ 102,36 da referência (diferença de centavos é arredondamento). A fórmula antiga subavaliava em ~2% (fator `1+g`). Também foi encontrado que `GordonValuationService` (PHP) era código morto e ignorava `risk_premium`.

**Solution:** Crescer o dividendo em todos os pontos de cálculo, trocando `dps/(ke−g)` por `dps×(1+g)/(ke−g)`: `resources/js/pages/gordon/Index.vue`, `resources/js/pages/screening/Valuation.vue`, `app/Http/Controllers/ValuationController.php` (`computeSummary`) e `app/Services/GordonValuationService.php`. Convenção adotada: **campo dps = dividendo trailing 12m (D0)**; o fair price sempre projeta D1. Validação: 64 testes + build OK.

**Source:** Revisão do modelo Gordon (usuário)

**Action item (pendente):** decidir se o `GordonValuationService` PHP deve passar a ser usado (hoje é código morto) e se deve incorporar `risk_premium` no discount rate para alinhar com o frontend.

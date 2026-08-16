# Changelog

Todas as mudanças relevantes do Fidax serão documentadas neste arquivo.

## [1.1.0] - 2026-08-15

### Adicionado

- Provider global único de toasts (`vue-sonner`) no `app.ts`, com `Toaster` montado uma única vez.
- Composable `useToast()` padronizado com helpers `success`, `error`, `info`, `warning`.
- Tipo `Flash` tipado em `AppPageProps` para flash messages do backend.
- Handler global `router.on('error')` para toast em erros de validação em ações fire-and-forget.
- Toast de erro no `loadMore` do screening (catch antes silenciado).
- `SectorMapper`: normalização EN→PT de setores/indústrias do BrAPI (Yahoo Finance) + StatusInvest.
- `NameNormalizer`: mapa manual de ~60 empresas B3 + normalização de strings (S.A., espaços, etc.).
- `FiiSegmentMapper`: classificação de FIIs em Tijolo/Papel/Híbrido/Fundo de Fundos + sub-segmentos.
- Command Artisan `assets:normalize-sectors` para re-normalizar dados existentes (com `--dry-run`).
- 3 arquivos de teste (SectorMapperTest, NameNormalizerTest, FiiSegmentMapperTest) com ~90 asserts.

### Corrigido

- `BankController`: typo `sucess` → `success` (mensagens de banco nunca exibidas).
- `BankController`: ramo de erro usava chave `success` em vez de `error`.
- `BankController::destroy`: adicionado flash de sucesso (antes sem feedback).
- `accounts/Index.vue`: trocado `confirm()` nativo por `ConfirmDialog` padronizado.

### Mudado

- `settings/Profile.vue` e `settings/Password.vue`: texto inline "Salvo." substituído por toast de sucesso via `on-success`.
- `<Toaster>` removido de `AppLayout`, `AuthSimpleLayout` e `Welcome` (corrigido bug de Toaster dentro de `<Head>` no Welcome).
- `useFlashMessages()` consolidado no root component (antes duplicado em 2 layouts).
- `AssetSyncService`: aplicação de normalizadores em todos os 4 caminhos de dados (syncSingle, syncSingleFromBrapi, enrichFromBrapi, mapBulkItem).
- `BrapiService`: normalizadores aplicados em `buildStockData` e `buildFiiData`.

### Documentação

- Seção "Toast Pattern" adicionada ao `PROJECT_CONTEXT.md`.
- Seção "Asset Sector Normalization" adicionada ao `PROJECT_CONTEXT.md`.

## [1.0.0] - 2026-07-07

### Entregue em produção

- Gerenciamento financeiro pessoal autenticado.
- Dashboard financeiro com receitas, despesas, saldo e visão por categoria.
- Cadastro de bancos, contas, categorias e lançamentos financeiros.
- Módulo inicial de investimentos.
- Cálculo de valuations e preço teto.

### Documentação

- Release 1 registrada como entregue em produção.
- Roadmap atualizado com Release 1.1 de pós-produção e Release 2 de planejamento financeiro.
- Backlog estruturado por milestones.

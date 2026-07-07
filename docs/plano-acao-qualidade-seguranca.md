# Plano de Ação — Qualidade, Segurança e Testes

Este plano consolida a análise QA do projeto Fidax feita após as mudanças no módulo de investimentos, integração com Brapi e ajustes de valuation.

## Objetivo

Elevar o projeto para um estado mais seguro, previsível e sustentável antes de promover as mudanças para produção.

O foco é corrigir:

- vulnerabilidades de dependências;
- endpoint público de cotação sem proteção;
- chamadas externas e escritas em banco durante requisições `GET`;
- inconsistência entre cotação unitária e saldo total;
- falhas de lint/format;
- lacunas de testes envolvendo serviços externos.

---

## Prioridade 0 — Segurança imediata

### 1. Atualizar dependências vulneráveis

Executar atualização controlada das dependências PHP e Node.

Comandos sugeridos:

```bash
composer update
npm audit fix
```

Depois validar:

```bash
composer audit
npm audit --audit-level=moderate
php artisan test
npm run build
```

Critério de aceite:

- `composer audit` sem vulnerabilidades críticas/altas relevantes;
- `npm audit --audit-level=moderate` sem vulnerabilidades críticas/altas relevantes;
- testes e build passando.

### 2. Proteger o endpoint `/api/quote`

O endpoint de cotação não deve ficar público sem autenticação, limitação de uso e validação forte.

Ações:

- adicionar autenticação;
- adicionar throttle;
- validar ticker com tamanho máximo e regex;
- retornar erro claro para símbolo inválido.

Exemplo de direção:

```php
Route::middleware(['auth:sanctum', 'throttle:30,1'])
    ->get('quote', QuoteController::class);
```

Também criar cobertura de teste para:

- usuário não autenticado;
- ticker vazio;
- ticker inválido;
- throttle, se aplicável;
- resposta válida da Brapi com `Http::fake()`.

### 3. Impedir testes de chamarem API real

Qualquer teste que passe por fluxos de Brapi deve usar `Http::fake()` e/ou `Http::preventStrayRequests()`.

Ações:

- adicionar `Http::preventStrayRequests()` nos testes de feature afetados;
- fakear respostas da Brapi em listagens e telas de valuation;
- garantir que nenhum teste dependa de rede externa.

Critério de aceite:

```bash
php artisan test
```

deve passar sem qualquer chamada HTTP real.

---

## Prioridade 1 — Correção estrutural dos dados

### 4. Separar cotação unitária de saldo total

Problema atual:

- a Brapi retorna preço unitário da ação;
- o sistema grava esse valor em `current_balance`;
- o model `Investiment` usa `current_balance` como saldo total.

Isso distorce patrimônio, rentabilidade e ganho/perda quando `quantity > 1`.

Ação recomendada:

- criar campo separado, por exemplo `current_price`;
- manter `current_balance` como saldo total;
- calcular saldo de ações como:

```text
current_balance = quantity * current_price
```

Arquivos/áreas a revisar:

- `app/Models/Investiment.php`
- `app/Http/Controllers/InvestimentController.php`
- `app/Http/Controllers/ValuationController.php`
- `app/Http/Controllers/PrecoTetoController.php`
- `app/Console/Commands/FetchStockPrices.php`
- telas Vue de investimentos e valuation
- factories e testes de feature

Critério de aceite:

- investimentos com `quantity > 1` exibem saldo total correto;
- DCF e preço teto continuam usando preço unitário;
- testes cobrem pelo menos um ativo com quantidade maior que 1.

### 5. Remover escritas em banco durante requisições `GET`

GETs devem ser idempotentes. Atualmente algumas telas fazem sync com Brapi e podem persistir dados ao carregar páginas.

Ações recomendadas:

- mover atualização de cotações para job, comando ou ação explícita;
- manter GETs apenas lendo dados existentes;
- usar `last_price_fetched_at` para decidir se a cotação está stale;
- adicionar botão manual “Atualizar cotação”, se fizer sentido para UX.

Possíveis alternativas:

1. comando agendado:

```bash
php artisan investiments:fetch-prices
```

2. job assíncrono por ativo;
3. endpoint autenticado para atualização manual.

Critério de aceite:

- acessar listagens não gera escrita no banco;
- telas não ficam dependentes da latência da Brapi;
- existe teste garantindo que listagens não fazem update inesperado.

### 6. Usar `last_price_fetched_at` como controle de stale data

Ações:

- definir janela de atualização, por exemplo 15 minutos, 1 hora ou diária;
- evitar chamadas repetidas para o mesmo ticker dentro da janela;
- reaproveitar cache da BrapiService.

Critério de aceite:

- ativo recém-atualizado não dispara nova chamada;
- ativo stale dispara atualização apenas no fluxo apropriado.

---

## Prioridade 2 — UX e validação

### 7. Mostrar erro de perpetuidade no DCF

O DCF agora segue a regra da planilha: crescimento na perpetuidade = última taxa projetada.

Risco atual:

- se a última taxa projetada for maior ou igual à taxa de desconto, o backend rejeita `terminal_growth_rate`;
- como o campo é readonly, o usuário pode não entender onde corrigir.

Ações:

- exibir erro de `terminal_growth_rate` próximo à tabela de crescimento ou ao campo readonly;
- destacar que o último ano projetado precisa ser menor que a taxa de desconto;
- opcionalmente validar no frontend antes do submit.

Critério de aceite:

- usuário recebe erro claro quando a última taxa projetada é inválida;
- teste cobre cenário `última taxa >= taxa de desconto`.

### 8. Melhorar validação de ticker

Ações:

- limitar tamanho;
- normalizar uppercase;
- validar formato esperado para ações B3;
- remover espaços e entradas inválidas.

Exemplo inicial para ações brasileiras:

```php
'name' => ['required', 'string', 'max:12', 'regex:/^[A-Z]{4}[0-9]{1,2}$/']
```

Observação: ajustar a regra se FIIs, ETFs ou ativos internacionais entrarem no escopo.

### 9. Evitar criação de investimento com cotação zero

Problema atual:

- se a cotação falhar ou o usuário submeter antes da busca terminar, o investimento pode ser criado com valor `0`.

Ações:

- exigir valor numérico `gt:0` no backend;
- bloquear submit enquanto busca cotação;
- permitir entrada manual quando a API falhar;
- exibir erro claro quando a cotação não for encontrada.

Critério de aceite:

- investimento de ação não nasce com cotação zero silenciosamente;
- teste cobre falha de cotação.

---

## Prioridade 3 — Qualidade de código

### 10. Corrigir lint e imports não usados

Erros identificados:

- `resources/js/pages/Welcome.vue`: `AppLogoIcon` importado e não usado;
- `resources/js/pages/investiments/Index.vue`: `props` declarado e não usado;
- `resources/js/pages/valuations/Show.vue`: `PageHeader` importado e não usado.

Validar com:

```bash
npx eslint .
```

### 11. Rodar Pint e Prettier

Backend:

```bash
vendor/bin/pint
vendor/bin/pint --test
```

Frontend:

```bash
npm run format
npm run format:check
```

Critério de aceite:

- Pint sem alterações pendentes;
- Prettier sem arquivos fora do padrão;
- ESLint sem erros.

### 12. Remover artefatos de planilha do versionamento

Foi identificado o arquivo:

- `resources/sheet.css` com aproximadamente 3,9 MB.

Não há referência aparente a esse arquivo no código.

Ações:

- remover se for artefato exportado de planilha;
- se for necessário, documentar seu uso;
- manter HTML/ZIP de planilhas fora do Git quando forem apenas referência local.

---

## Ordem recomendada de execução

1. Atualizar dependências vulneráveis.
2. Proteger `/api/quote`.
3. Isolar testes com `Http::fake()` e `Http::preventStrayRequests()`.
4. Separar `current_price` de `current_balance`.
5. Remover sync Brapi dos GETs.
6. Implementar controle de stale data com `last_price_fetched_at`.
7. Corrigir validações e UX do DCF.
8. Melhorar validação de ticker.
9. Evitar criação com cotação zero.
10. Corrigir lint, Pint e Prettier.
11. Remover artefatos desnecessários.
12. Rodar suíte final.

---

## Checklist final de qualidade

Antes de considerar a entrega pronta:

```bash
php artisan test
composer audit
npm audit --audit-level=moderate
vendor/bin/pint --test
npm run format:check
npx eslint .
npm run build
```

Todos devem passar.

---

## Critério de aceite geral

A entrega só deve ser considerada pronta quando:

- não houver vulnerabilidades críticas/altas conhecidas;
- `/api/quote` estiver protegido;
- GETs não causarem side effects de escrita;
- cotação unitária e saldo total estiverem semanticamente separados;
- testes não dependerem de API externa;
- lint, format, build e testes passarem;
- UX do DCF explicar claramente a regra da perpetuidade.

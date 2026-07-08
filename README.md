# Fidax

Fidax é uma plataforma financeira SaaS em evolução para controle pessoal, gestão de contas, categorias, lançamentos e investimentos, com base em Laravel + Vue.

## Status atual

### Release 1 em produção

A primeira release já foi colocada em produção com o núcleo operacional do produto:

- gerenciamento financeiro pessoal;
- dashboard financeiro autenticado;
- cadastro de bancos, contas, categorias e lançamentos;
- módulo inicial de investimentos;
- cálculo de valuations e preço teto.

Este repositório já contém uma base funcional com:
- autenticação e perfil de usuário
- dashboard autenticado
- cadastro de bancos
- cadastro de contas
- cadastro de categorias
- cadastro de lançamentos financeiros
- módulo inicial de investimentos
- tela e rotas de configurações

## Stack

- **Backend:** PHP 8+, Laravel 12+
- **Frontend:** Vue 3, Inertia.js, Composition API, Pinia
- **Banco de dados:** MySQL
- **Build tooling:** Vite, Tailwind CSS 4
- **Qualidade:** ESLint, Prettier, TypeScript

## Módulos atuais

- Usuários e autenticação
- Dashboard
- Bancos
- Contas
- Categorias
- Lançamentos (`releases`)
- Investimentos (`investiments`)
- Configurações de perfil, senha, aparência e 2FA

## Rotas principais

- `/` página inicial
- `/dashboard`
- `/banks`
- `/accounts`
- `/categories`
- `/releases`
- `/investiments`
- `/settings/*`

## Documentação

- [Handbook do projeto](docs/fidax-handbook.md)
- [PRD do produto](docs/prd.md)
- [Backlog e milestones](docs/backlog.md)
- [Changelog](CHANGELOG.md)

## Estrutura geral

```txt
app/
  Http/Controllers
  Http/Requests
  Models
  Services
database/
  migrations
  seeders
resources/
  js/
routes/
tests/
```

## Instalação local

### Pré-requisitos

- PHP 8+
- Composer
- Node.js 20+
- MySQL

### Passos

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev
php artisan serve
```

## Comandos úteis

```bash
npm run dev
npm run build
npm run lint
npm run format
npm run format:check
```

## Banco de dados

O projeto utiliza migrations para estruturar:
- `users`
- `banks`
- `accounts`
- `categories`
- `releases`
- `investiments`

## Convenções atuais

- Controllers finos
- Validação em Form Requests
- Interface web com Inertia + Vue
- Rotas protegidas por `auth` e `verified`

## Observações

- O módulo de investimentos ainda usa o nome `Investiment` no código atual.
- `Release` é a entidade operacional usada para lançamentos financeiros.
- O projeto está preparado para evoluir para uma arquitetura financeira mais ampla, com relatórios, consolidação patrimonial e IA.

## Roadmap resumido

- Release 1: gerenciamento financeiro e valuations em produção.
- Release 1.1: operação pós-produção, domínio, SSL, e-mails e pt-BR.
- Release 2: planejamento financeiro e lançamentos avançados.
- Release 3: consolidador de carteira.
- Release 4: screener e oportunidades.
- Release 5: central de documentos e research.
- Release 6: inteligência financeira assistida por IA.

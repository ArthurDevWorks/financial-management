# ADR 001: Padrão MVC e Camada de Serviço no Laravel

**Data:** 2026-05-22
**Status:** Aceito

## Contexto
O framework Laravel incentiva um desenvolvimento rápido, muitas vezes levando os desenvolvedores a utilizarem "Fat Controllers" e "Fat Models". Conforme a aplicação cresce, essa abordagem dificulta a manutenção, os testes unitários e a reutilização de código entre rotas API, web e comandos Artisan.

## Decisão
Decidimos que o Laravel neste projeto não será utilizado puramente com a abordagem padrão de "Model + Controller". Implementaremos uma separação de responsabilidades mais robusta:
1. **Form Requests** para validação de entrada HTTP.
2. **Controllers** atuarão estritamente como despachantes e formatadores de resposta HTTP.
3. **Services** ou **Actions** encapsularão as regras de negócio puras.
4. **Resources** serão usados obrigatoriamente para mapear Models para JSON em rotas de API.

## Consequências
* **Positivas:** Maior testabilidade. Código reutilizável entre jobs assíncronos e requests síncronos. Menor carga cognitiva ao ler um Controller.
* **Negativas:** Requer a criação de mais classes e arquivos em cenários muito simples (CRUD básico). Os desenvolvedores precisam se lembrar ativamente de não quebrar as camadas.

# Skill: Laravel Patterns

**Descrição:** Instruções e templates para criação de código no Laravel.

**Instruções para o Agente Executor:**
Ao implementar código Laravel, você DEVE seguir estas regras:
1. **Controllers:** Nunca coloque lógica de negócios neles. Eles apenas chamam Services ou Actions e retornam Resources ou Views.
2. **Services/Actions:** Use-os para concentrar a lógica de negócio principal.
3. **Requests:** Valide todas as entradas complexas de formulários ou APIs usando FormRequests (`php artisan make:request`).
4. **Resources:** Para rotas de API que retornam Models, use sempre API Resources (`php artisan make:resource`).

**Referência de Padrões:**
Consulte o arquivo `.opencode/standards/laravel.md` para regras mais específicas.

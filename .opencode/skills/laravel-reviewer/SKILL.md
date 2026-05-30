# Skill: Laravel Reviewer

**Descrição:** Instruções de revisão de código estritas para projetos Laravel.

**Instruções para o Agente Reviewer:**
Ao revisar o código, procure ativamente pelas seguintes falhas (Anti-Patterns):
1. **N+1 Queries:** Verifique se as queries usam `with()` quando há loops.
2. **Fat Controllers:** Rejeite Controllers que façam queries densas, lógicas pesadas ou envios de email. Sugira mover para Services/Jobs.
3. **Validação Ausente:** Se um Controller recebe `$request->all()` ou valida manualmente, sugira um FormRequest.
4. **Hardcoded Strings:** Credenciais ou chaves expostas no código em vez do `.env`.

**Referência de Padrões:**
Consulte o arquivo `.opencode/standards/laravel.md`.

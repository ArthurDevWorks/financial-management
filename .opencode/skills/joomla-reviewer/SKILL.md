# Skill: Joomla Reviewer

**Descrição:** Instruções de revisão de código focadas em segurança e arquitetura Joomla.

**Instruções para o Agente Reviewer:**
Ao revisar código de extensões Joomla:
1. **Injeção de SQL:** Verifique rigorosamente se a QueryBuilder foi usada. NUNCA permita concatenação direta de variáveis em strings SQL.
2. **Segurança (CSRF):** Valide se o `JSession::checkToken()` está sendo invocado no recebimento de requisições POST.
3. **Core Hacks:** Rejeite qualquer modificação no código base do Joomla. Exija que sejam usados eventos do Dispatcher através de Plugins.

**Referência de Padrões:**
Consulte o arquivo `.opencode/standards/joomla.md`.

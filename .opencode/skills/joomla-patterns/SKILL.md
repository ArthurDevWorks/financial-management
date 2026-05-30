# Skill: Joomla Patterns

**Descrição:** Instruções e templates para criação de código em extensões Joomla (MVC).

**Instruções para o Agente Executor:**
Ao implementar extensões Joomla:
1. Siga o padrão MVC estrito (Joomla 4+ prefere namespaces e a nova estrutura arquitetural).
2. Use DI (Dependency Injection) via Container do Joomla para acessar a Database e outros serviços globais.
3. Não faça requisições diretas ao banco sem passar pelos Models (ou Table classes, se apropriado).

**Referência de Padrões:**
Consulte o arquivo `.opencode/standards/joomla.md` para regras mais específicas.

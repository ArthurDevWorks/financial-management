# Skill: Debug Tracer

**Descrição:** Instrução para rastreamento profundo de erros (Root Cause Analysis).

**Instruções para o Agente Executor:**
1. Leia o último log de erro (ex: em `storage/logs/laravel.log` ou na saída do terminal).
2. Não adivinhe o problema. Se o erro apontar para uma linha específica, verifique o arquivo e a linha.
3. Se a stacktrace for complexa, rastreie o fluxo das variáveis a partir do Controller até o Service/Database.
4. Explique o problema encontrado e proponha a solução antes de alterar o código.

# Skill: ADR Writer

**Descrição:** Auxilia na criação de Architecture Decision Records.

**Instruções para o Agente Architect:**
1. Sempre que propor uma mudança substancial de tecnologia, design pattern global ou estrutura, você deve invocar essa skill.
2. Crie um novo arquivo no formato Markdown em `.opencode/context/adrs/`.
3. O nome do arquivo deve ser padronizado (ex: `002-nome-da-decisao.md`).

**Template do Arquivo:**
```markdown
# ADR [Número]: [Título da Decisão]

**Data:** YYYY-MM-DD
**Status:** [Proposto / Aceito / Rejeitado]

## Contexto
O que motivou a decisão? Qual o problema que precisamos resolver?

## Decisão
A solução técnica ou padrão adotado.

## Consequências
* **Positivas:** ...
* **Negativas:** ...
```

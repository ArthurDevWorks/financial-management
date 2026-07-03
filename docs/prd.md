# PRD Fidax

**Versão:** 1.0  
**Idioma:** pt-BR  
**Status:** Base estratégica do produto

## 1. Visão do Produto

O Fidax é uma plataforma financeira em evolução que começa como um sistema de gestão financeira pessoal e avança para um consolidador de investimentos e ferramenta de análise fundamentalista.

A proposta é permitir que o usuário organize finanças, acompanhe ativos com cotações atualizadas, faça valuations com dados de mercado, filtre oportunidades e, no futuro, centralize relatórios e documentos de ativos e FIIs.

## 2. Problema que o produto resolve

Hoje a jornada financeira do usuário costuma ficar fragmentada entre planilhas, apps de controle pessoal, corretoras, sites de análise e repositórios de documentos.

O Fidax centraliza esse fluxo em um único ambiente para reduzir retrabalho, aumentar a visibilidade do patrimônio e apoiar decisões de investimento com dados mais consistentes.

## 3. Objetivos do Produto

- Organizar a vida financeira pessoal.
- Permitir cadastro e acompanhamento de ativos.
- Atualizar cotações automaticamente via API.
- Apoiar cálculos de valuation com o preço mais atual do ativo.
- Evoluir para consolidação de carteira.
- Evoluir para triagem de oportunidades com filtros fundamentalistas.
- Centralizar documentos e relatórios de ativos e FIIs.

## 4. Público-alvo

- Pessoas que estão começando a organizar as finanças.
- Usuários de controle financeiro pessoal.
- Investidores iniciantes.
- Investidores intermediários e avançados.
- Usuários que fazem análise fundamentalista e acompanham carteira.

## 5. Escopo Funcional Geral

### 5.1 Gestão financeira

- Controle de receitas.
- Controle de despesas.
- Controle de contas bancárias.
- Controle de cartões e faturas.
- Controle de transferências.
- Controle de patrimônio.

### 5.2 Investimentos

- Cadastro de ativos.
- Consulta de cotação atual via API.
- Armazenamento de logo e metadados do ativo quando disponíveis.
- Cálculo de valuation e preço teto usando dados atualizados.
- Consolidação futura de posições e performance.

### 5.3 Pesquisa e oportunidade

- Filtros por indicadores fundamentalistas.
- Busca por critérios como P/VP, DY, P/L, liquidez e setor.
- Lista de ativos aderentes a uma tese.
- Comparação entre ativos.

### 5.4 Documentos

- Central de relatórios e documentos por ativo.
- Armazenamento de fatos relevantes, releases e PDFs.
- Busca por ativo, tipo e período.

## 6. Fora de Escopo Imediato

- Execução de ordens de compra e venda.
- Integração direta com corretoras.
- Robô de trading.
- Recomendação automática de compra e venda.
- Cobertura completa de todos os mercados logo na primeira release.

## 7. Princípios do Produto

- Menos fricção na entrada de dados.
- Dados atualizados com o mínimo de intervenção manual.
- Decisões baseadas em informação consolidada.
- Crescimento por releases incrementais.
- Experiência consistente com a stack atual do projeto.

## 8. Requisitos Não Funcionais

- Interface responsiva.
- Paginação em listagens.
- Integrações externas com timeout e retry.
- Logs de falhas e tratamento de indisponibilidade.
- Cache para reduzir custo e latência de APIs externas.
- Segurança baseada na autenticação já existente.

## 9. KPIs de Sucesso

- Percentual de ativos com cotação atualizada.
- Redução de edição manual de preços.
- Uso recorrente do módulo de valuation.
- Quantidade de filtros usados para descoberta de oportunidades.
- Quantidade de documentos centralizados por ativo.
- Retenção de usuários no módulo de investimentos.

## 10. Releases do Produto

### Release 1 - Gestão Financeira + Cadastro de Ações com Cotação

**Objetivo**  
Entregar o núcleo próximo da conclusão: gestão financeira pessoal com cadastro de ações e atualização automática da cotação para suportar valuation.

**Inclui**

- Módulo base de finanças pessoais.
- Cadastro e edição de ativos.
- Cadastro de ações com ticker.
- Integração com API de cotação.
- Uso da cotação atual em cálculos de valuation.
- Telas de listagem, criação, edição e visualização de valuation.
- Atualização de logo e preço mais recente do ativo.

**Valor entregue**

- Redução de entrada manual.
- Base funcional para análise inicial de investimentos.
- Primeira experiência de dado de mercado dentro do Fidax.

**Critério de aceite da release**

- O usuário cadastra uma ação e recebe cotação atual automaticamente.
- A listagem de investimentos exibe o valor mais recente.
- O valuation usa o preço mais atual disponível.
- A integração com a API externa não quebra a navegação quando falha.

### Release 2 - Consolidador de Carteira

**Objetivo**  
Transformar o Fidax em um consolidador de posições e rentabilidade.

**Inclui**

- Visão consolidada da carteira.
- Distribuição por classe de ativo.
- Rentabilidade acumulada e por período.
- Evolução de patrimônio.
- Dividendos e proventos.
- Resumo por ativo, setor e carteira.

**Valor entregue**

- Visão única da carteira.
- Acompanhamento de performance.
- Melhor leitura de risco e concentração.

### Release 3 - Screener e Descoberta de Oportunidades

**Objetivo**  
Permitir que o usuário encontre ativos que fazem sentido para sua tese de investimento usando filtros com dados retornados pela API.

**Inclui**

- Filtros por P/VP, DY, P/L, margem, liquidez, setor e preço.
- Combinação de múltiplos critérios.
- Shortlist de ativos.
- Comparação entre candidatos.
- Favoritos de filtro.

**Exemplo de uso**

- Encontrar ações com `P/VP < x`.
- Encontrar ações com `DY > 7%`.
- Combinar filtros para reduzir o universo e chegar em uma tese.

**Valor entregue**

- Menos tempo procurando ativos manualmente.
- Melhor triagem quantitativa.
- Apoio à decisão antes do valuation.

### Release 4 - Central de Documentos e Research

**Objetivo**  
Centralizar relatórios, documentos e materiais relevantes de ativos e FIIs.

**Inclui**

- Upload e organização de documentos.
- Associação a ativos e FIIs.
- Busca e classificação por tipo de documento.
- Histórico documental por empresa/fundo.
- Área para leitura e consulta de relatórios.

**Valor entregue**

- Menos dispersão de arquivos.
- Contexto histórico mais fácil de revisar.
- Base documental para suportar tese e valuation.

### Release 5 - Inteligência e Apoio à Decisão

**Objetivo**  
Adicionar camadas inteligentes sobre os dados já consolidados.

**Inclui**

- Insights sobre comportamento financeiro.
- Alertas automáticos.
- Recomendações de acompanhamento.
- Padrões de carteira e comportamento.

**Valor entregue**

- Mais contexto para o usuário.
- Menos trabalho manual.
- Melhor retenção e utilidade do produto.

## 11. Épicos do Produto

- Finanças pessoais.
- Cadastro e acompanhamento de ativos.
- Cotação e dados de mercado.
- Valuation e preço teto.
- Consolidação de carteira.
- Screener de oportunidades.
- Documentos e research.
- Inteligência financeira.

## 12. Histórias de Usuário

- Como usuário, quero cadastrar um ativo com ticker para acompanhar sua cotação.
- Como usuário, quero que o sistema busque o preço atual automaticamente para não depender de entrada manual.
- Como usuário, quero ver a cotação atual na lista de investimentos para acompanhar minha posição.
- Como usuário, quero calcular valuation com base no preço atual para ter análises mais confiáveis.
- Como usuário, quero filtrar ações por indicadores para encontrar oportunidades alinhadas à minha estratégia.
- Como usuário, quero centralizar relatórios e documentos para estudar melhor cada ativo.

## 13. Critérios Gerais de Aceitação

- Fluxos principais devem funcionar com login autenticado.
- Integrações externas devem falhar sem derrubar a aplicação.
- Dados de mercado devem ser reaproveitados entre telas e cálculos.
- O produto deve crescer em releases sem quebrar a base já entregue.

## 14. Riscos e Dependências

- Dependência de API externa para cotação.
- Qualidade e disponibilidade dos dados retornados pela fonte de mercado.
- Evolução do escopo pode exigir ajustes de modelagem.
- A expansão para screener e documentos pode demandar novos serviços e armazenamento.

## 15. Próximos Passos

1. Finalizar a Release 1.
2. Consolidar a visão de carteira.
3. Definir o modelo de filtros do screener.
4. Estruturar a central de documentos.
5. Planejar camadas de inteligência e alertas.

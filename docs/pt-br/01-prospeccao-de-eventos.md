# Prospecção de Eventos

[◂ Sumário da Documentação](indice.md) | [Armazenador de eventos ▸](02-armazenador-de-eventos.md)
-- | --

## Abordagem "DataBase First"

Na abordagem "DataBase First", grande parte da preocupação é focada na estruturação do banco de dados. Primeiro modela-se a estrutura do banco de dados para depois implementar a aplicação.

A implementação segue o seguinte fluxo:

1. **Análise de requisitos**: definir as regras de negócio;
2. **Modelagem de dados**: determinar as entidades que irão compor a estrutura do banco de dados;
3. **Automação na criação das tabelas**: criar scripts para gerar as tabelas no banco;
4. **Implementação**: as regras de negócio são implementadas com base nas entidades do banco de dados.

A manutenção segue o seguinte fluxo:

1. **Análise de requisitos**: definir as alterações nas regras de negócio;
2. **Modelagem de dados**: determinar uma nova estrutura para o banco de dados (remoção, criação e alteração de campos);
3. **Automação na criação das tabelas**: criar scripts para atualizar as tabelas no banco;
4. **Implementação**: as regras de negócio são implementadas com base nas novas entidades do banco de dados.

### Vantagens

Cada entidade terá uma tabela relacionada no banco de dados, de forma que, ao olhar para sua estrutura, poderemos identificar com facilidade os valores que formam o estado atual de cada entidade do sistema.

Mais fácil de entender, ensinar e implementar. 

### Desvantagens

Para manter os dados consolidados, operações de inserção e atualização precisam ser usadas. Inserções são muito rápidas, mas atualizações são mais custosas para o banco de dados (principalmente em aplicações muito acessadas).

Anter de implementar evoluções ou manutenções na aplicação, é preciso ajustar o banco de dados, o que dificultará o processo de automação do deploy.

Só teremos os dados finais de cada entidade, o que dificultará (principalmente em rotinas complexas) o processo de entendimento. Teremos periodicamente a pergunta: "Como o usuário conseguiu produzir esse resultado?"

## Abordagem "Event Sourcing"

Diferente da abordagem "DataBase First", o "Event Sourcing" (também conhecido como "Event Prospection") não foca nos dados, mas nos eventos ocorridos durante a execução da regra.

Como os dados finais são apenas uma "consequência" da execução dos diversos eventos dentro da regra de negócio, esta abordagem armazena os eventos ao invés dos dados. Com os eventos armazenados, temos a possibilidade de reexecutá-los a qualquer momento, repetindo exatamente as ações efetuadas pelo usuário.

A implementação segue o seguinte fluxo:

1. **Tabela de eventos**: criação de uma única tabela que armazenará todos os eventos do sistema;
2. **Análise de requisitos**: definir as regras de negócio;
3. **Modelagem do domínio**: determinar as entidades e seus respectivos eventos;
4. **Implementação**: as regras de negócio são implementadas com base nas entidades e eventos definidos.

A manutenção segue o seguinte fluxo:

1. **Análise de requisitos**: definir as regras de negócio;
2. **Modelagem do domínio**: determinar o que será alterado ou acrescentado nas entidades e eventos;
3. **Implementação**: as regras de negócio são atualizadas com base nas entidades e eventos definidos.

### Vantagens

Qualquer operação previamente efetuada poderá ser desfeita ou reexecutada a qualquer momento.

As informações geradas, pela execução de uma sequência de eventos, podem ser materializadas em bancos de dados separados, fornecendo grande flexibilidade. A execução dos eventos pode materializar os dados para vários destinos diferentes: uma tabela para relatórios, uma lista simples para buscas em uma grade de dados, ou qualquer outra estrutura.

Os dados poderão ser eliminados e materializados novamente a qualquer momento. No processo, tabelas muito simples e sem JOINs podem ser geradas, otimizando a consulta e gerando resultados instantãneos para o usuário.

Elimina a necessidade de normalização no banco de dados.

Possibilita identificar exatamente o que o usuário fez para chegar a um determinado resultado em qualquer operação.

Como não existe a preocupação com o "esquema" do banco de dados. Como as materializações criam suas estruturas em tempo de execução, o deploy se torna mais fácil e seguro, dependendo apenas do código fonte implementado.

### Desvantagens

Todas as entidades terão seus eventos armazenados de uma forma padrão, intencionalmente formatada para o sistema entender. Dessa forma, ao olhar para a tabela de eventos, não teremos tanta facilidade para entender a estrutura das entidades, mas será fácil entender o fluxo de eventos.

Por ser uma abordagem que não foca nos dados, a curva de aprendizado é maior, principalmente para desenvolvedores inexperientes.

[◂ Sumário da Documentação](indice.md) | [Armazenador de eventos ▸](02-armazenador-de-eventos.md)
-- | --

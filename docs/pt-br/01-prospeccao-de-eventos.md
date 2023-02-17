# Prospecção de Eventos

[◂ Sumário da Documentação](indice.md) | [Armazenador de eventos ▸](02-armazenador-de-eventos.md)
-- | --

## Abordagem "DataBase First"

Na abordagem "DataBase First", grande parte da preocupação é focada na estruturação do banco de dados.
Primeiro modela-se a estrutura que o banco de dados vai ter para depois começar a implementar a aplicação.

Na aplicação, a sequência de eventos, da regra de negócio, é executada e os dados resultantes são inseridos 
ou atualizados no banco de dados, seguindo as estruturas pré-determinadas. 

Ao olhar para o banco de dados, vemos os dados finais gerados pela regra de negócio da aplicação.

### Vantagens

Ao olhar para o banco de dados, podemos ver os valores de estado atual de cada entidade de forma
estruturada e fácil de entender.

### Desvantagens

Para manter os dados consolidados, tanto operações de inserção como de atualização precisam ser usadas. 
As operações de gravação são muito rápidas, mas as de atualização são custosas, pricipalmente para 
sistemas com alto número de acessos simultâneos.

Qualquer novo valor acrescentado à regra de negócio exigirá ajustar previamente a estrutura do banco 
de dados.

Em uma regra de negócio complexa, não é fácil saber exatamente tudo o que aconteceu com um determinado 
valor, que começou como 20.50 e terminou como 115.15.

## Abordagem "Event Prospection"

Diferente da abordagem anterior,  a Prospecção de Eventos tenta focar a maior preocupação naquilo que 
é mais importante para qualquer aplicação corporativa: as regras de negócio.

Ao invés de gravar os dados resultantes da regra de negócio, são gravados os eventos responsáveis por
gerá-los. Todos os eventos de uma regra de negócio são armazenados em sequencia, em uma única tabela 
no banco de dados, não precisando de esquema fixo para isso.

Ao olhar para o banco de dados, vemos a sequência de eventos ocorridos, de forma que podemos entender 
todo o processo percorrido até que o valor 20.50 se tornasse 115.15.

### Vantagens

Qualquer operação pode ser desfeita ou reexecutada a qualquer momento.

Informações preciosas podem ser analisadas consultando uma determinada sequência de eventos no banco de dados.

Como não existe a preocupação com o "esquema" do banco de dados, o deploy é mais fácil e seguro.

### Desvantagens

Ao olhar para o banco de dados, veremos a sequéncia de eventos, de forma que será mais difícil identificar,
somente pelo banco, todos os dados do estado atual de uma entidade.

[◂ Sumário da Documentação](indice.md) | [Armazenador de eventos ▸](02-armazenador-de-eventos.md)
-- | --

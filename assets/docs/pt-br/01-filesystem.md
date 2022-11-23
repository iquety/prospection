# Filesystem

--page-nav--

## Objetivo

Esta classe é usada para manipulação de arquivos e diretórios. O problema de segurança acontece quando é necessário consultar o caminho de um arquivo usando um valor fornecido pelo usuário. Caminhos relativos podem permitir a especulação de conteúdo privado do servidor.

Claro que um servidor corretamente configurado é capaz de impedir este tipo de ataque, mas erros humanos podem acontecer. Por esse motivo, faz sentido tratar todos os valores provenientes do usuário do sistema.

### Funcionalidades

Na construção de um novo objeto `Filesystem`, é preciso fornecer um **contexto**, ou seja, um diretório válido que servirá de limite para manipulação de arquivos e ditetórios.

Em outras palavras, apenas os arquivos e diretórios que estiverem dentro do diretório de contexto poderão ser manipulados pela instância de `Filesystem`.

Os métodos disponíveis são:

- getContextPath
- getDirectoryContents
- getDirectoryFiles
- getDirectorySubdirs
- getFileContents
- getFileRows
- getFilePermissions
- isDirectory
- isFile
- isReadable
- isWritable
- changePermissions
- makeDirectory
- setFileContents
- appendFileContents

--page-nav--

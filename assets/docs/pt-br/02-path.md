# Path

--page-nav--

## Funcionalidades

Na construção de um novo objeto `Path`, é preciso fornecer um **contexto**, ou seja, um diretório válido que servirá de limite para manipulação de caminhos.

Em outras palavras, apenas os nós de arquivos e diretórios que estiverem dentro do diretório de contexto poderão ser acessados pela instância de `Path`.

Se o contexto for o caminho para um arquivo, o contexto será ignorado e a manipulacao funcionará levando em consideracao que o caminho do arquivo é seguro.

Os métodos disponíveis são:

- addNodePath
- getAbsolutePath
- getContextPath
- getDirectory
- getExtension
- getFile
- getName
- getNodePath
- getPath
- isLocalPath
- isRelativePath

--page-nav--

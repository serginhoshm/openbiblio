# OpenBiblio — spin-off em português

Este repositório é um spin-off do OpenBiblio, um sistema de automação de bibliotecas. Ele parte da versão 0.7.1 e reúne alterações próprias, incluindo ajustes voltados ao uso local e à interface em português.

## Sobre o projeto

O OpenBiblio é uma aplicação web legada para administrar acervo e circulação de uma biblioteca. Neste repositório, as principais áreas do sistema são:

- `catalog/`: cadastro e pesquisa do acervo e de exemplares;
- `circ/`: circulação, incluindo empréstimos e devoluções;
- `opac/`: catálogo público de consulta;
- `admin/`: administração do sistema;
- `reports/`: relatórios;
- `install/` e `install_instructions.html`: instalação e configuração inicial.

O histórico deste spin-off registra uma alteração na pesquisa do acervo por código de barras: a consulta passou a exigir correspondência exata, em vez de encontrar códigos que apenas contenham o texto pesquisado. Essa mudança está no commit `b0a77a0` (`Change biblio query by barcode to be exact`), em `classes/BiblioSearchQuery.php`.

## Instalação

Consulte [`install_instructions.html`](install_instructions.html) para as instruções herdadas do projeto original. Elas descrevem dependências e configurações de uma versão antiga; verifique a compatibilidade e os requisitos de segurança do ambiente antes de expor a aplicação à rede.

## Licença

Consulte [`LICENSE`](LICENSE) e [`GPL.txt`](GPL.txt).

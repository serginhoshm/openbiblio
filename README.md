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

## Reescrita PHP 8.2+

A nova implementação está sendo desenvolvida de forma isolada na branch `rewrite/php82`, em um worktree separado. O checkout e os fluxos legados permanecem preservados enquanto os módulos são migrados e testados. Não aponte o servidor da versão atual para os novos arquivos nem execute a instalação antiga contra a base usada nos testes.

O inventário inicial encontrou 287 arquivos PHP no sistema original; 9 não passam nem pela análise sintática do PHP 8.3, devido à antiga sintaxe de acesso a caracteres/índices com chaves (por exemplo, em [`classes/Query.php`](classes/Query.php) e [`classes/Search.php`](classes/Search.php)). A camada de banco também depende das funções removidas `mysql_*`, e o repositório não tinha Composer nem testes automatizados. Por isso, a reescrita está sendo introduzida em módulos paralelos, sem editar esses arquivos legados.

Para preparar e executar a base inicial da reescrita, entre no worktree, instale o autoloader e inicie o servidor de desenvolvimento em uma porta separada:

```sh
cd ../openbiblio-php82
composer install
php -S 127.0.0.1:8081 -t modern/public modern/public/router.php
```

Abra `http://127.0.0.1:8081/`; `http://127.0.0.1:8081/health` verifica a aplicação HTTP, sem conectar ao banco. O OPAC em `http://127.0.0.1:8081/opac` pesquisa exemplares por correspondência exata de barcode e títulos públicos por termos, conectando ao banco somente ao enviar uma pesquisa. A circulação em `/circulation` oferece consulta interna de membro, empréstimos, devoluções, histórico e gerenciamento de reservas. Esses fluxos exigem login de funcionário com permissão de circulação; criar, editar ou remover membros também exige a permissão específica de manutenção de membros. Os formulários que alteram dados validam CSRF. As regras portadas incluem limites por material, multas, renovações, reservas vencidas, devolução, taxas de atraso e fila de exemplares para estante. A administração do catálogo já permite pesquisar registros existentes, cadastrar exemplares com barcode manual ou automático, editar seus dados e campos personalizados e remover exemplares não emprestados, reservados ou em processamento, incluindo seu histórico. As mudanças de status são realizadas pelos fluxos de circulação. O catálogo também permite criar e editar registros bibliográficos, com os campos centrais e subcampos MARC adicionais; a apresentação detalhada por vocabulários MARC e a manutenção dessas configurações ainda estão em migração. O cadastro básico e a edição de membros estão disponíveis em `/circulation/members/new` e pelo perfil do membro; barcodes podem ser automáticos ou manuais, e os campos customizados definidos no banco são exibidos e persistidos nos formulários e no perfil do membro. Pelo perfil também é possível consultar o histórico de empréstimos, a conta, lançar pagamentos/créditos/cobranças e remover transações, mantendo o esquema legado. A remoção de membro apaga conta, histórico e campos adicionais após confirmar que não existem empréstimos ou reservas ativos. Outros módulos administrativos ainda estão em migração. Os testes podem ser executados com `composer test` e a verificação de sintaxe com `composer lint`. A reescrita exige PHP 8.2 ou superior; relatórios e os demais fluxos administrativos permanecem em migração.

As páginas HTML da reescrita compartilham uma navegação principal com acesso à página inicial e ao OPAC, além dos módulos autorizados para o funcionário conectado e da opção de sair. A página inicial funciona como painel de entrada: mostra os fluxos disponíveis para a sessão e informa quais áreas ainda estão em migração. Os controles de acesso continuam sendo aplicados nas rotas do servidor; ocultar um link não substitui a autorização.

A administração do catálogo também permite criar e editar registros bibliográficos: dados centrais do registro, classificação, visibilidade no OPAC e subcampos MARC adicionais, com validação dos campos obrigatórios configurados por tipo de material. A busca administrativa e a lista de exemplares dão acesso ao editor. Registros podem ser removidos após confirmação somente quando não possuem exemplares ou reservas. Essa etapa ainda não reproduz integralmente a experiência legada: a apresentação detalhada pelos vocabulários MARC, os controles específicos de cada subcampo e a manutenção da configuração MARC por tipo de material continuam pendentes.

A conexão PDO está isolada em uma fábrica e recebe configuração do ambiente. Antes de habilitar módulos que usam o banco, configure `OPENBIBLIO_DB_HOST`, `OPENBIBLIO_DB_NAME`, `OPENBIBLIO_DB_USER` e `OPENBIBLIO_DB_PASSWORD`; `OPENBIBLIO_DB_PORT` é opcional (padrão `3306`). A conexão solicita `utf8mb4` para o cliente; isso não altera o charset nem o esquema existente, e o próprio MySQL converte os dados conforme a definição atual das tabelas. Use credenciais exclusivas e um banco de desenvolvimento isolado.

O esquema existente usa MyISAM. Os fluxos mutáveis usam bloqueios nomeados MySQL para serializar operações da nova implementação, mas o mecanismo não oferece rollback transacional: falhas durante atualizações em várias etapas, como edição bibliográfica e remoção de membro ou exemplar, podem deixar alterações parciais. Ainda não foi validado contra uma instância real do banco; antes de uso operacional, teste com uma cópia isolada dos dados e confirme a compatibilidade dos bloqueios e a recuperação desses casos.

### Publicação local isolada (uso experimental)

O script [`deploy/deploy-local.sh`](deploy/deploy-local.sh) publica somente a reescrita, sem substituir os arquivos legados. Execute-o a partir de um terminal local interativo:

```sh
sudo bash /home/sergio85/openbiblio-php82/deploy/deploy-local.sh
```

Ele exige confirmação explícita, pede a senha do MariaDB sem exibi-la, verifica as tabelas necessárias e cria um dump compactado com permissões restritas em `/var/backups/openbiblio/`. A configuração da conexão fica fora do document root, em `/etc/openbiblio/database.json`, legível apenas por `root` e pelo grupo `www-data`. O Apache escuta somente em `127.0.0.1:8081`; acesse `http://127.0.0.1:8081/`. O script interrompe sem sobrescrever se já existir `/var/www/html/openbiblio` ou uma configuração com esse nome.

**Não execute contra a base operacional sem um backup cuja restauração já tenha sido testada.** O dump que o script cria é verificado como arquivo gzip íntegro, mas não é restaurado automaticamente. A reescrita segue incompleta, não foi validada integralmente contra o esquema real, e as operações de catálogo e circulação podem modificar dados. A credencial compartilhada anteriormente no chat deve ser rotacionada; digite apenas a credencial nova no prompt local. A publicação não transforma esta versão em pronta para uso nem substitui uma validação de ponta a ponta.

Para publicar alterações posteriores da reescrita em uma instalação local criada por esse script, use [`deploy/update-local.sh`](deploy/update-local.sh) no worktree `rewrite/php82`:

```sh
sudo bash /home/sergio85/openbiblio-php82/deploy/update-local.sh
```

O atualizador exige confirmação explícita, roda os testes, verifica a conexão configurada com o banco sem modificar seus dados e publica uma nova release por troca atômica do link `current`. A release anterior é preservada e restaurada automaticamente se a publicação ou as verificações HTTP falharem. Ele não altera a configuração do Apache nem a configuração/estrutura/dados do banco; não use o atualizador em uma instalação cuja origem ou configuração não tenha sido verificada.

## Licença

Consulte [`LICENSE`](LICENSE) e [`GPL.txt`](GPL.txt).

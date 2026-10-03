# Memória de decisões e execução — OpenBiblio

**Atualizado em:** 2026-10-03
**Branch de trabalho:** `rewrite/php82`
**Worktree da reescrita:** `/home/sergio85/openbiblio-php82`

Este documento reúne o contexto, as decisões, o plano e o estado do trabalho para que a reescrita possa ser retomada em outra sessão. Não é uma declaração de que o sistema novo está completo ou pronto para produção.

## Objetivo e contexto

O repositório é um spin-off do OpenBiblio 0.7.1, um sistema web de automação de bibliotecas. A versão original é antiga e depende de APIs removidas do PHP e de construções sintáticas incompatíveis com versões atuais. O inventário inicial encontrou 287 arquivos PHP; 9 apresentaram erros de sintaxe no PHP 8.3, e a camada legada depende extensamente de funções `mysql_*`.

Uma alteração antiga lembrada pelo usuário foi localizada no commit `b0a77a0` (`Change biblio query by barcode to be exact`), em `classes/BiblioSearchQuery.php`: a pesquisa passou a exigir correspondência exata do código de barras, em vez de aceitar correspondências parciais. A busca nova deve conservar esse comportamento.

## Decisões tomadas

1. **Preservar a versão existente.** A reescrita fica em uma branch e worktree separados. O checkout legado segue em `main`; não se deve migrar código novo para `main`, fazer merge ou substituir a instalação atual até a reescrita estar integralmente testada e aprovada pelo usuário.
2. **Modernizar primeiro o PHP.** A primeira etapa tem como alvo PHP 8.2 ou superior. A modernização/alteração do banco foi deixada para outra etapa.
3. **Manter o esquema existente durante esta etapa.** A aplicação nova usa o banco MySQL/MariaDB e as tabelas legadas; não deve introduzir alterações de DDL como parte desta migração.
4. **Começar sem framework.** Foi escolhida uma estrutura modular própria, pequena, baseada em PHP moderno, Composer, PSR-4 e PDO, em vez de introduzir imediatamente um framework.
5. **Migrar gradualmente, com testes.** Os fluxos são portados em fatias funcionais, preservando regras do sistema legado, validando entradas, autorizando operações no servidor e escapando dados na saída.
6. **Fornecer uma experiência integrada.** O produto não deve depender de o usuário conhecer URLs dos módulos. A página inicial deve ser um painel útil e as páginas devem compartilhar navegação. A navegação pode ocultar módulos sem permissão, mas a autorização obrigatoriamente continua aplicada nas rotas.
7. **Manter o deploy experimental isolado.** A reescrita foi publicada em uma porta local separada, sem substituir o site legado. Publicações seguintes devem ser feitas como releases reversíveis e não devem alterar implicitamente o banco.
8. **Proteger credenciais e dados.** Segredos não devem ser gravados no código nem neste documento. O deploy deve solicitar a credencial localmente e manter a configuração fora do document root. O banco operacional exige backup cuja restauração tenha sido testada antes de qualquer uso operacional.

## Plano originalmente projetado

- Inventariar dependências e comportamento do sistema legado.
- Criar a fundação PHP atualizada: front controller, roteamento, configuração, tratamento de erros, PDO e testes.
- Migrar os módulos por prioridade, mantendo compatibilidade com o esquema atual.
- Preservar autenticação, regras de negócio, permissões e fluxos de interface do sistema original.
- Cobrir segurança (consultas preparadas, CSRF, sessões, escaping e autorização), além de paridade funcional por testes.
- Validar cada etapa e o conjunto contra cópia isolada do banco, revisar recuperação/backup e só então considerar aprovação e eventual merge.
- Avaliar o impacto de modernizar o banco separadamente; isso foi explicitamente deixado fora da fase PHP.

## Trabalho executado

### Estrutura e execução

- Criados `composer.json`/`composer.lock`, autoload PSR-4, aplicação HTTP, roteador, requisição/resposta, endpoint de saúde e workflow de CI para PHP 8.2/8.3.
- Criada uma camada de conexão PDO configurável por variáveis de ambiente ou arquivo JSON protegido fora da aplicação. A conexão solicita `utf8mb4`, sem alterar o esquema existente.
- Criado o script inicial `deploy/deploy-local.sh`. Ele verifica dependências/tabelas, exige confirmações, pede a senha do banco sem eco, cria um dump compactado, armazena configuração protegida e configura o Apache somente em `127.0.0.1:8081`, sem substituir a versão legada.
- O usuário executou esse deploy inicial e confirmou que funcionou. A aplicação foi disponibilizada localmente em `http://127.0.0.1:8081/`.
- Criado `deploy/update-local.sh` para versões posteriores: exige confirmação, roda testes, valida a conexão, copia uma release e troca o link `current` atomicamente, mantendo a release anterior e tentando restaurá-la se as verificações falharem. **Este atualizador ainda não foi executado pelo usuário nesta revisão.**

### Fluxos migrados

- **OPAC:** formulário público, pesquisa de exemplares por barcode exato e pesquisa de títulos por termos; não conecta ao banco até haver uma pesquisa; exclui bibliografias não públicas e escapa dados apresentados.
- **Autenticação:** login/logout, validação de credenciais no formato legado `MD5(LOWER(password))`, sessão e renovação do identificador, timeout, CSRF e permissões mapeadas dos flags legados.
- **Circulação:** consulta de membros; empréstimo/renovação; devolução; regras de limites, saldo, atraso e reservas; cálculo de multas; fila de exemplares para estante; criação e remoção de reservas.
- **Membros:** consulta, cadastro com barcode manual ou automático, edição, campos personalizados, histórico de empréstimos, conta/extrato, lançamentos e remoção de transações. A exclusão do membro exige confirmação, revalida e bloqueia empréstimos/reservas ativos, e remove dados relacionados.
- **Catálogo administrativo:** busca de bibliografias, administração de exemplares, cadastro/edição/remoção de cópias com campos personalizados e proteções contra remover exemplares ativos; cadastro/edição/remoção de registros bibliográficos, campos centrais e subcampos MARC adicionais, com validação de campos obrigatórios e bloqueio de exclusão quando existem cópias ou reservas.
- **Navegação:** menu compartilhado nas respostas HTML, com links adequados à sessão/permissões e opção de sair; página inicial em formato de painel com módulos disponíveis e indicação explícita das áreas em migração.
- **Documentação:** README em português descreve o spin-off, a reescrita, as rotas/funcionalidades atuais, limitações e procedimentos de execução/deploy.

## Estado atual da implementação

O código novo está em `modern/`, isolado da aplicação PHP legada. A cobertura é parcial: há funcionalidades reais em OPAC, autenticação, circulação, membros e catálogo, mas isso não equivale a uma reescrita completa.

Continuam em migração, entre outras áreas:

- administração geral, administração de funcionários e configurações;
- relatórios;
- apresentação MARC completa, vocabulários, controles específicos de subcampos e manutenção das configurações MARC por material;
- funcionalidades legadas não identificadas ou ainda não portadas após inventário funcional completo.

As rotas `/admin` e `/reports` ainda indicam migração (HTTP 501). O levantamento e os testes de paridade com o legado precisam continuar antes de se afirmar cobertura completa.

## Riscos e limites conhecidos

- O banco legado usa MyISAM. A transação PDO não fornece rollback efetivo para essas tabelas; operações com múltiplas instruções podem ficar parcialmente aplicadas se uma etapa falhar.
- Bloqueios nomeados MySQL são usados em operações mutáveis da reescrita para serializar fluxos novos, mas não garantem serialização com processos legados que não usem o mesmo bloqueio.
- Testes de persistência usam dublês PDO; não houve validação integral dos fluxos contra uma cópia real restaurada do banco. Compatibilidade com esquema/dados reais e recuperação de falhas continuam pendentes.
- O dump criado pelo deploy é verificado como gzip íntegro, mas não é automaticamente restaurado; é necessário testar a restauração separadamente.
- A revisão mais recente de navegação e painel está no código-fonte, mas ainda não foi publicada no Apache. O atualizador foi criado justamente para publicar a próxima release preservando a anterior.
- Uma credencial operacional foi compartilhada anteriormente na conversa. Ela foi omitida deste documento e não deve ser reutilizada; a recomendação registrada no deploy é rotacioná-la e inserir a nova apenas no prompt local.
- A reescrita não é considerada pronta para produção nem aprovada para merge.

## Verificação registrada nesta revisão

- Suíte PHP: **105 testes, 0 falhas**.
- `php -l` em todos os arquivos PHP de `modern/`: passou.
- `bash -n deploy/update-local.sh`: passou.
- `git diff --check`: passou.
- Estes resultados cobrem a implementação isolada e não substituem teste de integração com banco restaurado nem teste completo de aceitação pelo usuário.

## Como retomar

1. Trabalhar no worktree `/home/sergio85/openbiblio-php82`, branch `rewrite/php82`.
2. Preservar `/home/sergio85/openbiblio` em `main`; não fazer merge sem pedido e aprovação expressos.
3. Executar `php modern/tests/run.php` e `find modern -name '*.php' -print0 | xargs -0 -n1 php -l` depois das mudanças; usar Composer se estiver disponível.
4. Continuar a migração e a revisão de paridade, priorizando os módulos ainda pendentes e cobrindo regras/restrições do legado.
5. Antes de operações com dados reais, obter backup restaurável e validar em cópia isolada. Não usar o banco operacional como substituto de ambiente de teste.
6. Para publicar uma revisão local na instalação criada pelo deploy inicial, revisar o script e instruções e, com autorização do operador no computador, executar:

   ```sh
   sudo bash /home/sergio85/openbiblio-php82/deploy/update-local.sh
   ```

7. Fazer merge para `main` somente após conclusão, testes funcionais e de segurança, validação de banco/recuperação e aprovação explícita do usuário.

## Git e sincronização

No início desta consolidação, a branch `rewrite/php82` estava baseada no commit `8c60fb0`, sem alterações commitadas e sem branch homônima listada no remoto `origin`. O checkout `main` é separado. O próximo commit desta branch deve incluir a implementação da reescrita e esta memória; a sincronização remota deve publicar `rewrite/php82` sem integrar ou alterar `main`.

<?php
/* This file is part of a copyrighted work; it is distributed with NO WARRANTY.
 * See the file COPYRIGHT.html for more details.
 */
 
/**********************************************************************************
 *   Instructions for translators:
 *
 *   All gettext key/value pairs are specified as follows:
 *     $trans["key"] = "<php translation code to set the $text variable>";
 *   Allowing translators the ability to execute php code withint the transFunc string
 *   provides the maximum amount of flexibility to format the languange syntax.
 *
 *   Formatting rules:
 *   - Resulting translation string must be stored in a variable called $text.
 *   - Input arguments must be surrounded by % characters (i.e. %pageCount%).
 *   - A backslash ('\') needs to be placed before any special php characters 
 *     (such as $, ", etc.) within the php translation code.
 *
 *   Simple Example:
 *     $trans["homeWelcome"]       = "\$text='Welcome to OpenBiblio';";
 *
 *   Example Containing Argument Substitution:
 *     $trans["searchResult"]      = "\$text='page %page% of %pages%';";
 *
 *   Example Containing a PHP If Statment and Argument Substitution:
 *     $trans["searchResult"]      = 
 *       "if (%items% == 1) {
 *         \$text = '%items% result';
 *       } else {
 *         \$text = '%items% results';
 *       }";
 *
 **********************************************************************************
 */

#****************************************************************************
#*  Translation text used on multiple pages
#****************************************************************************
$trans["reportsCancel"]            = "\$text = 'Cancelar';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHdr"]                 = "\$text = 'Relatórios';";
$trans["indexDesc"]                = "\$text = 'Clique em relatório ou etiquetas no menu à esquerda para gerar relatórios ou etiquetas em .pdf.';";

#****************************************************************************
#*  Translation text for page report_list.php
#****************************************************************************
$trans["reportListHdr"]            = "\$text = 'Lista de Relatórios';";
$trans["reportListDesc"]           = "\$text = 'Escolha um link para gerar relatório.';";
$trans["reportListXmlErr"]         = "\$text = 'Ocorreu um erro durante a análise de definição xml do relatório.';";
$trans["reportListCannotRead"]     = "\$text = 'Não é possível ler o arquivo de estiqueta: %fileName%';";

#****************************************************************************
#*  Translation text for page label_list.php
#****************************************************************************
$trans["labelListHdr"]             = "\$text = 'Lista de Etiquetas';";
$trans["labelListDesc"]            = "\$text = 'Escolha um link para gerar etiquetas em .pdf.';";
$trans["displayLabelsXmlErr"]      = "\$text = 'Ocorreu um erro durante a análise de definição xml do relatório. Erro = ';";

#****************************************************************************
#*  Translation text for page letter_list.php
#****************************************************************************
$trans["letterListHdr"]            = "\$text = 'Lista de Cartas';";
$trans["letterListDesc"]           = "\$text = 'Escolha um link para gerar cartas em .pdf.';";
$trans["displayLettersXmlErr"]      = "\$text = 'Ocorreu um erro durante a análise de definição xml do relatório. Erro = ';";

#****************************************************************************
#*  Translation text for page report_criteria.php
#****************************************************************************
$trans["reportCriteriaHead1"]      = "\$text = 'Critério Para Procura (opcional)';";
$trans["reportCriteriaHead2"]      = "\$text = 'Ordenar Relatório (opcional)';";
$trans["reportCriteriaHead3"]      = "\$text = 'Tipo de Saída de Relatório';";
$trans["reportCriteriaCrit1"]      = "\$text = 'Critério 1:';";
$trans["reportCriteriaCrit2"]      = "\$text = 'Critério 2:';";
$trans["reportCriteriaCrit3"]      = "\$text = 'Critério 3:';";
$trans["reportCriteriaCrit4"]      = "\$text = 'Critério 4:';";
$trans["reportCriteriaEQ"]         = "\$text = '=';";
$trans["reportCriteriaNE"]         = "\$text = 'não =';";
$trans["reportCriteriaLT"]         = "\$text = '&lt;';";
$trans["reportCriteriaGT"]         = "\$text = '&gt;';";
$trans["reportCriteriaLE"]         = "\$text = '&lt ou =';";
$trans["reportCriteriaGE"]         = "\$text = '&gt ou =';";
$trans["reportCriteriaBT"]         = "\$text = 'entre';";
$trans["reportCriteriaAnd"]        = "\$text = 'e';";
$trans["reportCriteriaRunReport"]  = "\$text = 'Gerar Relatório';";
$trans["reportCriteriaSortCrit1"]  = "\$text = 'Ordenar 1:';";
$trans["reportCriteriaSortCrit2"]  = "\$text = 'Ordenar 2:';";
$trans["reportCriteriaSortCrit3"]  = "\$text = 'Ordenar 3:';";
$trans["reportCriteriaAscending"]  = "\$text = 'crescente';";
$trans["reportCriteriaDescending"] = "\$text = 'decrescente';";
$trans["reportCriteriaStartOnLabel"] = "\$text = 'Iniciar Impressão em Etiquetas:';";
$trans["reportCriteriaOutput"]     = "\$text = 'Tipo de Saída:';";
$trans["reportCriteriaOutputHTML"] = "\$text = 'HTML';";
$trans["reportCriteriaOutputCSV"]  = "\$text = 'CSV';";

#****************************************************************************
#*  Translation text for page run_report.php
#****************************************************************************
$trans["runReportReturnLink1"]     = "\$text = 'critério de seleção do relatório';";
$trans["runReportReturnLink2"]     = "\$text = 'lista de relatório';";
$trans["runReportTotal"]           = "\$text = 'Total de linhas:';";

#****************************************************************************
#*  Translation text for page display_labels.php
#****************************************************************************
$trans["displayLabelsStartOnLblErr"] = "\$text = 'Campo deve ser numérico.';";
$trans["displayLabelsXmlErr"]      = "\$text = 'Ocorreu um erro durante a análise de definição xml do relatório. Erro = ';";
$trans["displayLabelsCannotRead"]  = "\$text = 'Não é possível ler o arquivo de relatório: %fileName%';";

#****************************************************************************
#*  Translation text for page noauth.php
#****************************************************************************
$trans["noauthMsg"]                = "\$text = Você não está autorizado a utilizar a área de relatórios';";

#****************************************************************************
#*  Report Titles
#****************************************************************************
$trans["reportHolds"]              = "\$text = 'Reservas Realizadas Contendo Informações do Membro';";
$trans["reportCheckouts"]          = "\$text = 'Bibliografia Emprestada';";
$trans["Over Due Letters"]         = "\$text = 'Cartas Atrasadas';";
$trans["reportLabels"]             = "\$text = 'Lista de Impressão de Etiquetas';";
$trans["popularBiblios"]           = "\$text = 'Bibliografias Mais Populares';";
$trans["overdueList"]              = "\$text = 'Membros com livros Atrasados';";
$trans["balanceDueList"]           = "\$text = 'Balanço de Devoluções dos Membros';";

#****************************************************************************
#*  Label Titles
#****************************************************************************
$trans["labelsMulti"]              = "\$text = 'Etiquetas Múltiplas';";
$trans["labelsSimple"]             = "\$text = 'Uma Etiqueta';";

#****************************************************************************
#*  Column Text
#****************************************************************************
$trans["biblio.bibid"]             = "\$text = 'Biblioteca ID';";
$trans["biblio.create_dt"]         = "\$text = 'Adicionado em';";
$trans["biblio.last_change_dt"]    = "\$text = 'Alterado em';";
$trans["biblio.material_cd"]       = "\$text = 'Material Cd';";
$trans["biblio.collection_cd"]     = "\$text = 'Gênero';";
$trans["biblio.call_nmbr1"]        = "\$text = 'Chamada 1';";
$trans["biblio.call_nmbr2"]        = "\$text = 'Chamada 2';";
$trans["biblio.call_nmbr3"]        = "\$text = 'Chamada 3';";
$trans["biblio.title_remainder"]   = "\$text = 'Título';";
$trans["biblio.responsibility_stmt"] = "\$text = 'Resps';";
$trans["biblio.opac_flg"]          = "\$text = 'OPAC';";

$trans["biblio_copy.barcode_nmbr"] = "\$text = 'CódBar';";
$trans["biblio.title"]             = "\$text = 'Título';";
$trans["biblio.author"]            = "\$text = 'Autor';";
$trans["biblio_copy.status_begin_dt"]   = "\$text = 'Data de Ínicio do Status';";
$trans["biblio_copy.due_back_dt"]       = "\$text = 'Data de Devolução';";
$trans["member.mbrid"]             = "\$text = 'Membro ID';";
$trans["member.barcode_nmbr"]      = "\$text = 'Número do Membro';";
$trans["member.last_name"]         = "\$text = 'Sobrenome';";
$trans["member.first_name"]        = "\$text = 'Nome';";
$trans["member.address"]          = "\$text = 'Endereço';";
$trans["biblio_hold.hold_begin_dt"] = "\$text = 'Data de Reserva';";
$trans["member.home_phone"]        = "\$text = 'Tel. Residencial';";
$trans["member.work_phone"]        = "\$text = 'Tel. Comercial';";
$trans["member.email"]             = "\$text = 'Email';";
$trans["biblio_status_dm.description"] = "\$text = 'Status';";
$trans["settings.library_name"]    = "\$text = 'Nome da biblioteca';";
$trans["settings.library_hours"]   = "\$text = 'Horário da biblioteca';";
$trans["settings.library_phone"]   = "\$text = 'Tel. da biblioteca';";
$trans["days_late"]                = "\$text = 'Dias Atrasados';";
$trans["title"]                    = "\$text = 'Título';";
$trans["author"]                   = "\$text = 'Autor';";
$trans["due_back_dt"]              = "\$text = 'Devolução';";
$trans["checkoutCount"]            = "\$text = 'Número de Empréstimos';";

#****************************************************************************
#*  Outros
#****************************************************************************
$trans["Cataloging"]                       = "\$text = 'Catalogando';";
$trans["Acquisition"]                      = "\$text = 'Aquisições';";
$trans["Copy Search"]                      = "\$text = 'Buscar de Exemplares (Impressão de Etiquetas)';";
$trans["Duplicate Titles"]                 = "\$text = 'Títulos Duplicados';";
$trans["Circulation"]                      = "\$text = 'Circulação';";
$trans["Item Checkout History"]            = "\$text = 'Listar Retiradas Bibliográficas';";
$trans["Member Search"]                    = "\$text = 'Buscar Membros (Impressão de Carteiras)';";
$trans["Statistics"]                       = "\$text = 'Estatísticas';";
$trans["Most Popular Authors"]             = "\$text = 'Autores Mais Populares';";
$trans["Periodic Checkout Count"]          = "\$text = 'Contagem Periódica do Caixa';";

$trans["Report List"]                      = "\$text = 'Lista de Relatórios';";
$trans["Report Results"]                   = "\$text = 'Resultados do Relatório';";
$trans["results found"]                    = "\$text = 'Registro(s) encontrado(s)';";
$trans["Labels"]                           = "\$text = 'Imprimir Etiquetas';";
$trans["Skip Labels"]                      = "\$text = 'Pular Etiquetas';";
$trans["Submit"]                           = "\$text = 'Enviar';";
$trans["Report Criteria"]                  = "\$text = 'Critérios do Relatório';";
$trans["After (Date or yesterday)"]        = "\$text = 'Depois (Data ou ontem)';";
$trans["Before"]                           = "\$text = 'Antes';";
$trans["Collection"]                       = "\$text = 'Coleção';";
$trans["Material"]                         = "\$text = 'Material';";
$trans["Sort By"]                          = "\$text = 'Ordenar Por';";
$trans["Format"]                           = "\$text = 'Formato';";
$trans["Help"]                             = "\$text = 'Ajuda';";
$trans["Minimum balance"]                  = "\$text = 'Saldo Mínimo';";

$trans["Barcode Starts With"]              = "\$text = 'Código de Barras maior que';";
$trans["List of Barcodes (spaced)"]        = "\$text = 'Lista de Códigos de Barras (espaçados)';";
$trans["Newer than (Date or today)"]       = "\$text = 'Mais recente que (Data ou hoje)';";
$trans["Due before"]                       = "\$text = 'Antes de (ex: 01.01.2020)';";
$trans["Out since"]                        = "\$text = 'A partir de (ex: 01.01.2020)';";
$trans["As of"]                            = "\$text = 'A partir de (ex: 01.01.2020)';";
$trans["Name Contains"]                    = "\$text = 'Nome Contém';";
$trans["Address or Phone or Email Contains"] = "\$text = 'Endereço ou Telefone ou E-mail Contém';";
$trans["Placed before"]                      = "\$text = 'Colocado em reserva antes de';";
$trans["Placed since"]                       = "\$text = 'Colocado em reserva até';";
$trans["Cards"]                              = "\$text = 'Imprimir Carteirinhas';";
$trans["ID Cards, self-laminating"]          = "\$text = '';";
$trans["Labels"]                             = "\$text = '';";
$trans["Print list"]                         = "\$text = '';";
$trans["Group By"]                         = "\$text = 'Agrupar por';";
$trans["Bibliography"]                     = "\$text = 'Bibliografia';";
$trans["Bibliography Copy"]                     = "\$text = 'Cópia de Bibliografia';";
$trans["Author"]                     = "\$text = 'Autor';";
$trans["Title"]                     = "\$text = 'Titulo';";
$trans["Most Checkouts, Author, Title"]      = "\$text = 'Mais devolvidos, Autor, Titulo';";
$trans["# Checkouts, Author, Title"]      = "\$text = '# Devolvidos, Autor, Titulo';";
$trans["title"]      = "\$text = 'Titulo';";
$trans["# Checkouts, Author, Title"]      = "\$text = '# Devolvidos, Autor, Titulo';";
$trans["# Checkouts, Author, Title"]      = "\$text = '# Devolvidos, Autor, Titulo';";



























?>

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
#*  Translation text for class Biblio
#****************************************************************************
$trans["biblioError1"]            = "\$text = 'Número de chamada é obrigatório.';";

#****************************************************************************
#*  Translation text for class BiblioField
#****************************************************************************
$trans["biblioFieldError1"]       = "\$text = 'Campo é obrigatório.';";
$trans["biblioFieldError2"]       = "\$text = 'Tag deve ser numérica.';";
$trans["memberFieldError"]       = "\$text = 'hhhhh.';";

	

#****************************************************************************
#*  Translation text for class BiblioQuery
#****************************************************************************
$trans["biblioQueryQueryErr1"]    = "\$text = 'Erro ao acessar informação da bibliografia.';";
$trans["biblioQueryQueryErr2"]    = "\$text = 'Erro ao acessar informação do campo da bibliografia.';";
$trans["biblioQueryInsertErr1"]   = "\$text = 'Erro ao inserir informação da bibliografia.';";
$trans["biblioQueryInsertErr2"]   = "\$text = 'Erro ao inserir informação do campo da bibliografia.';";
$trans["biblioQueryUpdateErr1"]   = "\$text = 'Erro ao atualizar informação da bibliografia.';";
$trans["biblioQueryUpdateErr2"]   = "\$text = 'Erro ao limpar infomrmação de campo para atualizar.';";
$trans["biblioQueryDeleteErr"]    = "\$text = 'Erro ao apagar informação da bibliografia.';";

#****************************************************************************
#*  Translation text for class BiblioSearchQuery
#****************************************************************************
$trans["biblioSearchQueryErr1"]   = "\$text = 'Erro ao enumerar o resultado de busca por bibliografia.';";
$trans["biblioSearchQueryErr2"]   = "\$text = 'Erro ao procurar informações da biliografia.';";
$trans["biblioSearchQueryErr3"]   = "\$text = 'Erro ao ler informações da bibliografia.';";

#****************************************************************************
#*  Translation text for class BiblioCopy
#****************************************************************************
$trans["biblioCopyError1"]        = "\$text = 'Código de Barras é necessário.';";
$trans["biblioCopyError2"]        = "\$text = 'Código de Barras deve ser alfanumérico.';";

#****************************************************************************
#*  Translation text for class BiblioCopyQuery
#****************************************************************************
$trans["biblioCopyQueryErr1"]     = "\$text = 'Erro ao procurar por Código de Barras.';";
$trans["biblioCopyQueryErr2"]     = "\$text = 'O Código de Barras %barcodeNmbr% já está em uso.';";
$trans["biblioCopyQueryErr3"]     = "\$text = 'Erro ao inserir informação do exemplar bibliográfico.';";
$trans["biblioCopyQueryErr4"]     = "\$text = 'Erro ao acessar informação do exemplar bibliográfico.';";
$trans["biblioCopyQueryErr5"]     = "\$text = 'Erro ao atualizar informação do exemplar bibliográfico.';";
$trans["biblioCopyQueryErr6"]     = "\$text = 'Erro ao apagar informação do exemplar bibliográfico.';";
$trans["biblioCopyQueryErr7"]     = "\$text = 'Erro ao acessar informação do exemplar bibliográfico para obter o Código de Barras.';";
$trans["biblioCopyQueryErr8"]     = "\$text = 'Erro ao acessar informação do Gênero para checar dia da devolução.';";
$trans["biblioCopyQueryErr9"]     = "\$text = 'Erro ao checar exemplares devolvidos';";
$trans["biblioCopyQueryErr10"]    = "\$text = 'Erro ao checar limites de empréstimo';";
$trans["biblioCopyQueryErr11"]    = "\$text = 'Erro ao buscar copyid mais elevado.';";

#****************************************************************************
#*  Translation text for class BiblioFieldQuery
#****************************************************************************
$trans["biblioFieldQueryErr1"]    = "\$text = 'Erro de leitura no campo bibliográfico.';";
$trans["biblioFieldQueryErr2"]    = "\$text = 'Erro de leitura no campo bibliográfico.';";
$trans["biblioFieldQueryInsertErr"] = "\$text = 'Erro ao inserir nova bibliografia.';";
$trans["biblioFieldQueryUpdateErr"] = "\$text = 'Erro ao atualizar bibliografia.';";
$trans["biblioFieldQueryDeleteErr"] = "\$text = 'Erro ao remover bibliografia.';";

#****************************************************************************
#*  Translation text for class UsmarcBlockDmQuery
#****************************************************************************
$trans["usmarcBlockDmQueryErr1"]  = "\$text = 'Erro ao acessar os dados do bloco marc.';";

#****************************************************************************
#*  Translation text for class UsmarcTagDmQuery
#****************************************************************************
$trans["usmarcTagDmQueryErr1"]    = "\$text = 'Erro ao acessar os dados da tag marc.';";

#****************************************************************************
#*  Translation text for class UsmarcSubfieldDmQuery
#****************************************************************************
$trans["usmarcSubfldDmQueryErr1"] = "\$text = 'Erro ao acessar os dados do subcampo marc.';";

#****************************************************************************
#*  Translation text for class BiblioHoldQuery
#****************************************************************************
$trans["biblioHoldQueryErr1"]     = "\$text = 'Erro ao acessar aos dados de Reservas pelo id da bibliografia.';";
$trans["biblioHoldQueryErr2"]     = "\$text = 'Erro ao acessar aos dados de Reservas pelo id do membro.';";
$trans["biblioHoldQueryErr3"]     = "\$text = 'Erro ao pegar o id da biblioteca e o id do exemplar para colocar na Reserva.';";
$trans["biblioHoldQueryErr4"]     = "\$text = 'Erro ao inserir dados na Reserva.';";
$trans["biblioHoldQueryErr5"]     = "\$text = 'Erro ao apagar dados na Reserva.';";
$trans["biblioHoldQueryErr6"]     = "\$text = 'Erro ao pegar o primeiro membro da reserva para um exemplar.';";

#****************************************************************************
#*  Translation text for class ReportQuery
#****************************************************************************
$trans["reportQueryErr1"]         = "\$text = 'Relatório de erro na execução.';";

#****************************************************************************
#*  Translation text for class ReportCriteria
#****************************************************************************
$trans["reportCriteriaErr1"]      = "\$text = 'Valores não-numéricos não são válidos em colunas numéricas.';";
$trans["reportCriteriaDateTimeErr"] = "\$text = 'Formato de data/tempo inválido.';";
$trans["reportCriteriaDateErr"]   = "\$text = 'Formato de data inválido.';";

#****************************************************************************
#*  Translation text for class LabelFormat and LetterFormat
#****************************************************************************
$trans["labelFormatFontErr"]      = "\$text = 'Tipo de fonte especificado é inválido na etiqueta de definição xml. Os tipos de fontes válidos são Courier, Helvetica, e Times-Roman.';";
$trans["labelFormatFontSizeErr"]  = "\$text = 'Tamanho de fonte especificado é inválido na etiqueta de definição xml. O valor do tamanho da fonte deve ser numérica.';";
$trans["labelFormatFontSizeErr2"] = "\$text = 'Tamanho de fonte especificado é inválido na etiqueta de definição xml. O valor tamanho da fonte deve ser maior que zero.';";
$trans["labelFormatLMarginErr"]   = "\$text = 'A margem da esquerda especificada é inválida na etiqueta de definição xml. A margem deve ser numérica.';";
$trans["labelFormatLMarginErr2"]  = "\$text = 'A margem da esquerda especificada é inválida na etiqueta de definição xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatRMarginErr"]   = "\$text = A margem da direita especificada é inválida na etiqueta de definição xml. A margem deve ser numérica.';";
$trans["labelFormatRMarginErr2"]  = "\$text = 'A margem da direita especificada é inválida na etiqueta de definição xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatTMarginErr"]   = "\$text = 'A margem superior especificada é inválida na etiqueta de definição xml. A margem deve ser numérica.';";
$trans["labelFormatTMarginErr2"]  = "\$text = 'A margem superior especificada é inválida na etiqueta de definição xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatBMarginErr"]   = "\$text = 'A margem inferior especificada é inválida na etiqueta de definição xml. A margem deve ser numérica.';";
$trans["labelFormatBMarginErr2"]  = "\$text = 'A margem inferior especificada é inválida na etiqueta de definição xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatColErr"]       = "\$text = 'A coluna especificada é inválida na etiqueta de definição xml. O valor da coluna deve ser numérica.';";
$trans["labelFormatColErr2"]      = "\$text = 'A coluna especificada é inválida na etiqueta de definição xml. O valor da coluna deve ser maior que zero.';";
$trans["labelFormatWidthErr"]     = "\$text = 'A largura especificada é inválida na etiqueta de definição xml. A largura deve ser numérica.';";
$trans["labelFormatWidthErr2"]    = "\$text = 'A largura especificada é inválida na etiqueta de definição xml. O valor da largura deve ser maior que zero.';";
$trans["labelFormatHeightErr"]    = "\$text = 'A altura especificada é inválida na etiqueta de definição xml. A altura deve ser numérica.';";
$trans["labelFormatHeightErr2"]   = "\$text = 'A altura especificada é inválida na etiqueta de definição xml. O valor da altura deve ser maior que zero.';";
$trans["labelFormatNoLabelsErr"]  = "\$text = 'As linhas especificadas são inválidas na etiqueta de definição xml.';";

#****************************************************************************
#*  Translation text for class BiblioStatusHistQuery
#****************************************************************************
$trans["biblioStatusHistQueryErr1"] = "\$text = 'Erro ao pegar histórico na bibliográfia pelo bibliography id.';";
$trans["biblioStatusHistQueryErr2"] = "\$text = 'Erro ao pegar histórico na bibliográfia pelo member id';";
$trans["biblioStatusHistQueryErr3"] = "\$text = 'Erro ao inserir histórico na bibliográfia';";
$trans["biblioStatusHistQueryErr4"] = "\$text = 'Erro ao apagar histórico na bibliográfia pelo copy id';";
$trans["biblioStatusHistQueryErr5"] = "\$text = 'Erro ao apagar histórico na bibliográfia pelo member id';";

#****************************************************************************
#*  Translation text for class MemberAccountTransaction
#****************************************************************************
$trans["memberAccountTransError1"]  = "\$text = 'É necessária a quantidade.';";
$trans["memberAccountTransError2"]  = "\$text = 'A quantidade deve ser numérica.';";
$trans["memberAccountTransError3"]  = "\$text = 'É necessário a descrição.';";

#****************************************************************************
#*  Translation text for class MemberAccountQuery
#****************************************************************************
$trans["memberAccountQueryErr1"]    = "\$text = 'Erro ao acessar as informações do membro.';";
$trans["memberAccountQueryErr2"]    = "\$text = 'Erro ao inserir informações do membro.';";
$trans["memberAccountQueryErr3"]    = "\$text = 'Erro ao apagar informações do membro.';";

?>

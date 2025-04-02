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
$trans["biblioError1"]            = "\$text = 'N�mero de chamada � obrigat�rio.';";

#****************************************************************************
#*  Translation text for class BiblioField
#****************************************************************************
$trans["biblioFieldError1"]       = "\$text = 'Campo � obrigat�rio.';";
$trans["biblioFieldError2"]       = "\$text = 'Tag deve ser num�rica.';";
$trans["memberFieldError"]       = "\$text = 'hhhhh.';";

	

#****************************************************************************
#*  Translation text for class BiblioQuery
#****************************************************************************
$trans["biblioQueryQueryErr1"]    = "\$text = 'Erro ao acessar informa��o da bibliografia.';";
$trans["biblioQueryQueryErr2"]    = "\$text = 'Erro ao acessar informa��o do campo da bibliografia.';";
$trans["biblioQueryInsertErr1"]   = "\$text = 'Erro ao inserir informa��o da bibliografia.';";
$trans["biblioQueryInsertErr2"]   = "\$text = 'Erro ao inserir informa��o do campo da bibliografia.';";
$trans["biblioQueryUpdateErr1"]   = "\$text = 'Erro ao atualizar informa��o da bibliografia.';";
$trans["biblioQueryUpdateErr2"]   = "\$text = 'Erro ao limpar infomrma��o de campo para atualizar.';";
$trans["biblioQueryDeleteErr"]    = "\$text = 'Erro ao apagar informa��o da bibliografia.';";

#****************************************************************************
#*  Translation text for class BiblioSearchQuery
#****************************************************************************
$trans["biblioSearchQueryErr1"]   = "\$text = 'Erro ao enumerar o resultado de busca por bibliografia.';";
$trans["biblioSearchQueryErr2"]   = "\$text = 'Erro ao procurar informa��es da biliografia.';";
$trans["biblioSearchQueryErr3"]   = "\$text = 'Erro ao ler informa��es da bibliografia.';";

#****************************************************************************
#*  Translation text for class BiblioCopy
#****************************************************************************
$trans["biblioCopyError1"]        = "\$text = 'C�digo de Barras � necess�rio.';";
$trans["biblioCopyError2"]        = "\$text = 'C�digo de Barras deve ser alfanum�rico.';";

#****************************************************************************
#*  Translation text for class BiblioCopyQuery
#****************************************************************************
$trans["biblioCopyQueryErr1"]     = "\$text = 'Erro ao procurar por C�digo de Barras.';";
$trans["biblioCopyQueryErr2"]     = "\$text = 'O C�digo de Barras %barcodeNmbr% j� est� em uso.';";
$trans["biblioCopyQueryErr3"]     = "\$text = 'Erro ao inserir informa��o do exemplar bibliogr�fico.';";
$trans["biblioCopyQueryErr4"]     = "\$text = 'Erro ao acessar informa��o do exemplar bibliogr�fico.';";
$trans["biblioCopyQueryErr5"]     = "\$text = 'Erro ao atualizar informa��o do exemplar bibliogr�fico.';";
$trans["biblioCopyQueryErr6"]     = "\$text = 'Erro ao apagar informa��o do exemplar bibliogr�fico.';";
$trans["biblioCopyQueryErr7"]     = "\$text = 'Erro ao acessar informa��o do exemplar bibliogr�fico para obter o C�digo de Barras.';";
$trans["biblioCopyQueryErr8"]     = "\$text = 'Erro ao acessar informa��o do G�nero para checar dia da devolu��o.';";
$trans["biblioCopyQueryErr9"]     = "\$text = 'Erro ao checar exemplares devolvidos';";
$trans["biblioCopyQueryErr10"]    = "\$text = 'Erro ao checar limites de empr�stimo';";
$trans["biblioCopyQueryErr11"]    = "\$text = 'Erro ao buscar copyid mais elevado.';";

#****************************************************************************
#*  Translation text for class BiblioFieldQuery
#****************************************************************************
$trans["biblioFieldQueryErr1"]    = "\$text = 'Erro de leitura no campo bibliogr�fico.';";
$trans["biblioFieldQueryErr2"]    = "\$text = 'Erro de leitura no campo bibliogr�fico.';";
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
$trans["reportQueryErr1"]         = "\$text = 'Relat�rio de erro na execu��o.';";

#****************************************************************************
#*  Translation text for class ReportCriteria
#****************************************************************************
$trans["reportCriteriaErr1"]      = "\$text = 'Valores n�o-num�ricos n�o s�o v�lidos em colunas num�ricas.';";
$trans["reportCriteriaDateTimeErr"] = "\$text = 'Formato de data/tempo inv�lido.';";
$trans["reportCriteriaDateErr"]   = "\$text = 'Formato de data inv�lido.';";

#****************************************************************************
#*  Translation text for class LabelFormat and LetterFormat
#****************************************************************************
$trans["labelFormatFontErr"]      = "\$text = 'Tipo de fonte especificado � inv�lido na etiqueta de defini��o xml. Os tipos de fontes v�lidos s�o Courier, Helvetica, e Times-Roman.';";
$trans["labelFormatFontSizeErr"]  = "\$text = 'Tamanho de fonte especificado � inv�lido na etiqueta de defini��o xml. O valor do tamanho da fonte deve ser num�rica.';";
$trans["labelFormatFontSizeErr2"] = "\$text = 'Tamanho de fonte especificado � inv�lido na etiqueta de defini��o xml. O valor tamanho da fonte deve ser maior que zero.';";
$trans["labelFormatLMarginErr"]   = "\$text = 'A margem da esquerda especificada � inv�lida na etiqueta de defini��o xml. A margem deve ser num�rica.';";
$trans["labelFormatLMarginErr2"]  = "\$text = 'A margem da esquerda especificada � inv�lida na etiqueta de defini��o xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatRMarginErr"]   = "\$text = A margem da direita especificada � inv�lida na etiqueta de defini��o xml. A margem deve ser num�rica.';";
$trans["labelFormatRMarginErr2"]  = "\$text = 'A margem da direita especificada � inv�lida na etiqueta de defini��o xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatTMarginErr"]   = "\$text = 'A margem superior especificada � inv�lida na etiqueta de defini��o xml. A margem deve ser num�rica.';";
$trans["labelFormatTMarginErr2"]  = "\$text = 'A margem superior especificada � inv�lida na etiqueta de defini��o xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatBMarginErr"]   = "\$text = 'A margem inferior especificada � inv�lida na etiqueta de defini��o xml. A margem deve ser num�rica.';";
$trans["labelFormatBMarginErr2"]  = "\$text = 'A margem inferior especificada � inv�lida na etiqueta de defini��o xml. O valor da margem deve ser maior que zero.';";
$trans["labelFormatColErr"]       = "\$text = 'A coluna especificada � inv�lida na etiqueta de defini��o xml. O valor da coluna deve ser num�rica.';";
$trans["labelFormatColErr2"]      = "\$text = 'A coluna especificada � inv�lida na etiqueta de defini��o xml. O valor da coluna deve ser maior que zero.';";
$trans["labelFormatWidthErr"]     = "\$text = 'A largura especificada � inv�lida na etiqueta de defini��o xml. A largura deve ser num�rica.';";
$trans["labelFormatWidthErr2"]    = "\$text = 'A largura especificada � inv�lida na etiqueta de defini��o xml. O valor da largura deve ser maior que zero.';";
$trans["labelFormatHeightErr"]    = "\$text = 'A altura especificada � inv�lida na etiqueta de defini��o xml. A altura deve ser num�rica.';";
$trans["labelFormatHeightErr2"]   = "\$text = 'A altura especificada � inv�lida na etiqueta de defini��o xml. O valor da altura deve ser maior que zero.';";
$trans["labelFormatNoLabelsErr"]  = "\$text = 'As linhas especificadas s�o inv�lidas na etiqueta de defini��o xml.';";

#****************************************************************************
#*  Translation text for class BiblioStatusHistQuery
#****************************************************************************
$trans["biblioStatusHistQueryErr1"] = "\$text = 'Erro ao pegar hist�rico na bibliogr�fia pelo bibliography id.';";
$trans["biblioStatusHistQueryErr2"] = "\$text = 'Erro ao pegar hist�rico na bibliogr�fia pelo member id';";
$trans["biblioStatusHistQueryErr3"] = "\$text = 'Erro ao inserir hist�rico na bibliogr�fia';";
$trans["biblioStatusHistQueryErr4"] = "\$text = 'Erro ao apagar hist�rico na bibliogr�fia pelo copy id';";
$trans["biblioStatusHistQueryErr5"] = "\$text = 'Erro ao apagar hist�rico na bibliogr�fia pelo member id';";

#****************************************************************************
#*  Translation text for class MemberAccountTransaction
#****************************************************************************
$trans["memberAccountTransError1"]  = "\$text = '� necess�ria a quantidade.';";
$trans["memberAccountTransError2"]  = "\$text = 'A quantidade deve ser num�rica.';";
$trans["memberAccountTransError3"]  = "\$text = '� necess�rio a descri��o.';";

#****************************************************************************
#*  Translation text for class MemberAccountQuery
#****************************************************************************
$trans["memberAccountQueryErr1"]    = "\$text = 'Erro ao acessar as informa��es do membro.';";
$trans["memberAccountQueryErr2"]    = "\$text = 'Erro ao inserir informa��es do membro.';";
$trans["memberAccountQueryErr3"]    = "\$text = 'Erro ao apagar informa��es do membro.';";

?>

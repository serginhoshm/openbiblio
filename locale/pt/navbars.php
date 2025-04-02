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
#*  Translation text shared by various php files under the navbars dir
#****************************************************************************
$trans["login"]                    = "\$text = 'Login';";
$trans["logout"]                   = "\$text = 'Logout';";
$trans["help"]                     = "\$text = '- Ajuda';";

#****************************************************************************
#*  Translation text for page home.php
#****************************************************************************
$trans["homeHomeLink"]             = "\$text = '- Principal';";
$trans["homeLicenseLink"]          = "\$text = '- Licen�a';";

#****************************************************************************
#*  Translation text for page admin.php
#****************************************************************************
$trans["adminSummary"]             = "\$text = '- Sum�rio do Administrador';";
$trans["adminStaff"]               = "\$text = '- Administra��o de Volunt�rios';";
$trans["adminSettings"]            = "\$text = '- Op��es da Biblioteca';";
$trans["adminMaterialTypes"]       = "\$text = '- Tipos de Materiais';";
$trans["adminCollections"]         = "\$text = '- G�neros';";
$trans["adminThemes"]              = "\$text = '- Temas e Cores';";
$trans["adminTranslation"]         = "\$text = '- Tradu��o';";

//adicionados
$trans["Member Types"]			   = "\$text = '- Tipos de Membro';";
$trans["Member Fields"]			   = "\$text = '- Campos do Membro';";
$trans["Member Fields "]			   = "\$text = '- Campos do Membro';";
$trans["Checkout Privs"]		   = "\$text = '- Administrar Empr�stimos';";

#****************************************************************************
#*  Translation text for page cataloging.php
#****************************************************************************
$trans["catalogSummary"]           = "\$text = '- Sum�rio de Cat�logos';";
$trans["catalogSearch1"]           = "\$text = '- Procurar Biblio';";
$trans["catalogSearch2"]           = "\$text = '- Procurar Bibliogr�fia';";
$trans["catalogResults"]           = "\$text = '- Resultados da Busca';";
$trans["catalogBibInfo"]           = "\$text = '- Biblio Info';";
$trans["catalogBibEdit"]           = "\$text = '- Editar Basic';";
$trans["catalogBibEditMarc"]       = "\$text = '- Editar MARC';";
$trans["catalogBibMarcNewFld"]     = "\$text = '- Novo campo MARC';";
$trans["catalogBibMarcNewFldShrt"] = "\$text = '- Novo MARC';";
$trans["catalogBibMarcEditFld"]    = "\$text = '- Edita campo MARC';";
$trans["catalogCopyNew"]           = "\$text = '- Novo Exemplar';";
$trans["catalogCopyEdit"]          = "\$text = '- Editar Exemplar';";
$trans["catalogHolds"]             = "\$text = '- Reservas';";
$trans["catalogDelete"]            = "\$text = '- Apagar';";
$trans["catalogBibNewLike"]        = "\$text = '- Nova Bibliografia por compara��o';";
$trans["catalogBibNew"]            = "\$text = '- Nova Bibliografia';";
$trans["Upload Marc Data"]         = "\$text = '- Importar Dados do Marc';";

#****************************************************************************
#*  Translation text for page reports.php
#****************************************************************************
$trans["reportsSummary"]           = "\$text = '- Sum�rio do Relat�rio';";
$trans["reportsReportListLink"]    = "\$text = '- Lista de Relat�rios';";
$trans["reportsLabelsLink"]        = "\$text = '- Imprimir Etiquetas';";
$trans["reportsLettersLink"]       = "\$text = '- Imprimir Cartas';";

#****************************************************************************
#*  Translation text for page opac.php
#****************************************************************************
$trans["catalogSearch1"]           = "\$text = '- Procurar Bibliografias';";
$trans["catalogSearch2"]           = "\$text = '- Procurar Bibliografias';";
$trans["catalogResults"]           = "\$text = '- Resultados da Procura';";
$trans["catalogBibInfo"]           = "\$text = '- Informa��es da Bibliografias';";

#Added

$trans["memberInfo"]="\$text = '- Informa��es do Membro';";
$trans["memberSearch"]="\$text = '- Procurar Membros';";
$trans["editInfo"]="\$text = '- Editar Informa��o';";
$trans["checkoutHistory"]= "\$text = '- Hist�rico de retiradas';";
$trans["account"]="\$text = '- Conta';";
$trans["checkIn"]="\$text = '- Devolu��o';";
$trans["memberSearch"]= "\$text = '- Procurar Membros';";
$trans["newMember"]= "\$text = '- Novo Membro';";
//$trans["account"]        	= "\$text = 'Conta';";
?>

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
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHeading"]       = "\$text='Bem Vindo ao Sistema CT-ZL - OpenBiblio';";
$trans["indexIntro"]         = "\$text=
  'Use o menu acima para acessar as op��es
  administrativas.';";
$trans["indexTab"]           = "\$text='Item';";
$trans["indexDesc"]          = "\$text='Descri��o';";
$trans["indexCirc"]          = "\$text='Circula��o';";
$trans["indexCircDesc1"]     = "\$text='Nesta �rea � poss�vel administrar grava��es.';";
$trans["indexCircDesc2"]     = "\$text='Administra��o de Membros(novo, procurar, editar, apagar)';";
$trans["indexCircDesc3"]     = "\$text='Empr�stimos para Membros e tamb�m reservas, conta e hist�rico';";
$trans["indexCircDesc4"]     = "\$text='Bibliografia devolvida e lista de espera';";
//$trans["indexCircDesc5"]     = "\$text='Pagamento de multa por atraso ao Membro';";
$trans["indexCat"]           = "\$text='Cataloga��o';";
$trans["indexCatDesc1"]      = "\$text='Nesta �rea � poss�vel administrar sua bibliografia.';";
$trans["indexCatDesc2"]      = "\$text='Administrar bibliografia (novo, procurar, editar, apagar)';";
//$trans["indexCatDesc3"]      = "\$text='Importar bibliografia do banco USMarc';";
$trans["indexAdmin"]         = "\$text='Administra��o';";
$trans["indexAdminDesc1"]    = "\$text='Utilize esta �rea para controle de volunt�rios e op��es administrativas.';";
$trans["indexAdminDesc2"]    = "\$text='Administra��o de volunt�rios (novo, editar, senha, apagar)';";
$trans["indexAdminDesc3"]    = "\$text='Op��es gerais da Biblioteca';";
$trans["indexAdminDesc5"]    = "\$text='Tipos de Material da Biblioteca';";
$trans["indexAdminDesc4"]    = "\$text='G�neros da Biblioteca';";
$trans["indexAdminDesc6"]    = "\$text='Temas e Cores da Biblioteca';";
$trans["indexReports"]       = "\$text='Relat�rios';";
$trans["indexReportsDesc1"]  = "\$text='Use esta �rea para gerar relat�rios e etiquetas';";
$trans["indexReportsDesc2"]  = "\$text='Relat�rio.';";
$trans["indexReportsDesc3"]  = "\$text='Etiquetas.';";

?>

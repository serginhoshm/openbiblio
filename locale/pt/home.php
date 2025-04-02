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
  'Use o menu acima para acessar as opções
  administrativas.';";
$trans["indexTab"]           = "\$text='Item';";
$trans["indexDesc"]          = "\$text='Descrição';";
$trans["indexCirc"]          = "\$text='Circulação';";
$trans["indexCircDesc1"]     = "\$text='Nesta área é possível administrar gravações.';";
$trans["indexCircDesc2"]     = "\$text='Administração de Membros(novo, procurar, editar, apagar)';";
$trans["indexCircDesc3"]     = "\$text='Empréstimos para Membros e também reservas, conta e histórico';";
$trans["indexCircDesc4"]     = "\$text='Bibliografia devolvida e lista de espera';";
//$trans["indexCircDesc5"]     = "\$text='Pagamento de multa por atraso ao Membro';";
$trans["indexCat"]           = "\$text='Catalogação';";
$trans["indexCatDesc1"]      = "\$text='Nesta área é possível administrar sua bibliografia.';";
$trans["indexCatDesc2"]      = "\$text='Administrar bibliografia (novo, procurar, editar, apagar)';";
//$trans["indexCatDesc3"]      = "\$text='Importar bibliografia do banco USMarc';";
$trans["indexAdmin"]         = "\$text='Administração';";
$trans["indexAdminDesc1"]    = "\$text='Utilize esta área para controle de voluntários e opções administrativas.';";
$trans["indexAdminDesc2"]    = "\$text='Administração de voluntários (novo, editar, senha, apagar)';";
$trans["indexAdminDesc3"]    = "\$text='Opções gerais da Biblioteca';";
$trans["indexAdminDesc5"]    = "\$text='Tipos de Material da Biblioteca';";
$trans["indexAdminDesc4"]    = "\$text='Gêneros da Biblioteca';";
$trans["indexAdminDesc6"]    = "\$text='Temas e Cores da Biblioteca';";
$trans["indexReports"]       = "\$text='Relatórios';";
$trans["indexReportsDesc1"]  = "\$text='Use esta área para gerar relatórios e etiquetas';";
$trans["indexReportsDesc2"]  = "\$text='Relatório.';";
$trans["indexReportsDesc3"]  = "\$text='Etiquetas.';";

?>

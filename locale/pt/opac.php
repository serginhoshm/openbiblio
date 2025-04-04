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
$trans["opac_Header"]        = "\$text='Online Public Access Catalog (OPAC)';";
$trans["opac_WelcomeMsg"]    = "\$text=
  'Bem-vindo ao cat�logo de acesso p�blico de nossa biblioteca online. Procure em nossos cat�logos a informa��o da bibliografia desejada de nossa biblioteca.';";
$trans["opac_SearchTitle"]   = "\$text='Procurar bibliografia pela frase:';";
$trans["opac_Title"]         = "\$text='T�tulo';";
$trans["opac_Author"]        = "\$text='Autor';";
$trans["opac_Subject"]       = "\$text='Assunto';";
$trans["opac_Search"]        = "\$text='Procurar';";
$trans["biblioSearchOutIn"]        = "\$text='emprestar / devolver';";

 

?>

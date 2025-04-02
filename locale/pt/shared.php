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
#*  Common translation text shared among multiple pages
#****************************************************************************
$trans["sharedCancel"]             = "\$text = 'Cancelar';";
$trans["sharedDelete"]             = "\$text = 'Apagar';";

#****************************************************************************
#*  Translation text for page biblio_view.php
#****************************************************************************
$trans["biblioViewTble1Hdr"]       = "\$text = 'Informa��o da Bibliografia';";
$trans["biblioViewMaterialType"]   = "\$text = 'Tipo de Material';";
$trans["biblioViewCollection"]     = "\$text = 'G�nero';";
$trans["biblioViewCallNmbr"]       = "\$text = 'N�mero de Chamada';";
$trans["biblioViewTble2Hdr"]       = "\$text = 'Informa��o sobre Exemplares';";
$trans["biblioViewTble2Col1"]      = "\$text = 'C�digo de Barras n�';";
$trans["biblioViewTble2Col2"]      = "\$text = 'Descri��o';";
$trans["biblioViewTble2Col3"]      = "\$text = 'Status';";
$trans["biblioViewTble2Col4"]      = "\$text = 'Status Dt';";
$trans["biblioViewTble2Col5"]      = "\$text = 'Devolu��o';";
$trans["biblioViewTble2ColFunc"]   = "\$text = 'Fun��o';";
$trans["biblioViewTble2Coldel"]    = "\$text = 'apagar';";
$trans["biblioViewTble2Coledit"]   = "\$text = 'editar';";
$trans["biblioViewTble3Hdr"]       = "\$text = 'Informa��o Bibliogr�fica Adicional';";
$trans["biblioViewNoAddInfo"]      = "\$text = 'Sem informa��o bibliogr�fica adicional.';";
$trans["biblioViewNoCopies"]       = "\$text = 'Nenhum exemplar foi criado.';";
$trans["biblioViewOpacFlg"]        = "\$text = 'Mostrar no OPAC';";
$trans["biblioViewNewCopy"]        = "\$text = 'Adicionar novo exemplar';";
$trans["biblioViewNeweCopy"]       = "\$text = 'Adicionar novo exemplar eletr�nico';";
$trans["biblioViewYes"]            = "\$text = 'sim';";
$trans["biblioViewNo"]             = "\$text = 'n�o';";

#****************************************************************************
#*  Translation text for page biblio_search.php
#****************************************************************************
$trans["biblioSearchNoResults"]    = "\$text = 'Nenhum resultado encontrado.';";
$trans["biblioSearchResults"]      = "\$text = 'Resultado da Pesquisa';";
$trans["biblioSearchResultPages"]  = "\$text = 'P�ginas de Resultado';";
$trans["biblioSearchPrev"]         = "\$text = ' anterior';";
$trans["biblioSearchNext"]         = "\$text = ' pr�ximo';";
$trans["biblioSearchResultTxt"]    = "if (%items% == 1) {
                                        \$text = '%items% entradas encontradas.';
                                      } else {
                                        \$text = '%items% entradas encontradas.';
                                      }";
$trans["biblioSearchauthor"]       = "\$text = ' Ordenado por autor ';";
$trans["biblioSearchtitle"]        = "\$text = ' Ordenado por t�tulo ';";
$trans["biblioSearchSortByAuthor"] = "\$text = ' Ordenar por autor';";
$trans["biblioSearchbarcode_nmbr"] = "\$text = ' Ordenado por c�digo de barras ';";
$trans["biblioSearchSortByTitle"]  = "\$text = ' Ordenar por t�tulo';";
$trans["biblioSearchSortByBrCode"] = "\$text = ' Ordenar por c�digo de barras';";
$trans["biblioSearchTitle"]        = "\$text = 'T�tulo';";
$trans["biblioSearchAuthor"]       = "\$text = 'Autor';";
$trans["biblioSearchMaterial"]     = "\$text = 'Material';";
$trans["biblioSearchCollection"]   = "\$text = 'G�nero';";
$trans["biblioSearchCall"]         = "\$text = 'N�mero de Chamada';";
$trans["biblioSearchCopyBCode"]    = "\$text = 'C�digo de Barras';";
$trans["biblioSearchCopyStatus"]   = "\$text = 'Status';";
$trans["biblioSearchNoCopies"]     = "\$text = 'Nenhum exemplar dispon�vel.';";
$trans["biblioSearchHold"]         = "\$text = 'reservar';";
$trans["biblioSearchOutIn"]        = "\$text = 'emprestar/devolver';";
$trans["biblioSearchDetail"]       = "\$text = 'Mostrar informa��es detalhadas da bibliografia';";
$trans["biblioSearchBCode2Chk"]    = "\$text = 'C�digo de barras para retirada ou inser��o Form';";
$trans["biblioSearchBCode2Hold"]   = "\$text = 'C�digo de barras para reserva';";

#****************************************************************************
#*  Translation text for page loginform.php
#****************************************************************************
$trans["loginFormTbleHdr"]         = "\$text = 'Login de Volunt�rio';";
$trans["loginFormUsername"]        = "\$text = 'Usu�rio';";
$trans["loginFormPassword"]        = "\$text = 'Senha';";
$trans["loginFormLogin"]           = "\$text = 'Login';";

#****************************************************************************
#*  Translation text for page hold_del_confirm.php
#****************************************************************************
$trans["holdDelConfirmMsg"]        = "\$text = 'Voc� tem certeza que deseja apagar esta reserva?';";

#****************************************************************************
#*  Translation text for page hold_del.php
#****************************************************************************
$trans["holdDelSuccess"]           = "\$text='Reserva apagada com sucesso.';";

#****************************************************************************
#*  Translation text for page help_header.php
#****************************************************************************
$trans["helpHeaderTitle"]          = "\$text='Ajuda do OpenBiblio';";
$trans["helpHeaderCloseWin"]       = "\$text='Fechar Janela';";
$trans["helpHeaderContents"]       = "\$text='Conte�do';";
$trans["helpHeaderPrint"]          = "\$text='Imprimir';";

$trans["catalogResults"]           = "\$text='Resolutado da Pesquisa';";

#****************************************************************************
#*  Translation text for page header.php and header_opac.php
#****************************************************************************
$trans["headerTodaysDate"]         = "\$text='Data:';";
$trans["headerDateFormat"]         = "\$text='d.m.A';";
$trans["headerLibraryHours"]       = "\$text='Hor�rio:';";
$trans["headerLibraryPhone"]       = "\$text='Telefone:';";
$trans["headerHome"]               = "\$text='Principal';";
$trans["headerCirculation"]        = "\$text='Circula��o';";
$trans["headerCataloging"]         = "\$text='Catalogando';";
$trans["headerAdmin"]              = "\$text='Administra��o';";
$trans["headerReports"]            = "\$text='Relat�rios';";

#****************************************************************************
#*  Translation text for page footer.php
#****************************************************************************
$trans["footerLibraryHome"]        = "\$text='P�gina Principal';";
$trans["footerOPAC"]               = "\$text='OPAC';";
$trans["footerHelp"]               = "\$text='Ajuda';";
$trans["footerPoweredBy"]          = "\$text='Powered by OpenBiblio version';";
$trans["footerDatabaseVersion"]    = "\$text='database version';";
$trans["footerCopyright"]          = "\$text='Copyright';";
$trans["footerUnderThe"]           = "\$text='under the';";
$trans["footerGPL"]                = "\$text='GNU General Public License';";

?>

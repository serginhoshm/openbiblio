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
$trans["circCancel"]              = "\$text = 'Cancelar';";
$trans["circDelete"]              = "\$text = 'Apagar';";
$trans["circLogout"]              = "\$text = 'Sair';";
$trans["circAdd"]                 = "\$text = 'Adicionar';";
$trans["mbrDupBarcode"]           = "\$text = 'O c�digo de barras, %barcode%, est� em uso.';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHeading"]            = "\$text='Circula��o';";
$trans["indexCardHdr"]            = "\$text='Procurar por n�mero de inscri��o:';";
$trans["indexCard"]               = "\$text='Inscri��o:';";
$trans["indexSearch"]             = "\$text='Procurar';";
$trans["indexNameHdr"]            = "\$text='Procurar membro por sobrenome:';";
$trans["indexName"]               = "\$text='Sobrenome come�a com:';";

#****************************************************************************
#*  Translation text for page mbr_new_form.php, mbr_edit_form.php and mbr_fields.php
#****************************************************************************
$trans["Mailing Address:"] = "\$text='Endere�o:';";
$trans["mbrNewForm"]              = "\$text='Adicionar Novo';";
$trans["mbrEditForm"]             = "\$text='Editar';";
$trans["mbrFldsHeader"]           = "\$text='Membro:';";
$trans["mbrFldsCardNmbr"]         = "\$text='N�mero do Cart�o:';";
$trans["mbrFldsLastName"]         = "\$text='Sobrenome';";
$trans["mbrFldsFirstName"]        = "\$text='Nome:';";
$trans["mbrFldsAddr1"]            = "\$text='Endere�o:';";
$trans["mbrFldsAddr2"]            = "\$text='Bairro:';";
$trans["mbrFldsCity"]             = "\$text='Cidade:';";
$trans["mbrFldsStateZip"]         = "\$text='Estado, CEP:';";
$trans["mbrFldsHomePhone"]        = "\$text='Tel. Residencial:';";
$trans["mbrFldsWorkPhone"]        = "\$text='Tel. Comercial';";
$trans["mbrFldsEmail"]            = "\$text='Email:';";
$trans["mbrFldsClassify"]         = "\$text='Classe:';";
$trans["mbrFldsGrade"]            = "\$text='Grau:';";
$trans["mbrFldsTeacher"]          = "\$text='Escola:';";
$trans["mbrFldsSubmit"]           = "\$text='Enviar';";
$trans["mbrFldsCancel"]           = "\$text='Cancelar';";
$trans["mbrsearchResult"]         = "\$text='Resultados: ';";
$trans["mbrsearchprev"]           = "\$text='anterior';";
$trans["mbrsearchnext"]           = "\$text='pr�ximo';";
$trans["mbrsearchNoResults"]      = "\$text='Nenhum resultado foi encontrado.';";
$trans["mbrsearchFoundResults"]   = "\$text=' Resultados.';";
$trans["mbrsearchSearchResults"]  = "\$text='Procurar:';";
$trans["mbrsearchCardNumber"]     = "\$text='N�mero do Cart�o:';";
$trans["mbrsearchClassification"] = "\$text='Classifica��o:';";

#****************************************************************************
#*  Translation text for page mbr_new.php
#****************************************************************************
$trans["mbrNewSuccess"]           = "\$text='Membro adicionado com sucesso.';";

#****************************************************************************
#*  Translation text for page mbr_edit.php
#****************************************************************************
$trans["mbrEditSuccess"]          = "\$text='Membro atualizado com sucesso.';";

#****************************************************************************
#*  Translation text for page mbr_view.php
#****************************************************************************
$trans["mbrViewHead1"]            = "\$text='Informa��es do membro:';";
$trans["mbrViewName"]             = "\$text='Nome:';";
$trans["mbrViewAddr"]             = "\$text='Endere�o';";
$trans["mbrViewCardNmbr"]         = "\$text='N�mero do cart�o';";
$trans["mbrViewClassify"]         = "\$text='Classe:';";
$trans["mbrViewPhone"]            = "\$text='Telefone:';";
$trans["mbrViewPhoneHome"]        = "\$text='Res:';";
$trans["mbrViewPhoneWork"]        = "\$text='Com:';";
$trans["mbrViewEmail"]            = "\$text='Email:';";
$trans["mbrViewGrade"]            = "\$text='Forma��o:';";
$trans["mbrViewTeacher"]          = "\$text='Escola:';";
$trans["mbrViewHead2"]            = "\$text='Estat�sticas:';";
$trans["mbrViewStatColHdr1"]      = "\$text='Material';";
$trans["mbrViewStatColHdr2"]      = "\$text='Contador';";
$trans["mbrViewStatColHdr3"]      = "\$text='Empr�stimo m�x.';";
$trans["mbrViewStatColHdr4"]      = "\$text='Empr�stimo';";
$trans["mbrViewStatColHdr5"]      = "\$text='Renovar';";
$trans["mbrViewHead3"]            = "\$text='Empr�stimo de bibliografia:';";
$trans["mbrViewBarcode"]          = "\$text='N�mero do C�digo de Barras:';";
$trans["mbrViewCheckOut"]         = "\$text='Efetuar Empr�stimo';";
$trans["mbrViewHead4"]            = "\$text='Bibliografias em empr�stimos:';";
$trans["mbrViewOutHdr1"]          = "\$text='Retirado';";
$trans["mbrViewOutHdr2"]          = "\$text='Material';";
$trans["mbrViewOutHdr3"]          = "\$text='C�digo de Barras';";
$trans["mbrViewOutHdr4"]          = "\$text='T�tulo';";
$trans["mbrViewOutHdr5"]          = "\$text='Autor';";
$trans["mbrViewOutHdr6"]          = "\$text='Devolu��o';";
$trans["mbrViewOutHdr7"]          = "\$text='Atraso';";
$trans["mbrViewOutHdr8"]          = "\$text='Renovar';";
$trans["mbrViewOutHdr9"]          = "\$text='vez/es';";
$trans["mbrViewNoCheckouts"]      = "\$text='Nenhuma bibliografia em empr�stimo.';";
$trans["mbrViewHead5"]            = "\$text='Colocar em reserva:';";
$trans["mbrViewHead6"]            = "\$text='Bibliografias na reserva:';";
$trans["mbrViewPlaceHold"]        = "\$text='Reservar';";
$trans["mbrViewHoldHdr1"]         = "\$text='Fun��o';";
$trans["mbrViewHoldHdr2"]         = "\$text='Reservado';";
$trans["mbrViewHoldHdr3"]         = "\$text='Material';";
$trans["mbrViewHoldHdr4"]         = "\$text='C�digo de barras';";
$trans["mbrViewHoldHdr5"]         = "\$text='T�tulo';";
$trans["mbrViewHoldHdr6"]         = "\$text='Autor';";
$trans["mbrViewHoldHdr7"]         = "\$text='Status';";
$trans["mbrViewHoldHdr8"]         = "\$text='Devolu��o';";
$trans["mbrViewNoHolds"]          = "\$text='Nenhuma bibliografia reservada.';";
$trans["mbrViewBalMsg"]           = "\$text='Nota: Membro com balan�o em alarme %bal%.';";
$trans["mbrPrintCheckouts"]	  = "\$text='Imprimir empr�stimos';";
$trans["mbrViewDel"]              = "\$text='apagar';";

#****************************************************************************
#*  Translation text for page checkout.php
#****************************************************************************
$trans["checkoutBalErr"]          = "\$text='Membro deve pagar o balan�o antes de fazer empr�stimo.';";
$trans["checkoutErr1"]            = "\$text='C�digos de barras devem ser alfanum�ricos.';";
$trans["checkoutErr2"]            = "\$text='Nenhuma bibliografia com o c�digo de barras informado.';";
$trans["checkoutErr3"]            = "\$text='Bibliografia com o c�digo de barras %barcode% est� no empr�stimo.';";
$trans["checkoutErr4"]            = "\$text='Bibliografia com o c�digo de barras %barcode% n�o est� dispon�vel para empr�stimo.';";
$trans["checkoutErr5"]            = "\$text='Bibliografia com o c�digo de barras %barcode% reservado por outro membro.';";
$trans["checkoutErr6"]            = "\$text='Membro excedeu o m�ximo de empr�stimos para o material indicado.';";
$trans["checkoutErr7"]            = "\$text='Bibliografia com o c�digo de barras %barcode% alcan�ou o limite de renova��es pelo membro.';";
$trans["checkoutErr8"]            = "\$text='Bibliografia com o c�digo de barras %barcode% n�o pode ser renovado.';";

#****************************************************************************
#*  Translation text for page shelving_cart.php
#****************************************************************************
$trans["shelvingCartErr1"]        = "\$text='C�digos de barras devem ser alfanum�ricos.';";
$trans["shelvingCartErr2"]        = "\$text='Nenhuma bibliografia encontrada com este c�digo de barras.';";
$trans["shelvingCartTrans"]       = "\$text='Multa por atraso (C�digo de Barras =%barcode%)';";

#****************************************************************************
#*  Translation text for page checkin_form.php
#****************************************************************************
$trans["checkinFormHdr1"]         = "\$text='Devolu��o de bibliografia:';";
$trans["checkinFormBarcode"]      = "\$text='N�mero do C�digo de Barras:';";
$trans["checkinFormShelveButton"] = "\$text='Devolver';";
$trans["checkinFormCheckinLink1"] = "\$text='Devolu��o dos itens selecionados';";
$trans["checkinFormCheckinLink2"] = "\$text='Devolu��o para todos os itens';";
$trans["checkinFormHdr2"]         = "\$text='Lista de espera:';";
$trans["checkinFormColHdr1"]      = "\$text='Data';";
$trans["checkinFormColHdr2"]      = "\$text='C�digo de Barra';";
$trans["checkinFormColHdr3"]      = "\$text='T�tulo';";
$trans["checkinFormColHdr4"]      = "\$text='Autor';";
$trans["checkinFormEmptyCart"]    = "\$text='Nenhuma bibliografia na espera.';";

#****************************************************************************
#*  Translation text for page checkin.php
#****************************************************************************
$trans["checkinErr1"]             = "\$text='Nenhum item foi selecionado.';";

#****************************************************************************
#*  Translation text for page hold_message.php
#****************************************************************************
$trans["holdMessageHdr"]          = "\$text='Bibliografia colocada na espera!';";
$trans["holdMessageMsg1"]         = "\$text='A bibliografia com o c�digo de barras %barcode% que voc� est� tentando marcar tem uma ou mais reservas.  <b>Arquive por favor esta bibliografia na reserva, em vez de coloc�-la na espera. </b>  O status da sua bibliografia foi enviada para reserva.';";
$trans["holdMessageMsg2"]         = "\$text='Retornar a Devolu��o de bibliografia.';";

#****************************************************************************
#*  Translation text for page place_hold.php
#****************************************************************************
$trans["placeHoldErr1"]           = "\$text='O c�digo de barras deve ser num�rico';";
$trans["placeHoldErr2"]           = "\$text='O c�digo de barras n�o existe.';";
$trans["placeHoldErr3"]           = "\$text='O membro j� retirou esta bibliografia -- n�o foi colocada na espera';";

#****************************************************************************
#*  Translation text for page mbr_del_confirm.php
#****************************************************************************
$trans["mbrDelConfirmWarn"]       = "\$text = 'O membro, %name%, fez %checkoutCount% empr�stimo(s) e reservou %holdCount% itens.  Todos materiais emprestados devem ser colocados como devolvido e todas requisi��es devem ser apagadas antes de excluir este membro.';";
$trans["mbrDelConfirmReturn"]     = "\$text = 'retornar �s informa��es do membro';";
$trans["mbrDelConfirmMsg"]        = "\$text = 'Voc� tem certeza que deseja apagar o membro %name%?  Isso tamb�m ir� apagar todo o hist�rico de empr�stimos deste membro.';";

#****************************************************************************
#*  Translation text for page mbr_del.php
#****************************************************************************
$trans["mbrDelSuccess"]           = "\$text='Membro %name%, foi apagado.';";
$trans["mbrDelReturn"]            = "\$text='retornar � procura de membros';";

#****************************************************************************
#*  Translation text for page mbr_history.php
#****************************************************************************
$trans["mbrHistoryHead1"]         = "\$text='Hist�rico empr�stimos:';";
$trans["mbrHistoryNoHist"]        = "\$text='Hist�rico n�o encontrado.';";
$trans["mbrHistoryHdr1"]          = "\$text='C�digo de Barras';";
$trans["mbrHistoryHdr2"]          = "\$text='T�tulo';";
$trans["mbrHistoryHdr3"]          = "\$text='Autor';";
$trans["mbrHistoryHdr4"]          = "\$text='Novo Status';";
$trans["mbrHistoryHdr5"]          = "\$text='Data de mudan�a de status';";
$trans["mbrHistoryHdr6"]          = "\$text='Data de devolu��o';";

#****************************************************************************
#*  Translation text for page mbr_account.php
#****************************************************************************
$trans["mbrAccountLabel"]         = "\$text='Adicionar uma transa��o:';";
$trans["mbrAccountTransTyp"]      = "\$text='Tipo de transa��o:';";
$trans["mbrAccountAmount"]        = "\$text='Valor';";
$trans["mbrAccountDesc"]          = "\$text='Descri��o';";
$trans["mbrAccountHead1"]         = "\$text='Transa��es do membro:';";
$trans["mbrAccountNoTrans"]       = "\$text='Nenhuma transa��o encontrada.';";
$trans["mbrAccountOpenBal"]       = "\$text='Balan�o aberto';";
$trans["mbrAccountDel"]           = "\$text='apagar';";
$trans["mbrAccountHdr1"]          = "\$text='Fun��o';";
$trans["mbrAccountHdr2"]          = "\$text='Data';";
$trans["mbrAccountHdr3"]          = "\$text='Tipo Trans';";
$trans["mbrAccountHdr4"]          = "\$text='Descri��o';";
$trans["mbrAccountHdr5"]          = "\$text='Valor';";
$trans["mbrAccountHdr6"]          = "\$text='Balan�o';";

#****************************************************************************
#*  Translation text for page mbr_transaction.php
#****************************************************************************
$trans["mbrTransactionSuccess"]   = "\$text='Transa��o completa.';";

#****************************************************************************
#*  Translation text for page mbr_transaction_del_confirm.php
#****************************************************************************
$trans["mbrTransDelConfirmMsg"]   = "\$text='Voc� tem certeza que deseja apagar esta transa��o?';";

#****************************************************************************
#*  Translation text for page mbr_transaction_del.php
#****************************************************************************
$trans["mbrTransactionDelSuccess"] = "\$text='Transa��o apagada com sucesso.';";

#****************************************************************************
#*  Translation text for page mbr_print_checkouts.php
#****************************************************************************
$trans["mbrPrintCheckoutsTitle"]  = "\$text='Empr�stimos para %mbrName%';";
$trans["mbrPrintCheckoutsHdr1"]   = "\$text='Data atual:';";
$trans["mbrPrintCheckoutsHdr2"]   = "\$text='Membro:';";
$trans["mbrPrintCheckoutsHdr3"]   = "\$text='N�mero do Cart�o:';";
$trans["mbrPrintCheckoutsHdr4"]   = "\$text='Classe:';";
$trans["mbrPrintCloseWindow"]     = "\$text='Fechar Janela';";

$trans["RenewAll"]     = "\$text='Renovar tudo';";
$trans["opac_Callno"] = "\$text='Numero de chamada';";
$trans["Offline Circulation"] = "\$text='Numero de chamada';";

?>

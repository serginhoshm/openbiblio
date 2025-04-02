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
$trans["mbrDupBarcode"]           = "\$text = 'O código de barras, %barcode%, está em uso.';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHeading"]            = "\$text='Circulação';";
$trans["indexCardHdr"]            = "\$text='Procurar por número de inscrição:';";
$trans["indexCard"]               = "\$text='Inscrição:';";
$trans["indexSearch"]             = "\$text='Procurar';";
$trans["indexNameHdr"]            = "\$text='Procurar membro por sobrenome:';";
$trans["indexName"]               = "\$text='Sobrenome começa com:';";

#****************************************************************************
#*  Translation text for page mbr_new_form.php, mbr_edit_form.php and mbr_fields.php
#****************************************************************************
$trans["Mailing Address:"] = "\$text='Endereço:';";
$trans["mbrNewForm"]              = "\$text='Adicionar Novo';";
$trans["mbrEditForm"]             = "\$text='Editar';";
$trans["mbrFldsHeader"]           = "\$text='Membro:';";
$trans["mbrFldsCardNmbr"]         = "\$text='Número do Cartão:';";
$trans["mbrFldsLastName"]         = "\$text='Sobrenome';";
$trans["mbrFldsFirstName"]        = "\$text='Nome:';";
$trans["mbrFldsAddr1"]            = "\$text='Endereço:';";
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
$trans["mbrsearchnext"]           = "\$text='próximo';";
$trans["mbrsearchNoResults"]      = "\$text='Nenhum resultado foi encontrado.';";
$trans["mbrsearchFoundResults"]   = "\$text=' Resultados.';";
$trans["mbrsearchSearchResults"]  = "\$text='Procurar:';";
$trans["mbrsearchCardNumber"]     = "\$text='Número do Cartão:';";
$trans["mbrsearchClassification"] = "\$text='Classificação:';";

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
$trans["mbrViewHead1"]            = "\$text='Informações do membro:';";
$trans["mbrViewName"]             = "\$text='Nome:';";
$trans["mbrViewAddr"]             = "\$text='Endereço';";
$trans["mbrViewCardNmbr"]         = "\$text='Número do cartão';";
$trans["mbrViewClassify"]         = "\$text='Classe:';";
$trans["mbrViewPhone"]            = "\$text='Telefone:';";
$trans["mbrViewPhoneHome"]        = "\$text='Res:';";
$trans["mbrViewPhoneWork"]        = "\$text='Com:';";
$trans["mbrViewEmail"]            = "\$text='Email:';";
$trans["mbrViewGrade"]            = "\$text='Formação:';";
$trans["mbrViewTeacher"]          = "\$text='Escola:';";
$trans["mbrViewHead2"]            = "\$text='Estatísticas:';";
$trans["mbrViewStatColHdr1"]      = "\$text='Material';";
$trans["mbrViewStatColHdr2"]      = "\$text='Contador';";
$trans["mbrViewStatColHdr3"]      = "\$text='Empréstimo máx.';";
$trans["mbrViewStatColHdr4"]      = "\$text='Empréstimo';";
$trans["mbrViewStatColHdr5"]      = "\$text='Renovar';";
$trans["mbrViewHead3"]            = "\$text='Empréstimo de bibliografia:';";
$trans["mbrViewBarcode"]          = "\$text='Número do Código de Barras:';";
$trans["mbrViewCheckOut"]         = "\$text='Efetuar Empréstimo';";
$trans["mbrViewHead4"]            = "\$text='Bibliografias em empréstimos:';";
$trans["mbrViewOutHdr1"]          = "\$text='Retirado';";
$trans["mbrViewOutHdr2"]          = "\$text='Material';";
$trans["mbrViewOutHdr3"]          = "\$text='Código de Barras';";
$trans["mbrViewOutHdr4"]          = "\$text='Título';";
$trans["mbrViewOutHdr5"]          = "\$text='Autor';";
$trans["mbrViewOutHdr6"]          = "\$text='Devolução';";
$trans["mbrViewOutHdr7"]          = "\$text='Atraso';";
$trans["mbrViewOutHdr8"]          = "\$text='Renovar';";
$trans["mbrViewOutHdr9"]          = "\$text='vez/es';";
$trans["mbrViewNoCheckouts"]      = "\$text='Nenhuma bibliografia em empréstimo.';";
$trans["mbrViewHead5"]            = "\$text='Colocar em reserva:';";
$trans["mbrViewHead6"]            = "\$text='Bibliografias na reserva:';";
$trans["mbrViewPlaceHold"]        = "\$text='Reservar';";
$trans["mbrViewHoldHdr1"]         = "\$text='Função';";
$trans["mbrViewHoldHdr2"]         = "\$text='Reservado';";
$trans["mbrViewHoldHdr3"]         = "\$text='Material';";
$trans["mbrViewHoldHdr4"]         = "\$text='Código de barras';";
$trans["mbrViewHoldHdr5"]         = "\$text='Título';";
$trans["mbrViewHoldHdr6"]         = "\$text='Autor';";
$trans["mbrViewHoldHdr7"]         = "\$text='Status';";
$trans["mbrViewHoldHdr8"]         = "\$text='Devolução';";
$trans["mbrViewNoHolds"]          = "\$text='Nenhuma bibliografia reservada.';";
$trans["mbrViewBalMsg"]           = "\$text='Nota: Membro com balanço em alarme %bal%.';";
$trans["mbrPrintCheckouts"]	  = "\$text='Imprimir empréstimos';";
$trans["mbrViewDel"]              = "\$text='apagar';";

#****************************************************************************
#*  Translation text for page checkout.php
#****************************************************************************
$trans["checkoutBalErr"]          = "\$text='Membro deve pagar o balanço antes de fazer empréstimo.';";
$trans["checkoutErr1"]            = "\$text='Códigos de barras devem ser alfanuméricos.';";
$trans["checkoutErr2"]            = "\$text='Nenhuma bibliografia com o código de barras informado.';";
$trans["checkoutErr3"]            = "\$text='Bibliografia com o código de barras %barcode% está no empréstimo.';";
$trans["checkoutErr4"]            = "\$text='Bibliografia com o código de barras %barcode% não está disponível para empréstimo.';";
$trans["checkoutErr5"]            = "\$text='Bibliografia com o código de barras %barcode% reservado por outro membro.';";
$trans["checkoutErr6"]            = "\$text='Membro excedeu o máximo de empréstimos para o material indicado.';";
$trans["checkoutErr7"]            = "\$text='Bibliografia com o código de barras %barcode% alcançou o limite de renovações pelo membro.';";
$trans["checkoutErr8"]            = "\$text='Bibliografia com o código de barras %barcode% não pode ser renovado.';";

#****************************************************************************
#*  Translation text for page shelving_cart.php
#****************************************************************************
$trans["shelvingCartErr1"]        = "\$text='Códigos de barras devem ser alfanuméricos.';";
$trans["shelvingCartErr2"]        = "\$text='Nenhuma bibliografia encontrada com este código de barras.';";
$trans["shelvingCartTrans"]       = "\$text='Multa por atraso (Código de Barras =%barcode%)';";

#****************************************************************************
#*  Translation text for page checkin_form.php
#****************************************************************************
$trans["checkinFormHdr1"]         = "\$text='Devolução de bibliografia:';";
$trans["checkinFormBarcode"]      = "\$text='Número do Código de Barras:';";
$trans["checkinFormShelveButton"] = "\$text='Devolver';";
$trans["checkinFormCheckinLink1"] = "\$text='Devolução dos itens selecionados';";
$trans["checkinFormCheckinLink2"] = "\$text='Devolução para todos os itens';";
$trans["checkinFormHdr2"]         = "\$text='Lista de espera:';";
$trans["checkinFormColHdr1"]      = "\$text='Data';";
$trans["checkinFormColHdr2"]      = "\$text='Código de Barra';";
$trans["checkinFormColHdr3"]      = "\$text='Título';";
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
$trans["holdMessageMsg1"]         = "\$text='A bibliografia com o código de barras %barcode% que você está tentando marcar tem uma ou mais reservas.  <b>Arquive por favor esta bibliografia na reserva, em vez de colocá-la na espera. </b>  O status da sua bibliografia foi enviada para reserva.';";
$trans["holdMessageMsg2"]         = "\$text='Retornar a Devolução de bibliografia.';";

#****************************************************************************
#*  Translation text for page place_hold.php
#****************************************************************************
$trans["placeHoldErr1"]           = "\$text='O código de barras deve ser numérico';";
$trans["placeHoldErr2"]           = "\$text='O código de barras não existe.';";
$trans["placeHoldErr3"]           = "\$text='O membro já retirou esta bibliografia -- não foi colocada na espera';";

#****************************************************************************
#*  Translation text for page mbr_del_confirm.php
#****************************************************************************
$trans["mbrDelConfirmWarn"]       = "\$text = 'O membro, %name%, fez %checkoutCount% empréstimo(s) e reservou %holdCount% itens.  Todos materiais emprestados devem ser colocados como devolvido e todas requisições devem ser apagadas antes de excluir este membro.';";
$trans["mbrDelConfirmReturn"]     = "\$text = 'retornar às informações do membro';";
$trans["mbrDelConfirmMsg"]        = "\$text = 'Você tem certeza que deseja apagar o membro %name%?  Isso também irá apagar todo o histórico de empréstimos deste membro.';";

#****************************************************************************
#*  Translation text for page mbr_del.php
#****************************************************************************
$trans["mbrDelSuccess"]           = "\$text='Membro %name%, foi apagado.';";
$trans["mbrDelReturn"]            = "\$text='retornar à procura de membros';";

#****************************************************************************
#*  Translation text for page mbr_history.php
#****************************************************************************
$trans["mbrHistoryHead1"]         = "\$text='Histórico empréstimos:';";
$trans["mbrHistoryNoHist"]        = "\$text='Histórico não encontrado.';";
$trans["mbrHistoryHdr1"]          = "\$text='Código de Barras';";
$trans["mbrHistoryHdr2"]          = "\$text='Título';";
$trans["mbrHistoryHdr3"]          = "\$text='Autor';";
$trans["mbrHistoryHdr4"]          = "\$text='Novo Status';";
$trans["mbrHistoryHdr5"]          = "\$text='Data de mudança de status';";
$trans["mbrHistoryHdr6"]          = "\$text='Data de devolução';";

#****************************************************************************
#*  Translation text for page mbr_account.php
#****************************************************************************
$trans["mbrAccountLabel"]         = "\$text='Adicionar uma transação:';";
$trans["mbrAccountTransTyp"]      = "\$text='Tipo de transação:';";
$trans["mbrAccountAmount"]        = "\$text='Valor';";
$trans["mbrAccountDesc"]          = "\$text='Descrição';";
$trans["mbrAccountHead1"]         = "\$text='Transações do membro:';";
$trans["mbrAccountNoTrans"]       = "\$text='Nenhuma transação encontrada.';";
$trans["mbrAccountOpenBal"]       = "\$text='Balanço aberto';";
$trans["mbrAccountDel"]           = "\$text='apagar';";
$trans["mbrAccountHdr1"]          = "\$text='Função';";
$trans["mbrAccountHdr2"]          = "\$text='Data';";
$trans["mbrAccountHdr3"]          = "\$text='Tipo Trans';";
$trans["mbrAccountHdr4"]          = "\$text='Descrição';";
$trans["mbrAccountHdr5"]          = "\$text='Valor';";
$trans["mbrAccountHdr6"]          = "\$text='Balanço';";

#****************************************************************************
#*  Translation text for page mbr_transaction.php
#****************************************************************************
$trans["mbrTransactionSuccess"]   = "\$text='Transação completa.';";

#****************************************************************************
#*  Translation text for page mbr_transaction_del_confirm.php
#****************************************************************************
$trans["mbrTransDelConfirmMsg"]   = "\$text='Você tem certeza que deseja apagar esta transação?';";

#****************************************************************************
#*  Translation text for page mbr_transaction_del.php
#****************************************************************************
$trans["mbrTransactionDelSuccess"] = "\$text='Transação apagada com sucesso.';";

#****************************************************************************
#*  Translation text for page mbr_print_checkouts.php
#****************************************************************************
$trans["mbrPrintCheckoutsTitle"]  = "\$text='Empréstimos para %mbrName%';";
$trans["mbrPrintCheckoutsHdr1"]   = "\$text='Data atual:';";
$trans["mbrPrintCheckoutsHdr2"]   = "\$text='Membro:';";
$trans["mbrPrintCheckoutsHdr3"]   = "\$text='Número do Cartão:';";
$trans["mbrPrintCheckoutsHdr4"]   = "\$text='Classe:';";
$trans["mbrPrintCloseWindow"]     = "\$text='Fechar Janela';";

$trans["RenewAll"]     = "\$text='Renovar tudo';";
$trans["opac_Callno"] = "\$text='Numero de chamada';";
$trans["Offline Circulation"] = "\$text='Numero de chamada';";

?>

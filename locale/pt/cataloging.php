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
$trans["catalogSubmit"]            = "\$text = 'Enviar';";
$trans["catalogCancel"]            = "\$text = 'Cancelar';";
$trans["catalogRefresh"]           = "\$text = 'Atualizar';";
$trans["catalogDelete"]            = "\$text = 'Apagar';";
$trans["catalogFootnote"]          = "\$text = 'Campos marcados com %symbol% s�o obrigat�rios.';";
$trans["AnswerYes"]                = "\$text = 'Sim';";
$trans["AnswerNo"]                 = "\$text = 'N�o';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHdr"]                 = "\$text = 'Catalogar';";
$trans["indexBarcodeHdr"]          = "\$text = 'Procurar Bibliografia por C�digo de Barras';";
$trans["indexBarcodeField"]        = "\$text = 'C�digo de Barras';";
$trans["indexSearchHdr"]           = "\$text = 'Procurar Bibliografia por Texto';";
$trans["indexTitle"]               = "\$text = 'T�tulo';";
$trans["indexAuthor"]              = "\$text = 'Autor';";
$trans["indexSubject"]             = "\$text = 'Assunto';";
$trans["indexButton"]              = "\$text = 'Procurar';";

#****************************************************************************
#*  Translation text for page biblio_fields.php
#****************************************************************************
$trans["biblioFieldsLabel"]        = "\$text = 'Bibliografia';";
$trans["biblioFieldsMaterialTyp"]  = "\$text = 'Tipo de Material';";
$trans["biblioFieldsCollection"]   = "\$text = 'G�nero';";
$trans["biblioFieldsCallNmbr"]     = "\$text = 'N�mero de Chamada';";
$trans["biblioFieldsUsmarcFields"] = "\$text = 'Campos USMarc';";
$trans["biblioFieldsOpacFlg"]      = "\$text = 'Mostrar no OPAC';";

#****************************************************************************
#*  Translation text for page biblio_new.php
#****************************************************************************
$trans["biblioNewFormLabel"]       = "\$text = 'Adicionar Novo';";
$trans["biblioNewSuccess"]         = "\$text = 'A seguinte Bibliografia foi criada.  Para adicionar um exemplar, clique em \"Novo Exemplar\" no menu � esquerda ou \"Adicionar Novo Exemplar\" na informa��o a seguir.';";

#****************************************************************************
#*  Translation text for page biblio_edit.php
#****************************************************************************
$trans["biblioEditSuccess"]        = "\$text = 'Bibliografia atualizada com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_copy_new_form.php and biblio_copy_edit_form.php
#****************************************************************************
$trans["biblioCopyNewFormLabel"]   = "\$text = 'Adicionar Novo Exemplar';";
$trans["biblioCopyNewBarcode"]     = "\$text = 'C�digo de barras';";
$trans["biblioCopyNewDesc"]        = "\$text = 'Descri��o';";
$trans["biblioCopyNewAuto"]        = "\$text = 'Gerar automaticamente';";
$trans["biblioCopyNewValidBarco"]  = "\$text = 'Validar novo c�digo de barras';";
$trans["biblioCopyEditFormLabel"]  = "\$text = 'Editar c�pia';";
$trans["biblioCopyEditFormStatus"] = "\$text = 'Estat�stica';";

#****************************************************************************
#*  Translation text for page biblio_copy_new.php
#****************************************************************************
$trans["biblioCopyNewSuccess"]     = "\$text = 'Exemplar criado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_copy_edit.php
#****************************************************************************
$trans["biblioCopyEditSuccess"]    = "\$text = 'Exemplar atualizado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_copy_del_confirm.php
#****************************************************************************
$trans["biblioCopyDelConfirmErr1"] = "\$text = 'Imposs�vel apagar exemplar.  O exemplar deve estar na biblioteca antes de ser exclu�do.';";
$trans["biblioCopyDelConfirmMsg"]  = "\$text = 'Voc� tem certeza que deseja excluir o exemplar com o c�gido de barras %barcodeNmbr%?  Isto vai apagar todos as modifica��es hist�ricas do status para este exemplar.';";

#****************************************************************************
#*  Translation text for page biblio_copy_del.php
#****************************************************************************
$trans["biblioCopyDelSuccess"]     = "\$text = 'O C�digo de Barra %barcode%  do exemplar foi apagado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_marc_list.php
#****************************************************************************
$trans["biblioMarcListMarcSelect"] = "\$text = 'Adicionar novo MARC';";
$trans["biblioMarcListHdr"]        = "\$text = 'Informa��es MARC';";
$trans["biblioMarcListTbleCol1"]   = "\$text = 'Fun��o';";
$trans["biblioMarcListTbleCol2"]   = "\$text = 'Tag';";
$trans["biblioMarcListTbleCol3"]   = "\$text = 'Descri��o da tag';";
$trans["biblioMarcListTbleCol4"]   = "\$text = 'Ind 1';";
$trans["biblioMarcListTbleCol5"]   = "\$text = 'Ind 2';";
$trans["biblioMarcListTbleCol6"]   = "\$text = 'Subcmp';";
$trans["biblioMarcListTbleCol7"]   = "\$text = 'Descri��o de Subcampo';";
$trans["biblioMarcListTbleCol8"]   = "\$text = 'Dados do Campo';";
$trans["biblioMarcListNoRows"]     = "\$text = 'Nenhum MARC encontrado.';";
$trans["biblioMarcListEdit"]       = "\$text = 'editar';";
$trans["biblioMarcListDel"]        = "\$text = 'apagar';";

#****************************************************************************
#*  Translation text for page usmarc_select.php
#****************************************************************************
$trans["usmarcSelectHdr"]          = "\$text = 'Seletor de MARC';";
$trans["usmarcSelectInst"]         = "\$text = 'Selecione um campo MARC';";
$trans["usmarcSelectNoTags"]       = "\$text = 'Nenhum tag encontrado.';";
$trans["usmarcSelectUse"]          = "\$text = 'usar';";
$trans["usmarcCloseWindow"]        = "\$text = 'Fechar Janela';";

#****************************************************************************
#*  Translation text for page biblio_marc_new_form.php
#****************************************************************************
$trans["biblioMarcNewFormHdr"]     = "\$text = 'Adicionar novo campo MARC';";
$trans["biblioMarcNewFormTag"]     = "\$text = 'Tag';";
$trans["biblioMarcNewFormSubfld"]  = "\$text = 'Subcampo';";
$trans["biblioMarcNewFormData"]    = "\$text = 'Dados do Campo';";
$trans["biblioMarcNewFormInd1"]    = "\$text = 'Indicador 1';";
$trans["biblioMarcNewFormInd2"]    = "\$text = 'Indicador 2';";
$trans["biblioMarcNewFormSelect"]  = "\$text = 'Selecionar';";

#****************************************************************************
#*  Translation text for page biblio_marc_new.php
#****************************************************************************
$trans["biblioMarcNewSuccess"]     = "\$text = 'Campo MARC foi adicionado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_marc_edit_form.php
#****************************************************************************
$trans["biblioMarcEditFormHdr"]    = "\$text = 'Editar campo MARC';";

#****************************************************************************
#*  Translation text for page biblio_marc_edit.php
#****************************************************************************
$trans["biblioMarcEditSuccess"]    = "\$text = 'Campo MARC foi atualizado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_marc_del_confirm.php
#****************************************************************************
$trans["biblioMarcDelConfirmMsg"]  = "\$text = 'Voc� tem certeza que deseja apagar o campo com a tag %tag% e o subcampo %subfieldCd%?';";

#****************************************************************************
#*  Translation text for page biblio_marc_del.php
#****************************************************************************
$trans["biblioMarcDelSuccess"]     = "\$text = 'Campo MARC apagado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_del_confirm.php
#****************************************************************************
$trans["biblioDelConfirmWarn"]     = "\$text = 'Esta bibliografia tem %copyCount% exemplar(es) em %holdCount% reservas.  Por favor, apague estes exemplares e/ou reservas antes de apagar esta bibliografia.';";
$trans["biblioDelConfirmReturn"]   = "\$text = 'retornar �s informa��es bibliogr�ficas';";
$trans["biblioDelConfirmMsg"]      = "\$text = 'Voc� tem certeza que deseja apagar a bibliografia com o t�tulo %title%?';";

#****************************************************************************
#*  Translation text for page biblio_del_confirm.php
#****************************************************************************
$trans["biblioDelMsg"]             = "\$text = 'Bibliografia, %title%, foi apagada.';";
$trans["biblioDelReturn"]          = "\$text = 'voltar a pesquisa';";

#****************************************************************************
#*  Translation text for page biblio_hold_list.php
#****************************************************************************
$trans["biblioHoldListHead"]       = "\$text = 'Bibliografia reservada:';";
$trans["biblioHoldListNoHolds"]    = "\$text = 'Nenhum registro de reserva.';";
$trans["biblioHoldListHdr1"]       = "\$text = 'Fun��o';";
$trans["biblioHoldListHdr2"]       = "\$text = 'Copiar';";
$trans["biblioHoldListHdr3"]       = "\$text = 'Colocar na reserva';";
$trans["biblioHoldListHdr4"]       = "\$text = 'Membro';";
$trans["biblioHoldListHdr5"]       = "\$text = 'Status';";
$trans["biblioHoldListHdr6"]       = "\$text = 'Devolu��o';";
$trans["biblioHoldListdel"]        = "\$text = 'Apagar';";

#****************************************************************************
#*  Translation text for page noauth.php
#****************************************************************************
$trans["NotAuth"]                 = "\$text = 'Voc� n�o est� autorizado a usar �rea Catalogando';";

#****************************************************************************
#*  Translation text for page upload_usmarc.php and upload_usmarc_form.php
#****************************************************************************
$trans["MarcUploadTest"]            = "\$text = 'Carregar como Teste';";
$trans["MarcUploadTestTrue"]        = "\$text = 'Verdadeiro';";
$trans["MarcUploadTestFalse"]       = "\$text = 'Falso';";
$trans["MarcUploadTestFileUpload"]  = "\$text = 'Inserir Arquivo USMarc';";
$trans["MarcUploadRecordsUploaded"] = "\$text = 'Registros Atualizados';";
$trans["MarcUploadMarcRecord"]      = "\$text = 'Registros MARC';";
$trans["MarcUploadTag"]             = "\$text = 'Tag';";
$trans["MarcUploadSubfield"]        = "\$text = 'Sub';";
$trans["MarcUploadData"]            = "\$text = 'Dado';";
$trans["MarcUploadRawData"]         = "\$text = 'Dado Vazio:';";
$trans["UploadFile"]                = "\$text = 'Inserir Arquivo';";

#****************************************************************************
#*  Translation text for page usmarc_select.php
#****************************************************************************
$trans["PoweredByOB"]                 = "\$text = 'Powered by OpenBiblio';";
$trans["Copyright"]                   = "\$text = 'Copyright &copy; 2002-2005';";
$trans["underthe"]                    = "\$text = 'under the';";
$trans["GNU"]                 = "\$text = 'GNU General Public License';";

$trans["catalogResults"]                 = "\$text = 'Resultados';";

#****************************************************************************
#*  Outros
#****************************************************************************
$trans["indexKeyword"]                 = "\$text = 'Palavra chave';";




?>

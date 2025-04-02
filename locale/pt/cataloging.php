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
$trans["catalogFootnote"]          = "\$text = 'Campos marcados com %symbol% são obrigatórios.';";
$trans["AnswerYes"]                = "\$text = 'Sim';";
$trans["AnswerNo"]                 = "\$text = 'Não';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHdr"]                 = "\$text = 'Catalogar';";
$trans["indexBarcodeHdr"]          = "\$text = 'Procurar Bibliografia por Código de Barras';";
$trans["indexBarcodeField"]        = "\$text = 'Código de Barras';";
$trans["indexSearchHdr"]           = "\$text = 'Procurar Bibliografia por Texto';";
$trans["indexTitle"]               = "\$text = 'Título';";
$trans["indexAuthor"]              = "\$text = 'Autor';";
$trans["indexSubject"]             = "\$text = 'Assunto';";
$trans["indexButton"]              = "\$text = 'Procurar';";

#****************************************************************************
#*  Translation text for page biblio_fields.php
#****************************************************************************
$trans["biblioFieldsLabel"]        = "\$text = 'Bibliografia';";
$trans["biblioFieldsMaterialTyp"]  = "\$text = 'Tipo de Material';";
$trans["biblioFieldsCollection"]   = "\$text = 'Gênero';";
$trans["biblioFieldsCallNmbr"]     = "\$text = 'Número de Chamada';";
$trans["biblioFieldsUsmarcFields"] = "\$text = 'Campos USMarc';";
$trans["biblioFieldsOpacFlg"]      = "\$text = 'Mostrar no OPAC';";

#****************************************************************************
#*  Translation text for page biblio_new.php
#****************************************************************************
$trans["biblioNewFormLabel"]       = "\$text = 'Adicionar Novo';";
$trans["biblioNewSuccess"]         = "\$text = 'A seguinte Bibliografia foi criada.  Para adicionar um exemplar, clique em \"Novo Exemplar\" no menu à esquerda ou \"Adicionar Novo Exemplar\" na informação a seguir.';";

#****************************************************************************
#*  Translation text for page biblio_edit.php
#****************************************************************************
$trans["biblioEditSuccess"]        = "\$text = 'Bibliografia atualizada com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_copy_new_form.php and biblio_copy_edit_form.php
#****************************************************************************
$trans["biblioCopyNewFormLabel"]   = "\$text = 'Adicionar Novo Exemplar';";
$trans["biblioCopyNewBarcode"]     = "\$text = 'Código de barras';";
$trans["biblioCopyNewDesc"]        = "\$text = 'Descrição';";
$trans["biblioCopyNewAuto"]        = "\$text = 'Gerar automaticamente';";
$trans["biblioCopyNewValidBarco"]  = "\$text = 'Validar novo código de barras';";
$trans["biblioCopyEditFormLabel"]  = "\$text = 'Editar cópia';";
$trans["biblioCopyEditFormStatus"] = "\$text = 'Estatística';";

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
$trans["biblioCopyDelConfirmErr1"] = "\$text = 'Impossível apagar exemplar.  O exemplar deve estar na biblioteca antes de ser excluído.';";
$trans["biblioCopyDelConfirmMsg"]  = "\$text = 'Você tem certeza que deseja excluir o exemplar com o cógido de barras %barcodeNmbr%?  Isto vai apagar todos as modificações históricas do status para este exemplar.';";

#****************************************************************************
#*  Translation text for page biblio_copy_del.php
#****************************************************************************
$trans["biblioCopyDelSuccess"]     = "\$text = 'O Código de Barra %barcode%  do exemplar foi apagado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_marc_list.php
#****************************************************************************
$trans["biblioMarcListMarcSelect"] = "\$text = 'Adicionar novo MARC';";
$trans["biblioMarcListHdr"]        = "\$text = 'Informações MARC';";
$trans["biblioMarcListTbleCol1"]   = "\$text = 'Função';";
$trans["biblioMarcListTbleCol2"]   = "\$text = 'Tag';";
$trans["biblioMarcListTbleCol3"]   = "\$text = 'Descrição da tag';";
$trans["biblioMarcListTbleCol4"]   = "\$text = 'Ind 1';";
$trans["biblioMarcListTbleCol5"]   = "\$text = 'Ind 2';";
$trans["biblioMarcListTbleCol6"]   = "\$text = 'Subcmp';";
$trans["biblioMarcListTbleCol7"]   = "\$text = 'Descrição de Subcampo';";
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
$trans["biblioMarcDelConfirmMsg"]  = "\$text = 'Você tem certeza que deseja apagar o campo com a tag %tag% e o subcampo %subfieldCd%?';";

#****************************************************************************
#*  Translation text for page biblio_marc_del.php
#****************************************************************************
$trans["biblioMarcDelSuccess"]     = "\$text = 'Campo MARC apagado com sucesso.';";

#****************************************************************************
#*  Translation text for page biblio_del_confirm.php
#****************************************************************************
$trans["biblioDelConfirmWarn"]     = "\$text = 'Esta bibliografia tem %copyCount% exemplar(es) em %holdCount% reservas.  Por favor, apague estes exemplares e/ou reservas antes de apagar esta bibliografia.';";
$trans["biblioDelConfirmReturn"]   = "\$text = 'retornar às informações bibliográficas';";
$trans["biblioDelConfirmMsg"]      = "\$text = 'Você tem certeza que deseja apagar a bibliografia com o título %title%?';";

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
$trans["biblioHoldListHdr1"]       = "\$text = 'Função';";
$trans["biblioHoldListHdr2"]       = "\$text = 'Copiar';";
$trans["biblioHoldListHdr3"]       = "\$text = 'Colocar na reserva';";
$trans["biblioHoldListHdr4"]       = "\$text = 'Membro';";
$trans["biblioHoldListHdr5"]       = "\$text = 'Status';";
$trans["biblioHoldListHdr6"]       = "\$text = 'Devolução';";
$trans["biblioHoldListdel"]        = "\$text = 'Apagar';";

#****************************************************************************
#*  Translation text for page noauth.php
#****************************************************************************
$trans["NotAuth"]                 = "\$text = 'Você não está autorizado a usar área Catalogando';";

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

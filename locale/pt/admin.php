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
$trans["adminSubmit"]              = "\$text = 'Enviar';";
$trans["adminCancel"]              = "\$text = 'Cancelar';";
$trans["adminDelete"]              = "\$text = 'Apagar';";
$trans["adminUpdate"]              = "\$text = 'Atualizar';";
$trans["adminFootnote"]            = "\$text = 'Campos marcados com %symbol% s�o obrigat�rios.';";

#****************************************************************************
#*  Translation text for page index.php
#****************************************************************************
$trans["indexHdr"]                 = "\$text = 'Administra��o';";
$trans["indexDesc"]                = "\$text = 'Use o menu � sua esquerda para gerenciar os organizadores da biblioteca e itens administrativos.';";

#****************************************************************************
#*  Translation text for page collections*.php
#****************************************************************************
$trans["adminCollections_delReturn"]                 = "\$text = 'Voltar a lista de g�neros';";
$trans["adminCollections_delStart"]                 = "\$text = 'G�neros, ';";

#****************************************************************************
#*  Translation text for page collections_del.php
#****************************************************************************
$trans["adminCollections_delEnd"]                 = "\$text = ', foi apagado.';";

#****************************************************************************
#*  Translation text for page collections_del_confirm.php
#****************************************************************************
$trans["adminCollections_del_confirmText"]                 = "\$text = 'Voc� realmente deseja apagar este g�nero, ';";

#****************************************************************************
#*  Translation text for page collections_edit.php
#****************************************************************************
$trans["adminCollections_editEnd"]                 = "\$text = ', foi atualizado.';";

#****************************************************************************
#*  Translation text for page collections_edit_form.php
#****************************************************************************
$trans["adminCollections_edit_formEditcollection"]                 = "\$text = 'Editar G�nero:';";
$trans["adminCollections_edit_formDescription"]                 = "\$text = 'Descri��o:';";
$trans["adminCollections_edit_formDaysdueback"]                 = "\$text = 'Dias para Devolu��o:';";
$trans["adminCollections_edit_formDailyLateFee"]                 = "\$text = 'Multas Di�rias:';";
$trans["adminCollections_edit_formNote"]                 = "\$text = '*Nota:';";
$trans["adminCollections_edit_formNoteText"]                 = "\$text = 'Inserindo zero na op��o Dias de Devolu��o desabilitar� os empr�stimos para g�neros.';";

#****************************************************************************
#*  Translation text for page collections_list.php
#****************************************************************************
$trans["adminCollections_listAddNewCollection"]                 = "\$text = 'Adicionar Novo G�nero';";
$trans["adminCollections_listCollections"]                 = "\$text = 'G�neros:';";
$trans["adminCollections_listFunction"]                 = "\$text = 'Fun��o';";
$trans["adminCollections_listDescription"]                 = "\$text = 'Descri��es';";
$trans["adminCollections_listDaysdueback"]                 = "\$text = 'Dias para<br>Devolu��o';";
$trans["adminCollections_listDailylatefee"]                 = "\$text = 'Multas<br>Di�rias';";
$trans["adminCollections_listBibliographycount"]                 = "\$text = 'Bibliografia<br>Contador';";
$trans["adminCollections_listEdit"]                 = "\$text = 'Editar';";
$trans["adminCollections_listDel"]                 = "\$text = 'apagar';";
$trans["adminCollections_ListNote"]                 = "\$text = '*Nota:';";
$trans["adminCollections_ListNoteText"]                 = "\$text = 'A fun��o apagar em G�nero somente estar� avaliado se o contador da bibliografia for zero.<br> Se deseja apagar o G�nero com o contador da bibliografia maior que zero, voc� deve primeiro alterar o tipo de material contido na bibliografia para outro tipo de material.';";

#****************************************************************************
#*  Translation text for page collections_new.php
#****************************************************************************
$trans["adminCollections_newAdded"]                 = "\$text = ', foi adicionado.';";

#****************************************************************************
#*  Translation text for page collections_new_form.php
#****************************************************************************
$trans["adminCollections_new_formAddnewcollection"]                 = "\$text = 'Adicionar Novo G�nero:';";
$trans["adminCollections_new_formDescription"]                 = "\$text = 'Descri��o:';";
$trans["adminCollections_new_formDaysdueback"]                 = "\$text = 'Dias para Devolu��o:';";
$trans["adminCollections_new_formDailylatefee"]                 = "\$text = 'Multas Di�rias:';";
$trans["adminCollections_new_formNote"]                 = "\$text = '*Nota:';";
$trans["adminCollections_new_formNoteText"]                 = "\$text = 'Inserindo zero na op��o Dias para Devolu��o desabilitar� os empr�stimos para g�neros.';";

#****************************************************************************
#*  Translation text for page materials_del.php
#****************************************************************************
$trans["admin_materials_delMaterialType"]                 = "\$text = 'Tipo do material, ';";
$trans["admin_materials_delMaterialdeleted"]                 = "\$text = ', foi apagado.';";
$trans["admin_materials_Return"]                 = "\$text = 'voltar a lista de Tipos de Material';";

#****************************************************************************
#*  Translation text for page materials_del_form.php
#****************************************************************************
$trans["admin_materials_delAreyousure"]                 = "\$text = 'Voc� deseja realmente apagar este Tipo de Material, ';";

#****************************************************************************
#*  Translation text for page materials_edit_form.php
#****************************************************************************
$trans["admin_materials_delEditmaterialtype"]                 = "\$text = 'Editar o Tipo de Material:';";
$trans["admin_materials_delDescription"]                 = "\$text = 'Descri��o:';";
$trans["admin_materials_delunlimited"]                 = "\$text = '(coloque 0 para ilimitado)';";
$trans["admin_materials_delImagefile"]                 = "\$text = 'Arquivo de Imagem:';";
$trans["admin_materials_delNote"]                 = "\$text = '*Nota:';";
$trans["admin_materials_delNoteText"]                 = "\$text = 'Arquivos de imagem devem estar localizados no diret�rio openbiblio/images.';";

#****************************************************************************
#*  Translation text for page materials_edit.php
#****************************************************************************
$trans["admin_materials_editEnd"]                 = "\$text = ', foi atualizado.';";

#****************************************************************************
#*  Translation text for page materials_list.php
#****************************************************************************
$trans["admin_materials_listAddmaterialtypes"]                 = "\$text = 'Adicionar Novo Tipo de Material';";
$trans["admin_materials_listMaterialtypes"]                 = "\$text = 'Tipo de Material:';";
$trans["admin_materials_listFunction"]                 = "\$text = 'Fun��o';";
$trans["admin_materials_listDescription"]                 = "\$text = 'Descri��o';";
$trans["admin_materials_listLimits"]                 = "\$text = 'Limites';";
$trans["admin_materials_listCheckoutlimit"]                 = "\$text = 'Devolu��o';";
$trans["admin_materials_listRenewallimit"]                 = "\$text = 'Renova��o';";
$trans["admin_materials_listImageFile"]                 = "\$text = 'Imagem<br>Arquivo';";
$trans["admin_materials_listBibcount"]                 = "\$text = 'Bibliografia<br>Contador';";
$trans["admin_materials_listEdit"]                 = "\$text = 'editar';";
$trans["admin_materials_listDel"]                 = "\$text = 'apagar';";
$trans["admin_materials_listNote"]                 = "\$text = '*Nota:';";
$trans["admin_materials_listNoteText"]                 = "\$text = 'A fun��o apagar em tipos de material somente estar� avaliado se o contador da bibliografia for zero.<br> Se deseja apagar o tipo de material com o contador da bibliografia maior que zero, voc� deve primeiro alterar o tipo de material contido na bibliografia para outro tipo de material.';";

#****************************************************************************
#*  Translation text for page materials_new.php
#****************************************************************************
$trans["admin_materials_listNewadded"]                 = "\$text = ', foi atualizado.';";

#****************************************************************************
#*  Translation text for page materials_new_form.php
#****************************************************************************
$trans["admin_materials_new_formNoteText"]                 = "\$text = 'Arquivos de imagem devem estar localizados no diret�rio openbiblio/images.';";

#****************************************************************************
#*  Translation text for page noauth.php
#****************************************************************************
$trans["admin_noauth"]                 = "\$text = 'Voc� n�o est� autorizado a utilizar a �rea de administra��o.';";

#****************************************************************************
#*  Translation text for page settings_edit.php
#****************************************************************************

#****************************************************************************
#*  Translation text for page settings_edit_form.php
#****************************************************************************
$trans["admin_settingsUpdated"]                 = "\$text = 'Os dados foram atualizados.';";
$trans["admin_settingsEditsettings"]                 = "\$text = 'Editar as Op��es da Biblioteca:';";
$trans["admin_settingsLibName"]                 = "\$text = 'Nome da Biblioteca:';";
$trans["admin_settingsLibimageurl"]                 = "\$text = 'Endere�o da imagem da biblioteca:';";
$trans["admin_settingsOnlyshowimginheader"]                 = "\$text = 'Mostrar imagem no cabe�ote:';";
$trans["admin_settingsLibhours"]                 = "\$text = 'Hor�rio da Biblioteca:';";
$trans["admin_settingsLibphone"]                 = "\$text = 'Telefone da Biblioteca:';";
$trans["admin_settingsLibURL"]                 = "\$text = 'Site da Biblioteca:';";
$trans["admin_settingsOPACURL"]                 = "\$text = 'OPAC:';";
$trans["admin_settingsSessionTimeout"]                 = "\$text = 'Expirar Busca Ap�s:';";
$trans["admin_settingsMinutes"]                 = "\$text = 'minutos';";
$trans["admin_settingsSearchResults"]                 = "\$text = 'Resultado da busca:';";
$trans["admin_settingsItemsperpage"]                 = "\$text = ' itens por p�gina';";
$trans["admin_settingsPurgebibhistory"]                 = "\$text = 'Remo��o do hist�rico da bibliografia ap�s:';";
$trans["admin_settingsmonths"]                 = "\$text = 'meses';";
$trans["admin_settingsBlockCheckouts"]                 = "\$text = 'Bloquear Empr�stimos at� a Devolu��o:';";
$trans["admin_settingsLocale"]                 = "\$text = 'Localizar:';";
$trans["admin_settingsHTMLChar"]                 = "\$text = 'Caract�res HTML:';";
$trans["admin_settingsHTMLTagLangAttr"]                 = "\$text = 'Atributos de linguagem de campos HTML:';";

//adicionados
$trans["Max. hold length:"]			   = "\$text = 'Manter emprestado no m�ximo';";
$trans["days"]						   = "\$text = 'dias';";

#****************************************************************************
#*  Translation text for all staff pages
#****************************************************************************
$trans["adminStaff_Staffmember"]                 = "\$text = 'Membro volut�rio,';";
$trans["adminStaff_Return"]                 = "\$text = 'retornar � lista de volunt�rios';";
$trans["adminStaff_Yes"]                 = "\$text = 'Sim';";
$trans["adminStaff_No"]                 = "\$text = 'N�o';";


#****************************************************************************
#*  Translation text for page staff_del.php
#****************************************************************************
$trans["adminStaff_delDeleted"]                 = "\$text = ', foi apagado.';";

#****************************************************************************
#*  Translation text for page staff_delete_confirm.php
#****************************************************************************
$trans["adminStaff_del_confirmConfirmText"]                 = "\$text = 'Voc� deseja realmente apagar este Volunt�rio, ';";

#****************************************************************************
#*  Translation text for page staff_edit.php
#****************************************************************************
$trans["adminStaff_editUpdated"]                 = "\$text = ', foi atualizado.';";

#****************************************************************************
#*  Translation text for page staff_edit_form.php
#****************************************************************************
$trans["adminStaff_edit_formHeader"]                 = "\$text = 'Editar Infoma��es do Volunt�rio:';";
$trans["adminStaff_edit_formLastname"]                 = "\$text = 'Sobrenome:';";
$trans["adminStaff_edit_formFirstname"]                 = "\$text = 'Nome:';";
$trans["adminStaff_edit_formLogin"]                 = "\$text = 'Usu�rio:';";
$trans["adminStaff_edit_formAuth"]                 = "\$text = 'Autoriza��o:';";
$trans["adminStaff_edit_formCirc"]                 = "\$text = 'Circula��o';";
$trans["adminStaff_edit_formUpdatemember"]                 = "\$text = 'Atualizar Membros';";
$trans["adminStaff_edit_formCatalog"]                 = "\$text = 'Cat�logo';";
$trans["adminStaff_edit_formAdmin"]                 = "\$text = 'Administra��o';";
$trans["adminStaff_edit_formReports"]                 = "\$text = 'Relat�rio';";
$trans["adminStaff_edit_formSuspended"]                 = "\$text = 'Suspenso:';";

#****************************************************************************
#*  Translation text for page staff_list.php
#****************************************************************************
$trans["adminStaff_list_formHeader"]                 = "\$text = 'Adicionar Novo Volunt�rio';";
$trans["adminStaff_list_Columnheader"]                 = "\$text = ' Membros Volunt�rios:';";
$trans["adminStaff_list_Function"]                 = "\$text = 'Fun��o';";
$trans["adminStaff_list_Edit"]                 = "\$text = 'editar';";
$trans["adminStaff_list_Pwd"]                 = "\$text = 'senha';";
$trans["adminStaff_list_Del"]                 = "\$text = 'apagar';";

#****************************************************************************
#*  Translation text for page staff_new.php
#****************************************************************************
$trans["adminStaff_new_Added"]                 = "\$text = ', foi adicionado.';";

#****************************************************************************
#*  Translation text for page staff_new_form.php
#****************************************************************************
$trans["adminStaff_new_form_Header"]          	= "\$text = 'Adicionar novo funcion�rio:';";
$trans["adminStaff_new_form_Password"]          = "\$text = 'Senha:';";
$trans["adminStaff_new_form_Reenterpassword"]   = "\$text = 'Re-digite a senha:';";

#****************************************************************************
#*  Translation text for page staff_pwd_reset.php
#****************************************************************************
$trans["adminStaff_pwd_reset_Passwordreset"]   = "\$text = 'A senha foi resetada.';";

#****************************************************************************
#*  Translation text for page staff_pwd_reset_form.php
#****************************************************************************
$trans["adminStaff_pwd_reset_form_Resetheader"]   = "\$text = 'Resetar a senha do Volunt�rio:';";

#****************************************************************************
#*  Translation text for theme pages
#****************************************************************************
$trans["adminTheme_Return"]                 = "\$text = 'retornar a lista de temas';";
$trans["adminTheme_Theme"]                 = "\$text = 'Tema, ';";

#****************************************************************************
#*  Translation text for page theme_del.php
#****************************************************************************
$trans["adminTheme_Deleted"]                 = "\$text = ', foi apagado.';";
#****************************************************************************
#*  Translation text for page theme_del_confirm.php
#****************************************************************************
$trans["adminTheme_Deleteconfirm"]                 = "\$text = 'Voc� deseja realmente apagar este tema, ';";
#****************************************************************************
#*  Translation text for page theme_edit.php
#****************************************************************************
$trans["adminTheme_Updated"]                 = "\$text = ', foi atualizado.';";

#****************************************************************************
#*  Translation text for page theme_edit_form.php
#****************************************************************************
$trans["adminTheme_Preview"]                 = "\$text = 'Visualizar Tema';";

#****************************************************************************
#*  Translation text for page theme_list.php
#****************************************************************************
$trans["adminTheme_Changetheme"]                 = "\$text = 'Aleterar Tema Atual:';";
$trans["adminTheme_Choosetheme"]                 = "\$text = 'Escolher um Novo Tema:';";
$trans["adminTheme_Addnew"]                 = "\$text = 'Adicionar Novo Tema';";
$trans["adminTheme_themes"]                 = "\$text = 'Temas:';";
$trans["adminTheme_function"]                 = "\$text = 'Fun��o';";
$trans["adminTheme_Themename"]                 = "\$text = 'Nome do Tema';";
$trans["adminTheme_Usage"]                 = "\$text = 'Uso';";
$trans["adminTheme_Edit"]                 = "\$text = 'editar';";
$trans["adminTheme_Copy"]                 = "\$text = 'copiar';";
$trans["adminTheme_Del"]                 = "\$text = 'apagar';";
$trans["adminTheme_Inuse"]                 = "\$text = 'Em uso';";
$trans["adminTheme_Note"]                 = "\$text = '*Nota:';";
$trans["adminTheme_Notetext"]                 = "\$text = 'A fun��o Apagar n�o est� avaliado no que est� sendo usado.';";

#****************************************************************************
#*  Translation text for page theme_list.php
#****************************************************************************
$trans["adminTheme_Theme2"]                 = "\$text = 'Tema:';";
$trans["adminTheme_Tablebordercolor"]                 = "\$text = 'Cor da Borda da Tabela:';";
$trans["adminTheme_Errorcolor"]                 = "\$text = 'Cor do Erro:';";
$trans["adminTheme_Tableborderwidth"]                 = "\$text = 'Largura da borda da tabela:';";
$trans["adminTheme_Tablecellpadding"]                 = "\$text = 'Espa�amento das c�lulas:';";
$trans["adminTheme_Title"]                 = "\$text = 'T�tulo';";
$trans["adminTheme_Mainbody"]                 = "\$text = 'Corpo Principal';";
$trans["adminTheme_Navigation"]                 = "\$text = 'Navega��o';";
$trans["adminTheme_Tabs"]                 = "\$text = 'Tabs';";
$trans["adminTheme_Backgroundcolor"]                 = "\$text = 'Cor de Fundo:';";
$trans["adminTheme_Fontface"]                 = "\$text = 'Tipo de Fonte:';";
$trans["adminTheme_Fontsize"]                 = "\$text = 'Tamanho da Fonte:';";
$trans["adminTheme_Bold"]                 = "\$text = 'negrito';";
$trans["adminTheme_Fontcolor"]                 = "\$text = 'Cor da Fonte:';";
$trans["adminTheme_Linkcolor"]                 = "\$text = 'Cor do Link:';";
$trans["adminTheme_Align"]                 = "\$text = 'Alinhamento:';";
$trans["adminTheme_Right"]                 = "\$text = 'A Direita';";
$trans["adminTheme_Left"]                 = "\$text = 'A Esquerda';";
$trans["adminTheme_Center"]                 = "\$text = 'Centralizado';";
$trans["adminTheme_HeaderWording"]                 = "\$text = 'Editar';";


#****************************************************************************
#*  Translation text for page theme_new.php
#****************************************************************************
$trans["adminTheme_new_Added"]                 = "\$text = ', foi adicionado.';";

#****************************************************************************
#*  Translation text for page theme_new_form.php
#****************************************************************************

#****************************************************************************
#*  Translation text for page theme_preview.php
#****************************************************************************
$trans["adminTheme_preview_Themepreview"]                 = "\$text = 'Pr�via do Tema';";
$trans["adminTheme_preview_Librarytitle"]                 = "\$text = 'T�tulo da Biblioteca';";
$trans["adminTheme_preview_CloseWindow"]                 = "\$text = 'Fechar Janela';";
$trans["adminTheme_preview_Home"]                 = "\$text = 'Principal';";
$trans["adminTheme_preview_Circulation"]   = "\$text = 'Circula��o';";
$trans["adminTheme_preview_Cataloging"]    = "\$text = 'Catalogando';";
$trans["adminTheme_preview_Admin"]         = "\$text = 'Administrador';";
$trans["adminTheme_preview_Samplelink"]    = "\$text = 'a';";
$trans["adminTheme_preview_Thisstart"]     = "\$text = 'Esta � uma pr�via do ';";
$trans["adminTheme_preview_Thisend"]       = "\$text = 'tema.';";
$trans["adminTheme_preview_Samplelist"]    = "\$text = 'Lista de Amostras:';";
$trans["adminTheme_preview_Tableheading"]  = "\$text = 'Cabe�ote da tabela';";
$trans["adminTheme_preview_Sampledatarow1"]= "\$text = 'Dados de amostra da fileira 1';";
$trans["adminTheme_preview_Sampledatarow2"]= "\$text = 'Dados de amostra da fileira 2';";
$trans["adminTheme_preview_Sampledatarow3"]= "\$text = 'Dados de amostra da fileira 3';";
$trans["adminTheme_preview_Samplelink"]    = "\$text = 'link da amostra';";
$trans["adminTheme_preview_Sampleerror"]   = "\$text = 'erro da amostra';";
$trans["adminTheme_preview_Sampleinput"]   = "\$text = 'Inserir Amostra';";
$trans["adminTheme_preview_Samplebutton"]  = "\$text = 'Bot�o da Amostra';";
$trans["adminTheme_preview_Poweredby"]     = "\$text = 'Powered by OpenBiblio';";
$trans["adminTheme_preview_Copyright"]     = "\$text = 'Copyright &copy; 2002-2005 Dave Stevens';";
$trans["adminTheme_preview_underthe"]      = "\$text = 'under the';";
$trans["adminTheme_preview_GNU"]           = "\$text = 'GNU General Public License';";

#****************************************************************************
#*  Translation text for page mbr_classify_del.php
#****************************************************************************
$trans["Classification type, %desc%, has been deleted."]            = "\$text = 'Tipo de Classifica��o, %desc%, foi apagado';";
$trans["return to member classification list"]						= "\$text = 'retornar a lista de classifica��o de membro';";

#****************************************************************************
#*  Translation text for page mbr_classify_del_confirm.php
#****************************************************************************
$trans["Are you sure you want to delete classification '%desc%'?"] 	= "\$text = 'Voc� tem certeza que deseja apagar a classifica��o %desc%?';";

#****************************************************************************
#*  Translation text for page mbr_classify_edit.php
#****************************************************************************
$trans["Classification type, %desc%, has been updated."]			= "\$text = 'O tipo de classifica��o, %desc%, foi atualizado';";

#****************************************************************************
#*  Translation text for page mbr_classify_edit_form.php
#****************************************************************************
$trans["Edit Classification Type"]									= "\$text = 'Editar tipo de classifica��o';";
$trans["Description:"]												= "\$text = 'Descri��o:';";
$trans["Max. Fines:"]												= "\$text = 'Max. Fines:';";

#****************************************************************************
#*  Translation text for page mbr_classify_list.php
#****************************************************************************
$trans["edit"]														= "\$text = 'editar';";
$trans["del"]														= "\$text = 'apagar';";
$trans["Members"]													= "\$text = 'Membros';";
$trans["*Note:"]													= "\$text = 'Nota:';";
$trans["The delete function is only available on classifications that have a member count of zero.  If you wish to delete a classification with a member count greater than zero you will first need to change those members to another classification."]											= "\$text = 'A fun��o apagar em classifica��o somente estar� avaliado se o contador de membros for zero.<br> Se deseja apagar a classifica��o com o contador da bibliografia maior que zero, voc� deve primeiro alterar os membros para outra classifica��o.';";

#****************************************************************************
#*  Translation text for page mbr_classify_new.php
#****************************************************************************
$trans["Classification type, %desc%, has been added."]				= "\$text = 'O tipo de classifica��o, %desc%, foi adicionado';";

#****************************************************************************
#*  Translation text for page mbr_classify_new.php
#****************************************************************************
$trans["Add new classification type"]								= "\$text = 'Adicionar novo tipo de classifica��o';";

#****************************************************************************
#*  Translation text for page member_fields_del.php
#****************************************************************************
  $trans["return to member field list"]       		 			= "\$text = 'Retornar � lista de Campos de Membro';";
 $trans["Member field, %desc%, has been deleted."] 				= "\$text = 'O Campo de Membro, %desc%, foi apagado';";
 
#****************************************************************************
#*  Translation text for page member_fields_confirm.php
#****************************************************************************
  $trans["Are you sure you want to delete field '%desc%'?"] 	= "\$text = 'Voc� deseja realmente apagar o campo %desc%?';";

#****************************************************************************
#*  Translation text for page member_fields_edit.php
#****************************************************************************
  $trans["Member field, %desc%, has been updated."] 			= "\$text = 'O Campo de Membro, %desc%, foi atualizado';";
  
#****************************************************************************
#*  Translation text for page member_fields_edit_form.php
#****************************************************************************
  $trans["Edit Member Field"]									= "\$text = 'Editar Campos de Membro';";
  $trans["Code:"]												= "\$text = 'C�digo';";
  $trans["Description:"]										= "\$text = 'Descri��o';";
  
#****************************************************************************
#*  Translation text for page member_fields_edit_list.php
#****************************************************************************
  $trans["Add new custom field"]								= "\$text = 'Adicionar novo campo';";
  $trans["Custom Member FIelds"]								= "\$text = 'Administrar Campos de Membro';";
  $trans["Description"]											= "\$text = 'Descri��o';";
  $trans["function"]											= "\$text = 'fun��o';";
  $trans["Code"]												= "\$text = 'C�digo';";
 
#****************************************************************************
#*  Translation text for page member_fields_new.php
#****************************************************************************
  $trans["Member field, %desc%, has been added."]				= "\$text = 'Campo de membro, %desc%, foi adicionado';";
  $trans["Add new member classificaiton"]						= "\$text = 'Adicionar nova classifica��o de membro';";
  $trans["Member Classifications List"]							= "\$text = 'Lista de Classifica��o de Membros';";
  
#****************************************************************************
#*  Translation text for page member_fields_new_form.php
#****************************************************************************
  $trans["Add custom member field"]								= "\$text = 'Adicionar campo de membro';";
  
#****************************************************************************
#*  Translation text for page checkout_privs_edit.php
#****************************************************************************
  $trans["Privileges updated"]									= "\$text = 'Privil�gios atualizados';";
  
#****************************************************************************
#*  Translation text for page checkout_privs_edit.php
#****************************************************************************
  $trans["Edit Checkout Privileges"]							= "\$text = 'Editar Privil�gios de Empr�stimos';";
  $trans["Material Type:"]										= "\$text = 'Tipo de Material:';";
  $trans["Member Classification:"]								= "\$text = 'Classifica��o do Membro';";
  $trans["Checkout Limit:"]										= "\$text = 'Limite de Retirada:';";
  $trans["Renewal Limit:"]										= "\$text = 'Limite de Renova��o:';";
  
#****************************************************************************
#*  Translation text for page checkout_privs_edit.php
#****************************************************************************
  $trans["Checkout Privileges"]									= "\$text = 'Administrar Empr�stimos';";
  $trans["Material Type"]										= "\$text = 'Tipo de Material';";
  $trans["Member Classification"]								= "\$text = 'Classifica��o do Membro';";
  $trans["Checkout Limit"]										= "\$text = 'Limite de Retirada';";
  $trans["Renewal Limit"]										= "\$text = 'Limite de Renova��o';";
  $trans["Custom Copy Fields"]								    = "\$text = 'Campos de c�pias personalizado';";
  $trans["Copy Fields"]								            = "\$text = 'Campos de c�pias';";
  
  
?>

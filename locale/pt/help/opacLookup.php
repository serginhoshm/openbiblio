<h1>Visualisando C�digo de Barras</h1>
<br><br>
O link de Procurar para Empr�stimo, Devolu��o e Reserva abre uma janela popup id�ntica ao Online Public Access Catalog (OPAC). Na �gina de resultados para a busca, cada Barra de C�digos do Exemplar posuui um link adicional (Empr�stimo, Devolu��o e Reserva). Quando selecionado, o popup fechar� e retornar� com o c�digo de barras para a janela primaria, pronto para ser enviado.
<br><br>

Sub-se��es:
<ul>
  <li><a href="#exam">Exemplo: selecionando N�mero do C�digo de Barras a partir do Resultado da Procura</a></li>
  <li><a href="#seri">Reconhecendo Serial de exemplares em C�digo de Barras gerados automaticamente</a></li>
</ul>
<br><br>

<a name="exam">
O exemplo a seguir mostra o Resultado da Busca com os links para selecionar o N�mero do C�digo de Barras.</a>
No seu browser coloque o ponteiro do mouse sobre o link e uma pequena caixa de texto explicar� a a��o do link.
<br><br>

<!--**************************************************************************
    *  Printing result table EXAMPLE ALMOST COMPLETELY TRANSLATED BY $loc->getText 
    ************************************************************************** -->
<table class="primary">
  <tbody><tr>
    <th colspan="3" align="left" nowrap="yes" valign="top">
      <?php echo $loc->getText("biblioSearchResults"); ?>:
    </th>
  </tr>
  
  <tr>

    <td class="primary" rowspan="2" align="center" nowrap="true" valign="top">
      1.<br>
      <a href="#exam" title="<?php echo $loc->getText("biblioSearchDetail"); ?>">
      <img src="../images/book.gif" alt="book" align="bottom" border="0" height="20" width="20"></a>
    </td>
    <td class="primary" colspan="2" valign="top">
      <table class="primary" width="100%">
        <tbody><tr>

          <td class="noborder" valign="top" width="1%"><b><?php echo $loc->getText("biblioSearchTitle"); ?>:</b></td>
          <td class="noborder" colspan="3"><a href="#exam" title="<?php echo $loc->getText("biblioSearchDetail"); ?>">Ribsy</a></td>
        </tr>
        <tr>
          <td class="noborder" valign="top"><b><?php echo $loc->getText("biblioSearchAuthor"); ?>:</b></td>
          <td class="noborder" colspan="3">Cleary,Beverly</td>
        </tr>

        <tr>
          <td class="noborder" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchMaterial"); ?>:</b></font></td>
          <td class="noborder" colspan="3"><font class="small">book</font></td>
        </tr>
        <tr>
          <td class="noborder" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchCollection"); ?>:</b></font></td>
          <td class="noborder" colspan="3"><font class="small">Juvenile Fiction</font></td>

        </tr>
        <tr>
          <td class="noborder" nowrap="yes" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchCall"); ?>:</b></font></td>
          <td class="noborder" colspan="3"><font class="small">JF Cle </font></td>
        </tr>
      </tbody></table>
    </td>
  </tr>

        <tr>
        <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyBCode"); ?></b>: 000051          
            <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Chk"); ?>"><?php echo $loc->getText("biblioSearchOutIn"); ?></a> | <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Hold"); ?>"><?php echo $loc->getText("biblioSearchHold"); ?></a>
                  </font></td>
        <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyStatus"); ?></b>: devolvido</font></td>
      </tr>

    
  <tr>
    <td class="primary" rowspan="2" align="center" nowrap="true" valign="top">
      2.<br>
      <a href="#exam" title="<?php echo $loc->getText("biblioSearchDetail"); ?>">
      <img src="../images/book.gif" alt="book" align="bottom" border="0" height="20" width="20"></a>
    </td>
    <td class="primary" colspan="2" valign="top">
      <table class="primary" width="100%">

        <tbody><tr>
          <td class="noborder" valign="top" width="1%"><b><?php echo $loc->getText("biblioSearchTitle"); ?>:</b></td>
          <td class="noborder" colspan="3"><a href="#exam" title="<?php echo $loc->getText("biblioSearchDetail"); ?>">Henry Huggins</a></td>
        </tr>
        <tr>
          <td class="noborder" valign="top"><b><?php echo $loc->getText("biblioSearchAuthor"); ?>:</b></td>
          <td class="noborder" colspan="3">Cleary,Beverly</td>

        </tr>
        <tr>
          <td class="noborder" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchMaterial"); ?>:</b></font></td>
          <td class="noborder" colspan="3"><font class="small">book</font></td>
        </tr>
        <tr>
          <td class="noborder" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchCollection"); ?>:</b></font></td>

          <td class="noborder" colspan="3"><font class="small">Juvenile Fiction</font></td>
        </tr>
        <tr>
          <td class="noborder" nowrap="yes" valign="top"><font class="small"><b><?php echo $loc->getText("biblioSearchCall"); ?>:</b></font></td>
          <td class="noborder" colspan="3"><font class="small">JF Cle </font></td>
        </tr>
      </tbody></table>

    </td>
  </tr>
        <tr>
        <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyBCode"); ?></b>: 000061          
            <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Chk"); ?>"><?php echo $loc->getText("biblioSearchOutIn"); ?></a> | <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Hold"); ?>"><?php echo $loc->getText("biblioSearchHold"); ?></a>
                  </font></td>
        <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyStatus"); ?></b>: devolvido</font></td>

      </tr>
              <tr>
            <td class="primary" align="center" nowrap="true" valign="top"><font class="small">
              3.
            </font></td>
            <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyBCode"); ?></b>: 000062              
                <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Chk"); ?>"><?php echo $loc->getText("biblioSearchOutIn"); ?></a> | <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Hold"); ?>"><?php echo $loc->getText("biblioSearchHold"); ?></a>

                          </font></td>
            <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyStatus"); ?></b>: devolvido</font></td>
          </tr>
                    <tr>
            <td class="primary" align="center" nowrap="true" valign="top"><font class="small">
              4.
            </font></td>
            <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyBCode"); ?></b>: 000063              
                <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Chk"); ?>"><?php echo $loc->getText("biblioSearchOutIn"); ?></a> | <a href="#exam" title="<?php echo $loc->getText("biblioSearchBCode2Hold"); ?>"><?php echo $loc->getText("biblioSearchHold"); ?></a>

                          </font></td>
            <td class="primary" nowrap="true"><font class="small"><b><?php echo $loc->getText("biblioSearchCopyStatus"); ?></b>: devolvido</font></td>
          </tr>
            </tbody>
</table><br>

<a name="seri">
No exemplo do Resultado de Procura de Visualiza��o do C�digo de Barras acima, somente o(s) �ltimo(s) digito(s) do c�digo de barras da bibliografia s�o diferentes.</a>
Isto � porque eles est�o atribu�dos como 
<a href="../shared/help.php?page=biblioCopyEdit#seri">N�mero de Serial de exemplares integrados com o N�mero do C�digo de Barras</a>
quando foram adicionados.
<br>
Isto e mais sobre adicionar exemplares est�o explicados na se��o <a href="../shared/help.php?page=biblioCopyEdit">Novo Exemplar e Editar Exemplar</a> da p�gina de ajuda.
<br><br>
Note que os n�meros do Resultado da Procura, na coluna da esquerda, � independente do N�mero de Serial de exemplares integrados com o N�mero do C�digo de Barras.
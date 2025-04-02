<h1>Novo Exemplar e Editar Exemplar:</h1>
<center><font class="error">N�o edite exemplares que estajam sob empr�stimo
(<a href="http://sourceforge.net/tracker/index.php?func=detail&aid=1162251&group_id=50071&atid=458474">
Bug 1162251</a>).</font>
</center>
<br><br>

Sub-se��es:
<ul>
  <li><a href="#desc">Descri��o de Campos</a></li>
  <li><a href="#barc">Inserir um n�mero de c�digo de barras atrav�z de uma estrutura exeterna de numera��o</a></li>
  <li><a href="#auto">N�mero de C�digo de Barras - Gerar automaticamente</a></li>
  <li><a href="#seri">N�mero de Serial de exemplares integrados no N�mero do C�digo de Barras</a></li>
</ul>
<br><br>

<a name="desc">
A tabela seguinte informa as descri��es para cada campo localizado na p�ginas Novo Exemplar e Editar Exemplar.</a>
<br><br>

<table class="primary">
  <tr><th>Campo</th><th>Descri��o</th></tr>
  <tr>
    <td class="primary" valign="top">N�mero do C�digo de Barras</td>
    <td class="primary" valign="top">O c�digo identifica��o �nico de um exemplar deve ser alfab�tico e/ou num�rico, no m�ximo 20 caract�res. Este campo � requerido para a identificar um exemplar para a Circula��o (Empr�stimo, Devolu��o, Reserva).
<br>Veja em: 
<a href="../shared/help.php?page=barcodes">Entendendo o C�digo de Barras</a>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">Descri��o</td>
    <td class="primary" valign="top">Pequeno texto descrevendo as caracter�stcas para o exemplar.</td>
  </tr>
  <tr>
    <td class="primary" valign="top">Status</td>
    <td class="primary" valign="top"><b>Somente em Editar Exemplar</b>.
<br>Veja em: 
<a href="../shared/help.php?page=status">Entendendo as mudan�as de status da bibliografia</a>
    </td>
  </tr>
</table>
<br><br>

<a name="barc">Inserir um n�mero de c�digo de barras atrav�z de uma estrutura exeterna de numera��o</a>:
<ul>
  <li>Insira o N�mero do C�digo de Barras manualmente, ou use um scanner de c�digo de barras se o exemplar ja estiver etiquetado,</li>
  <li>Enviar (Gerar Automaticamente n�o pode estar selecionado).</li>
</ul>
<br>

<a name="auto">
Enviando um novo exemplar com a op��o <input name="autobarco" type="checkbox" checked> Gerar automaticamente</a> selecionado para o campo do N�mero do C�digo de Barras, o Openbiblio tentar� atribuir um �nico N�mero do C�digo de Barras automaticamente, seguindo as regras internas da estrutura de numera��o:
<ul>
  <li>A primeira parte � calculado a partir do <i>bibid</i> pelo qual a bibliografia � conhecida internamente pelo OpenBiblio, incluindo zeros. O tamanho padr�o � de 5 digitos, mas pode ser alterado atrav�z do valor de $nzeros em biblio_copy_new.php </li>
  <li>A �ltima parte � igual aa parte interna do <i>copyid</i> do g�nero.</li>
</ul>
<br><br>

<a name="seri">
N�meros de Serial de exemplares integrados no N�mero do C�digo de Barras</a> facilita ao inserir as informa��es de um exemplar a partir de um simples cart�o de arquivo quando cada membro n�o estiver atribu�do, somente n�meros de serial para multiplas c�pias de um t�tulo.
<br>
A p�gina Visualisando C�digo de Barras possui a informa��o 
<a href="../shared/help.php?page=opacLookup#seri">Reconhecendo Seriais de exemplares em C�digo de Barras gerados automaticamente</a>.
<br>
Quando adicionar exemplares marcados com n�meros de serial � Bibliografia:
<ul>
  <li>Selecionar Gerar automatiamente,</li>
  <li>enviar novos exemplares at� o N�mero de Serial do Exemplar corresponder com �ltimo(s) digito(s) do N�mero do C�digo de Barras gerado autmaticamente,</li>
  <li>apagar permanentemente exemplares n�o avaliados e editar o status para os outros.</li>
</ul>
<br><br>

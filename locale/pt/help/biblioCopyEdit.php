<h1>Novo Exemplar e Editar Exemplar:</h1>
<center><font class="error">Não edite exemplares que estajam sob empréstimo
(<a href="http://sourceforge.net/tracker/index.php?func=detail&aid=1162251&group_id=50071&atid=458474">
Bug 1162251</a>).</font>
</center>
<br><br>

Sub-seções:
<ul>
  <li><a href="#desc">Descrição de Campos</a></li>
  <li><a href="#barc">Inserir um número de código de barras atravéz de uma estrutura exeterna de numeração</a></li>
  <li><a href="#auto">Número de Código de Barras - Gerar automaticamente</a></li>
  <li><a href="#seri">Número de Serial de exemplares integrados no Número do Código de Barras</a></li>
</ul>
<br><br>

<a name="desc">
A tabela seguinte informa as descrições para cada campo localizado na páginas Novo Exemplar e Editar Exemplar.</a>
<br><br>

<table class="primary">
  <tr><th>Campo</th><th>Descrição</th></tr>
  <tr>
    <td class="primary" valign="top">Número do Código de Barras</td>
    <td class="primary" valign="top">O código identificação único de um exemplar deve ser alfabético e/ou numérico, no máximo 20 caractéres. Este campo é requerido para a identificar um exemplar para a Circulação (Empréstimo, Devolução, Reserva).
<br>Veja em: 
<a href="../shared/help.php?page=barcodes">Entendendo o Código de Barras</a>
    </td>
  </tr>
  <tr>
    <td class="primary" valign="top">Descrição</td>
    <td class="primary" valign="top">Pequeno texto descrevendo as característcas para o exemplar.</td>
  </tr>
  <tr>
    <td class="primary" valign="top">Status</td>
    <td class="primary" valign="top"><b>Somente em Editar Exemplar</b>.
<br>Veja em: 
<a href="../shared/help.php?page=status">Entendendo as mudanças de status da bibliografia</a>
    </td>
  </tr>
</table>
<br><br>

<a name="barc">Inserir um número de código de barras atravéz de uma estrutura exeterna de numeração</a>:
<ul>
  <li>Insira o Número do Código de Barras manualmente, ou use um scanner de código de barras se o exemplar ja estiver etiquetado,</li>
  <li>Enviar (Gerar Automaticamente não pode estar selecionado).</li>
</ul>
<br>

<a name="auto">
Enviando um novo exemplar com a opção <input name="autobarco" type="checkbox" checked> Gerar automaticamente</a> selecionado para o campo do Número do Código de Barras, o Openbiblio tentará atribuir um único Número do Código de Barras automaticamente, seguindo as regras internas da estrutura de numeração:
<ul>
  <li>A primeira parte é calculado a partir do <i>bibid</i> pelo qual a bibliografia é conhecida internamente pelo OpenBiblio, incluindo zeros. O tamanho padrão é de 5 digitos, mas pode ser alterado atravéz do valor de $nzeros em biblio_copy_new.php </li>
  <li>A última parte é igual aa parte interna do <i>copyid</i> do gênero.</li>
</ul>
<br><br>

<a name="seri">
Números de Serial de exemplares integrados no Número do Código de Barras</a> facilita ao inserir as informações de um exemplar a partir de um simples cartão de arquivo quando cada membro não estiver atribuído, somente números de serial para multiplas cópias de um título.
<br>
A página Visualisando Código de Barras possui a informação 
<a href="../shared/help.php?page=opacLookup#seri">Reconhecendo Seriais de exemplares em Código de Barras gerados automaticamente</a>.
<br>
Quando adicionar exemplares marcados com números de serial à Bibliografia:
<ul>
  <li>Selecionar Gerar automatiamente,</li>
  <li>enviar novos exemplares até o Número de Serial do Exemplar corresponder com último(s) digito(s) do Número do Código de Barras gerado autmaticamente,</li>
  <li>apagar permanentemente exemplares não avaliados e editar o status para os outros.</li>
</ul>
<br><br>

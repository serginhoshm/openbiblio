<h1>Entendendo o c�digo de barras:</h1>
<br><br>

Sub-se��es:
<ul>
  <li><a href="#limi">Limita��es para c�digo de membros do OpenBiblio</a></li>
  <li><a href="#memb">C�digo de barra dos membros</a></li>
  <li><a href="#copy">C�digo de barras de exemplares da Bibliografia</a></li>
  <li><a href="#scan">Usando um scanner de c�digo de barras</a></li>
  <li><a href="#labe">Imprimindo c�digo de barras e etiquetando exemplares</a></li>
  <li><a href="#link">Alguns links a entender melhor o c�digo de barras</a></li>
</ul>
<br><br>


<a name="limi">Limita��es para c�digo de membros do OpenBiblio </a>:
<ul>
  <li>Os c�digos devem possuir caract�res alfab�ticos e/ou num�ricos: a-z A-Z 0-9<br>
Embora muitas simbologias de c�digos (tipos de c�digo) possam ser interpretadas, o OpenBiblio n�o aceitar� caract�res como - $ % SPACE . / +
  </li>
  <li>Ap�s enviado, os caract�res alfab�ticos ser�o convertidos to lowercase.</li>
  <li>N�mero m�ximo de carat�res para um c�digo � 20</li>
</ul>
Quando escolher uma estrutura de n�mero, deve-se ter consci�ncia que h� limita��es. Tamb�m deve-se ter conhecimento do uso da simbologia que voc� vai imprimir para n�o haver conflitoscom as limita��es.
<br><br><br>
  
<a name="memb">C�digo de barras dos Membros</a>:
<br>
Para ter uma efici�ncia no desempenho de empr�stimo, especialmente usando um scanner de c�digo de barras, use uma estrutura de numera��o utilizando zeros no come�o para o c�digo de barras de membros. Se todo membro tiver um c�digo de barras com um n�mero fixo de digitos, o OpenBiblio ser� for�ado a mostrar a p�gina Informa��es do Membro ap�s scanear ou digitar o c�digo de barras do membro corretamente. Isto permitir� subsequentemente que seja scaneado o c�digo de barras de um exemplar para retirada.

<br>
Se o c�digo de barras, tanto scaneado como digitado, n�o possuir um n�mero de d�gitos fixo, a p�gina de Resultado de Procura apare�er�, mesmo que o c�digo de barras dos membros seja �nico. A raz�o para isto se d� pela Procura de Membros por N�mero do Cart�o n�o procurar com a combina��o exata, assim como o Procurar Membros pelo Sobrenome, ele pega N�mero do Cart�o como Procurar Frase truncado.
<br><br><br>

<a name="copy">C�digo de barras de exemplares da Bibliografia</a>:
<br>
Se sua biblioteca estiver com os exemplares etiquetados com os c�digos de barras, tenha certeza que a estrutura de numera��o n�o entre em conflito com as
<a href="#limi">Limita��es para o c�digo de baras do OpenBiblio</a>.
<br>
Se a sua biblioteca nunca atribuiu uma numera��o �nica para os exemplares, voc� deve decidir a estrutura de numera��o mais vi�vel ou usar
<a href="../shared/help.php?page=biblioCopyEdit#auto">N�meros de C�digos de Barra - Gerar Automaticamente</a>.
Isto possui uma propriedade �til quando alternado para um simples arquivo de cart�o: 
<a href="../shared/help.php?page=biblioCopyEdit#seri">N�mero de Serial de exemplares integrados no N�mero do C�digo de Barras</a> 
facilitam a inser��o de informa��es de um exemplar a partir de um simples arquivo de cart�o quando cada n�mero n�o estiver designado. 
<br><br><br>

<a name="scan">Usando um scanner de c�digo de barras:</a>
<br>
Qualquer c�digo de barras scaneado que emula o teclado � suportado pelo OpenBiblio. Isto inclui Scaners com USB e scaners que possuem conex�o entre o computador e o teclado.<br>
A maioria dos scaners de c�digo de barras podem ser programados para automaticamente adicionar um proceso de retorno (o mesmo que pressionar ENTER no teclado) a cada scan. Assim, o OpenBiblio poder� processar seus pedidos a medida do poss�vel assim como scanear o item.
<br><br><br>

<a name="labe">
Imprimindo c�digo de barras e etiquetando exemplares</a> produzem uma circula��o eficiente, especialmente utlizando os c�digos e barras atuais e um dispostitivo de scaneamento de c�digo de barras.
<ul>
  <li>Etiquetas contendo textos e n�meros podem ser impressos usando um relat�rio em PDF: Aba de relat�rio, Imprimir Etiquetas.</li>
  <li>Os c�digos de barras atuais podem ser prozidos fora do OpenBiblio, por exemplo usando o Word e uma fonte com c�digo de barras.</li>
</ul>
<br>

<a name="link">Alguns links a entender melhor o c�digo de barras</a>
<br>
<a href="javascript:popSecondaryLarge('http://en.wikipedia.org/wiki/Barcode')">Wikipedia: barcode</a>
<br>
<a href="javascript:popSecondaryLarge('http://www.barcodesymbols.com/')">http://www.barcodesymbols.com/</a>
<br>
<a href="javascript:popSecondaryLarge('http://www.barcodefaq.com/')">http://www.barcodefaq.com/</a>

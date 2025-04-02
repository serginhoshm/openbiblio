<h1>Entendendo o código de barras:</h1>
<br><br>

Sub-seções:
<ul>
  <li><a href="#limi">Limitações para código de membros do OpenBiblio</a></li>
  <li><a href="#memb">Código de barra dos membros</a></li>
  <li><a href="#copy">Código de barras de exemplares da Bibliografia</a></li>
  <li><a href="#scan">Usando um scanner de código de barras</a></li>
  <li><a href="#labe">Imprimindo código de barras e etiquetando exemplares</a></li>
  <li><a href="#link">Alguns links a entender melhor o código de barras</a></li>
</ul>
<br><br>


<a name="limi">Limitações para código de membros do OpenBiblio </a>:
<ul>
  <li>Os códigos devem possuir caractéres alfabéticos e/ou numéricos: a-z A-Z 0-9<br>
Embora muitas simbologias de códigos (tipos de código) possam ser interpretadas, o OpenBiblio não aceitará caractéres como - $ % SPACE . / +
  </li>
  <li>Após enviado, os caractéres alfabéticos serão convertidos to lowercase.</li>
  <li>Número máximo de caratéres para um código é 20</li>
</ul>
Quando escolher uma estrutura de número, deve-se ter consciência que há limitações. Também deve-se ter conhecimento do uso da simbologia que você vai imprimir para não haver conflitoscom as limitações.
<br><br><br>
  
<a name="memb">Código de barras dos Membros</a>:
<br>
Para ter uma eficiência no desempenho de empréstimo, especialmente usando um scanner de código de barras, use uma estrutura de numeração utilizando zeros no começo para o código de barras de membros. Se todo membro tiver um código de barras com um número fixo de digitos, o OpenBiblio será forçado a mostrar a página Informações do Membro após scanear ou digitar o código de barras do membro corretamente. Isto permitirá subsequentemente que seja scaneado o código de barras de um exemplar para retirada.

<br>
Se o código de barras, tanto scaneado como digitado, não possuir um número de dígitos fixo, a página de Resultado de Procura apareçerá, mesmo que o código de barras dos membros seja único. A razão para isto se dá pela Procura de Membros por Número do Cartão não procurar com a combinação exata, assim como o Procurar Membros pelo Sobrenome, ele pega Número do Cartão como Procurar Frase truncado.
<br><br><br>

<a name="copy">Código de barras de exemplares da Bibliografia</a>:
<br>
Se sua biblioteca estiver com os exemplares etiquetados com os códigos de barras, tenha certeza que a estrutura de numeração não entre em conflito com as
<a href="#limi">Limitações para o código de baras do OpenBiblio</a>.
<br>
Se a sua biblioteca nunca atribuiu uma numeração única para os exemplares, você deve decidir a estrutura de numeração mais viável ou usar
<a href="../shared/help.php?page=biblioCopyEdit#auto">Números de Códigos de Barra - Gerar Automaticamente</a>.
Isto possui uma propriedade útil quando alternado para um simples arquivo de cartão: 
<a href="../shared/help.php?page=biblioCopyEdit#seri">Número de Serial de exemplares integrados no Número do Código de Barras</a> 
facilitam a inserção de informações de um exemplar a partir de um simples arquivo de cartão quando cada número não estiver designado. 
<br><br><br>

<a name="scan">Usando um scanner de código de barras:</a>
<br>
Qualquer código de barras scaneado que emula o teclado é suportado pelo OpenBiblio. Isto inclui Scaners com USB e scaners que possuem conexão entre o computador e o teclado.<br>
A maioria dos scaners de código de barras podem ser programados para automaticamente adicionar um proceso de retorno (o mesmo que pressionar ENTER no teclado) a cada scan. Assim, o OpenBiblio poderá processar seus pedidos a medida do possível assim como scanear o item.
<br><br><br>

<a name="labe">
Imprimindo código de barras e etiquetando exemplares</a> produzem uma circulação eficiente, especialmente utlizando os códigos e barras atuais e um dispostitivo de scaneamento de código de barras.
<ul>
  <li>Etiquetas contendo textos e números podem ser impressos usando um relatório em PDF: Aba de relatório, Imprimir Etiquetas.</li>
  <li>Os códigos de barras atuais podem ser prozidos fora do OpenBiblio, por exemplo usando o Word e uma fonte com código de barras.</li>
</ul>
<br>

<a name="link">Alguns links a entender melhor o código de barras</a>
<br>
<a href="javascript:popSecondaryLarge('http://en.wikipedia.org/wiki/Barcode')">Wikipedia: barcode</a>
<br>
<a href="javascript:popSecondaryLarge('http://www.barcodesymbols.com/')">http://www.barcodesymbols.com/</a>
<br>
<a href="javascript:popSecondaryLarge('http://www.barcodefaq.com/')">http://www.barcodefaq.com/</a>

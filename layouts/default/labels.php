<?php
/* This file is part of a copyrighted work; it is distributed with NO WARRANTY.
 * See the file COPYRIGHT.html for more details.
 */

require_once('../classes/Lay.php');

class Layout_labels {
  var $p;

  function init($params) {
    $this->p = $params;
  }
  function render($rpt) {
    $lay = new Lay;
      $lay->container('Lines', array(
        'margin-top'=>'0.8in', 
        'margin-left'=>'0mm', 'margin-right'=>'0.0in'
      ));
        $lay->container('Columns');
          list(, $skip) = $this->p->getFirst('skip');
          for ($i = 0; $i < $skip; $i++) {
            $lay->container('Column', array(
              'height'=>'85in', 'width'=>'4in',
            ));
            $lay->close();
          }
		  $cont=$skip;
		  $marginleft='20mm';
		  
          while ($row = $rpt->each()) {
			$cont++;
            $lay->container('Line', array(
              //'height'=>'1in', 'width'=>'3.8333in',
			    'height'=>'1in', 'width'=>'4in',
              //'y-align'=>'center',
            ));
			
			$lay->container('Column', array('x-align'=>'center', 'margin-left'=>$marginleft));
                $lay->pushFont('Helvetica', 11);
                  $lay->text($row['callno']);
                $lay->popFont();
              $lay->close();
            $lay->container('Column', array('x-align'=>'center', 'margin-left'=>'20mm'));
                $lay->pushFont('Code39JK', 28);
                  $lay->text('*'.strtoupper($row['barcode_nmbr']).'*');
                $lay->popFont();
				$lay->pushFont('Courier', 10);
                  $lay->text(strtoupper($row['barcode_nmbr']));
                $lay->popFont();
              $lay->close();
            $lay->close();
			if($cont == 10){
				$marginleft = '30mm';
			}
			if($cont==20){
				$cont=0;
				$marginleft='20mm';
			}
          }
		  
        $lay->close();
      $lay->close();
    $lay->close();
  }
}

?>

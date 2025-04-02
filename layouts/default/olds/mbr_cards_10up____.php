<?php
/* This file is part of a copyrighted work; it is distributed with NO WARRANTY.
 * See the file COPYRIGHT.html for more details.
 */

// Business Card Letter 2x5 (Avery 5371, etc.)

require_once('../classes/Lay.php');

date_default_timezone_set('America/Sao_Paulo');

class Layout_mbr_cards_10up {
  var $p;
 /* function paramDefs() {
    return array(
      array('string', 'skip', array('title'=>'Pular Carteiras', 'default'=>'0')),
    );
  }*/
  function init($params) {
    $this->p = $params;
  }
  
  function render($rpt) {
	
    $lay = new Lay;
      $lay->container('Columns', array(
        'margin-top'=>'0.5in', 'margin-bottom'=>'0.0in',
        'margin-left'=>'2.40in', 'margin-right'=>'0.75in',//'margin-left'=>'0.10in'
      ));
            $margin = 0;
			$skip = 0;
			$linha = 1;
			
            while ($row = $rpt->each()) {

            $lay->container('Line', array('margin-top'=>'1.1in'));
			  $lay->container('Column', array('margin-left'=>'0.57in', 'width'=>'3.0in', 'height'=>'1.13in'));//'margin-left'=>'0.57in'
                    $lay->pushFont('Helvetica', 10);
                      $lay->container('TextLines');
					  if(strlen($row['first_name'].' '.$row['last_name']) > 25){
						  $lay->text(strtoupper($row['first_name'].' '.substr($row['last_name'], 0, 1).'.'));
					  }else{
                        $lay->text(strtoupper($row['first_name'].' '.$row['last_name']));
					  }
                      $lay->close();
					  
					 $lay->container('Line', array('height'=>'0.06in','width'=>'0.01in'));
					 $lay->close();
					 
                      $lay->container('TextLines', array('margin-left'=>'0.81in'));//'margin-left'=>'0.81in'
                        $lay->text($row['data']);
                      $lay->close();
					  
					  $lay->container('Line', array('height'=>'0.06in','width'=>'0.01in'));
					  $lay->close();
					  
					  $lay->container('TextLines', array('margin-left'=>'0.17in'));//'margin-left'=>'0.17in'
                        $lay->text(date('d/m/Y'));
                      $lay->close();
					  
                    //$lay->popFont();
					 $lay->close();
                    $lay->container('Column', array('margin-left'=>'2.0in', 'width'=>'4.0in', 'x-align'=>'center', 'margin-top'=>'0.0in'));
				  	$lay->pushFont('Courier', 10);
                      $lay->text(strtoupper($row['barcode_nmbr']));
                    $lay->popFont();
					//$lay->pushFont('Code39JK', 36);
                      $lay->text('*'.strtoupper($row['barcode_nmbr']).'*');
                    $lay->close();
			   $lay->close();
			   
			   if($linha == 6){
				   $margintop=0;
				   $linha = 1;
			   }
            }
			 
          $lay->close();
      
      $lay->close();
	  
    $lay->close();
	
  }
}

?>

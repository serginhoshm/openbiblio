<?php
/* This file is part of a copyrighted work; it is distributed with NO WARRANTY.
 * See the file COPYRIGHT.html for more details.
 */

// Business Card Letter 2x5 (Avery 5371, etc.)

require_once('../classes/Lay.php');

date_default_timezone_set('America/Sao_Paulo');

class Layout_mbr_cards_10up {
  var $p;
  function paramDefs() {
    return array(
      array('string', 'skip', array('title'=>'Pular Carteiras', 'default'=>'0')),
    );
  }
  function init($params) {
    $this->p = $params;
  }
  
  function render($rpt) {
	
    $lay = new Lay;
      $lay->container('Columns', array(
        'margin-top'=>'0.0in', 'margin-bottom'=>'0in',
        'margin-left'=>'0.10in', 'margin-right'=>'0.75in',
      ));
            $margin = 0;
			$skip = 0;
			//list(, $skip) = $this->p->getFirst('skip');
			if($skip == '0'){
				$margin = '0in';
				$linha = 1;
			}
			if($skip == '1'){
				$linha = 2;
				$margin = '3.1in';
			}
			if($skip == '2'){
				$linha = 3;
				$margin = '5.6in';
			}
			if($skip == '3'){
				$linha = 4;
				$margin = '7.10in';
			}
			if($skip == '4'){
				$linha = 5;
				$margin = '10.4in';
			}
			
            while ($row = $rpt->each()) {

					if($linha==1){
						$margintop = '1.31in';
					}
					if($linha==2){
						$margintop = '1.75in';
					}
					if($linha==3){
						$margintop = '1.77in';
					}
					if($linha==4){
						$margintop = '1.75in';
					}
					if($linha==5){
						$margintop = '1.79in';
					}
				$linha++;
            $lay->container('Line', array('margin-top'=>$margintop));
			  $lay->container('Column', array('margin-left'=>'0.57in', 'width'=>'3.0in'));
                    $lay->pushFont('Helvetica', 8);
                      $lay->container('TextLines');
					  if(strlen($row['first_name'].' '.$row['last_name']) > 25){
						  $lay->text(strtoupper($row['first_name'].' '.substr($row['last_name'], 0, 1).'.'));
					  }else{
                        $lay->text(strtoupper($row['first_name'].' '.$row['last_name']));
					  }
                      $lay->close();
					  
					 $lay->container('Line', array('height'=>'0.09in','width'=>'0.01in'));
					 $lay->close();
					 
                      $lay->container('TextLines', array('margin-left'=>'0.81in'));
                        $lay->text($row['data']);
                      $lay->close();
					  
					  $lay->container('Line', array('height'=>'0.05in','width'=>'0.01in'));
					  $lay->close();
					  
					  $lay->container('TextLines', array('margin-left'=>'0.17in'));
                        $lay->text(date('d/m/Y'));
                      $lay->close();
					  
                    //$lay->popFont();
					 $lay->close();
                    $lay->container('Column', array('margin-left'=>'2.0in', 'width'=>'4.0in', 'x-align'=>'center', 'margin-top'=>'0.0in'));
				  	$lay->pushFont('Courier', 8);
                      $lay->text(strtoupper($row['barcode_nmbr']));
                    $lay->popFont();
					$lay->pushFont('Code39JK', 34);
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

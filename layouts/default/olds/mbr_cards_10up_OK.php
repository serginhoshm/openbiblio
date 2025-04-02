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
        'margin-top'=>'0.0in', 'margin-bottom'=>'0in',
        'margin-left'=>'0.10in', 'margin-right'=>'0.75in',//'margin-left'=>'0.10in'
      ));
            $margin = 0;
			$skip = 0;
			$linha = 1;
			
            while ($row = $rpt->each()) {

					if($linha==1){
						$margintop = '1.40in';//$margintop = '1.44in';		//33 ficou certo para uma folha com 5 etiquetas				
					}
					if($linha==2){
						$margintop = '1.76in';//$margintop = '1.75in';
					}
					if($linha==3){
						$margintop = '1.75in';//$margintop = '1.77in';
					}
					if($linha==4){
						$margintop = '1.79in';//$margintop = '1.75in';
					}
					if($linha==5){
						$margintop = '1.70in';//$margintop = '1.79in';//no dia testar o último aluno
					}
				$linha++;
            $lay->container('Line', array('margin-top'=>$margintop));
			  $lay->container('Column', array('margin-left'=>'0.57in', 'width'=>'3.0in'));//'margin-left'=>'0.57in'
                    $lay->pushFont('Helvetica', 8);
                      $lay->container('TextLines');
					  if(strlen($row['first_name'].' '.$row['last_name']) > 25){
						  $lay->text(strtoupper($row['first_name'].' '.substr($row['last_name'], 0, 1).'.'));
					  }else{
                        $lay->text(strtoupper($row['first_name'].' '.$row['last_name']));
					  }
                      $lay->close();
					  
					 $lay->container('Line', array('height'=>'0.08in','width'=>'0.01in'));
					 $lay->close();
					 
                      $lay->container('TextLines', array('margin-left'=>'0.81in'));//'margin-left'=>'0.81in'
                        $lay->text($row['data']);
                      $lay->close();
					  
					  $lay->container('Line', array('height'=>'0.07in','width'=>'0.01in'));
					  $lay->close();
					  
					  $lay->container('TextLines', array('margin-left'=>'0.17in'));//'margin-left'=>'0.17in'
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

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
        'margin-top'=>'0in', 'margin-bottom'=>'0in',
        'margin-left'=>'0.20in', 'margin-right'=>'0.0in',//'margin-left'=>'0.10in'
      ));
         
            while ($row = $rpt->each()) {

            $lay->container('Line', array(
			 'height'=> '2.3in', 'width'=>'8in'));
			
			  $lay->container('Column', array('margin-left'=>'0.85in', 'width'=>'4in', 'margin-top'=>'1.45in'));//'margin-left'=>'0.57in' 'margin-top'=>'1.6in'
                    $lay->pushFont('Helvetica', 8);//10
                      $lay->container('TextLines');
					  if(strlen($row['first_name'].' '.$row['last_name']) > 20){
						  $lay->text(strtoupper($row['first_name'].' '.substr($row['last_name'], 0, 1).'.'));
					  }else{
                        $lay->text(strtoupper($row['first_name'].' '.$row['last_name']));
					  }
                      $lay->close();
					  $lay->container('Line', array('height'=>'0.05in','width'=>'0.01in'));
					  $lay->close();
                      $lay->container('TextLines', array('margin-left'=>'0.81in'));//'margin-left'=>'0.81in'
                        $lay->text($row['data']);
                      $lay->close();
					  $lay->container('Line', array('height'=>'0.05in','width'=>'0.01in'));
					 $lay->close();
					  $lay->container('TextLines', array('margin-left'=>'0.17in'));//'margin-left'=>'0.17in'
                        $lay->text(date('d/m/Y'));
                      $lay->close();
				
					 $lay->close();
					 
					 $lay->container('Column', array( 'x-align'=>'center', 'margin-left'=>'1in',  'width'=>'4.0in', 'margin-top'=>'1.3in'));//'margin-top'=>'1.6in'
                    //$lay->container('TextLines', array('margin-left'=>'0.81in'));//'margin-left'=>'0.81in'
				  	$lay->pushFont('Courier', 10);
                      $lay->text(strtoupper($row['barcode_nmbr']));
                    $lay->popFont();
					$lay->pushFont('Code39JK', 34);
                      $lay->text('*'.strtoupper($row['barcode_nmbr']).'*');
                    $lay->close();
			  
			   $lay->close();
            }
			 
         
       $lay->close();
   
	  
    $lay->close();
	
  }
}

?>

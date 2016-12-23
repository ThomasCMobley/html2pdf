<?php
use Vpm3\Html2Pdf\Html2Pdf; 
 
class HtmlTest extends PHPUnit_Framework_TestCase {
 
  public function testHtml()
  {
    $html2pdf = new Html2Pdf();
    $parameters = [
        'apiurl'=>'https://yofd0pxi8l.execute-api.us-west-2.amazonaws.com/html2pdf/lambdapdf',
        'apikey'=>'47YexK2GHWapTiVm2aSGyalRylF5pvAkads7fgSJ',
        'bucket'=>'tclassifieds',
        'filepath'=>'',
        'filename'=>'',
        'data'=>'http::/thomascmobley.com',
        'fontlinks'=>['https://fonts.googleapis.com/css?family=Lora'],
        'domain'=>'http://vstage.thomascmobley.com',
        'options'=>['footerCenter'=>'Page [page] of [toPage]']
    ];
    $return = $html2pdf->transform($parameters);
    echo print_r($return,true ).PHP_EOL;
    $this->assertTrue(is_object($return));
  }
 
}


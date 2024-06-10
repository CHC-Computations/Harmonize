<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this)); 

$this->setTitle( $this->transEsc("Places") );
$currentCore = 'places';


$this->facetsCode = $this->routeParam[0] ?? 'null';
$this->addJS("results.maps.start('$this->facetsCode');");	
	
	

echo $this->render('head.php');
echo $this->render('core/header.php');
echo '<div class="main">';
echo $this->render('places/fullMap.php');
echo '</div>';
echo $this->render('core/footer.php');
	



?>
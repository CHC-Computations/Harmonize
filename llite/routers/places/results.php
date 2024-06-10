<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->buffer->setSql($this->sql);

$this->setTitle( $this->transEsc("Places") );

$placesList = [];

$addStr = '';
if (count($this->GET)>0) {
	foreach ($this->GET as $k=>$v)
		$parts[]= $k.'='.$v;
	$addStr = implode('&', $parts);	
	} 

$this->addJS("page.ajax('ajaxBox','wiki/map.show.places?$addStr');");
?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class='main'>
	<?= $this->render('places/fullMap.php') ?>
</div>
<?= $this->render('core/footer.php') ?>


 
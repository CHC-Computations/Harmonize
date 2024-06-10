<?php 
require_once('functions/class.helper.php');
require_once('functions/class.places.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('places', 	new places($this->sql)); 
$this->addClass('maps', 	new maps()); 
$this->buffer->setSql($this->sql);

$this->setTitle( $this->transEsc("Places") );

$placesList = $this->places->getFullList();
foreach ($placesList as $k=>$place) {
	$placesList[$k] = $this->maps->addPoint($place);
	}

$this->addJS("page.ajax('apiCheckBox','places/import');");

?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class='main'>
	<?= $this->render('places/fullMap.php', ['places' =>$placesList]); ?>
	<div id="apiCheckBox"></div>
</div>
<?= $this->render('core/footer.php') ?>



<?php 
require_once('functions/klasa.helper.php');
require_once('functions/klasa.places.php');
require_once('functions/klasa.maps.php');

$marcRecord = false;
$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
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



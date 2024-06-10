<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
// require_once('functions/class.places.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->buffer->setSql($this->sql);
$export = $this->getConfig('place-card');
$export = $this->getConfig('facets');


$place = $this->getParam('GET','place');
$res = $this->buffer->getPlaceParams($place, 'extended');
$neighborhood = $this->buffer->getNeighborhoodPlaces($res);

$this->setTitle( $this->transEsc("Place") );
$this->setTitle( $this->transEsc("Place").": ".$res['display_name'] );


$stat = $this->solr->getPlaceStats($place);
		
?>



<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class='main'>
	<?= $this->render('places/simplePlace.php', ['place' =>$res, 'stat'=>$stat, 'neighborhood'=>$neighborhood]); ?>
	
	
</div>
<?= $this->render('core/footer.php') ?>



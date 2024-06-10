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

$this->setTitle( $this->transEsc("Geocoding Places With Wikidata") );


$facetName = 'geographic_facet';
$query[] =  $this->solr->facetsCountCode($facetName);
$res = $this->solr->getFacets('biblio', [$facetName], $query);
$totalResults = $this->solr->getFacetsCount($facetName);

$OC = "page.ajax('apiCheckBox', 'wiki/geocode.places/$facetName/0/$totalResults');";	

?>
<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class='main'>
	<div class="container">
		<br/><br/>
		<p><b><?= $this->helper->numberFormat($totalResults) ?></b> names to check. </p>
		<button class="btn btn-default" OnClick="<?=$OC?>">Start to geocoding... </button>
		
		<div id="apiCheckBox"></div>

	</div>
</div>
<?= $this->render('core/footer.php') ?>

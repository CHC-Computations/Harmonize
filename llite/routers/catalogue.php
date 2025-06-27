<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));
$this->addClass('bookcart',	new bookcart());

$currentCore = 'biblio';


if (empty($this->routeParam[0])) {
	$renderer = 'catalogue-empty';
	$results = [];	
	$params = ['currentCore'=>$currentCore];
	} else {
	$currFacet = $this->routeParam[0];
	$facetName = $this->configJson->$currentCore->facets->solrIndexes->$currFacet->name ?? $currFacet;
	$renderer = 'catalogue';
	
	$query = [];
	$query['limit']=[
				'field' => 'facet.limit',
				'value' => 999
				];
	$query['facet.sort']=[
				'field' => 'facet.sort',
				'value' => 'index'
				];
	$query[] = $this->solr->facetsCountCode($currFacet);
	
	$results = $this->solr->getFacets($currentCore, [$currFacet], $query);
	$params = ['results'=>$results[$currFacet], 'currentFacet'=>$currFacet, 'currentCore'=>$currentCore, 'facetName'=>$facetName, 'total' => $this->solr->getFacetsCount($currFacet)];
	}

?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<div class="container">
<?= $this->render('search/'.$renderer.'.php', $params ) ?> 
</div>
<?= $this->render('helpers/report.error.php') ?> 
<?= $this->render('core/footer.php') ?>


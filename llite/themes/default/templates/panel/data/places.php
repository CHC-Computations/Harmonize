<?php 

$indexes = [
	'subjecthits'=>'geographic_facet',
	'pubplacehits'=>'geographicpublication_str_mv'
	];
	
foreach ($indexes as $hitField => $facetName) { 	
 
	$query[] =  $this->solr->facetsCountCode($facetName);
	$res = $this->solr->getFacets('biblio', [$facetName], $query);
	$totalResults = $this->solr->getFacetsCount($facetName);
	
	$OC = "page.ajax('apiCheckBox', 'wiki/geocode.places/$facetName/$hitField/0/$totalResults');";	
	$line[] = '<p><button class="btn btn-default" OnClick="'.$OC.'">Start to geocoding places: '.$hitField.' <b>'.$this->helper->numberFormat($totalResults).'</b></button></p>';
	}

$t = $this->psql->querySelect("SELECT count(*) as all, (SELECT count(*) FROM places_wiki WHERE lon IS NULL OR lat IS NULL) as empty FROM places_wiki;");
if (is_array($t)) {
	$res = current($t);
	$div = $res['all']-$res['empty'];
	$OC = "page.ajax('apiCheckBox', 'nominatim.openstreetmap/geocode.places/0/$res[empty]');";
	$line[] = '<p>We have <b>'.$res['all'].'</b> places in database. 
		Records with coordinates: <b>'.$div.'</b>.
		Records to check: <b>'.$res['empty'].'</b><br/> 
		<button class="btn btn-default" OnClick="'.$OC.'">Find coordinates with nominatim.openstreetmap.org API</button>
		</p>';
	}
	

?>

<div class='main'>
	<div class="container">
		<br/><br/>
		<?= implode('',$line) ?>
		<div id="apiCheckBox"></div>
		
	</div>
</div>
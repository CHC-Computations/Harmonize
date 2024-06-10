<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.buffer.php');
require_once('functions/class.maps.php');
require_once('functions/class.solr.php');

$this->addClass('helper', 	new helper()); 
$this->addClass('maps',		new maps()); 
$this->addClass('buffer',	new buffer()); 
$this->addClass('solr',		new solr($this)); 


$currentCore = 'places';

$query['q'] = $this->solr->lookFor($this->getParam('GET', 'lookfor'), 'allfields' );		
$query['sort']=[ 
		'field' => 'sort',
		'value' => 'biblio_count desc'
		];
$query[] = [
		'field' => 'q.op', 
		'value' => 'AND'	
		];
$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];
$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => 6
			];
$query['facet.mincount ']=[ 
			'field' => 'facet.mincount',
			'value' => 1
			];

foreach ($this->configJson->$currentCore->facets->facetsMenu as $facetField) {
	if (!empty($facetField->template) && ($facetField->template == 'timeGraph'))
		$query['facet.offset'.$facetField->solr_index]=[ 
				'field' => 'f.'.$facetField->solr_index.'.facet.limit', // keeping offset only on first field
				'value' => 9999
				];
	$query[]=[ 
				'field' => 'facet.field',
				'value' => $facetField->solr_index
				];
	}

$query['rows']=[ 
		'field' => 'rows',
		'value' => 50
		];
		
$this->facetsCode = $this->routeParam[0] ?? 'null';		
		

$times = [];
$results = $this->solr->getQuery($currentCore, $query); 
$results = $this->solr->resultsList();
$facets = $this->solr->facetsList();
$totalResults = $recSum = $this->solr->totalResults();
#echo "alerts".$this->helper->pre($this->solr->alert);


if ($totalResults>0) {
	
	$first = current($results);
	$recMax = $first->biblio_count;
	$last = end($results);
	$recMin = $last->biblio_count;
	
	$lp = 0;
	foreach ($results as $place) {
		$lp++;
		$pjs = [];
		
		if (!empty($place->latitiude)) $tlat[] = $place->latitiude;
		if (!empty($place->longitiude)) $tlon[] = $place->longitiude;
		}
	

	
	$addStr = http_build_query($this->GET);
	$lon['min'] = min($tlon);
	$lat['min'] = min($tlat);
	$lon['max'] = max($tlon);
	$lat['max'] = max($tlat);
	
	$js[] = "map.fitBounds([[$lat[max],$lon[max]],[$lat[min],$lon[min]]]);";
	$js[] = "$('#mapStartZoom').val(map.getZoom());";
	
	$js[] = "$('#mapBoundN').val(map.getBounds().getNorth());";
	$js[] = "$('#mapBoundS').val(map.getBounds().getSouth());";
	$js[] = "$('#mapBoundE').val(map.getBounds().getEast());";
	$js[] = "$('#mapBoundW').val(map.getBounds().getWest());";
	$js[] = "$('#mapZoom').val(map.getZoom());";
	$js[] = "map.on('moveend', function() { 
			//page.ajax('ajaxBox','wiki/map.moved?$addStr&N='+map.getBounds().getNorth()+'&S='+map.getBounds().getSouth()+'&W='+map.getBounds().getWest()+'&E='+map.getBounds().getEast());
			$('#mapBoundN').val(map.getBounds().getNorth());
			$('#mapBoundS').val(map.getBounds().getSouth());
			$('#mapBoundE').val(map.getBounds().getEast());
			$('#mapBoundW').val(map.getBounds().getWest());
			$('#mapZoom').val(map.getZoom());
			results.maps.moved('{$this->facetsCode}');
			});";
	
	echo $this->transEsc('Total results').': <strong>'.$this->helper->numberFormat($totalResults).'</strong>';
	echo '<input type="hidden" name="totalResults" id="totalResults" value="'.$totalResults.'">';
	echo '<input type="hidden" name="visibleResults" id="visibleResults" value="'.$this->solr->visibleResults().'">';
	
	$this->addJS(implode("\n", $js));	

	} else {
	echo $this->transEsc('No results');	
	$this->addJS('$("#mapPopupCurrentView").html("");');
	}
	
	
	
#echo $this->helper->pre($this->GET);
#echo $this->helper->pre($this->POST);
#echo $this->helper->pre($this->routeParam);

?>
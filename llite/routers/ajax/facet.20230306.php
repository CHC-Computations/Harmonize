<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');

#$this->addJS("$('.collapse'+'.sidefl').collapse('hide');");

$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 
$this->addClass('buffer', 	new marcBuffer()); 

$facetsOptions = $this->getConfig('facets');

if (!empty($this->routeParam[1])) {
	$this->facetsCode = $this->routeParam[1];
	$query['facets'] = $this->buffer->getFacets($this->sql, $this->facetsCode);	
	} else 
	$this->facetsCode = 'null';	

if (!empty($this->GET['sj'])) {
	$query['q'] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->GET['sj'])
			];
	} else 
	if (!empty ($lookfor = $this->getParam('GET', 'lookfor')))
		$query['q'] = $this->solr->lookFor($lookfor, $type = $this->getParam('GET', 'type') );			
	



if (!empty($this->settings->facets->facetsMenu)) {
	foreach ($this->settings->facets->facetsMenu as $gr=>$facet) 
		if (!empty($facet->solr_index))
			$query[] = [
				'field' => 'facet.field',
				'value' => $facet->solr_index
				];
		
	$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => $this->settings->facets->defaults->facetLimit
			];
	$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];	
	}  
	


$formattedFacets = $this->getConfigParam('facets', 'formattedFacets');
$transletedFacets = $this->getConfigParam('facets', 'facetOptions', 'transletedFacets');

#echo "results<pre>".print_r($this->buffer->usedFacets,1).'</pre>';
#echo "results<pre>".print_r($query,1).'</pre>';
	

switch ($this->routeParam[0]) {
	default: echo $this->transEsc('unknow facet'); break;
	case 'all_facets' : {
		if (!empty($this->buffer->usedFacets)) 
			echo $this->render('search/facets-active.php', ['activeFacets' => $this->buffer->usedFacets, 'facets'=>$facetsOptions, ] );
		
		$results = $this->solr->getFacets('biblio', array_keys($facetsOptions['searchFacets']), $query);
		
		# echo "results<pre>".print_r($results,1).'</pre>';
		# echo implode(' ', $this->solr->alert);
		
		foreach ($facetsOptions['searchFacets'] as $currentFacet=>$facetName) {
			
			if ( array_key_exists($currentFacet, $results) & array_key_exists($currentFacet, $facetsOptions['searchFacets']) ) {
				
				if (!in_array($currentFacet, $facetsOptions['cascade'])) { // ingnore here if shoud be in cascade!
					if (empty($facetsOptions['specialTemplate'][$currentFacet]))
						$facetsOptions['specialTemplate'][$currentFacet] = 'normalFacet';
					
					switch ($facetsOptions['specialTemplate'][$currentFacet]) {
						case 'dateFacet' : 
								unset($query['limit']);
								$results = $this->solr->getCleanedYears('biblio', [$currentFacet], $query);
								if (!empty($results[$currentFacet]))
									echo $this->render('search/facet-years-box.php', [
											'facet' => $currentFacet, 
											'facetName' => $facetsOptions['facetList'][$currentFacet], 
											'facets' => $results[$currentFacet],
											'currFacet' => $currentFacet,
											] );
								break;
						case 'blockFacet' :
								/*
								echo "block";
								echo $this->render('search/facet-box.php', [
											'facet'=>$currentFacet, 
											'facetName'=>$facetsOptions['facetList'][$currentFacet], 
											'facets'=>$results[$currentFacet],
											'cascadeRes'=> $cascadeRes
											] );
								*/
								$blocks = $this->solr->getFullList2('biblio', $currentFacet, $query);
								$extra = [];
								foreach ($blocks->results as $k=>$v) 
									if (!is_numeric($k)) {
										$extra[$k]=$v;
										unset($blocks->results[$k]);
										}
								ksort($blocks->results);
								#echo "<pre>".print_r($blocks,1).'</pre>';	
								echo $this->render('search/facet-centuries-box.php', [
											'facet' => $currentFacet, 
											'facetName' => $facetsOptions['facetList'][$currentFacet], 
											'facets' => $blocks->results,
											'extraFacets' => $extra,
											'currFacet' => $currentFacet,
											] );
								break;
						default :
								#echo "formattedFacets<pre>".print_r($formattedFacets,1).'</pre>';
								#echo "transletedFacets<pre>".print_r($transletedFacets,1).'</pre>';
								$translated = false;
								$formatter = null;
								if (in_array($currentFacet, $transletedFacets))
									$translated = true;
								if (array_key_exists($currentFacet, $formattedFacets))
									$formatter = $formattedFacets[$currentFacet];
								if (array_key_exists($currentFacet, $facetsOptions['cascade']) && (!empty($results[$facetsOptions['cascade'][$currentFacet]])) ) {
									$cascadeRes = $results[$facetsOptions['cascade'][$currentFacet]];
									} else {
									$cascadeRes = [];
									}
									
								echo $this->render('search/facet-box.php', [
											'facet'		 => $currentFacet, 
											'facetName'  => $facetsOptions['facetList'][$currentFacet], 
											'facets'	 => $results[$currentFacet],
											'cascadeRes' => $cascadeRes,
											'translated' => $translated,
											'formatter'	 => $formatter
											] );
								break;
						} // switch 

					}
				}
			}
		}
	} // switch 


# echo "GET<pre>".print_r($this->GET,1).'</pre>';
# echo "params<pre>".print_r($this->routeParam,1).'</pre>';
# echo "results<pre>".print_r($results,1).'</pre>';
# echo "facets.ini<pre>".print_r($facets,1).'</pre>';

?>

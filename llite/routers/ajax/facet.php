<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.wikidata.php');

$currentCore = 'biblio';

#$this->addJS("$('.collapse'+'.sidefl').collapse('hide');");

$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this));


if (!empty($this->routeParam[1])) {
	$this->facetsCode = $this->routeParam[1];
	$query['facets'] = $this->buffer->getFacets($this->facetsCode);	
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
	
$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];	


#echo "buffer->usedFacets<pre>".print_r(@$this->buffer->usedFacets,1).'</pre>';
#echo "query<pre>".print_r($query,1).'</pre>';


if (empty($facets)) $facets = [];
switch ($this->routeParam[0]) {
	default: echo $this->transEsc('unknow facet'); break;
	case 'all_facets' : {
		if (!empty($this->buffer->usedFacets)) 
			echo $this->render('search/facets-active.php', ['activeFacets' => $this->buffer->usedFacets ] );
		
		if (!empty($this->configJson->$currentCore->facets->facetsMenu)) {
			$facets = $this->helper->getNeededFacets($this->configJson->biblio->facets->facetsMenu);	
			$results = $this->solr->getFacets('biblio', $facets, $query);
			$fullResults = $results;
			
			
			foreach ($this->configJson->$currentCore->facets->facetsMenu as $gr=>$facet) {
				
				$stepSetting = clone $this->configJson->$currentCore->facets->defaults;
				if (!empty($facet->template))
					$stepSetting->template = $facet->template;
				if (!empty($facet->translated))
					$stepSetting->translated = $facet->translated;
				if (!empty($facet->formatter))
					$stepSetting->formatter = $facet->formatter;
				if (!empty($facet->child))
					$stepSetting->child = $facet->child;
				
				switch ($stepSetting->template) {
					case 'box' :
							if (!empty($results[$facet->solr_index]))
								echo $this->render('search/facet-box.php', [
										'facet'		 => $facet,
										'facets'	 => $results[$facet->solr_index],
										'stepSetting' => $stepSetting
										] );
							break;			
					case 'groupBox' :
							#echo $this->helper->pre($facet);
							echo $this->render('search/facet-groupBox.php', [
										'groupName'  => $facet->name, 
										'list' 	 	 => $facet->groupList,
										'stepSetting' => $stepSetting,
										'fullResults' => $fullResults
										] );
							break;			
					case 'timeGraph' :
							unset($query['limit']);
							$results = $this->solr->getCleanedYears('biblio', [$facet->solr_index], $query);
							if (!empty($results[$facet->solr_index]))
								echo $this->render('search/facet-years-box.php', [
										'facet' 	=> $facet->solr_index, 
										'facetName' => $facet->name, 
										'facets' => $results[$facet->solr_index],
										'currFacet' => $facet->solr_index,
										] );
							break;	
					case 'graph' :		
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
					}
				}
			}
		}
	} // switch 


# echo $this->helper->pre($fullResults); 
			 
# echo $this->helper->pre($this->settings->facets);		
# echo "GET<pre>".print_r($this->GET,1).'</pre>';
# echo "params<pre>".print_r($this->routeParam,1).'</pre>';

# echo "facets.ini<pre>".print_r($facets,1).'</pre>';

?>

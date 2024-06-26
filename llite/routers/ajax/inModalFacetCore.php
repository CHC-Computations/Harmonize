<?php 
if (empty($this)) die;
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 

# echo $this->helper->pre($this->routeParam); 

$currAction = $this->routeParam[0];
$currFacet = $this->routeParam[1];
$userLang = $this->routeParam[2];
$core = $this->routeParam[4];
$this->facetsCode = $this->routeParam[6] ?? null;

$this->loadJsonSettings($core);

@$facetName = $this->configJson->$core->facets->solrIndexes->$currFacet->name ?? $this->configJson->$core->facets->facetsMenu->$currFacet->name;

$queryoptions=[];
$queryoptions[]=[ 
				'field' => 'facet.sort',
				'value' => 'count'
				];
$queryoptions['limit']=[ 
				'field' => 'facet.limit',
				'value' => 19
				];

if (!empty ($this->GET['q'])) {
	$ss = $this->GET['q'];
	$queryoptions[]=[ 
				'field' => 'facet.contains.ignoreCase',
				'value' => 'true'
				];
	$queryoptions[]=[ 
				'field' => 'facet.contains',
				'value' => $ss
				];
	}
$queryoptions[] = $this->buffer->getFacets($this->facetsCode);
$queryoptions['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			
if (!empty($this->GET['sj'])) {
	$queryoptions[] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->GET['sj'])
			];
	}
	
	
if (!empty ($this->GET['add'])) {
	$_SESSION['facets_chosen'][$currFacet][$this->GET['add']]='ok';
	}	
if (!empty ($this->GET['remove'])) {
	unset($_SESSION['facets_chosen'][$currFacet][$this->GET['remove']]);
	}	
	 



switch ($currAction) {
	case 'build' :
			if (!empty($facetName)) {

				$results = $this->solr->getFacets($core, [$currFacet], $queryoptions);
				echo $this->render('search/inmodal/facet-search-box-core.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet], 'core'=>$core ] );
				} else {
				echo $this->transEsc('We don`t know a facet like').': <b>'.$core.'.'.$currFacet.'</b><br/>';	
				}
		break;		
	case 'search' : 			
			if (!empty($facetName)) {
				$results = $this->solr->getFacets($core, [$currFacet], $queryoptions);
				if (empty($results[$currFacet])) 
					echo $this->render('search/inmodal/no-results.php');
					else 
					echo $this->render('search/inmodal/facet-search-box-results-core.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet], 'core'=>$core ] );
				
				} else {
				echo $this->transEsc('We don`t know a facet like').': <b>'.$core.'.'.$currFacet.'</b><br/>';	
				}
				
			#echo "params<pre>".print_r($this->routeParam,1).'</pre>';
			#echo "GET<pre>".print_r($this->GET,1).'</pre>';
			#echo time();
				
		break;
	}
	
# echo "results<pre>".print_r($results,1).'</pre>';
# echo $this->helper->pre($this->routeParam);

?>
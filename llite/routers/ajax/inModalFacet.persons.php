<?php 
if (empty($this)) die;
$this->addClass('buffer', new marcBuffer()); 
$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this->config)); 


$currAction = $this->routeParam[0];
$currFacet = $this->routeParam[1];
$core = $this->routeParam[2];
$this->facetsCode = $this->routeParam[3];

$this->loadJsonSettings($core);
$facetName = $this->$core->facets->facetsMenu->$currFacet->name;
$queryoptions=[];
if (!empty ($this->GET['sort'])) {
	$queryoptions[]=[ 
				'field' => 'facet.sort',
				'value' => $this->GET['sort']
				];
	}
if (!empty ($this->GET['q'])) {
	$ss = $this->GET['q'];
	
	$queryoptions[]=[ 
				'field' => 'facet.contains.ignoreCase',
				'value' => 'false'
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
			if (!empty($this->persons->facets->facetsMenu->$currFacet)) {

				$results = $this->solr->getFacets($core, [$currFacet], $queryoptions);
				echo $this->render('search/inmodal/facet-search-box.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet], 'core'=>$core ] );
				} else {
				echo $this->transEsc('We don`t know a facet like').': <b>'.$core.'.'.$currFacet.'</b><br/>';	
				}
		break;		
	case 'search' : 			
			if (!empty($this->persons->facets->facetsMenu->$currFacet)) {
				$results = $this->solr->getFacets($core, [$currFacet], $queryoptions);
				if (empty($results[$currFacet])) 
					echo $this->render('search/inmodal/no-results.php');
					else 
					echo $this->render('search/inmodal/facet-search-box-results.php', ['facetName'=>$facetName, 'currFacet'=>$currFacet, 'facets'=>$results[$currFacet], 'core'=>$core ] );
				
				} else {
				echo $this->transEsc('We don`t know a facet like').': <b>'.$core.'.'.$currFacet.'</b><br/>';	
				}
				
			#echo "params<pre>".print_r($this->routeParam,1).'</pre>';
			#echo "GET<pre>".print_r($this->GET,1).'</pre>';
			#echo time();
				
		break;
	}
	
#echo "results<pre>".print_r($results,1).'</pre>';

?>
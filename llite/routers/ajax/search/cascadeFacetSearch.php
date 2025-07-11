<?php
if (empty($this)) die;

$currentCore = 'biblio';
$facet = new stdClass;
$currentCore = 'biblio';
$this->facetsCode = $this->routeParam[0];
$facet->solr_index = $this->routeParam[1];

$this->addClass('helper', new helper()); 
$this->addClass('solr', new solr($this)); 
$this->addClass('buffer', 	new buffer()); 


$stepSetting = clone $this->configJson->$currentCore->facets->defaults;
$ss = $this->POST['lookfor'];
$facet->formatter = $this->POST['formatter'];
$facet->translated = $this->POST['translated'];

if (!empty($facet->translated))
	$stepSetting->translated = $facet->translated;
if (!empty($facet->formatter))
	$stepSetting->formatter = $facet->formatter;

$query[] = $uf = $this->buffer->getFacets( $this->facetsCode);	
if (!empty($this->GET['sj'])) {
	$query['q'] = [ 'field' => 'q',	'value' => $this->solr->advandedSearch($this->GET['sj'])];
	} else 
	$query['q'] = $this->solr->lookFor($lookfor = $this->getParam('GET', 'lookfor'), $type = $this->getParam('GET', 'type') );			
$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];
$query['limit'] = [
		'field' => 'facet.limit',
		'value' => $stepSetting->facetLimit
		];

if ($ss<>'') {
	$query[]=[ 
					'field' => 'facet.contains.ignoreCase',
					'value' => 'true'
					];
	$query[]=[ 
				'field' => 'facet.contains',
				'value' => $ss
				];
	}



#$query[] =  $this->solr->facetsCountCode($facet->solr_index);
$results = $this->solr->getFacets($currentCore, [$facet->solr_index], $query);

if (is_Array($results)) {
	$lines = [];
	if (!empty($results[$facet->solr_index])) {
		foreach ($results[$facet->solr_index] as $name=>$count) {
			if ($count>0) {
				$tname = $name;
				if (!empty($stepSetting->formatter)) {
					$formatter = $stepSetting->formatter;
					$tname = $this->helper->$formatter($name);
					}
				if ($stepSetting->translated == 'true')
					$tname = $this->transEsc($tname);
				
				if ($this->buffer->isActiveFacet($facet->solr_index, $name)) {
					$key = $this->buffer->createFacetsCode(
							$this->buffer->removeFacet($facet->solr_index, $name)
							);
					$lines[] = '<a href="'.$this->buildUri('results', ['core'=>$currentCore, 'facetsCode'=>$key]).'" class="facet js-facet-item active" >
									<span class="text">'.$tname.'</span>
									<i class="right-icon glyphicon glyphicon-remove" ></i>
								</a>';
				
					} else {
					$key = $this->buffer->createFacetsCode(
							$this->buffer->addFacet($facet->solr_index, $name)
							);
					$lines[] = '<a href="'.$this->buildUri('results', ['core'=>$currentCore, 'facetsCode'=>$key]).'" class="facet js-facet-item" >
									<span class="text">'.$tname.'</span>
									<span class="badge">'.$this->helper->numberFormat($count).'</span>
								</a>';
				
					}
				
							
				}
			}
		
		}
	if (count($lines)>=1) {
		echo implode('', $lines);
		} else {
		echo $this->transEsc('No results');	
		}
	}
$this->addJS("$('#subfacetCascadeResults_{$facet->solr_index}').css('opacity', '1');");
			
// echo $this->helper->pre($this->routeParam); 
// echo 'lookfor:'.$this->helper->pre($ss);
// echo 'POST:'.$this->helper->pre($query);


?>
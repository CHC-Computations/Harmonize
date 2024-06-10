<?php
if (empty($this)) die;
#require_once('../functions/class.solr.php');

$this->addClass('solr', new solr($this));
$limit = 16;

if (!empty($this->GET['q'])) {
	$queryString = $this->solr->clearStr($this->GET['q']);
	if (isset($this->GET['f'])) 
		$in = $this->GET['f'];
		else 
		$in = '';
	
	
	$queryoptions[]=[ 
				'field' => 'facet.limit',
				'value' => 10
				];
	$queryoptions[]=[ 
				'field' => 'facet.mincount',
				'value' => 1
				];
	$queryoptions[]=[ 
				'field' => 'facet.sort',
				'value' => 'count'
				];
	$queryoptions[]=[ 
				'field' => 'facet.contains.ignoreCase',
				'value' => 'true'
				];
	$queryoptions[]=[ 
				'field' => 'facet.contains',
				'value' => $queryString
				];
	$results = $this->solr->getFacets('biblio', ['author'], $queryoptions);
	
	#print_r($results);
	
	
	if (!empty($results['author'])) {
		foreach ($results['author'] as $word=>$weight)
			$Tper[] = ['word' => $word];
	
		echo '{"results":'.json_encode($Tper).'}';	
		} else {
		$Tper[] = "no results";
		echo '{"results":'.json_encode($Tper).'}';	
		}
	}

?>
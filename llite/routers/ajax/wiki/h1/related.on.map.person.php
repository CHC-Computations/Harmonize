<?php
$useForSearch = $prefix.'mainAuthor';
$baseConditions = [];


$personalPoints = [
	'P19' => 'birthPlace',
	'P551' => 'residencePlace',
	'P20' => 'deathPlace',
	];
$relationsToTake = [
	'publicationPlace' => 'publicationPlace',
	'subjectPlace' => 'subjectPlace',
	];

$lastPoint = '';
foreach ($personalPoints as $property=>$pointName) {
	$points = $this->wiki->getPropIds($property);
	if (!empty($points))
		foreach ($points as $point) {
			$toShow['residencePlace'][$point] = $toShow['residencePlace'][$point] ?? $pointName;
			$placesToTake[] = $point;
			@$placesRoles[$point][$pointName]++;
			if (!empty($lastPoint)) 
				$linesToDraw['residencePlace'][$point] = $lastPoint;
			$lastPoint = $point; 
			}
	}
	

foreach ($relationsToTake as $relationKey=>$relationName) {	
	$query = [];
	$query['q'] = [
			'field' => 'q',
			'value' => '*:*'
			];
	$query['fq'] = [
			'field' => 'fq',
			'value' => 'with_roles_wiki:"'.$useForSearch.'"'
			];
			
	$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];
	$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => 9999 
			];
	$query['facet.mincount ']=[ 
			'field' => 'facet.mincount',
			'value' => 1
			];
	$query[]=[ 
			'field' => 'facet.field',
			'value' => 'with_roles_wiki'
			];
	$query[]=[ 
			'field' => 'facet.contains',
			'value' => '|'.$relationKey
			];
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 0
			];
	$query[]=[ 
			'field' => 'start',
			'value' => 0
			];		

	$results = $this->solr->getQuery('biblio', $query); 
	#$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();	
	if (!empty($facets['with_roles_wiki']))
		foreach ($facets['with_roles_wiki'] as $value=>$pointCount) {
			$point = explode('|',$value)[0];
			$toShow[$relationKey][$point] = $pointCount ;
			$placesToTake[] = $point;
			$placesRoles[$point][$relationKey] = $pointCount;
			}
	$totalResults = $this->solr->totalResults();
	}

if (!empty($toShow)) {
	foreach ($toShow as $key=>$values) 
		$buttons[$key] = (object)[
			'name' => $key,
			'count' => count($values),
			'state' => true
			];
	}


?>

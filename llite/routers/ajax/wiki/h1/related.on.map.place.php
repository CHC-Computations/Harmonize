<?php
$useForSearch = $prefix.$this->wiki->getML('labels', $this->configJson->settings->multiLanguage->order);
$userLabel = $this->helper->formatMultiLang($this->wiki->getML('labels', $this->configJson->settings->multiLanguage->order));

$baseConditions = [];
$replaceHeaders = [
	'deathPlacePR' => $this->transEsc('They died here'),
	'birthPlacePR' => $this->transEsc('They were born here'),
	'publicationPlace' => $this->transEsc('Place of publication of works with subject place').' '.$userLabel,
	'subjectPlace' => $this->transEsc('Subject place of works publicated in').' '.$userLabel,
	];
			

$personPoints = [
	'deathPlacePR' => [
			'fq' => 'birth_place:"'.$useForSearch.'"',
			'fl' => 'wikiq,death_place,death_year,ML_self',
			'facet.field' => 'death_place',
			],
	'birthPlacePR' => [
			'fq' => 'death_place:"'.$useForSearch.'"',
			'fl' => 'wikiq,birth_place,birth_year,ML_self',
			'facet.field' => 'birth_place',
			]
	];
	
$relationsToTake = [
	'publicationPlace' => $prefix.'subjectPlace',
	'subjectPlace' => $prefix.'publicationPlace',
	];

$lastPoint = '';
	
foreach ($personPoints as $relationKey=>$queryOptions) {	
	$query = [];
	$query['q'] = [
			'field' => 'q',
			'value' => '*:*'
			];
	$query['fq'] = [
			'field' => 'fq',
			'value' => $queryOptions['fq']
			];
	$query['fl'] = [
			'field' => 'fl',
			'value' => $queryOptions['fl']
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
	$query['facet.field']=[ 
			'field' => 'facet.field',
			'value' => $queryOptions['facet.field']
			];
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 9999
			];
	$query[]=[ 
			'field' => 'start',
			'value' => 0
			];		
	$query[]=[ 
			'field' => 'sort',
			'value' => 'biblio_count desc'
			];		

	$results = $this->solr->getQuery('persons', $query); 
	$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();	
	
	#echo $this->helper->preCollapse('query', $query);	 
	#echo $this->helper->preCollapse('results', $results);	 
	
	if (!empty($facets[$queryOptions['facet.field']]))
		foreach ($facets[$queryOptions['facet.field']] as $value=>$pointCount) {
			$point = explode('|',$value)[0];
			#if ($point != $wikiq) {
				$toShow[$relationKey][$point] = $pointCount ;
				@$totalPointsCounts[$point] += $pointCount ;
				$placesToTake[] = $point;
				$placesRoles[$point][$relationKey] = $pointCount;
				$linesToDraw[$relationKey][$point] = $wikiq;
			#	}
			}
	if (!empty($results)) 
		foreach ($results as $row) {
			$fieldName = $queryOptions['facet.field'];
			if (!empty($row->$fieldName)) {
				$where = explode('|',current($row->$fieldName))[0];
				$name = $this->helper->formatMultiLang(current($row->ML_self));
				$replaceStr[$where][$relationKey][] = "<a href='".$this->buildUrl('wiki/record/'.$row->wikiq)."'>$name</a>";
				}
			}
	
	$totalResults = $this->solr->totalResults();
	}

#echo $this->helper->preCollapse('replacceStr', $replaceStr);




foreach ($relationsToTake as $relationKey=>$useForSearch) {	
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
			@$totalPointsCounts[$point] += $pointCount ;
			$placesToTake[] = $point;
			$placesRoles[$point][$relationKey] = $pointCount;
			$linesToDraw[$relationKey][$point] = $wikiq;
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

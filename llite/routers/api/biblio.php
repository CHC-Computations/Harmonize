<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));

$currentCore = 'biblio';
$maxPage = $this->configJson->$currentCore->summaryBarMenu->pagination->maxPagesAlowed ?? 100;

$response = new stdClass;
	

$lookfor = $this->GET['lookfor'] ?? '';
$type = $this->GET['type'] ?? 'allfields';

$currentPage = $this->GET['page'] ?? 1;
$currentPage = (int)$currentPage;

$limit = $this->GET['limit'] ?? 20;
$limit = (int)$limit;
if ($limit < 0) $limit = 1;
if ($limit > $this->configJson->api->maxLimit) $limit = $this->configJson->api->maxLimit;

$maxResults = $this->configJson->api->maxResults ?? 999999;

$sort = $this->GET['sort'] ?? null;
$resultSize = $this->GET['resultSize'] ?? 'small';

$paramsToCheck = ['resultSize', 'sort'];
foreach ($paramsToCheck as $param) {
	if (!empty($$param) && !in_array($$param, $this->configJson->api->$param)) {
		$response->warnings[] = "incorrect value in '$param'. '$param' has been skipped in your query.";
		$$param = null;
		}
	}	

$withFacets = $this->GET['withFacets'] ?? null;
$facetsList = [];
if (!empty($withFacets)) {
	$facetsList = explode(',', $withFacets);
	foreach ($facetsList as $k=>$facet) {
		if (!empty($facet) && !in_array($facet, $this->configJson->api->withFacets)) {
			$response->warnings[] = "incorrect value in 'withFacets'. Incorrect value has been skipped in your query.";
			unset($facetsList[$k]);
			}
		}
	}
	
$useFacet = $this->GET['useFacet'] ?? null;
	
	

	

if ($currentPage*$limit < $maxResults) {

	$query = [];
	if ($resultSize == 'extended')
		$query['fields'] = [
				'field' => 'fl',
				'value' => 'id,title,author,relations'
				];
		else 		
		$query['fields'] = [
				'field' => 'fl',
				'value' => 'id,title,author'
				];

	$query['q'] = $this->solr->lookFor($lookfor, $type );		
	
			
	$query['rows']=[ 
			'field' => 'rows',
			'value' => $limit
			];
	
	if (!empty($sort) && ($sort!=='relevance'))
		$query['sort']=[ 
				'field' => 'sort',
				'value' => $sort
				];
		
	
	if (!empty($currentPage))
		$query['start']=[ 
			'field' => 'start',
			'value' => $currentPage*$limit - $limit
			];		
	
	$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];
	
	if (!empty($facetsList)) {
		$query['facet']=[ 'field' => 'facet','value' => 'true'];
		#$query['facet.limit']=[ 'field' => 'facet.limit','value' => 6];
		$query['facet.mincount ']=[ 'field' => 'facet.mincount','value' => 1 ];
		foreach ($facetsList as $field) 
			$query[] = [
				'field' => 'facet.field',
				'value' => $field
				];
		} 
	
	if (!empty($useFacet)) {
		$query['fq']=[ 
				'field' => 'fq',
				'value' => $useFacet
				];
		}
	
	
	$results = $this->solr->getQuery('biblio',$query); 
	$response->totalResults = $this->solr->totalResults();
	$response->docs = $this->solr->resultsList();
	
	#$response->query = $query;
	#$response->solrLink = $this->solr->alert;
	
	if (!empty($response->docs)) {
		foreach ($response->docs as $key=>$result) {
			foreach ($result as $field=>$values) {
				if (!empty($this->configJson->$currentCore->facets->solrIndexes->$field->formatter)) {
					$formatter = $this->configJson->$currentCore->facets->solrIndexes->$field->formatter;
					if (is_array($values))
						foreach ($values as $skey=>$value) 
							$result->$field[$skey] = strip_tags($this->helper->$formatter($value));
					if (is_string($values))
						$result->$field = strip_tags($this->helper->$formatter($values));
					
					} else if (!empty($this->configJson->$currentCore->facets->solrIndexes->$field->translated) && ($this->configJson->$currentCore->facets->solrIndexes->$field->translated)) {
					if (is_array($values))
						foreach ($values as $skey=>$value) 
							$result->$field[$skey] = $this->transEsc($value);
					if (is_string($values))
						$result->$field = $this->transEsc($values);
					} else if ($field == 'relations') 
					$result->$field = json_decode($values);
				}
			}
		}
	$response->facets = $this->solr->facetsList();
	#echo $this->helper->pre($response);
	
	
	
	} else {
	$response = (object)['error' => 'Maximum permitted number of pages exceeded'];	
	}

$fn = 'searchResults.json';
$toPrint = json_encode($response);

$len=strlen($toPrint);
header("Content-type: application/json");
header("Content-Length: $len");
header("Content-Disposition: inline; filename=$fn");
print $toPrint;	

?>
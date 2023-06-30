<?php
if (empty($this)) die;
require_once('functions/klasa.persons.2.php');
require_once('functions/klasa.wikidata.php');
require_once('functions/klasa.wikidata.libri.php');

$this->setTitle($this->transEsc('Persons'));

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('persons', 	new persons($this->config)); 
$this->addClass('solr', 	new solr($this->config)); 


$this->persons->register('psql', $this->psql);
$this->buffer->setSql($this->sql);


if (!empty($this->GET['limit']))
	$this->saveUserParam('limit',$this->GET['limit']);
	else if (empty($this->getUserParam('limit')))
	$this->saveUserParam('limit', $this->config['search']['pagination']['default_rpp']);


$lookFor = $this->postParam('lookfor');
if (empty($lookFor) && !empty($this->GET['lookfor']))
	$lookFor = $this->GET['lookfor'];

$WAR = [];
if ($lookFor<>'') {
	$queryString = explode(' ', $this->urlName2($lookFor));
	$WAR[] = $WHERE = "(name_search ILIKE '%".implode("%' AND name_search ILIKE '%", $queryString)."%')";
	} 
	




	$query['q']=[ 
			'field' => 'q',
			'value' => '*:*'
			];
		
	$query['fq']=[ 
			'field' => 'fq',
			'value' => 'record_type:person'
			];
		
	$query['sort']=[ 
			'field' => 'sort',
			'value' => 'biblio_count desc'
			];
		
	
	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'true'
				];
	$query['facet.limit']=[ 
				'field' => 'facet.limit',
				'value' => 6
				];
	
	$facetsList = array('birth_date', 'birth_place','related_place', 'country', 'occupation', 'gender'); //'birth_year' = tu jakiś problem jest ;
	foreach ($facetsList as $facetField)
		$query[]=[ 
					'field' => 'facet.field',
					'value' => $facetField
					];
	 
	$query['rows']=[ 
			'field' => 'rows',
			'value' => $this->getUserParam('limit')
			];
			
	if (!empty($this->getCurrentPage()>1))
		$query[]=[ 
			'field' => 'start',
			'value' => $this->getCurrentPage()*$this->getUserParam('limit') - $this->getUserParam('limit')
			];		

	$times = [];
	$times['before query'] = $this->runTime();
	# $this->solr->cleanQuery('biblio'); 
	$results = $this->solr->getQuery('wikidata',$query); 
	$times['after query'] = $this->runTime();
	$results = $this->solr->resultsList();
	$times['after resultsList'] = $this->runTime();
	$facets = $this->solr->facetsList();
	#echo $this->helper->pre($this->solr->alert);
	$times['after facetsList'] = $this->runTime();
    #$results = json_decode(file_get_contents("http://147.231.80.162:8983/solr/lite.wikidata/select?facet.field=birth_place&facet.limit=6&facet=true&fq=record_type%3Aperson&indent=true&q.op=OR&q=*%3A*&sort=biblio_count%20desc"));


?>


<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('persons/home.php', ['facets'=>$facets, 'results'=>$results, 'totalResults'=>$this->solr->totalResults()] ) ?>
<?= $this->render('core/footer.php') ?>
<?php 
	$times['after all'] = $this->runTime();
	echo $this->helper->pre($times);
?>

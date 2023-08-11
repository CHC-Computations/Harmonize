<?php
if (empty($this)) die;
require_once('functions/klasa.wikidata.php');
require_once('functions/klasa.wikidata.libri.php');

$this->setTitle($this->transEsc('Persons'));

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('solr', 	new solr($this->config)); 

$this->loadJsonSettings('persons');

$this->buffer->setSql($this->sql);


if (!empty($this->GET['limit']))
	$this->saveUserParam('limit',$this->GET['limit']);
	else if (empty($this->getUserParam('limit')))
	$this->saveUserParam('limit', $this->config['search']['pagination']['default_rpp']);

if (!empty($this->routeParam[3])) {
	$this->facetsCode = $this->routeParam[3];	
	$query[] = $this->buffer->getFacets( $this->facetsCode);	
	} else 
	$this->facetsCode = 'null';	
$lookfor = $this->postParam('lookfor');
if (empty($lookFor) && !empty($this->GET['lookfor'])) {
	$lookfor = $this->GET['lookfor'];

	$query['q']=[ 
			'field' => 'q',
			'value' => $lookfor
			];
	
	} else 
	$query['q']=[ 
			'field' => 'q',
			'value' => '*:*'
			];


if (!empty($this->routeParam[2])) 
	$this->sortCode = $this->routeParam[2]; 
	else 
	$this->sortCode = 'bc';
if (!empty($this->persons->sorting->{$this->sortCode}->solrField)) {
	$query['sort']=[ 
		'field' => 'sort',
		'value' => $this->persons->sorting->{$this->sortCode}->solrField
		];
	} else {
	$this->sortCode = 'bc';
	$query['sort']=[ 
		'field' => 'sort',
		'value' => 'biblio_count desc'
		];
	}


$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];
$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => 6
			];

foreach ($this->persons->facets->facetsMenu as $facetField) {
	if (!empty($facetField->template) && ($facetField->template == 'timeGraph'))
		$query['facet.offset'.$facetField->solr_index]=[ 
				'field' => 'f.'.$facetField->solr_index.'.facet.limit', // keeping offset only on first field
				'value' => 9999
				];
	$query[]=[ 
				'field' => 'facet.field',
				'value' => $facetField->solr_index
				];
	}
 
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
$results = $this->solr->getQuery('persons',$query); 
$results = $this->solr->resultsList();
$facets = $this->solr->facetsList();
#echo $this->helper->pre($query);
#echo $this->helper->pre($this->solr->alert);

?>


<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('persons/home.php', ['facets'=>$facets, 'results'=>$results, 'totalResults'=>$this->solr->totalResults()] ) ?>
<?= $this->render('core/footer.php') ?>



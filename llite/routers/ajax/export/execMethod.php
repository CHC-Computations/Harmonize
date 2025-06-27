<?php 
if (empty($this)) die;
if ($this->user->isLoggedIn()) {
	$currentCore = 'biblio';
	require_once('functions/class.helper.php');
	require_once('functions/class.forms.php');
	require_once('functions/class.exporter.php');
	require_once('functions/class.wikidata.php');


	$this->addClass('buffer', 	new buffer()); 
	$this->addClass('solr', 	new solr($this)); 
	$this->addClass('helper', 	new helper()); 
	$this->addClass('wikidata', 	new wikidata($this)); 

	$this->buffer->bufferTime = 86400*360; // we don't want to update external records during export (because it cost time)

	$path = './files/exports/';
	$fileName = $this->user->full()->email;			
	$folder = $path.$fileName;
	if (!is_dir($folder)) {
		mkdir($folder);
		chmod($folder, 0775);
		}
	 
	$query['q'] = $this->solr->lookFor(
				$lookfor = $this->getParam('GET', 'lookfor'), 
				$type = $this->getParam('GET', 'type') 
				);	
	if (!empty($this->GET['sj'])) 
		$query['q'] = [ 
				'field' => 'q',
				'value' => $this->solr->advandedSearch($this->getParam('GET', 'sj'))
				];
	if (!empty($this->routeParam[1])) {
			$this->facetsCode = $this->routeParam[1];	
			$query['fq'] = $this->buffer->getFacets($this->facetsCode);	
			} else 
			$this->facetsCode = 'null';		

	$fileFormat = $this->routeParam[0];
	$exportParams = $this->configJson->$currentCore->exports->formats->$fileFormat;
	$exportParams->fileFormat = $fileFormat;

	if (empty($exportParams->getValues))
		$exportParams->getValues = new stdClass;
	$exportParams->getValues->licence = 'Licences';
	$exportParams->getValues->source_db_str = 'Source Database';

	$query[]=[ 
			'field' => 'facet',
			'value' => 'true'
			];		
	$query[]=[ 
			'field' => 'rows',
			'value' => '0'
			];		
	
	$query[]=[ 
			'field' => 'facet.limit',
			'value' => '10'
			];		
			
	$query[]=[ 
			'field' => 'start',
			'value' => 0
			];		

	foreach ($exportParams->getValues as $indexName=>$indexDesc) // never empty here
		$query[]=[ 
			'field' => 'facet.field',
			'value' => $indexName
			];

	if (!empty($exportParams->getCount)) {
		foreach ($exportParams->getCount as $indexName=>$indexDesc)
			$query[] =  $this->solr->facetsCountCode($indexName);
		$indexes = array_keys((array)$exportParams->getCount);	
		}
	
	$results = $this->solr->getQuery($currentCore, $query); 	

	$licenceTable = $this->helper->getLicence();


	echo $this->render('export/method2.php', ['exportParams' => $exportParams, 'licenceTable'=>$licenceTable] );
			
	# echo "fullResponse<pre>".print_r($this->solr->fullResponse,1)."</pre>";
	# echo "facetsList<pre>".print_r($this->solr->facetsList(),1)."</pre>";
	# echo "licenceTable<pre>".print_r($licenceTable,1)."</pre>";
	
		
		
	} else 
	echo $this->transEsc('You must be logged in to export records');	

?>



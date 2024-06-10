<?php
if (empty($this)) die;

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 
	
#echo $this->helper->pre($this->routeParam);

$currentCore = $this->GET['core'] ?? '';
if (!empty($this->configJson->$currentCore)) {
	if (!empty($this->configJson->$currentCore->title))
		$title = $this->configJson->$currentCore->title;
		else 
		$title = $this->configJson->$currentCore->title = UcFirst($currentCore);

	require_once('functions/class.wikidata.php');
	require_once('functions/class.wikidata.libri.php');
	
	$this->setTitle($this->transEsc($title));

	$this->addClass('solr', 		new solr($this)); 
	

	#$this->GET['sorting'] = $this->routeParam[2] ?? null;
	if (!empty($this->configJson->$currentCore->summaryBarMenu))
		foreach ($this->configJson->$currentCore->summaryBarMenu as $block=>$values) 
			if (!empty($this->GET[$block]))
				$this->saveUserParam($currentCore.':'.$block, $this->GET[$block]);
				else if (empty($this->getUserParam($currentCore.':'.$block))) 
				$this->saveUserParam($currentCore.':'.$block, $this->configJson->$currentCore->summaryBarMenu->$block->default);

	$sort = $this->getUserParamMeaning($currentCore, 'sorting', 'value');
	if (!empty($this->GET['swl'])) { // start with letter ...
		$sl = strtolower(substr($this->GET['swl'],0,1));
		#echo "Starting with: $sl<br/>";
		switch ($sort) {
			case 'biblio_labels asc': $sfield = 'biblio_labels'; break;
			default : $sfield = '';
			}
		if ($sfield<>'')
			$query[] = [
					'field' => 'q',
					'value' => "($sfield:$sl*)"
					];
		}
		
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


	if (!empty($this->getUserParamMeaning($currentCore, 'sorting', 'value'))) {
		$query['sort']=[ 
			'field' => 'sort',
			'value' => $this->getUserParamMeaning($currentCore, 'sorting', 'value')
			];
		} else {
		$sortCode = $this->configJson->$currentCore->summaryBarMenu->$block->default;
		$query['sort']=[ 
			'field' => 'sort',
			'value' => $this->configJson->$currentCore->summaryBarMenu->$block->optionsAvailable->$sortCode
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
	$query['facet.mincount ']=[ 
				'field' => 'facet.mincount',
				'value' => 1
				];

	foreach ($this->configJson->$currentCore->facets->facetsMenu as $facetField) {
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
			'value' => $this->getUserParam($currentCore.':pagination')
			];
			
	if (!empty($this->getCurrentPage()>1))
		$query[]=[ 
			'field' => 'start',
			'value' => $this->getCurrentPage()*$this->getUserParam($currentCore.':pagination') - $this->getUserParam($currentCore.':pagination')
			];		

	if (!empty($this->GET['facetsCode'])) {
		$this->facetsCode = $this->GET['facetsCode'];	
		$query['fq'] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';	

	$times = [];
	$results = $this->solr->getQuery($currentCore, $query); 
	$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();
	#echo "results".$this->helper->pre($results);
	#echo "alerts".$this->helper->pre($this->solr->alert);
	$totalResults = $this->solr->totalResults();
	
	echo $this->render('head.php');
	echo $this->render('core/header.php');
	echo $this->render('wikiResults/home.php', ['currentCore'=>$currentCore, 'facets'=>$facets, 'results'=>$results, 'totalResults'=>$totalResults] );
	#echo $this->helper->pre($_SESSION);
	#echo $this->helper->pre($results);
	echo $this->render('core/footer.php');
	
	
	} else {
	include ('routers/error404.php');
	}
?>






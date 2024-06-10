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


if ($this->getCurrentPage()	< $maxPage) {

	$this->forms->values($this->GET);
 
	if (!empty($this->configJson->$currentCore->summaryBarMenu))
		foreach ($this->configJson->$currentCore->summaryBarMenu as $block=>$values) 
			if (!empty($this->GET[$block]))
				$this->saveUserParam($currentCore.':'.$block, $this->GET[$block]);
				else if (empty($this->getUserParam($currentCore.':'.$block))) 
				$this->saveUserParam($currentCore.':'.$block, $this->configJson->$currentCore->summaryBarMenu->$block->default);



	if (!empty($this->GET['swl'])) { // start with letter ...
		$sl = strtolower(substr($this->GET['swl'],0,1));
		#echo "Starting with: $sl<br/>";
		switch ($sort) {
			case 'author_sort asc': $sfield = 'author_sort'; break;
			case 'title_sort asc': $sfield = 'title_sort'; break;
			default : $sfield = '';
			}
		if ($sfield<>'')
			$query[] = [
					'field' => 'q',
					'value' => "($sfield:$sl*)"
					];
		}


	$lookfor = $this->getParam('GET', 'lookfor');
	$type = $this->getParam('GET', 'type');

	$query = [];
	$query['fields'] = [
			'field' => 'fl',
			'value' => 'id,title,author'
			];

	if (!empty($this->GET['sj'])) {
		#echo "Advanced: <pre>".print_r(json_decode($this->GET['sj']),1)."</pre>";
		# echo $this->solr->advandedSearch($this->GET['sj']);
		$query['q'] = [ 
				'field' => 'q',
				'value' => $this->solr->advandedSearch($this->GET['sj'])
				];
		} else 
		$query['q'] = $this->solr->lookFor($lookfor, $type );		
	
	/*
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
	*/
	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'true'
				];
			
	$query['rows']=[ 
			'field' => 'rows',
			'value' => $this->getUserParam($currentCore.':pagination')
			];
	
	
	if (!empty($this->getCurrentPage()>1))
		$query['start']=[ 
			'field' => 'start',
			'value' => $this->getCurrentPage()*$this->getUserParam($currentCore.':pagination') - $this->getUserParam($currentCore.':pagination')
			];		
	
	$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];
	
	/*		
	$query[]=[ 
			'field' => 'hl',
			'value' => 'true'
			];
	$query[]=[ 
			'field' => 'hl.simple.pre',
			'value' => '<mark>'
			];
	$query[]=[ 
			'field' => 'hl.simple.post',
			'value' => '</mark>'
			];
	*/
	if (!empty($this->GET['facetsCode'])) {
		$this->facetsCode = $this->GET['facetsCode'];	
		$query['fq'] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';	
	

	$results = $this->solr->getQuery('biblio',$query); 
	$response = new stdClass;
	$response->totalResults = $this->solr->totalResults();
	$response->docs = $this->solr->resultsList();
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
					
					}
				if (!empty($this->configJson->$currentCore->facets->solrIndexes->$field->translated) && ($this->configJson->$currentCore->facets->solrIndexes->$field->translated)) {
					if (is_array($values))
						foreach ($values as $skey=>$value) 
							$result->$field[$skey] = $this->transEsc($value);
					if (is_string($values))
						$result->$field = $this->transEsc($values);
					
					}
				}
			}
		}
	
	#echo $this->helper->pre($response);

	$fn = 'searchResults.json';
	$toPrint = json_encode($response);
	
	$len=strlen($toPrint);
	header("Content-type: application/json");
	header("Content-Length: $len");
	header("Content-Disposition: inline; filename=$fn");
	print $toPrint;	
	
	}

# echo "<pre>".print_r($_SESSION,1)."</pre>";
?>
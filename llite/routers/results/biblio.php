<?php 
if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.bookcart.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));
$this->addClass('bookcart',	new bookcart());

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

	$sort = $this->getUserParamMeaning($currentCore, 'sorting', 'value');

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


	if (!empty($this->GET['sj'])) {
		#echo "Advanced: <pre>".print_r(json_decode($this->GET['sj']),1)."</pre>";
		# echo $this->solr->advandedSearch($this->GET['sj']);
		$query['q'] = [ 
				'field' => 'q',
				'value' => $this->solr->advandedSearch($this->GET['sj'])
				];
		} else 
		$query['q'] = $this->solr->lookFor($lookfor, $type );		
	
	
	if (!empty($this->getUserParamMeaning($currentCore, 'sorting', 'value'))) {
		if ($this->getUserParamMeaning($currentCore, 'sorting', 'value') !== 'relevance')
			$query['sort']=[ 
				'field' => 'sort',
				'value' => $this->getUserParamMeaning($currentCore, 'sorting', 'value')
				];
		} else {
		$sortCode = $this->configJson->$currentCore->summaryBarMenu->$block->default;
		if (is_string($this->configJson->$currentCore->summaryBarMenu->$block->optionsAvailable->$sortCode))
		$query['sort']=[ 
			'field' => 'sort',
			'value' => $this->configJson->$currentCore->summaryBarMenu->$block->optionsAvailable->$sortCode
			];
		}
	
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
			'value' => 'AND'
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
	
	# echo $this->helper->pre($query);
	$results = $this->solr->getQuery('biblio',$query); 
	$this->setTitle("ELB | ".$this->transEsc('Results'));

	$this->saveUserParam($currentCore.':query', json_encode($query));
	$this->saveUserParam($currentCore.':GET', json_encode($this->GET));
	
	$results = $this->solr->resultsList();
	
	if (!empty($lookfor))
		$this->buffer->saveSearch('biblio', $lookfor);

	} else {
	$results = new stdClass;
	$results->exception = '<h2>'.$this->transEsc('For reasons of server performance, we can now only present the first 100 pages of results').'. '.$this->transEsc('Try using filters or search').'.</h2>';
	$results->exception .= '<a href="'. $this->buildUrl('results/biblio', ['facetsCode'=>$this->facetsCode, 'page'=>1]) .'">'. $this->transEsc('Go back to the first page') .'</a>';
	}

if (!empty($this->buffer->usedFacets['user_list']) & ($this->solr->totalResults()==0)){
	$results = new stdClass;
	$results->exception = $this->helper->alertIco('warning', 'ph ph-clock-clockwise', $this->transEsc('The data in ELB has been re-indexed. Restoration of collection is in progress.'.'<div id="restoreCollectionAjaxBox">'.$this->helper->loader2().'</div>')); 
	$this->addJS("page.post('restoreCollectionAjaxBox', 'user/lists/restore.collection', ".json_encode($this->buffer->usedFacets['user_list']).")");
	}	

$this->head->meta = implode("\n", $this->meta);
?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('search/home.php', ['results'=>$results, 'currentCore'=>$currentCore] ) ?> 
<?= $this->render('helpers/report.error.php') ?> 
<?= $this->render('core/footer.php') ?>

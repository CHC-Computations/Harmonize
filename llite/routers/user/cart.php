<?php 
if (empty($this)) die();


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


	$lookfor = $this->getParam('GET', 'lookfor');
	$type = $this->getParam('GET', 'type');

	$myListCount = $this->buffer->myListCount();
	$results = $this->buffer->myListResults();
	
	#echo $this->helper->pre($results);
	$query['q'] = [
				'field' => 'q',
				'value'	=> 'id:('.implode(' OR ',$results).')'
				];
	
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
	
	if (!empty($this->GET['facetsCode'])) {
		$this->facetsCode = $this->GET['facetsCode'];	
		$query['fq'] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';	
	
	# $this->solr->cleanQuery('biblio'); 
	$results = $this->solr->getQuery('biblio',$query); 
	$this->setTitle("ELB | ".$this->transEsc('Results'));

	$this->saveUserParam($currentCore.':query', json_encode($query));
	$this->saveUserParam($currentCore.':GET', json_encode($this->GET));
	

	$results = $this->solr->resultsList();
	
	if (!empty($lookfor))
		$this->buffer->saveSearch('biblio', $lookfor);

	} else {
	$results = new stdClass;
	$results->exception = $this->transEsc('For reasons of server performance, we can now only present the first 100 pages of results').'. '.$this->transEsc('Try using filters or search').'. ';
	}


?>

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?>
<?= $this->render('user/cart.php', ['results'=>$results, 'currentCore'=>$currentCore]) ?>
<?= $this->render('core/footer.php') ?>


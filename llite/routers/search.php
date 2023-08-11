<?php 
if (empty($this)) die;

require_once('functions/klasa.helper.php');
require_once('functions/klasa.forms.php');
require_once('functions/klasa.converter.php');

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->settings)); 
$this->addClass('helper', 	new helper()); 
$this->addClass('forms', 	new forms()); 
$this->addClass('convert', 	new converter());


if ($this->getCurrentPage()<100) {

	$this->buffer->setSql($this->sql);
	$this->forms->values($this->GET);

	if (!empty($this->GET['limit']))
		$this->saveUserParam('limit',$this->GET['limit']);
		else if (empty($this->getUserParam('limit')))
		$this->saveUserParam('limit', $this->config['search']['pagination']['default_rpp']);

	if (!empty($this->GET['view']))
		$this->saveUserParam('view',$this->GET['view']);
		else if (empty($this->getUserParam('view')))
		$this->saveUserParam('view', $this->config['search']['pagination']['default_view']);
	 
	if (!empty($this->routeParam[2])) {
		$this->saveUserParam('sort',$this->routeParam[2]);
		} else if (empty($this->getUserParam('sort')))
		$this->saveUserParam('sort', $this->config['search']['pagination']['default_sort']);

	$sortSymbol = $this->getUserParam('sort');
	if (stristr($sortSymbol, ',')) { // mulit sort
		$sorts = explode(',',$sortSymbol);
		foreach ($sorts as $sortSymbol) {
			if ((!empty($this->getIniParam('search', 'sortoptions')[$sortSymbol])) && ($sortSymbol !== 'r')) {
				$sortTable[] = $this->getIniParam('search', 'sortoptions')[$sortSymbol];
				}
			$query[]=[ 
					'field' => 'sort',
					'value' => implode(',',$sortTable)
					];	
			}
		} elseif (!empty($this->getIniParam('search', 'sortoptions')[$sortSymbol])) { // single sort
		$sort = $this->getIniParam('search', 'sortoptions')[$sortSymbol];
		if ($sort!=='relevance') 
			$query[]=[ 
					'field' => 'sort',
					'value' => $sort
					];
		}


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

	if (!empty($this->routeParam[3])) {
		$this->facetsCode = $this->routeParam[3];	
		$query[] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';		

	$lookfor = $this->getParam('GET', 'lookfor');
	$type = $this->getParam('GET', 'type');
	$this->user->saveParam('last_search', $this->selfUrl());
	$this->user->saveParam('lookfor', $lookfor);
	$this->user->saveParam('type', $type);
	$this->user->saveParam('curr_page', $this->getCurrentPage());
	$this->user->saveParam('facetsCode', $this->facetsCode);

	#echo "lookfor: $lookfor, $type<Br>";

	if (!empty($this->GET['sj'])) {
		#echo "Advanced: <pre>".print_r(json_decode($this->GET['sj']),1)."</pre>";
		# echo $this->solr->advandedSearch($this->GET['sj']);
		$query['q'] = [ 
				'field' => 'q',
				'value' => $this->solr->advandedSearch($this->GET['sj'])
				];
		} else 
		$query['q'] = $this->solr->lookFor($lookfor, $type );			
			
	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'true'
				];
			
	$query[]=[ 
			'field' => 'rows',
			'value' => $this->getUserParam('limit')
			];
			
	if (!empty($this->getCurrentPage()>1))
		$query[]=[ 
			'field' => 'start',
			'value' => $this->getCurrentPage()*$this->getUserParam('limit') - $this->getUserParam('limit')
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

	# $this->solr->cleanQuery('biblio'); 
	$results = $this->solr->getQuery('biblio',$query); 
	$this->setTitle("Libri ".$this->transEsc('results'));

	# echo "<pre>".print_r($query,1)."</pre>";	
	# echo implode('',$this->solr->alert);

	$_SESSION['p_GET'] = $this->GET;
	$_SESSION['p_facetsCode'] = $this->facetsCode;
	$_SESSION['p_sort'] = $this->getUserParam('sort');

	$results = $this->solr->resultsList();
	#$_SESSION['curr_results'] = $results;
	#$_SESSION['last_search'] = $this->selfUrl();


	} else {
	$results = new stdClass;
	$results->exception = $this->transEsc('For reasons of server performance, we can now only present the first 100 pages of results').'. '.$this->transEsc('Try using filters or search').'. ';
	}

# echo "<pre>".print_r($_SESSION,1)."</pre>";
?> 

<?= $this->render('head.php') ?>
<?= $this->render('core/header.php') ?> 
<?= $this->render('search/home.php', ['results'=>$results] ) ?> 
<?= $this->render('core/footer.php') ?>

<?php 
if (empty($this)) die;
$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 

# echo "routeParam".$this->helper->pre($this->routeParam);
$is_current = false;
$currentRecId = $this->routeParam[0];
if (!empty($this->getUserParam('biblio:GET')))
	$this->GET = json_decode($this->getUserParam('biblio:GET'), true);
$currentCore = $this->GET['core'] ?? 'biblio';
$currentPage = $this->GET['page'] ?? 1;
if ($currentPage == 0) $currentPage = 1;
$maxPage = $this->configJson->$currentCore->summaryBarMenu->pagination->maxPagesAlowed ?? 100;
$limit = $this->GET['pagination'] ?? 10;


if ($currentPage < $maxPage) {
	
	
	if (!empty($this->getUserParamMeaning($currentCore, 'sorting', 'value'))) {
		if ($this->getUserParamMeaning($currentCore, 'sorting', 'value') !== 'relevance')
			$query['sort']=[ 
				'field' => 'sort',
				'value' => $this->getUserParamMeaning($currentCore, 'sorting', 'value')
				];
		} 
	
	if (!empty($this->GET['swl'])) { // start with letter ...
		$sl = strtolower(substr($this->GET['swl'],0,1));
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

	if (!empty($this->GET['facetsCode'])) {
		$this->facetsCode = $this->GET['facetsCode'];
		$query[] = $this->buffer->getFacets($this->facetsCode);	
		} else 
		$this->facetsCode = 'null';		

	$lookfor = $this->getParam('GET', 'lookfor');
	$type = $this->getParam('GET', 'type');


	if (!empty($this->GET['sj'])) {
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
	
	$query['fl']=[ 
			'field' => 'fl',
			'value' => 'id, title, title_short'
			];
	
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	$first = $lp = $this->solr->firstResultNo();
	
	$curr_lp = '??';
	
	

	$is_current = false;
	$next = '';
	$prev = '';
	$lp = 1;

	$pozList = '<div class="hiddenList">';
	$span = '';
	$cp_link = $cn_link = '';

	if (!empty($results))
		foreach ($results as $row) {
			$title = $this->helper->setLength($row->title,40);
			if (($is_current) && ($next == '')) {
				$next = $row->id;
				$next_title = $row->title;
				}
			if ($currentRecId == $row->id) {
				$span = '<a class="list-group-item active">'; 
				$curr_lp = $lp;
				$is_current = true;
				} else {
				$span = '<a href="'.$row->id.'.html" class="list-group-item">';
				}
			if (!$is_current) {
				$prev = $row->id;
				$prev_title = $row->title;
				}
			$pozList .= $span.$title.'</a>';
			$lp++;
			}
	$pozList.= "</div>";

	
		
	$currentResultsPage = $this->buildUri('results',[
					'core' => $currentCore,
					'page'=>$currentPage,
					'facetsCode' => $this->GET['facetsCode'] ?? 'r',
					'sorting'=> $this->GET['sorting'] ?? 'r',
					'lookfor' => $lookfor,
					'type' => $type
					]);
	}

$idParts = explode('.',$currentRecId);
$t = $this->psql->querySelect("SELECT * FROM elb_publication a 
	JOIN elb_publication_error b ON a.id = b.id_publication
	JOIN elb_errors e ON b.id_error = e.id
	WHERE  a.id_source_db = '{$idParts[0]}' AND a.raw_id = '{$idParts[1]}'");
if (is_array($t)) {
	echo '<div class="container" style="padding-top:8px; font-size:0.9em;">';
	echo '<p style="cursor:pointer;" data-toggle="collapse" data-target="#issueRaport"><i class="ph-bold ph-warning"></i> '.$this->transEsc('Some problems were reported for this record').'. <small>* This is an experiment. This part will not be visible in the official version until it is completed and tested.</small></p><div id="issueRaport" class="collapse"><ul>';
	foreach ($t as $row) 
		echo '<li>'.$row['msg'].'</li>';
	echo '</ul>
		</div>
	</div>';	
	#echo $this->helper->pre($t);
	}


?>

	<?php if (!empty($this->solr->totalResults())): ?>
		<div class="btn-breadcrumbs">
			<a href="<?= $currentResultsPage ?>"><i style="transform: rotate(-90deg);" class="glyphicon glyphicon-share-alt"></i> <?= '#'.$curr_lp.' '.$this->transEsc('of').' '.$this->helper->numberFormat($this->solr->totalResults())  ?></a>			
			<?= $pozList ?>
		</div>
		
	<?php endif; ?>


	<?php if ($is_current): ?>
		<div class="border-nav border-nav-left">

			<a href="<?= $currentResultsPage ?>" class="btn-slide">
				<div class="label-solid"><i style="transform: rotate(-90deg);" class="glyphicon glyphicon-share-alt"></i></div>
				<div class="label-slider"><span><?= $this->transEsc('Back to list') ?></span></div>
			</a>			

			<?php if (!empty($prev_title)): ?>  
				<a href="<?= $prev ?>.html"  class="btn-slide" rel="nofollow">
					<div class="label-solid"><i class="glyphicon glyphicon-arrow-left"></i></div>
					<div class="label-slider"><?= $this->helper->setLength($prev_title,17) ?></div>
				</a>
			<?php endif; ?>
			
		</div>	

		<?php if (!empty($next_title)): ?>  
			<div class="border-nav border-nav-right">	

				<a href="<?= $next ?>.html<?= $cn_link ?>" class="btn-slide" rel="nofollow">
					<div class="label-solid"><i class="glyphicon glyphicon-arrow-right"></i></div>
					<div class="label-slider"><?= $this->helper->setLength($next_title,17) ?></div>
				</a>
			</div>
		<?php endif; ?>
	<?php endif; ?>

<script>
	
	function rTWS() {
		var top = $('header').height();
		$('.btn-breadcrumbs').animate({'top': top+'px'});
		}
	rTWS();
	
</script>


<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');

$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->config)); 
$this->addClass('helper', 	new helper()); 

$curr_id = $this->routeParam[0];

$last_search 	= $this->user->loadParam('last_search');
$lookfor 		= $this->user->loadParam('lookfor');
$type 			= $this->user->loadParam('type');
$curr_page 		= $this->user->loadParam('curr_page');
$this->facetsCode 	= $this->user->loadParam('facetsCode');

# echo "<div style='position: fixed; top: 150px; right:100px; border:solid 1px red; background:yellow; padding:20px;'>Page: #$curr_page</div>";
if (!empty($_SESSION['cp'][$curr_id]))  {
	$curr_page = $_SESSION['cp'][$curr_id];
	# echo "<div style='position: fixed; top: 200px; right:100px; border:solid 1px red; background:yellow; padding:20px;'>switch to: $curr_page</div>";
	
	
	$this->user->saveParam('curr_page', $curr_page);

	}
if (!empty($_SESSION['cp']))
	unset($_SESSION['cp']);	

if (!empty($this->getUserParam('limit')))
	$limit = $this->getUserParam('limit');
	else 
	$limit = $this->config['search']['pagination']['default_rpp'];

if (empty($this->getUserParam('sort')))
	$ksort = $this->config['search']['pagination']['default_sort'];
	else 
	$ksort = $this->getUserParam('sort');

// query building 

if (!empty($this->getIniParam('search', 'sortoptions')[$this->getUserParam('sort')])) {
	$sort = $this->getIniParam('search', 'sortoptions')[$this->getUserParam('sort')];
	if ($sort!=='relevance') 
		$query[]=[ 
				'field' => 'sort',
				'value' => $sort
				];
	}
$query[] = $this->buffer->getFacets($this->facetsCode);	
$query[] = $this->solr->lookFor($lookfor, $type);			
$query[]=[ 
		'field' => 'facet',
		'value' => 'false'
		];
$query['rows']=[ 
		'field' => 'rows',
		'value' => $limit
		];
$query[]=[ 
		'field' => 'facet.limit',
		'value' => '10'
		];		
$query['start']=[ 
		'field' => 'start',
		'value' => $curr_page*$limit - $limit
		];		
	
$results = $this->solr->getQuery('biblio',$query); 
$results = $this->solr->resultsList();
$first = $lp = $curr_lp = $this->solr->firstResultNo();


$is_curr = false;
$next = '';

$pozList = '<div class="hiddenList">';
$span = '';
$cp_link = $cn_link = '';

foreach ($results as $row) {
	$title = $this->helper->setLength($row->title,40);
	if (($is_curr) && ($next == '')) {
		$next = $row->id;
		$next_title = $row->title;
		}
	if ($curr_id == $row->id) {
		$span = '<a class="list-group-item active">'; 
		$curr_lp = $lp;
		$is_curr = true;
		} else {
		$span = '<a href="'.$row->id.'.html" class="list-group-item">';
		}
	if (!$is_curr) {
		$prev = $row->id;
		$prev_title = $row->title;
		}
	$pozList .= $span.$title.'</a>';
	$lp++;
	}
$pozList.= "</div>";



if (($curr_lp == $first) && ($curr_page>1)) { 
	$query['rows']=[ 
		'field' => 'rows',
		'value' => 1
		];
	$query['start']=[ 
		'field' => 'start',
		'value' => $curr_page*$limit-$limit-1
		];		
	
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	$row = current($results);
	$prev = $row->id;
	$prev_title = $row->title;
	$_SESSION['cp'][$row->id] = ($curr_page-1);
	}

$lr = $this->solr->lastResultNo();
	

if (($curr_lp == $lr) && ($lp<$this->solr->totalResults())) { 
	
	$query['rows']=[ 
		'field' => 'rows',
		'value' => 1
		];
	$query['start']=[ 
		'field' => 'start',
		'value' => $curr_page*$limit
		];		
	# echo "<pre>".print_R($query,1)."</pre>";
	
	$results = $this->solr->getQuery('biblio',$query); 
	$results = $this->solr->resultsList();
	$row = current($results);
	$next = $row->id;
	$next_title = $row->title;
	$_SESSION['cp'][$row->id] = ($curr_page+1);
	
	}



# echo "<pre style='border:0px; background:transparent;'>".print_r($this->routeParam,1)."</pre>";
# echo "<pre style='border:0px; background:transparent;'>".print_r($results,1)."</pre>";
$curr_results_page = $this->buildUri('search/results',[
				'page'=>$curr_page,
				'sort'=> $ksort,
				'lookfor' => $lookfor,
				'type' => $type
				]);
?>

<?php if (!empty($last_search)): ?>
	<div class="btn-breadcrumbs">
		<a href="<?= $curr_results_page ?>"><i style="transform: rotate(-90deg);" class="glyphicon glyphicon-share-alt"></i> <?= '#'.$curr_lp.' '.$this->transEsc('of').' '.$this->helper->numberFormat($this->solr->totalResults())  ?></a>			
		<?= $pozList ?>
	</div>
	
<?php endif; ?>


<?php if (!empty($last_search)): ?>
	<div class="border-nav border-nav-left">

		<a href="<?= $last_search ?>" class="btn-slide">
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


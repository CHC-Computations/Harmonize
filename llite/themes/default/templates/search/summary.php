<?php 

 $pages = $this->getIniParam('search', 'pagination', 'rpp');
 $sorts = $this->getIniParam('search', 'sortnames');

$rp = $this->routeParam;

foreach ($pages as $k=>$v) {
	$rp[2] = $k;
	$Tap[$k] = [
		'key' => $v,
		'href' => $this->buildUri('search/'.implode('/',$this->routeParam), ['limit'=>$v]),
		'name' => $v
		];
	}

$sortT = $this->getUserParam('sort');
if (stristr($sortT,',')) { // mulisort
	$sortT = explode(',', $sortT);
	foreach ($sortT as $k=>$v)
		$TabOfSorts[$v]=$k+1;
		
	}
foreach ($sorts as $k=>$v) {
	$rp[2] = $k;
	if (!empty($TabOfSorts[$k]))
		$lab = ' <span class="label label-info" style="float:right;">'.$TabOfSorts[$k].'</span>';
		else 
		$lab = '';
	$Tas[$k] = [
		'key' => $k,
		'href' => $this->buildUri('search/'.implode('/',$rp)),
		'name' => $this->transEsc($v).$lab
		];
	}

 
?>
<div class="search-header hidden-print">
	<div class="sidebar-buttons">
		<button type="button" id="slideinbtn" class="ico-btn" OnClick="facets.SlideIn();" title="<?= $this->transEsc('Show side panel')?>"><i class="ph-sidebar-simple-bold"></i></button>
	</div>
    <div class="search-stats">
        <span><?= $this->transEsc('Total results')?>: <b><?= number_format($this->solr->totalResults(),0,'','.'); ?></b>, </span>
		<span><?= $this->transEsc('showing')?>: <?= $this->solr->firstResultNo()?> - <?= $this->solr->lastResultNo()?> </span>
	</div>
	<div class="search-controls">
		<?= 
		$this->helper->dropDown(
			$Tap,
			$this->getUserParam('limit'),
			$this->transEsc('Results per page')
			)
		?>
	</div>
	<div class="search-controls">
		<?= 
		$this->helper->dropDown(
			$Tas, 
			$this->getUserParam('sort'),
			$this->transEsc('Sort by')
			)
		?>
	</div>

    <div class="search-controls">
		<div class="view-buttons hidden-xs">

		<?php 
		$views = $this->config['search']['views'];
		
		foreach ($views as $k=>$v) {
			if ($k == $this->getUserParam('view'))
				echo '<a style="color:black;" title="'.$this->transEsc('Current view').': '.$v.'">
					<i class="glyphicon glyphicon-'.$k.'" alt="'.$v.'"></i>
					<span class="sr-only">'.$v.'</span>
				</a>&nbsp;';
				else 
				echo '<a href="'.$this->buildUri('search/'.implode('/',$this->routeParam), ['view'=>$k] ).'"  title="'.$this->transEsc('Switch view to').' '.$v.'" >
					<i class="glyphicon glyphicon-'.$k.'" alt="'.$v.'"></i>
					<span class="sr-only">'.$v.'</span>
				</a>&nbsp;';
			}
		
		?> 
		</div>
	</div>
</div>

<?php 

 #echo "<pre>".print_r($this->solr,1)."</pre>";

?>
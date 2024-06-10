<?php 
$lab = '';

$rp = $this->routeParam;

if (!empty($this->configJson->$currentCore->summaryBarMenu)) {
	foreach ($this->configJson->$currentCore->summaryBarMenu as $block=>$values) {
		$currentB[$block] = $this->getUserParam($currentCore.':'.$block) ?? '';
		$data = $values->optionsAvailable;
		foreach ($data as $k=>$v) {
			if (is_object($v)) {
				$rp[2] = $k;
				if (!empty($v->icon))
					$icon = '<i class="'.$v->icon.'" alt="'.$v->name.'"></i> ';
					else 
					$icon = '';
				$menu[$block][$k] = [
					'key' => $k,
					'href' => $this->buildUri('results/'.$currentCore.'/', [$block => $k]), //$v->value
					'name' => $icon.$this->transEsc($v->name)
					];
				} else {
				$menu[$block][$k] = [
					'key' => $v,
					'href' => $this->buildUri('results/'.$currentCore.'/', [$block => $v]),
					'name' => $this->transEsc($v)
					];	
				}
			}
		}
	}


/*
// mulitSorts
$currentSort = $this->getUserParam($currentCore.':sorting') ?? '';
if (stristr($currentSort,',')) { // mulisort
	$sortT = explode(',', $currentSort);
	foreach ($currentSort as $k=>$v)
		$TabOfSorts[$v]=$k+1;
	}

$sorts = $this->configJson->$currentCore->summaryBarMenu->sorting->optionsAvailable;
foreach ($sorts as $k=>$v) {
	$rp[2] = $k;
	if (!empty($TabOfSorts[$k]))
		$lab = ' <span class="label label-info" style="float:right;">'.$TabOfSorts[$k].'</span>';
		else 
		$lab = '';
	}
*/
	
		

 
?>
<div class="search-header hidden-print">
	<div class="sidebar-buttons">
		<button type="button" id="slideinbtn" class="ico-btn" OnClick="facets.SlideIn();" title="<?= $this->transEsc('Show side panel')?>"><i class="ph-sidebar-simple-bold"></i></button>
	</div>
    <div class="search-stats">
		<span class="main-title"><b><?= $this->transEsc($this->configJson->$currentCore->title) ?></b> |</span>
        <span><?= $this->transEsc('Total results')?>: <b><?= $this->helper->numberFormat($this->solr->totalResults()); ?></b>, </span>
		<span><?= $this->transEsc('showing')?>: <?= $this->solr->firstResultNo()?> - <?= $this->solr->lastResultNo()?> </span>
	</div>
	
	<?php
	if (!empty($menu))
		foreach ($menu as $block => $blockMenu)
			echo '<div class="search-controls">'.
				$this->helper->dropDown(
					$blockMenu,
					$this->getUserParam($currentCore.':'.$block),
					$this->transEsc($this->configJson->$currentCore->summaryBarMenu->$block->title)
					).
				'</div>';	
	?>
</div>

<?php 

 #echo "<pre>".print_r($this->solr,1)."</pre>";

?>
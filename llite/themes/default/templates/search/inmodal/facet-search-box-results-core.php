<?php 
$limit = 50;
#echo $this->helper->pre($this->$core);


$ch_array = [];
$lp = 0;
$facet = [];

	
$lines = [];	
$lp = 0;
$maks = 6;
$maksV = max($facets);
#echo "formatter: $formatter, translated: $translated<br/>";

foreach ($facets as $k=>$v) {
	$color = $this->helper->getGraphColor($lp);
	
	$lk = $currFacet.'_'.hash('crc32b', $k);
	$oc = "OnClick=\"facets.cores.AddRemove('add', '$k', '$lp', '$currFacet')\"";
	$sel = 'class="ph-square-bold"';
	if (!empty($_SESSION['facets_chosen'][$currFacet][$k])) {
		$sel = 'class="ph-check-square-bold"';
		$oc = "OnClick=\"facets.cores.AddRemove('remove', '$k', '$lp', '$currFacet')\"";
		}
		
	if ($v>0) {	
	
		$tk = $this->helper->convertC($core, $currFacet, $k);
		$lines[] = //href="'.$this->basicUri('search/results').'?'.$this->searcher->addFacet($facetName, $k).'" 
			
			'<tr id="trow_'.$lk.'" OnMouseOver="facets.graphActive(\''.$lk.'\');"  OnMouseOut="facets.graphDisActive(\''.$lk.'\');" '.$oc.'>
				<td style="vertical-align:middle; text-align:center;" >
					<i id="tcheck_'.$lk.'" '.$sel.'></i> 
				</td>
				<td>
					<a  
						data-title="'.$k.'" 
						data-count="'.$v.'">
						<span class="text">'.$tk.'</span>
					</a>
				</td>
				<td >'.$this->helper->percentBox($v,$maksV,$color).'</td>
			</tr>';
		$graphData[$lp] = [
				'uid' => $lk,
				'label' => $k,
				'color' => $color,
				'count' => $v
				];	
		}
		
	$lp++;
	}
# echo "<pre>".print_R($facets,1)."</pre>";

	
$proc = round((array_sum($facets)/$this->solr->totalResults())*100,1);
$msg = $this->transEsc('PieGraph includes about').' <span class="pie" style="--p:'.$proc.';--c:#5F3D8D;">'.$proc.'%</span> '.$this->transEsc('of all results').'.';
if ($proc>100) 
	$msg .= '<br/><small>* '.$this->transEsc('some results may fall into several categories').'</small>';
if ($proc<=0.1)
	$msg = $this->transEsc('PieGraph includes less than').' 0.1% '.$this->transEsc('of all results').'.';
	

?>
<?php if (count($lines)>0): ?>
	<div class="row">
		<div class="col-sm-6">
			<div class="facet-list-limited">
				<table class="list">
					<tbody><?= implode('',$lines) ?></tbody>
				</table>
				
				<?php 
				if (count($lines)>=$limit) 
					echo $this->transEsc("Only the most popular options are shown").".";
				?>
			</div>
	 
		</div>
		<div class="col-sm-6 visible-lg visible-md" style="vertical-align: bottom;">
			<div class="text-center" style="padding:10px;">
				<?php if (!empty($graphData)) echo $x=$this->helper->drawSVGPie($graphData) ?>
			</div>
			<div class="text-right">
			<?= $msg ?>
			</div>
			
		</div>
	</div>
<?php else: ?>
	<?=$this->transEsc('No results') ?>
<?php endif; ?>
			

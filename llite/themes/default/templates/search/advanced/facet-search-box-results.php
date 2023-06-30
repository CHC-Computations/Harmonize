<?php 
$limit = 50;
$authorFormat = array('author_facet', 'author_facet_s', 'topic_person_str_mv');

$choosen = '';	
$ch_array = [];
$lp = 0;
$facet = [];


if (!empty($_SESSION['facetsChosen'][$currFacet])) 
	$ch_array = $_SESSION['facetsChosen'][$currFacet];
	
	
$lines = [];	
$lp = 0;
$maks = 6;
$maksV = max($facets);

foreach ($facets as $k=>$v) {
	$color = $this->helper->getGraphColor($lp);
	$lp = hash('crc32b', $k);
	$oc = "advancedSearch.AddRemove('change','$k', '$currFacet', '$lp')";
	$sel = 'class="ph-square-bold"';
	if (array_key_exists($k,$ch_array))  {
		$sel = 'class="ph-check-square-bold"';
		}
		
	if ($v>0) {	
		if (in_Array($currFacet, $authorFormat))
			$k = $this->helper->authorFormat($k);
		$lk = $currFacet.'_'.$lp;
		$lines[] = //href="'.$this->basicUri('search/results').'?'.$this->searcher->addFacet($facetName, $k).'" 
			
			'<tr id="trow_'.$lk.'" 
					OnMouseOver="facets.graphActive(\''.$lk.'\');"  
					OnMouseOut="facets.graphDisActive(\''.$lk.'\');" 
					OnClick="'.$oc.'"
					>
				<td style="vertical-align:middle; text-align:center;" ><i id="tcheck_'.$lk.'" '.$sel.'></i> </td>
				<td>
					<a   
						data-title="'.$this->transEsc($k).'" 
						data-count="'.$v.'">
						<span class="text">'
						.$this->transEsc($k)
						.'</span>
					</a>
				</td>
				<td >'.$this->helper->percentBox($v,$maksV,$color).'</td>
			</tr>';
		$graphData[$currFacet.'_'.$lp] = [
				'label' => $this->transEsc($k),
				'color' => $color,
				'count' => $v
				];	
		}
	
	}
# echo "<pre>".print_R($facets,1)."</pre>";

if ($this->solr->totalResults()>0) {	
	$proc = round((array_sum($facets)/$this->solr->totalResults())*100,1);
	$msg = $this->transEsc('PieGraph includes about').' <span class="pie" style="--p:'.$proc.';--c:#5F3D8D;">'.$proc.'%</span> '.$this->transEsc('of all results').'.';
	if ($proc>100) 
		$msg .= '<br/><small>* '.$this->transEsc('some results may fall into several categories').'</small>';
	if ($proc<=0.1)
		$msg = $this->transEsc('PieGraph includes less than').' 0.1% '.$this->transEsc('of all results').'.';
		
	}
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

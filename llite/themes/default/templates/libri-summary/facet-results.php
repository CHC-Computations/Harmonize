<?php 

$choosen = '';	
$ch_array = [];
$facet = [];
$lines = [];	

$maks = 6;
$maksV = max($facets);


foreach ($facets as $k=>$v) {
	$color = $this->helper->getGraphColor($lp);
	
	$oc = "OnClick=\"facets.AddRemove('add','$k','$lp')\"";
	$sel = 'class="far fa-square"';
	if (array_key_exists($k,$ch_array))  {
		$sel = 'class="far fa-check-square"';
		$oc = "OnClick=\"facets.AddRemove('remove','$k')\"";
		}
		
	if ($v>0) {	
		
		$input_values = $this->buffer->addFacet($currFacet, $k);
		$key = $this->buffer->createFacetsCode($this->sql, $input_values);
			
		$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key);
		
		$lines[] = //href="'.$this->basicUri('search/results').'?'.$this->searcher->addFacet($facetName, $k).'" 
			
			'<tr id="trow_'.$lp.'" OnMouseOver="facets.graphActive('.$lp.');"  OnMouseOut="facets.graphDisActive('.$lp.');" '.$oc.'>
				<td>
					<a  href="'.$link.'"
						data-title="'.$this->transEsc($k).'" 
						data-count="'.$v.'">
						<span class="text">'
						.$this->transEsc($k)
						.'</span>
					</a>
				</td>
				<td >'.$this->helper->percentBox($v,$maksV,$color).'</td>
			</tr>';
		$graphData[$lp] = [
				'label' => $this->transEsc($k),
				'color' => $color,
				'count' => $v
				];	
		}
		
	$lp++;
	}
	

	
$proc = round((array_sum($facets)/$this->solr->totalResults())*100,1);
$msg = $this->transEsc('PieGraph includes about').' <span class="pie" style="--p:'.$proc.';--c:#5F3D8D;">'.$proc.'%</span> '.$this->transEsc('of all results').'.';
if ($proc>100) 
	$msg .= '<br/><small>* '.$this->transEsc('some results may fall into several categories').'</small>';
if ($proc<=0.1)
	$msg = $this->transEsc('PieGraph includes less than').' 0.1% '.$this->transEsc('of all results').'.';
	

?>

<div class="row">
	<div class="col-sm-6">
		
		<table class="list">
			<tbody><?= implode('',$lines) ?></tbody>
		</table>
 
	</div>
	<div class="col-sm-6" style="vertical-align: bottom;">
		<div class="text-center" style="padding:10px;">
		<?= $x=$this->helper->drawSVGPie($graphData) ?>
		</div>
		<div class="text-right">
		</div>
		
	</div>
</div>
<div class="text-right"><?= $msg ?></div>
			
<?= $choosen ?>
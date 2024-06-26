<?php 
		
		$drawId = uniqid();
		$view = 200;
		$arr = $facets;
		$delGraph = false;
		if (count($facets)>0) {

			// tmp - remove values higer than 21 (because we know that is error in data)
			foreach ($facets as $k=>$v)
			if ($k>21)
				unset($facets[$k]);
			// end-tmp
		
			$max = max($facets);
			if ($max == 0) {
				$max = 1;
				$delGraph = true;
				}
			$min = min($facets);
			
			$max_d = max(array_keys($facets));
			$min_d = min(array_keys($facets));
			$min_str = $max_str = '';
			if ($min_d<0) {
				$min_d = -$min_d;
				$min_str = ' '.$this->transEsc('b.c.');
				}
			if ($max_d<0) {
				$max_d = -$max_d;
				$max_str = ' '.$this->transEsc('b.c.');
				}
			$footer = '';
			$graph = '<div class="text-center" style="padding:10px;">
				<div class="graph-area" style="margin-left:auto; margin-right:auto;">';
			#echo "Max: $max<br><br>";
			$lp = 0;
			foreach ($facets as $k=>$v) if ($k<>0) {
				$lp++;
				$pr = round(($v/$max)*$view);
				if (($pr == 0) & ($v > 0)) $pr = 1;
				#echo "$k: $v -> $pr<Br>";
				$int = $k;
				$bc_str = '';
				if ($k<0) {
					$int = -$k;
					$bc_str = 'b.c.';
					} 
				
				if ($this->buffer->isActiveFacet($facet,$k)) {
					$active = "active";
					$key = $this->buffer->createFacetsCode(
							$this->buffer->removeFacet($facet, $k)
							);
					} else {
					$active = '';	
					$key = $this->buffer->createFacetsCode(
							$this->buffer->addFacet($facet, $k)
							);
					}
				
				$OMO = "$('#centuriesCurrentValue').html('".$this->helper->integerToRoman($int).' '.$this->transEsc($bc_str.' century').' <span class=badge>'.$v."</span>')";
				$OMOut = "$('#centuriesCurrentValue').html('&nbsp;')";
				$graph .='
					<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="graph-cloud '.$active.'" title="'.$this->helper->integerToRoman($int).$bc_str.': '.$v.'" onMouseOver="'.$OMO.'" OnMouseOut="'.$OMOut.'">
						<div class="graph-straw" style="height:'.$pr.'px; width:10px;" id="centuries_bar_'.$k.'" ></div>
					</a>';
				}
			$graph .= "</div>";
			$graph .= '<div class="graph-footer"></div>';

			$graph .="<div style='float:left'>".$this->helper->integerToRoman($min_d).$min_str."</div>";
			$graph .="<div style='float:right'>".$this->helper->integerToRoman($max_d).$max_str."</div>";
			$graph .="<div style='display:block; text-align:center; width:500px;' id='centuriesCurrentValue'>&nbsp;</div>";
			$graph .="</div>";
			} 
	if ($delGraph) $graph = '';
	$extra = '';
	if (!empty($extraFacets) && (count($extraFacets)>0))
		foreach ($extraFacets as $name=>$count) {
			if ($this->buffer->isActiveFacet($facet,$name)) {
					$key = $this->buffer->createFacetsCode(	$this->buffer->removeFacet($facet, $name) );
					$extra .= '<div class="facetTop">
					  <a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" id="" class="facet js-facet-item active" data-title="'.$name.'" data-count="'.$count.'">
						<span class="text">'.$this->transEsc($name).'</span>
						<i class="right-icon glyphicon glyphicon-remove" ></i>
					  </a>
					</div>';		
					} else {
					$key = $this->buffer->createFacetsCode(	$this->buffer->addFacet($facet, $name) );
					if ($count>0)		
					$extra .= '<div class="facetTop">
					  <a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" id="" class="facet js-facet-item" data-title="'.$name.'" data-count="'.$count.'">
						<span class="text">'.$this->transEsc($name).'</span>
						<span class="badge">'.$this->helper->numberFormat($count).'</span>
					  </a>
					</div>';		
					}
				
			
			
			
		}
	
?>
	
<?= 

$this->helper->PanelCollapse(
					uniqid(),
					$this->transEsc($facetName),
					$extra.'</div>'.$graph
					);
?>


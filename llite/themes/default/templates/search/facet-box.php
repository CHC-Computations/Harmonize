<?php 
$panelId = uniqid();

$lines = [];	
$lp = 0;
$maks = 5;
if (is_array($facets)) {
	foreach ($facets as $k=>$v) {
		if ($v>0) {
			$lp++;
			$input_value = $facet->solr_index.':"'.$k.'"';
			$tk = $k;
			
			if (!empty($stepSetting->formatter))	$tk = $this->helper->{$stepSetting->formatter}($k);
			if ($stepSetting->translated) $tk = $this->transEsc($k);
			
			if ($this->buffer->isActiveFacet($facet->solr_index,$k)) {
				$key = $this->buffer->createFacetsCode(
							$this->sql, 
							$this->buffer->removeFacet($facet->solr_index, $k)
							);
				$lines[]=
					'<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item active" data-title="'.$k.'" data-count="'.$v.'" >
						<span class="text">'.$tk.'</span>
						<i class="right-icon glyphicon glyphicon-remove" ></i>
					</a>';
				
				} else {
				
				$input_values = $this->buffer->addFacet($facet->solr_index, $k);
				$key = $this->buffer->createFacetsCode($this->sql, $input_values);
				if (!empty($facet->child)) {
					
					$this->addJS("facets.cascade2('{$key}', '{$key}', ".json_encode($facet->child).");");
					$CFD = '<div class="facetCascade" id="facetLink'.$key.'">'.$this->render('search/facet-cascade-empty.php').'</div>';
					} else
					$CFD = '';
				
				$lines[] = '
					<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
					  <a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" id="facetBase'.$key.'" class="facet js-facet-item" data-title="'.$this->transEsc($k).'" data-count="'.$v.'" >
						<span class="text">'.$tk.'</span>
						<span class="badge">'.$this->helper->numberFormat($v).'</span>
						<i class="ph-caret-right-bold" id="caret_'.$key.'" style="color:transparent; margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
					  </a>
					  '.$CFD.'
					</div>';
				}
			}
		if ($lp>=$maks) {
			$lines[] = '<a OnClick="facets.InModal(\''.$this->transEsc($facet->name).'\',\''.$facet->solr_index.'\')" class="facet last-facet-item"><span class="text">'.$this->transEsc('See all').'...</span></a>';
			break;
			}
		}
	
	
	
	if (count($lines)>0) {	
		echo $this->helper->PanelCollapse(
			$panelId,
			$this->transEsc($facet->name).'
					<a class="facet-btn" data-lightbox="" rel="nofollow" title="" OnClick="facets.InModal(\''.$this->transEsc($facet->name).'\',\''.$facet->solr_index.'\')">
						<i class="ph-chart-pie-slice-bold" title="'.$this->transEsc('more options').'"></i>
					</a>
					',
			implode('',$lines) 
			);
		}
	}
?>

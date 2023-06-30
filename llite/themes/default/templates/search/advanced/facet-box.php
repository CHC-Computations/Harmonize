<?php 
$authorFormat = array('author_facet', 'author_facet_s', 'topic_person_str_mv');
	
$lines = [];	
$lp = 0;
$maks = 6;
if (is_array($facets)) {
	foreach ($facets as $k=>$v) {
		if ($v>0) {
			$lp++;
			$input_value = $facet.':"'.$k.'"';
			$tk = $k;
			if (in_array($facet, $authorFormat))
					$tk = $this->helper->authorFormat($k);
				
			#echo "<pre>".print_r($facets,1)."</pre>";
			
			if ($this->buffer->isActiveFacet($facet,$k)) {
				$key = $this->buffer->createFacetsCode(
							$this->sql, 
							$this->buffer->removeFacet($facet, $k)
							);
				$lines[]=
					'<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item active" data-title="'.$this->transEsc($k).'" data-count="'.$v.'" >
						<span class="text">'.$this->transEsc($tk).'</span>
						<i class="right-icon glyphicon glyphicon-remove" ></i>
					</a>';
				
				if (!empty($this->config['facets']['cascade'][$facet])) {
					$cascade = $this->config['facets']['cascade'][$facet];
					if (count($cascadeRes)>0) {
						
						# $lines[] = ' cascade: '.$cascade;
						# $lines[] = '<pre>'.print_r($cascadeRes,1).'</pre>';
						$lines[] = '<div class="subfacets">';
						foreach ($cascadeRes as $sk => $sv) if ($sv>0) {
							$input_values = $this->buffer->addFacet($cascade, $sk);
							$key = $this->buffer->createFacetsCode($this->sql, $input_values);
							
							$lines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet subfacet js-facet-item" data-title="'.$this->transEsc($sk).'" data-count="'.$sv.'" >
									<span class="text">'.$this->transEsc($sk).'</span>
									<span class="badge">'.number_format($sv,0,'','.').'</span>
								</a>';
							}
						$lines[]="</div>";	
						}
					
					
					}
				
				
				
				} else {
				
				$input_values = $this->buffer->addFacet($facet, $k);
				$key = $this->buffer->createFacetsCode($this->sql, $input_values);
			
				
				$lines[]=
					'<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item" data-title="'.$this->transEsc($k).'" data-count="'.$v.'" >
						<span class="text">'.$this->transEsc($tk).'</span>
						<span class="badge">'.number_format($v,0,'','.').'</span>
					</a>';
				}
			}
		if ($lp>$maks) {
			$lines[] = '<a OnClick="facets.InModal(\''.$this->transEsc($facetName).'\',\''.$facet.'\')" class="facet last-facet-item"><span class="text">'.$this->transEsc('See all').'...</span></a>';
			break;
			}
		}
	
	
	
	if (count($lines)>0) {	
		echo $this->helper->PanelCollapse(
			uniqid(),
			 $this->transEsc($facetName).'
					<a class="facet-btn" data-lightbox="" rel="nofollow" title="" OnClick="facets.InModal(\''.$this->transEsc($facetName).'\',\''.$facet.'\')">
						<i class="ph-chart-pie-slice-bold" title="'.$this->transEsc('more options').'"></i>
					</a>
					',
			implode('',$lines),
			'',
			false
			);
		}
	}
?>

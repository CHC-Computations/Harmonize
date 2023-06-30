<?php 
$panelId = uniqid();

$lines = [];	
$lp = 0;
$maks = 5;
if (is_object($list)) {
	foreach ($list as $key=>$facet) {
		
		#echo '<b>'.$key.'</b>:'; #.$this->helper->pre($facet);
		#echo $this->helper->pre($facet);
			
		$lp++;
		if (empty($facet->groupList)) {
			
			if (!empty($facet->solr_index) && !empty($fullResults[$facet->solr_index])) {
				$facet->parent = 'group';
				
				if (!empty($facet->template) && ($facet->template == 'graph')) {
					$this->addJS("facets.cascade2('$key', '{$this->facetsCode}', ".json_encode($facet).");");
					$renderer = 'search/facet-cascade-empty.php';
					$params = [];
					} else {
					$slines = [];
					foreach ($fullResults[$facet->solr_index] as $name=>$count) {
						if ($count>0) {
							$tname = $name;
							if (!empty($facet->formatter)) {
								$formatter = $facet->formatter;
								$tname = $this->helper->$formatter($name);
								}
							if (!empty($facet->translated) && ($facet->translated))
								$tname = $this->transEsc($tname);
								else 
								$facet->translated = 0;
							
							if ($this->buffer->isActiveFacet($facet->solr_index, $name)) {
								$key = $this->buffer->createFacetsCode(
										$this->sql, 
										$this->buffer->removeFacet($facet->solr_index, $name)
										);
								$slines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item active" >
												<span class="text">'.$this->transEsc($tname).'</span>
												<i class="right-icon glyphicon glyphicon-remove" ></i>
											</a>';
							
								} else {
								$key = $this->buffer->createFacetsCode(
										$this->sql, 
										$this->buffer->addFacet($facet->solr_index, $name)
										);
								$slines[] = '<a href="'.$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, $this->GET).'" class="facet js-facet-item" >
												<span class="text">'.$this->transEsc($tname).'</span>
												<span class="badge">'.$this->helper->numberFormat($count).'</span>
											</a>';
								}
							}
						}
					$renderer = 'search/facet-cascade-search.php';
					$params = ['lines'=>$slines, 'facet'=>$facet, 'stepSetting'=>$facet, 'total' => $this->solr->getFacetsCount($facet->solr_index)];
					}
					
				$lines[] = '
					<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
					  <a id="facetBase'.$key.'" class="facet js-facet-item">
						<span class="text">'.$this->transEsc($facet->name).'</span>
						<i class="ph-caret-right-bold" id="caret_'.$key.'" style=" margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
					  </a>
					  <div class="facetCascade" id="facetLink'.$key.'">
						'.$this->render($renderer, $params).'
						
					  </div>
					</div>';
				}
			} else {
			$lines[] = '
				<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
				  <a id="facetBase'.$key.'" class="facet js-facet-item">
					<span class="text">'.$this->transEsc($facet->name).'</span>
					<i class="ph-caret-right-bold" id="caret_'.$key.'" style=" margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
				  </a>
				  <div class="facetCascade" id="facetLink'.$key.'">
					'.$this->render('search/facet-cascade-groupBox.php', [
										'groupName'  => $facet->name, 
										'list' 	 	 => $facet->groupList,
										'stepSetting' => $stepSetting,
										'fullResults' => $fullResults
										] ).'
				  </div>
				</div>';
			}
		}
	
	
	if (count($lines)>0) {	
		echo $this->helper->PanelCollapse(
			$panelId,
			$this->transEsc($groupName),
			implode('',$lines) 
			);
		}
	}
?>

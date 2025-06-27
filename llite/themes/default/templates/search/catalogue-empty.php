<?php 

function printLink($cms, $item) {
	return '<li><a href="'.$cms->buildUrl('catalogue/'.$item->solr_index).'">'.$item->name.'</a></li>';
	}
		
if (!empty($this->configJson->$currentCore->facets->facetsMenu)) {
	echo '<h1>'.$this->transEsc('Choose a category to browse').':</h1>';
	echo '<div class="catalogue-browse">
		<ul>';
					
	foreach ($this->configJson->$currentCore->facets->facetsMenu as $gr=>$facet) {
		$showFacet = true;
		if (!empty($facet->limit)) {
			if ($this->user->isLoggedIn() && $this->user->hasPower($facet->limit)) 
				$showFacet = true;
				else 
				$showFacet = false;
			}
		if ($showFacet) {	
			$stepSetting = clone $this->configJson->$currentCore->facets->defaults;
			if (!empty($facet->template))
				$stepSetting->template = $facet->template;
			if (!empty($facet->translated))
				$stepSetting->translated = $facet->translated;
			if (!empty($facet->formatter))
				$stepSetting->formatter = $facet->formatter;
			if (!empty($facet->child))
				$stepSetting->child = $facet->child;
			
			switch ($stepSetting->template) {
				case 'box' :
						if (!empty($facet->solr_index)) {
							echo printLink($this, $facet);
							}
						break;			
				case 'groupBox' :
						echo '<li>'.$facet->name;
						echo '<ul>';
						foreach ($facet->groupList as $item) {
							if (!empty($item->solr_index)) 
								echo printLink($this, $item);
								else 
								if (!empty($item->groupList)) {
									echo '<li>'.$item->name.'<ul>';
									foreach ($item->groupList as $sitem)
										if (!empty($sitem->solr_index)) 
											echo printLink($this, $sitem);
									echo '</ul></li>';
									}
							}
						echo '</ul></li>';
						#echo $this->helper->pre($facet);
						break;			
				default: 
						/*
						echo "<h3>".$facet->name.'</h3>';
						echo $this->helper->pre($facet);
						echo $this->helper->pre($stepSetting);
						break;
						*/
				}
			
			}
		}
	echo '</ul>
			</div>';	
	}



?>
<?php

	
	if (true || !empty($tabsToShow['map']) && $tabsToShow['map']) {
		$mapDraw = '<div style="border:solid 1px lightgray;  margin-top:20px;">';
		$mapDraw.= $this->maps->drawWorldMap();
		$mapDraw.= "</div>";
		$mapDraw.= '<div id="mapRelationsAjaxArea">'.$this->helper->loader2().'
				
				<input type="checkbox" checked id="map_checkbox_1" >
				<input type="checkbox" checked id="map_checkbox_2" >
				<input type="checkbox" checked id="map_checkbox_3" >

				</div>';
		
		$extraTabs['map'] 		= ['label' => $this->transEsc('Map'), 'content' => $mapDraw];
		$this->addJS("page.post('mapRelationsAjaxArea', 'wiki/h1/related.on.map/{$this->wiki->getID()}/{$recType}');");
		}
		
	if (true || !empty($tabsToShow['bibliographicalStatistics']) && $tabsToShow['bibliographicalStatistics']) {
		$extraTabs['bstats'] 	= [
				'label' => $this->transEsc('Bibliographical statistics'), 
				'content' => '<div id="bibliographicalStatistics">'.$this->helper->loader2().'</div>'
				];
		$this->addJS("page.post('bibliographicalStatistics', 'wiki/bibliographicalStatistics/{$this->wiki->getID()}/{$recType}');");
		}
	
	if (true || !empty($tabsToShow['comparisonOfRolesInBibliography']) && $tabsToShow['comparisonOfRolesInBibliography']) {
		$extraTabs['cStats'] 	= [
				'label' => $this->transEsc('Comparison of roles in bibliography'), 
				'content' => '<div id="comparisonOfRolesInBibliography">'.$this->helper->loader2().'</div>'
				];
		$this->addJS("page.post('comparisonOfRolesInBibliography', 'wiki/comparisonOfRolesInBibliography/{$this->wiki->getID()}/{$recType}');");
		}
	
	if (true || !empty($tabsToShow['related']) && $tabsToShow['related']) {	
		$extraTabs['related'] 	= [
				'label' => $this->transEsc('Related persons'), 
				'content' => '<div id="related2this">'.$this->helper->loader2().'</div>'
				];		
		$this->addJS("page.post('related2this', 'wiki/h1/related.persons/{$this->wiki->getID()}/{$recType}');");
		}
	
	if (!empty($extraTabs))
		echo $this->helper->tabsCarousel( $extraTabs , 'map' ?? null);

?>

<div id="drawPoints"></div> 
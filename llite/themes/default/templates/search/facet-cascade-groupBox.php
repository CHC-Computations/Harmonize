<?php 


$lines = [];	
$lp = 0;
$maks = 5;
if (is_object($list) or is_array($list)) {
	
	foreach ($list as $key=>$facet) {
		$facet = (object)$facet;
		$lp++;
		$this->addJS("facets.cascade2('$key', '{$this->facetsCode}', ".json_encode($facet).");");
		$lines[] = '
			<div class="facetTop" OnMouseOver="facets.place(\''.$key.'\')" OnMouseOut="facets.out(\''.$key.'\')">
			  <a href="#" id="facetBase'.$key.'" class="facet js-facet-item">
				<span class="text">'.$this->transEsc($facet->name).'</span>
				<i class="ph-caret-right-bold" id="caret_'.$key.'" style=" margin-right:-7px; margin-top:4px; font-size:0.8em;"></i>
			  </a>
			  <div class="facetCascade" id="facetLink'.$key.'">
				'.$this->render('search/facet-cascade-empty.php').'
			  </div>
			</div>';
		}
	
	
	if (count($lines)>0) {	
		echo '<div class="subfacetCascade">';
		echo implode('',$lines);
		echo '</div>';
		}
	}
?>

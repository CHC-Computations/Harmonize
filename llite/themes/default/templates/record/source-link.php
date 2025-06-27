<?php 
	$linkStr = '';
	if (!empty($source->title)) {
		$displayName = $source->title;
		$onlyName = $source->title;
		
		if (!empty($facetField)) {
			if (empty($source->best_label))
				$source->best_label = $onlyName;
			
			// TO DO HERE ! ->best_label should always be here!
			#echo $facetField.$this->helper->pre($source);
			$facetsCode = $this->buffer->createFacetsCode([$facetField.':"'.$source->best_label.'"']);
			$linkStr = '<a href="'. $this->buildUrl('results', ['core'=>'biblio', 'facetsCode' =>$facetsCode ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Show results using filter').': '.$this->helper->facetName('biblio', $facetField).' = '. $onlyName .'">'. $displayName .'</a> ';
			} else {
			$linkStr ='<a href="'. $this->buildUrl('results/biblio/', ['lookfor' =>$onlyName, 'type'=> 'allfields' ]) .'" data-toggle="tooltip" title="'. $this->transEsc('Look for').': '. $onlyName .'">'. $displayName .'</a> ';	
			}
		$linkStr = ''.$displayName.' ';
				
		if (!empty($source->relatedParts))
			$linkStr.='<small>'.$source->relatedParts.'</small>';
		$linkStr.='<br/>';
		}
?>
<?= $linkStr ?>
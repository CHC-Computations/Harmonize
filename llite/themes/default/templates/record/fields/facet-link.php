<?php 
	
if (!empty($value)) {
	$value = (array)$value;
	if (is_array($value))
		foreach ($value as $key => $rowValue) {
			$printValue = $this->helper->convertC('biblio', $facetField, $rowValue);
			$res[] = '<a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode([$facetField.':"'.$rowValue.'"'])]).'">'.$this->transEsc($rowValue).'</a>';
			}
	if (count($res)>1)
		$value = '<ol><li>'.implode('</li><li>', $res).'</li></ol>';
		else 
		$value = current($res);
	
	if (is_string($value))	
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$value.'</dd>
				</dl>
			';
		
		
	}

?>
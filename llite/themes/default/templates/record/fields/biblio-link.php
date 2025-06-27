<?php 
$translated = $translated ?? false;

if (!empty($value)) {
	$value = (array)$value;
	$Tval = [];
	if (is_array($value))
		foreach ($value as $val) {
			$valueStr = $this->helper->convert($facetField, $val);
			$Tval[] = '<a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode([$facetField.':"'.$val.'"'])]).'" title="'. $this->transEsc('Show results using filter').': '.$label.' = '.$valueStr.'">'.$valueStr.'</a>';
			}
	
	if (count($Tval)>1)
		$displayValue = "<ol><li>".implode('</li><li>', $Tval).'</li></ol>';
		else 
		$displayValue = current($Tval);
		
	echo '
			<dl class="detailsview-item">
			  <dt class="dv-label">'.$label.':</dt>
			  <dd class="dv-value">'.$displayValue.'</dd>
			</dl>
		';
		
	}

?>
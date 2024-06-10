<?php 
$translated = $translated ?? false;

if (!empty($value)) {
	$value = (array)$value;
	$Tval = [];
	if (is_array($value))
		foreach ($value as $val) {
			if ($translated)
				$valueStr = $this->transEsc($val);
				else 
				$valueStr = $val;
			$Tval[] = '<a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode([$facetField.':"'.$val.'"'])]).'">'.$valueStr.'</a>';
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
<?php 
$facetField = 'form_genre';
if (!empty($value)) {
	$value = (array)$value;
	if (is_array($value))
		foreach ($value as $formGenre) 
			$res[] = '<a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode([$facetField.':"'.$formGenre->name.'"'])]).'">'.$formGenre->name.'</a>';
			
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
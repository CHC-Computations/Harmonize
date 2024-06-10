<?php 

if (!empty($value)) {
	
	$Tsubjects = (array)$value;
	$Tvalues = [];
	
	foreach ($Tsubjects as $subjectGroup) {
		$lineLinks = [];
		$facetsValues = [];
		foreach ($subjectGroup as $subject) {
			$facetValues[] = $facetField.':"'.$subject.'"';
			$facetsCode = $this->buffer->createFacetsCode($facetValues);
			$lineLinks[] = '<a href="'.$this->buildUrl('results', ['core'=>'biblio', 'facetsCode'=>$facetsCode]).'">'.$subject.'</a>';
			}
		$Tvalues[] = implode(' &gt; ', $lineLinks);	
		}
	$value = '<ol><li>'.implode('</li><li>', $Tvalues).'</li></ol>';
	
	
	echo '
			<dl class="detailsview-item">
			  <dt class="dv-label">'.$label.':</dt>
			  <dd class="dv-value">'.$value.'</dd>
			</dl>
		';
	
	}

?>
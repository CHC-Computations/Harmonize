<?php 

if (!empty($value)) {
	
	$tLines = (array)$value;

	$lineLinks = [];
	foreach ($tLines as $string=>$count) {
		$lineLinks[] = $string.' <span style="float:right" class="badge" title="'.$this->transEsc('number of publications').'" data-toggle="tooltip">'.$count.'/'.$publicationsTotal	.'</span>';
		
		}
	$print = '<ol><li>'.implode('</li><li>', $lineLinks).'</li></ol>';
	
	
	echo '
			<dl class="detailsview-item">
			  <dt class="dv-label">'.$label.':</dt>
			  <dd class="dv-value">'.$print.'</dd>
			</dl>
		';
		
	}

?>
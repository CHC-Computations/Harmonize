<?php 

if (!empty($value)) {
	
	$tLines = (array)$value;

	$lineLinks = [];
	$total = 0;
	$i = 0;
	foreach ($tLines as $string=>$langArray) {
		$i++;
		$lineLinks[$i] = $string;
		foreach ($langArray as $lang=>$count) {
			$lineLinks[$i].=' <span class="label label-success" title="'.$this->transEsc('number of publications').'" data-toggle="tooltip">'.$lang.' ('.$count.')</span>';
			$total += $count;
			}
		
		}
	$i++;
	$addPrint = '<small style="display:block; margin-top:10px;">'.$this->transEsc('number of publications having information about original title').': <b>'.$total.'</b> / '.$publicationsTotal.'</small>';
	$print = '<ol><li>'.implode('</li><li>', $lineLinks).'</li></ol>';
	
	echo '
			<dl class="detailsview-item">
			  <dt class="dv-label">'.$label.':</dt>
			  <dd class="dv-value">'.$print.$addPrint.'</dd>
			</dl>
		';
		
	}

?>
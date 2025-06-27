<?php 

if (!empty($value)) {
	$tLines = (array)$value;
	$timeLine = '<div class="line-time">';
		
	for ($i = array_key_first($value); $i<=array_key_last($value); $i++) {
		$names = [];
		if (!empty($tLines[$i]))
			foreach ($tLines[$i] as $string=>$id)
				$names[] = '<small><a href="'.$this->basicUri('results/biblio/record/'.$id).'.html">'.$string.'</a></small>';
		
		$timeLine.='
				<div class="line-time-row">
					<div class="line-time-year">'.$i.'</div>
					<div class="line-time-point"></div>
				</div>
				<div class="line-time-row">
					<div class="line-time-year"></div>
					<div class="line-time-break"></div>
					<div class="line-time-name">'.implode('<br/>', $names).'</div>
				</div>
				';
		}
	$timeLine .= '</div>';
	
	
	echo '
			<dl class="detailsview-item">
			  <dt class="dv-label">'.$label.':</dt>
			  <dd class="dv-value">'.$timeLine.'</dd>
			</dl>
		';
		
	}

?>
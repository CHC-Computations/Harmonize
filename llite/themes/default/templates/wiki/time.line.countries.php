<?php 
 
	if (!empty($value) && is_array($value)) {
		$timeLine = '<div class="line-time">';
		foreach ($value as $k=>$range) {
			$timeLine.='
				<div class="line-time-row">
					<div class="line-time-year">'.$this->strToDate($range->dateTo).'</div>
					<div class="line-time-point"></div>
				</div>
				<div class="line-time-row">
					<div class="line-time-year"></div>
					<div class="line-time-break"></div>
					<div class="line-time-name"> <span class="name">'.$this->render('wiki/link.mini.php', ['value'=>$range->wikiId]).'</span></div>
				</div>
				';
			}
		$timeLine.= '
				<div class="line-time-row">
					<div class="line-time-year">'.$this->strToDate($range->dateFrom).'</div>
					<div class="line-time-point"></div>
				</div>
				';	
		$timeLine.= '</div>';
		
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$timeLine.'</dd>
				</dl>
			';
		
		}
?>
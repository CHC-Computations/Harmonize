<?php 
 
	if (!empty($value) && is_array($value)) {
		$timeLine = '<div class="line-time">';
		foreach ($value as $k=>$range) {
			$names = [];
			foreach ($range->names as $langCode=>$name) {
				$addStyle = '';
				if (!empty($name->deprecated))
					$addStyle = 'style="text-decoration: line-through"';
					else {
					$title = '';
					if (!empty($name->dateFrom)) 
						if ($name->dateFrom == '-9999-00-00T00:00:00Z')
							$title = $this->transEsc('since its inception');
							else 
							$title = $this->transEsc('since').' '.$this->strToDate($name->dateFrom);	
							
					$names[] = '<span class="langCode">'.$langCode.'</span> <span class="name" '.$addStyle.' title="'.$title.'">'.$name->value.'</span>';
					}
				}
			$timeLine.='
				<div class="line-time-row">
					<div class="line-time-year">'.$this->strToDate($range->dateTo).'</div>
					<div class="line-time-point"></div>
				</div>
				<div class="line-time-row">
					<div class="line-time-year"></div>
					<div class="line-time-break"></div>
					<div class="line-time-name">'.implode('<br/>', $names).'</div>
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
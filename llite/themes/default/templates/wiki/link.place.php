<?php 
	if (!empty($value)) {
		if (is_Array($value)) {
			$link = implode('<br/>', $value);
			foreach ($value as $v) {
				$this->wikiRec->loadRecord($v); 
				
				$oldName = $this->wikiRec->getHistoricalCityName($time);
				$currentName = $this->wikiRec->get('labels');
				$currentLang = $this->wikiRec->returnLang;
				if ($oldName->name <> $currentName) 
					$valuestr = $oldName->name.' (<small>'.$this->transEsc('at present in').' '.$currentLang.':</small> '.$currentName.')';
					else
					$valuestr = $currentName;
				
				$links[] = '<a href="'.$this->buildURL('wiki/record/'.$v).'">'.$valuestr.'</a>';
				if (!empty($mapPoint = $this->wikiRec->getCoordinates('P625'))) {
					$point['lon'] = $mapPoint->longitude;
					$point['lat'] = $mapPoint->latitude;
					$point['name'] = $this->wikiRec->get('labels');
					$point['desc'] = $label;
					$point['link'] = $this->buildURL('wiki/record/'.$v);
					$point['marker'] = 'marker';
					$point['color'] = 'green';
					$this->maps->saveMapsPoint($point);
					}
				}
			$link = implode('<br/>', $links);
			
			} else {
			$this->wikiRec->loadRecord($value); 
			$oldName = $this->wikiRec->getHistoricalCityName($time);
			$currentName = $this->wikiRec->get('labels');
			$currentLang = $this->wikiRec->returnLang;
			if ($oldName->name <> $currentName) 
				$valuestr = $oldName->name.' (<small>'.$this->transEsc('at present in').' '.$currentLang.':</small> '.$currentName.')';
				else
				$valuestr = $currentName;
			
			$link = '<a href="'.$this->buildURL('wiki/record/'.$value).'">'.$valuestr.'</a>';
			if (!empty($mapPoint = $this->wikiRec->getCoordinates('P625'))) {
				$point['lon'] = $mapPoint->longitude;
				$point['lat'] = $mapPoint->latitude;
				$point['name'] = $this->wikiRec->get('labels');
				$point['desc'] = $label;
				$point['link'] = $this->buildURL('wiki/record/'.$value);
				$point['marker'] = 'marker';
				$point['color'] = 'green';
				$this->maps->saveMapsPoint($point);
				}
			}
		
		#$link .="<br/>$value, $time";
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$link.'</dd>
				</dl>
			';
			
		
		}
?>
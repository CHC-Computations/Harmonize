<?php 
	
	if (!empty($value)) {
		if (is_Array($value)) {
			$link = implode('<br/>', $value);
			foreach ($value as $v) {
				$this->wikiRec->loadRecord($v); 
				$links[] = '<a href="'.$this->buildURL('wiki/record/'.$v).'">'.$this->wikiRec->get('labels').'</a>';
				if (!empty($mapPoint = $this->wikiRec->getCoordinates('P625'))) {
					$point['lon'] = $mapPoint->longitude;
					$point['lat'] = $mapPoint->latitude;
					$point['name'] = $this->wikiRec->get('labels');
					$point['desc'] = $label;
					$point['link'] = $this->buildURL('wiki/record/'.$v);
					$point['marker'] = 'marker';
					$point['color'] = 'red';
					$this->maps->saveMapsPoint($point);
					}
				}
			$link = implode('<br/>', $links);
			
			} else {
			$this->wikiRec->loadRecord($value); 
			$link = '<a href="'.$this->buildURL('wiki/record/'.$value).'">'.$this->wikiRec->get('labels').'</a>';
			if (!empty($mapPoint = $this->wikiRec->getCoordinates('P625'))) {
				$point['lon'] = $mapPoint->longitude;
				$point['lat'] = $mapPoint->latitude;
				$point['name'] = $this->wikiRec->get('labels');
				$point['desc'] = $label;
				$point['link'] = $this->buildURL('wiki/record/'.$value);
				$point['marker'] = 'marker';
				$point['color'] = 'red';
				$this->maps->saveMapsPoint($point);
				}
			}
		
 
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value">'.$link.'</dd>
				</dl>
			';
		}
?>
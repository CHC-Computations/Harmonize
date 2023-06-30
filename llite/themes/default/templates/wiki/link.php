<?php 
	
	if (!empty($value)) {
		if (is_Array($value)) {
			$link = implode('<br/>', $value);
			foreach ($value as $v) {
				$rec = new wikidata($v); 
				$rec->setUserLang($this->user->lang['userLang']);
				$links[] = '<a href="'.$this->buildURL('wiki/record/'.$v).'">'.$rec->get('labels').'</a>';
				if (!empty($mapPoint = $rec->getCoordinates('P625'))) {
					$point['lon'] = $mapPoint->longitude;
					$point['lat'] = $mapPoint->latitude;
					$point['name'] = $rec->get('labels');
					$point['desc'] = $label;
					$point['link'] = $this->buildURL('wiki/record/'.$v);
					$point['marker'] = 'marker';
					$point['color'] = 'red';
					$this->maps->saveMapsPoint($point);
					}
				}
			$link = implode('<br/>', $links);
			
			} else {
			$rec = new wikidata($value); 
			$rec->setUserLang($this->user->lang['userLang']);
			$link = '<a href="'.$this->buildURL('wiki/record/'.$value).'">'.$rec->get('labels').'</a>';
			if (!empty($mapPoint = $rec->getCoordinates('P625'))) {
				$point['lon'] = $mapPoint->longitude;
				$point['lat'] = $mapPoint->latitude;
				$point['name'] = $rec->get('labels');
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
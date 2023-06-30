<?php 
 
	if (!empty($value)) {
		echo '
				<dl class="detailsview-item">
				  <dt class="dv-label">'.$label.':</dt>
				  <dd class="dv-value"><a href="https://www.openstreetmap.org/#map=8/'.$value->latitude.'/'.$value->longitude.'">'.$value->latitude.','.$value->longitude.'</a></dd>
				</dl>
			';
		
		
		$point['lon'] = $value->longitude;
		$point['lat'] = $value->latitude;
		$point['name'] = $title;
		$point['desc'] = $label;
		$point['marker'] = 'marker';
		$point['color'] = 'blue';
		$this->maps->saveMapsPoint($point);
		
		
		}
?>
<?PHP


class maps {
	
	protected $cms;
	
	public $JS;
	public $mapsPoints;
	
	var $longs = []; 
	var $lats = []; 
	
	public function register($key, $value) {
		$this->$key = $value;
		}
		
	
	public function addPoint($place) {
		if (!empty($place['lat'])) {
			$markers = '';
			if (empty($place['icon'])) 
				$place['icon'] = 'VectorPointFiolet';
			$markers .= 'var marker = L.marker(['.$place['lat'].', '.$place['lon'].'], {icon: '.$place['icon'].'}).addTo(map);'; // 
			$this->longs[$place['lon']] = $place['lon'];
			$this->lats[$place['lat']] = $place['lat'];
				
			
			if (!empty($place['link']))
				$markers .= 'marker.bindPopup("'.$place['link'].'")'; 
			
			return $markers;
			}
		}	
	
	public function saveMapsPoint($point) {
		$this->mapsPoints[] = $point;
		}
		
	public function getMapsPoints() {
		if (!empty($this->mapsPoints))
			return $this->mapsPoints;
			else 
			return [];
		}
	
	
	public function mapMarkers() {
		return "
		var IconMarkerBlue = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_fiolet.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
		var IconMarkerGreen = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_green.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
		var IconMarkerRed = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_red.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
			
		var CircleNone = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_transparent.svg',
				iconSize: [42, 46],
				iconAnchor: [21, 23],
				popupAnchor: [0, -24]
			});
		var CircleActive = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_active.svg',
				iconSize: [42, 46],
				iconAnchor: [21, 23],
				popupAnchor: [0, -24]
			});
		var CircleTop = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_top.svg',
				iconSize: [42, 46],
				iconAnchor: [21, 23],
				popupAnchor: [0, -24]
			});
		var CircleMiddle = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_middle.svg',
				iconSize: [42, 46],
				iconAnchor: [21, 23],
				popupAnchor: [0, -24]
			});
		var CircleBottom = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_bottom.svg',
				iconSize: [42, 46],
				iconAnchor: [21, 23],
				popupAnchor: [0, -24]
			});
		var CircleSBottom = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/circle_bottom.svg',
				iconSize: [12, 16],
				iconAnchor: [6, 8],
				popupAnchor: [0, -8]
			});
			
		var IconSmallmarkerBlue = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_fiolet.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconSmallmarkerGreen = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_green.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconSmallmarkerRed = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/marker_red.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconPointBlue = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/po_blue.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		var IconPointGreen = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/po_green.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		var IconPointRed = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/po_red.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		var VectorPointFiolet = L.icon({
				iconUrl: '".$this->cms->HOST."themes/default/images/maps/point_fiolet.svg',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
			
			
			";
		
		}
	
	public function drawPolygon($points = [], $options = []) {
		
		}
	
	public function drawEuropeMap($Tp = array()) {
		$points = '';
		$lp = 0;
		
		$map = '';
		$markers = '';
		#$map .='<pre>'.print_r($Tp,1).'</pre>';
		foreach ($Tp as $k=>$p) 
			if ($p['lat']!=='') {
				$lp++;
				$markers .= 'var marker = L.marker(['.$p['lat'].', '.$p['lon'].']).addTo(map);';
				$markers .= 'marker.bindPopup("'.$p['map_label'].'").openPopup();';
				}
		 
		$map.= '<div id="map" ></div>';
		
		
		
		$this->JS = $this->mapMarkers()."
			var map = L.map('map').setView([53.581,23.063], 4);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 8,
				attribution: '© OpenStreetMap'
				}).addTo(map);
			$markers
			 ";
		$map.= '<script>'.$this->JS.'</script>';	 
		
		return $map;
		}	
		

	public function drawWorldMap($Tp = array()) {
		$points = '';
		$lp = 0;
		
		$map = '';
		$markers = '';
		# echo '<pre>'.print_r($Tp,1).'</pre>';
		if (!empty($Tp))
			foreach ($Tp as $k=>$p) {
				$lp++;
				$markers .= $p;
				}		
		foreach ($this->getMapsPoints() as $k=>$p) {
			$lp++;
			if (!empty($p['link']))
				$label = "<h3><a href='".$p['link']."'>".$p['name'].'</a></h3>';
				else 
				$label = '<h3>'.$p['name'].'</h3>';
			if (!empty($p['desc']))
				$label .= $p['desc'];
			
			$specMark = '';
			if (!empty($p['marker']) & !empty($p['color']))
				$specMark = ', {icon: Icon'.ucfirst($p['marker']).ucfirst($p['color']).'}';
			
			$markers .= 'var marker = L.marker(['.$p['lat'].', '.$p['lon'].']'.$specMark.').addTo(map);';
			$markers .= 'marker.bindPopup("'.$label.'");'; 
			$longs["{$p['lon']}"] = $p['lon'];
			$lats["{$p['lat']}"] = $p['lat'];
			}
			
			
		if (!empty($longs) && is_array($longs)) {
			$Latcenter = str_replace(',','.', max($lats)-(max($lats)-min($lats))/2);
			$Loncenter = str_replace(',','.', max($longs)-(max($longs)-min($longs))/2);
			} else {
			$Latcenter = 30.0; //53.581;
			$Loncenter = 23.063;
			}
			
		
		/*
		L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}.png', {
			attribution: '&copy; <a href='https://www.openstreetmap.org/copyright'>OpenStreetMap</a> contributors, &copy; <a href='https://carto.com/attribution'>CARTO</a>'
			}).addTo(map);
	
		*/
		$map.= '<div id="map" ></div>';
		$this->JS = $this->mapMarkers()."
			var map = L.map('map').setView([$Latcenter,$Loncenter], 2);
			L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
				maxZoom: 16,
				minZoom: 2,
				maxBounds: [[-90,-180], [90,180]],
				maxBoundsViscosity: 1.0,
				attribution: '© OpenStreetMap'
				}).addTo(map);
			map.invalidateSize();	
			".$markers;
		$map.= '<script>'.$this->JS.'</script>';	 
		
		return $map;
		}	
		
	}
?>
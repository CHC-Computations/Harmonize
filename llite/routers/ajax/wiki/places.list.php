<?php 
if (empty($this)) die;
require_once('functions/klasa.maps.php');
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this->config)); 
$this->addClass('buffer', 	new marcBuffer()); 

$this->buffer->setSQL($this->sql);

$results = json_decode(file_get_contents('http://localhost:8983/solr/lite.biblio/select?fq=(author_facet_s%3A%22'.urlencode($this->GET['author']).'%22)&q=*%3A*&q=*%3A*&facet=true&rows=0&facet.limit=10000&facet.sort=count&facet.mincount=1&facet.field=geographic_facet'));

echo "<h4>The places ".$this->helper->authorFormat($this->GET['author'])." wrote about:</h4>";
if (!empty($results->facet_counts->facet_fields->geographic_facet)) {
	
	$res = $results->facet_counts->facet_fields->geographic_facet;
	foreach ($res as $k2=>$v2)
		if ($k2 % 2 == 0) {
			$key = $v2;
			} else 
			$Tres[$key] = $v2;
	$markers = '';
	foreach ($Tres as $placeName=>$placeCount) {
		$place = $this->buffer->getPlaceParams($placeName);
		if (!empty($place['lat'])) {
			$link = "<h3>$place[name]</h3>$place[display_name]";
			$markers .= 'var marker = L.marker(['.$place['lat'].', '.$place['lon'].'], {icon: IconSmallmarkerBlue}).addTo(map);'; // 
			$markers .= 'marker.bindPopup("'.$link.'");'; 
			echo '<span title="place pointed on map" style="display:inline-block;"><img src="'.$this->HOST.'themes/default/images/maps/po_blue.png"> '.$placeName.' <span class=badge>'.$placeCount.'</span></span> ';
			} else 
			echo "<span style='opacity:0.5' title='place not pointed on map' style='display:inline-block;'>$placeName <span class=badge>$placeCount</span></span> ";
			
		}	
	$this->addJS($markers);	
	}



?>
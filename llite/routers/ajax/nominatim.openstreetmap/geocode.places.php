<?php
if (empty($this)) die;
$this->addClass('buffer', 	new marcBuffer()); 
$this->buffer->setSql($this->sql);
#echo "<pre>".print_R($this->routeParam,1).'</pre>';
$offset = $this->routeParam[0];
$step = $offset+1;
$totalResults = $this->routeParam[1];

$save = new stdClass;

$t = $this->psql->querySelect($Q = "SELECT name FROM places_wiki WHERE lon IS NULL OR lat IS NULL ORDER BY name LIMIT 1 OFFSET $offset;");
$W = 'nothig';
if (is_array($t)) {
	$rec = current($t);
	$placeName = $rec['name'];
	
	echo "<p></p>";
	echo $this->helper->percent($step,$totalResults);
	echo "<p></p>";
	echo "Checking: $step. <b>".$placeName.'</b> ';

	$geo = $this->buffer->getPlaceParams($placeName);
	if (empty($geo) && stristr($placeName, '(')) {
		$tmp = explode('(', $placeName);
		$geo = $this->buffer->getPlaceParams(urlencode(trim($tmp[0])));
		} 
	
	#echo "<pre>".print_r($geo,1).'</pre>';
	if (!empty($geo) && is_array($geo)) {
		$save->lon = $geo['lon'];
		$save->lat = $geo['lat'];
		
		foreach ($save as $k=>$v) {
			$ch[] = $k.'='.$this->psql->isNull($v);
			}
		$W = $this->psql->query($Q = "UPDATE places_wiki SET ".implode(', ',$ch)." WHERE name='$placeName';");
		echo ' --- OK!';
		} else {
		$W = 1;	
		}
		
	echo '<br/><br/>';
	}
	
	
if ($step<$totalResults) {
	$OC = "page.ajax('apiCheckBox', 'nominatim.openstreetmap/geocode.places/$step/$totalResults');";	
	if ($W == 1) 
		$this->addJS ($OC);
		#echo '<button class="btn btn-succes" onClick="'.$OC.'">next</button>';
		else {
		echo '<button class="btn btn-succes" onClick="'.$OC.'">next</button>';
		echo "<div class='alert alert-info'>";				
		echo "$Q<br/>";
		echo "zapis: $W <br/>";
		echo '</div>';
		}
	}

?>
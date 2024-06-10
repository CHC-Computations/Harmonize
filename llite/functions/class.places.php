<?php

#require_once 'File/MARC.php';


class places {
	
	
	public function __construct($sql) {
		$this->sql = $sql;
		}

	public function baseURL($link) {
		$this->baseURL = $link;
		}

	public function prepareMapPoint($place) {
		$markers = '';
		
		$link = "<h3><a href='".$this->baseURL."?place=".urlencode($place['name'])."'>$place[name]</a></h3>$place[display_name]";
		
		$markers .= 'var marker = L.marker(['.$place['lat'].', '.$place['lon'].'], {icon: myIcon}).addTo(map);'; // 
		$markers .= 'marker.bindPopup("'.$link.'")'; 
		
		return $markers;
		}	


	public function getFullList() {
		$res = $this->sql->query($Q = "SELECT * FROM `libri_places`;");
		if ($res->num_rows>0) {
			while ($row = mysqli_fetch_assoc($res)) {
				$T[] = $row;
				}
			return $T;	
			}
		}
		
	}

?>
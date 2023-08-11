<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.maps.php');
$this->addClass('helper', new helper()); 
$this->addClass('maps',	new maps()); 


$addStr = '';
if (count($this->GET)>0) {
	foreach ($this->GET as $k=>$v)
		$parts[]= $k.'='.$v;
	$addStr = implode('&', $parts);	
	} 

$WAR = "lon IS NOT NULL AND lat IS NOT NULL AND wikiq IS NOT NULL";
if (!empty ($this->GET['lookfor'])) {
	$sstring = $this->urlName2($this->GET['lookfor']);
	$WAR .= " AND sstring ILIKE '%{$sstring}%'";
	}

if (empty($this->routeParam[0])) {
	###########################################################################################################################################
	##
	##										FIRST STEP
	##
	###########################################################################################################################################
	
		$t = $this->psql->querySelect($Q = "SELECT count(*) as recsum, max(subjecthits+pubplacehits+personhits) as recmax FROM places_on_map WHERE $WAR;");
		if (is_array($t)) {
			$res = current($t);
			$recSum = $res['recsum'];
			$recMax = $res['recmax'];
			echo $this->transEsc('Points on map').": $recSum<br/>";
			$this->addJS("page.ajax('ajaxBox','wiki/places.show.on.map/$recSum/0/$recMax?$addStr');");
			} else {
			echo $this->tranEsc('Map is empty');	
			}
		
	} else {
	###########################################################################################################################################
	##
	##										Checkig points 
	##
	###########################################################################################################################################
	$max = $this->routeParam[0];
	$offset = $this->routeParam[1];
	$pmax = $this->routeParam[2];
	$step = 400;
	
	$PlaceCircleBasic = "color: '#76679B', fillColor: '#5F3D8D', weight: 2, fillOpacity: 0.5";
	$PlaceCircleHover = "color: 'red', fillColor: 'yellow', weight: 1, fillOpacity: 0.5";
	$emptyStr = ''; //$this->transEsc('Point on map to see details');

	$lon['min'] = $lat['min'] = -999;
	$lon['max'] = $lat['max'] = 999;
		
	$t = $this->psql->querySelect("SELECT DISTINCT * FROM places_on_map WHERE $WAR ORDER BY wikiq LIMIT $step OFFSET $offset;"); 
	if (is_array($t)) {	
		foreach ($t as $res) {
			############################################################ adding point to map
			
			$place = $res;
			$place['sumplaces'] = $place['subjecthits']+$place['pubplacehits']+$place['personhits'];
			$pjs = [];
			$rad = round(($place['sumplaces']/$pmax)*4500)+400;	
			$proc = round(($place['sumplaces']/$pmax)*0.7, 2)+0.2;	
			
			$place['link'] = "<h3><a href='{$this->buildUrl('wiki/record/Q'.$res['wikiq'])}'>$res[name]</a></h3>";
			#if (!empty($res['names'])) 	$place['link'] .= $this->transEsc('Other names: ').$res['names'].'<br/><br/>';
			$place['link'] .= $this->transEsc('Exists as').':<br/>';
			$place['link'] .= $this->transEsc('Subject place').': '.$res['subjecthits'].'<br/>';
			$place['link'] .= $this->transEsc('Publication place').': '.$res['pubplacehits'].'<br/>';
			$place['link'] .= $this->transEsc('Person place').': '.$res['personhits'].'<br/>';
			$key = $res['wikiq'];
			
			
			$pjs[] = "var scircle_$res[wikiq] = L.circle([$place[lat], $place[lon]], {".$PlaceCircleBasic.", radius: $rad }).addTo(map); ";
			$pjs[] = "scircle_$res[wikiq].on({
					mouseover: function () {
							//$('#poitedDet').html(\"$place[link]\");
							var layer = this;
							layer.setStyle({".$PlaceCircleHover."});
							// layer.bringToFront();
							},
					mouseout: function () {
							//$('#poitedDet').html('{$emptyStr}');
							var layer = this;
							layer.setStyle({".$PlaceCircleBasic."});
							},
					click: function () {
							$('#poitedDet').html(\"$place[link]\");
							}
				});
				scircle_$res[wikiq].bindPopup(\"{$place['link']}\")";
			$js[] = implode("\n", $pjs);
			}
		
		echo $this->helper->percent($offset, $max);
		$this->addJS(implode("\n", $js));	

		$stepNext = $offset+$step;
		$nextJS = "page.ajax('ajaxBox','wiki/places.show.on.map/$max/$stepNext/$pmax?$addStr');";
		$this->addJS($nextJS);
		#echo '<div class="text-right"><button class="btn btn-xs" OnClick="'.$nextJS.'">*</button></div><br/><br/>';
		#echo '<textarea>'.$nextJS.'</textarea><br/><br/>';
		} else {
		
		$t = $this->psql->querySelect($Q = "SELECT sum(subjecthits) as subjects, sum(pubplacehits) as pubplaces, sum(personhits) as personplaces  FROM places_on_map WHERE $WAR;");
		
		$OMO = "$('#poitedDet').html(this.title);";
		$sums = '<div style="margin-top:10px; margin-bottom:10px; width:100%; text-align:center;"><div class="btn-group">';
		if (is_array($t)) {
			$res = current($t);
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Subject places shown on the map').'"><i class="ph-notebook-bold"></i> '.$this->helper->numberFormat($res['subjects']).'</button>';
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Publication places shown on the map').'"><i class="ph-house-line-bold"></i> '.$this->helper->numberFormat($res['pubplaces']).'</button>';
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Places of birth or death of persons appearing in the bibliography').'"><i class="ph-person-simple-bold"></i> '.$this->helper->numberFormat($res['personplaces']).'</button>';
			
			}
		$sums.="</div></div>";
		
		echo '<div class="detailsview">';
		echo '<dl class="detailsview-item"><dt class="dv-label">'.$this->transEsc("Points on map").':</dt><dd class="dv-value"><strong>'.$this->helper->numberFormat($max).'</strong></dd></dl>';
		echo '</div>';
		echo $sums;
		#echo $this->transEsc("Max publications/point").": <strong>".$this->helper->numberFormat($pmax).'</strong><br/>';
		echo "<div id='poitedDet'>".$emptyStr."</div>";
				
		}

	} 


?>
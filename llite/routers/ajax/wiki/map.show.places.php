<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.maps.php');
$this->addClass('helper', new helper()); 
$this->addClass('maps',	new maps()); 

$bufferFileName = './files/maps/'.$this->langCode.'-fullMap.js';

$addStr = '';
if (count($this->GET)>0) {
	foreach ($this->GET as $k=>$v)
		$parts[]= $k.'='.$v;
	$addStr = implode('&', $parts);	
	} 

$WAR = "lon IS NOT NULL AND lat IS NOT NULL AND wikiq IS NOT NULL";

// ?q=*:*&q.op=OR&indent=true&fq=latitiude:[51 TO 53]&fq=longitiude:[15 TO 23]&sort=total_count desc

if (!empty ($this->GET['lookfor'])) {
	$sstring = $this->urlName2($this->GET['lookfor']);
	$WAR .= " AND sstring ILIKE '%{$sstring}%'";
	}

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
		
	###########################################################################################################################################
	##
	##										Drawing points 
	##
	###########################################################################################################################################
	$step = 100;
	
	$colorTop = '#f3984e';
	$colorMiddle = '#f0cc41';
	$colorBottom = '#88d167';
	
	// meybe usefull? https://github.com/moravcik/Leaflet.TextIcon
	
	$PlaceCircleBasic = "color: '#76679B', fillColor: '#5F3D8D', weight: 2, fillOpacity: 0.5";
	$PlaceCircleHover = "color: 'red', fillColor: 'yellow', weight: 1, fillOpacity: 0.5";
	$emptyStr = ''; //$this->transEsc('Point on map to see details');
		
	$t = $this->psql->querySelect("SELECT DISTINCT *, (subjecthits+pubplacehits+personhits) as totalhits FROM places_on_map WHERE $WAR ORDER BY totalhits DESC LIMIT $step;"); 
	if (is_array($t)) {
		$lp = 0;
		$partCount = count($t);
		$last = end($t);
		$recMin = $last['totalhits'];
		
		$colStep = ($recMax-$recMin)/3;
		$col1 = $recMax-$colStep;
		$col2 = $recMin+$colStep;
		
		foreach ($t as $res) {
			
			############################################################ adding point to map
			$lp++;
			$place = $res;
			$pjs = [];
			
			$icon = 'SBottom';
			if ($lp<10) { $label = 'Bottom'; $icon = 'None'; }
			if ($lp<6) { $label = 'Middle'; $icon = 'None'; }
			if ($lp<3) { $label = 'Top'; $icon = 'None'; }
			
			$tlat[] = $place['lat'];
			$tlon[] = $place['lon'];
			
			$wikiName = $this->helper->formatWiki($res['wikiq']);
			$place['link'] = "<h3><a href='{$this->buildUrl('wiki/record/Q'.$res['wikiq'])}'>$wikiName</a></h3>";
			#if (!empty($res['names'])) 	$place['link'] .= $this->transEsc('Other names: ').$res['names'].'<br/><br/>';
			$place['link'] .= $this->transEsc('Exists as').':<br/>';
			$place['link'] .= $this->transEsc('Subject place').': '.$res['subjecthits'].'<br/>';
			$place['link'] .= $this->transEsc('Publication place').': '.$res['pubplacehits'].'<br/>';
			$place['link'] .= $this->transEsc('Person place').': '.$res['personhits'].'<br/>';
			$key = $res['wikiq'];
			
			$pjs[] = "var smarker_$res[wikiq] = L.marker([$place[lat], $place[lon]], {icon: Circle$icon }); ";
			$pjs[] = "smarker_$res[wikiq].addTo(map);";
			if ($lp<10) $pjs[] = "smarker_$res[wikiq].bindTooltip('".$this->helper->badgeFormat($place['totalhits'])."' , {permanent: true, direction: 'center', className: 'label-{$label}' });";
			$pjs[] = "smarker_$res[wikiq].on({click: function () { $('#poitedDet').html(\"$place[link]\");	}});";
			$pjs[] = "smarker_$res[wikiq].bindPopup(\"{$place['link']}\")";
			$js[] = implode("\n", $pjs);
			}
		
		$lon['min'] = min($tlon);
		$lat['min'] = min($tlat);
		$lon['max'] = max($tlon);
		$lat['max'] = max($tlat);
		$js[] = "map.fitBounds([[$lat[max],$lon[max]],[$lat[min],$lon[min]]]);";
		$js[] = "$('#mapStartZoom').val(map.getZoom());";
		# $js[] = "map.on('zoomend', function() { $('#mapLastAction').html('zoom end'); });";
		$js[] = "map.on('moveend', function() { 
					// page.ajax('ajaxBox','wiki/places.show.on.map?$addStr&N='+map.getBounds().getNorth()+'S='+map.getBounds().getSouth()+'W='+map.getBounds().getWest()+'E='+map.getBounds().getEast());
					$('#mapBoundN').val(map.getBounds().getNorth());
					$('#mapBoundS').val(map.getBounds().getSouth());
					$('#mapBoundE').val(map.getBounds().getEast());
					$('#mapBoundW').val(map.getBounds().getWest());
					$('#mapZoom').val(map.getZoom());
					results.maps.moved('$recSum', '$lp');
					});";
		$this->addJS(implode("\n", $js));	

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
		echo '<dl class="detailsview-item"><dt class="dv-label">'.$this->transEsc("Total results").':</dt><dd class="dv-value"><strong>'.$this->helper->numberFormat($recSum).'</strong></dd></dl>';
		echo '<dl class="detailsview-item"><dt class="dv-label">'.$this->transEsc("Shown on map").':</dt><dd class="dv-value"><strong id="mapMovedActions">'.$this->helper->numberFormat($lp).'</strong></dd></dl>';
		echo '</div>';
		echo $sums;
		#echo $this->transEsc("Max publications/point").": <strong>".$this->helper->numberFormat($pmax).'</strong><br/>';
		echo "<div id='poitedDet'>".$emptyStr."</div>";
		echo '
			<div id="mapLastAction" class="text-center" style="display:none;">
				<hr><small>The part below will disappear when I`m done with it</small><br/>
				N: <input id="mapBoundN"><br/>
				S: <input id="mapBoundS"><br/>
				E: <input id="mapBoundE"><br/>
				W: <input id="mapBoundW"><br/>
				ZS: <input id="mapStartZoom"><br/>
				Z: <input id="mapZoom"><br/>
				<button class="btn btn-success" type="button" OnClick="results.maps.moved('.$recSum.', '.$lp.');"><i class="ph-bold ph-map-pin"></i> Reload</button>
			</div>';
				
		}
		
		
		
	} else {
	echo $this->tranEsc('No results');	
	}
		
	
	

	


?>
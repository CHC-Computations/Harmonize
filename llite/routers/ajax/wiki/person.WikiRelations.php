<?php 
require_once('functions/klasa.buffer.php');
require_once('functions/klasa.wikidata.php');

$this->addClass('solr', 	new solr($this->config)); 
$this->addClass('buffer', 	new marcbuffer()); 


$wikiId = $this->routeParam[0];
$wikiIntId = substr($wikiId,1);

$rightMenu = '';
$mch = 0;

$t = $this->psql->querySelect("SELECT name, viaf_id, year_born, year_death, place_born, place_death, solr_str FROM persons WHERE wikiq='$wikiIntId';");
if (is_array($t)) {
	$person = current($t);
	if ($person['place_born']<>'') {
		$pointStart = $person['place_born'];
		}
	if ($person['place_death']<>'')
		$pointEnd = $person['place_death'];
	
	$Tcoor[$person['place_born']] = $person['place_born'];
	$Tcoor[$person['place_death']] = $person['place_death'];
	
	}
	

$wiki = new wikidata($wikiId); 
$wiki->setUserLang($this->user->lang['userLang']);

$livingPlaces = $wiki->getPropIds('P551');
if (is_Array($livingPlaces))
	foreach ($livingPlaces as $placeId) {
		$placeWikiQ = substr($placeId,1);
		$Tcoor[$placeWikiQ] = $placeWikiQ;
		}

$facetsOptions = $this->getConfig('facets','facetList');
$facetsNames = [
	'geographicpublication_str_mv' => ['color' => 'purple', 'name'=>'Publication places'],
	'geographic_facet' => ['color' => 'red', 'name'=>'Subject places']
	];
$query['q'] = ['field' => 'q', 'value' => 'author_wiki:'.$wikiIntId]; // persons_wiki_str_mv - if any role 
$query['limit'] = ['field' => 'facet.limit', 'value' => 1000];
$results = $this->solr->getFacets('biblio', ['geographicpublication_str_mv', 'geographic_facet'], $query);
if (!empty($results))
	foreach ($results as $index=>$places)
		foreach ($places as $place=>$count) {
			$tmp = explode('|',$place);
			if (($tmp[0]<>'')&($tmp[0]<>'not found')) {
				$Tcoor[$tmp[0]] = $tmp[0];
				@$checkedResults[$index][$tmp[0]] += $count;
				$checkedSolrStr[$index][$tmp[0]] = $place;
				} else 
				unset($results[$index][$place]);
			}

if (!empty($Tcoor) && is_Array($Tcoor)) {
	foreach ($Tcoor as $coor)
		if (!empty($coor))
			$Tin[$coor] = $coor;
	if (!empty($Tin)) {
		$t = $this->psql->querySelect("SELECT wikiq,lat||','||lon as coor, lat, lon, name FROM places_on_map WHERE wikiq IN (".implode(',', $Tin).") AND lat IS NOT NULL AND lon IS NOT NULL;");
		if (is_Array($t))
			foreach ($t as $coor)
				$Tcoor[$coor['wikiq']] = $coor;


		####################################################################################################################################################################
		$mch = 0;
		$js = [];
		if (!empty($checkedResults))
			foreach ($checkedResults as $index=>$places) {
				$mch++;
				if ($this->routeParam[$mch] == 'true') {
					foreach ($places as $placeWikiQ=>$count) {
						if (!empty($Tcoor[$placeWikiQ]['lat'])) {
							$gpoint = $Tcoor[$placeWikiQ];
							
							$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
							$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
							$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
							$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
							if (empty($placesOnMap[$gpoint['coor']]['color']))
								$placesOnMap[$gpoint['coor']]['color'] = $facetsNames[$index]['color'];
								else 
								$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
							@$placesOnMap[$gpoint['coor']]['count'] += $count;
							$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = $count;
							$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc($facetsNames[$index]['name']);
							$placesOnMap[$gpoint['coor']]['roles'][$index]['link'] = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["$index:\"{$checkedSolrStr[$index][$placeWikiQ]}\"", "author_wiki:\"{$wikiIntId}\""]));
							$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $facetsNames[$index]['color'];
							
							
							$lat = $gpoint['lat'];
							$lon = $gpoint['lon'];
							$Tlat[$lat] = $lat;
							$Tlon[$lon] = $lon;
							}	
						}
					// <img src="'.$this->HOST.'themes/default/images/maps/po_'.$color.'.png">
					$checked = 'checked';
					} else 
					$checked = '';	
				$color = strtolower($facetsNames[$index]['color']);	
				$rightMenu.= '<label class="switch"><input type="checkbox" '.$checked.' id="map_checkbox_'.$mch.'" OnChange="results.maps.addPersonRelatations(\''.$wikiId.'\')"><span class="slider slider-'.$color.' round"></span></label> ';
				$rightMenu.= $this->transEsc($facetsNames[$index]['name']).' <span class="badge">'.$this->helper->badgeFormat(count($checkedResults[$index])).'</span><br/>';
				}



		$mch++;
		$lp = 0;
			
		if ($this->routeParam[$mch] == 'true') {
			// <img src="'.$this->HOST.'themes/default/images/maps/po_green.png">
			$index = 'livingPlaces';
			
			if (!empty($pointStart)) {			
				$gpoint = $Tcoor[$pointStart];
						
				$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
				$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
				$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
				$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
				if (empty($placesOnMap[$gpoint['coor']]['color']))
					$placesOnMap[$gpoint['coor']]['color'] = 'green';
					else 
					$placesOnMap[$gpoint['coor']]['color'] = 'mixed';	
				@$placesOnMap[$gpoint['coor']]['count'] += '1';
				$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = '1';
				$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc('Place of birth');
				
				
				$lat = $gpoint['lat'];
				$lon = $gpoint['lon'];
				$Tlat[$lat] = $lat;
				$Tlon[$lon] = $lon;
				$lastPoint = $gpoint['coor'];
				$lp = 1;
				}
				
			if (!empty($livingPlaces)){
				foreach ($livingPlaces as $placeId) {
					$placeWikiQ = substr($placeId,1);
					if (!empty($Tcoor[$placeWikiQ]['lat'])) {
						$lp++;
						$gpoint = $Tcoor[$placeWikiQ];
						
						$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
						$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
						$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
						$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
						
						if (empty($placesOnMap[$gpoint['coor']]['color']))
							$placesOnMap[$gpoint['coor']]['color'] = 'green';
							else 
							$placesOnMap[$gpoint['coor']]['color'] = 'mixed';	
						@$placesOnMap[$gpoint['coor']]['count'] += 1;
						@$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] += 1;
						$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc('Residence place');
						if (!empty($lastPoint))
							$js[] = "var apolygon = L.polygon([[$lastPoint], [{$gpoint['coor']}]], {color: 'green', weight: 3, opacity: 0.4, smoothFactor: 1}).addTo(map);";
						$lastPoint = $gpoint['coor'];

						$lat = $gpoint['lat'];
						$lon = $gpoint['lon'];
						$Tlat[$lat] = $lat;
						$Tlon[$lon] = $lon;
						} else {
						$pointsErrors[] = "No coordinates for $placeId<br/>";	
						}
					}
				} 
			if (!empty($pointEnd)) {	
				$lp++;
				$gpoint = $Tcoor[$pointEnd];
				if (!empty($lastPoint))
					$js[] = "var apolygon = L.polygon([[$lastPoint], [{$gpoint['coor']}]], {color: 'green', weight: 3, opacity: 0.4, smoothFactor: 1}).addTo(map);";
					$lastPoint = $gpoint['coor'];

				$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
				$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
				$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
				$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
							
				if (empty($placesOnMap[$gpoint['coor']]['color']))
					$placesOnMap[$gpoint['coor']]['color'] = 'green';
					else 
					$placesOnMap[$gpoint['coor']]['color'] = 'mixed';	
				@$placesOnMap[$gpoint['coor']]['count'] += '1';
				$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = '1';
				$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc('Place of death');
				
				$lat = $gpoint['lat'];
				$lon = $gpoint['lon'];
				$Tlat[$lat] = $lat;
				$Tlon[$lon] = $lon;
				}
			$checked = 'checked';
			} else 
			$checked = '';	
		$rightMenu .= '<label class="switch"><input type="checkbox" '.$checked.' id="map_checkbox_'.$mch.'" OnChange="results.maps.addPersonRelatations(\''.$wikiId.'\')"><span class="slider slider-green round"></span></label> '.$this->transEsc('Residence places').' <span class="badge">'.$this->helper->badgeFormat($lp).'</span>';
		$rightMenu .= ' <small>'.$this->transEsc('order may not be correct').'.</small><br/>';
			

		$mixed = 0;
		if (!empty($placesOnMap)) 
			foreach ($placesOnMap as $coor=>$place) {
				$pjs = [];
				#echo $this->helper->pre($place);
				$point['link'] = "<h3><a href='{$place['headLink']}'>{$place['wikiName']}</a></h3>";
				foreach ($place['roles'] as $index=>$role) 
					if (!empty($role['link']))
						$point['link'] .= "<a href='$role[link]'>{$role['title']} <span class='badge badge-$role[color]'>$role[count]</span></a><br/>";
						else
						$point['link'] .= "{$role['title']} <span class=badge>$role[count]</span><br/>";
				$key = uniqid();
				$pjs[] = "var smarker_$key = L.marker([$coor], {icon: CircleNone }); ";
				$pjs[] = "smarker_$key.addTo(map);";
				$pjs[] = "smarker_$key.bindTooltip('".$this->helper->badgeFormat($place['count'])."' , {permanent: true, direction: 'center', className: 'relationLabel-{$place['color']}' });";
				$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
				$js[] = implode("\n", $pjs);
				if ($place['color'] == 'mixed') 
					$mixed++;
				}
		if ($mixed>0)
			$rightMenu .= '<span class="relationLabel-mixed">'.$mixed.'</span> &nbsp;&nbsp; '.$this->transEsc('Mixed relation points').' <br/><br/>';		

		if (!empty($Tlat)) { 
			$latMin = min($Tlat);
			$lonMin = min($Tlon);
			$latMax = max($Tlat);
			$lonMax = max($Tlon);
			
			if (($latMax-$latMin)>160) {
				$latMin = 80;
				$latMax = -80;
				}
			$latlonMin = $latMin.','.$lonMin;
			$latlonMax = $latMax.','.$lonMax;


			$js[] = "map.fitBounds([[$latlonMax],[$latlonMin]]);";
			}
		$js[] = "$('#mapRelationsAjaxArea').css('opacity', '1');";

		$leftMenu = '
			<button>Only points</button><br/>
			<button class="active">Points with numbers</button><br/>
			<button>Points with number & label</button>
			';
		$leftMenu = '';	
		echo $this->transEsc('Display on map').':<br/>
			<div class="row">
				<div class="col-sm-8">'.$rightMenu.'</div>
				<div class="col-sm-4">'.$leftMenu.'</div>
			</div>
			';

		#echo $this->helper->alert('danger', 'Test data. Please do not draw conclusions based on the graph yet! ;-)');
		#echo $this->helper->pre($placesOnMap);	
		#echo '<br/><br/><br/><textarea style="width:100%">'.implode("\n", $js).'</textarea>';

		// map.removeLayer(marker)
		$this->addJS ("map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); ");
		$this->addJS(implode("\n", $js));	
		}
	}
?> 
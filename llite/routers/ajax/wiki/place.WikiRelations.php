<br/>
<?php 
require_once('functions/klasa.buffer.php');
require_once('functions/klasa.wikidata.php');

$this->addClass('solr', 	new solr($this->config)); 
$this->addClass('buffer', 	new marcbuffer());
$wikiId = $this->routeParam[0];
$wikiIntId = substr($wikiId,1);

$Tcoor[$wikiIntId] = $wikiIntId;

$arrivals = $migrated = [];

########## taking data from sql 

$t = $this->psql->querySelect("SELECT wikiq, name, year_born, year_death, place_born, place_death FROM persons WHERE place_born='$wikiIntId' OR place_death='$wikiIntId';");
if (is_array($t)) {
	foreach ($t as $person) {
		if ($person['place_born']<>$wikiIntId) 
			$arrivals[$person['place_born']][] = $person;
		if ($person['place_death']<>$wikiIntId)
			$migrated[$person['place_death']][] = $person;
		
		$Tcoor[$person['place_born']] = $person['place_born'];
		$Tcoor[$person['place_death']] = $person['place_death'];
		}
	}
	

########## taking data from solr 

$facetsOptions = $this->getConfig('facets','facetList');
$facetsNames = [
	'geographicpublication_str_mv' => ['color' => 'purple', 'name'=>'Place of publication of works with subject place'],
	'geographic_facet' => ['color' => 'red', 'name'=>'Subject place of works publicated in']
	];
$query['q'] = ['field' => 'q', 'value' => 'geowiki_str_mv:'.$wikiIntId];
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


########## checkig for coordinations for all data 

foreach ($Tcoor as $coor)
	if (!empty($coor))
		$Tin[$coor] = $coor;
$t = $this->psql->querySelect("SELECT wikiq,lat||','||lon as coor, lat, lon, name FROM places_on_map WHERE wikiq IN (".implode(',', $Tin).") AND lat IS NOT NULL AND lon IS NOT NULL;");
if (is_Array($t))
	foreach ($t as $coor)
		$Tcoor[$coor['wikiq']] = $coor;



######### preparing points

$js = [];
$this->addJS ("map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); ");

if (!empty($Tcoor[$wikiIntId]['coor'])) {
	$base = $Tcoor[$wikiIntId]['coor'];
	$baseName = $this->helper->formatWiki($wikiIntId);

	$mch = 0;

	if (!empty($checkedResults))
		foreach ($checkedResults as $index=>$places) {
			$mch++;
			if ($this->routeParam[$mch] == 'true') {
				foreach ($places as $placeWikiQ=>$count) {
					if (!empty($Tcoor[$placeWikiQ]['lat'])) {
						$gpoint = $Tcoor[$placeWikiQ];
						
						$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
						$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
						$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
						$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
						
						if (empty($placesOnMap[$gpoint['coor']]['color']))
							$placesOnMap[$gpoint['coor']]['color'] = $facetsNames[$index]['color'];
							else 
							$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
						@$placesOnMap[$gpoint['coor']]['count'] += $count;
						$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = $count;
						$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc($facetsNames[$index]['name']).' '.$baseName;
						$placesOnMap[$gpoint['coor']]['roles'][$index]['link'] = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["$index:\"{$checkedSolrStr[$index][$placeWikiQ]}\"", "persons_wiki_str_mv:\"{$wikiIntId}\""]));
						$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $facetsNames[$index]['color'];
						
						
						$lat = $gpoint['lat'];
						$lon = $gpoint['lon'];
						$Tlat[$lat] = $lat;
						$Tlon[$lon] = $lon;
						}	
					}
				$checked = 'checked';
				} else 
				$checked = '';	
			
			echo $this->render('helpers/switch.php', [
						'color'=>$facetsNames[$index]['color'], 
						'checked'=>$checked, 
						'id'=>'map_checkbox_'.$mch, 
						'onChange'=> "results.maps.addPlaceRelatations('$wikiId')", 
						'label'=>$this->transEsc($facetsNames[$index]['name']).' '.$baseName, 
						'badge'=>count($checkedResults[$index])
						]);
			}


	if (!empty($arrivals)){
		$color = 'mediumturquoise';
		$index = 'arrivals';		
		$mch++;
		if ($this->routeParam[$mch] == 'true') {	
			foreach ($arrivals as $point=>$persons) 
				if (!empty($Tcoor[$point]['lat'])) {
					$gpoint = $Tcoor[$point];
					
					$count = count($persons);
					$placeLink = '';
					$lp = 0;
					foreach ($persons as $person) {
						$lp++;
						$placeLink .= "<a href='{$this->buildUrl('wiki/record/Q'.$person['wikiq'])}'>$person[name]</a><br/>";
						if ($lp>6) {
							$placeLink .= $this->transEsc('and more').'...';
							break;
							}
						}
					
					$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
					$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
					$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
					$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
						
					if (empty($placesOnMap[$gpoint['coor']]['color'])) 
						$placesOnMap[$gpoint['coor']]['color'] = $color;
						else if ($placesOnMap[$gpoint['coor']]['color'] !== $color) 
						$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
						
					@$placesOnMap[$gpoint['coor']]['count'] += $count;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = $count;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc('They were born here');
					$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $color;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['addText'] = $placeLink;
					
					
					$lat = $gpoint['lat'];
					$lon = $gpoint['lon'];
					$Tlat[$lat] = $lat;
					$Tlon[$lon] = $lon;
					}
			$checked = 'checked';
			} else 
			$checked = '';

		echo $this->render('helpers/switch.php', [
						'color'=>$color, 
						'checked'=>$checked, 
						'id'=>'map_checkbox_'.$mch, 
						'onChange'=> "results.maps.addPlaceRelatations('$wikiId')", 
						'label'=>$this->transEsc('Birthplace of newcomers'), 
						'badge'=>count($arrivals)
						]);
		}
		
		
	if (!empty($migrated)){
		$color = 'darkgreen';
		$index = 'migrated';		
		$mch++;
		if ($this->routeParam[$mch] == 'true') {	
			foreach ($migrated as $point=>$persons) 
				if (!empty($Tcoor[$point]['lat'])) {
					$gpoint = $Tcoor[$point];
					
					$count = count($persons);
					$placeLink = '';
					$lp = 0;
					foreach ($persons as $person) {
						$lp++;
						$placeLink .= "<a href='{$this->buildUrl('wiki/record/Q'.$person['wikiq'])}'>$person[name]</a><br/>";
						if ($lp>6) {
							$placeLink .= $this->transEsc('and more').'...';
							break;
							}
						}
					
					
					$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
					$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
					$placesOnMap[$gpoint['coor']]['wikiq'] = $gpoint['wikiq'];
					$placesOnMap[$gpoint['coor']]['wikiName'] = $this->helper->formatWiki($gpoint['wikiq']);
						
					if (empty($placesOnMap[$gpoint['coor']]['color']))
						$placesOnMap[$gpoint['coor']]['color'] = $color;
						else 
						$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
					@$placesOnMap[$gpoint['coor']]['count'] += $count;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = $count;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $this->transEsc('They died here');
					$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $color;
					$placesOnMap[$gpoint['coor']]['roles'][$index]['addText'] = $placeLink;
					
					$lat = $gpoint['lat'];
					$lon = $gpoint['lon'];
					$Tlat[$lat] = $lat;
					$Tlon[$lon] = $lon;
					
					/*
					$pjs[] = "var mmarker = L.marker([{$Tcoor[$point]['coor']}], {icon: IconPointRed }).addTo(map); ";
					$pjs[] = "mmarker.bindPopup(\"{$placeLink}\")";
					$pjs[] = "var mpolygon = L.polygon([[$base], [{$Tcoor[$point]['coor']}]], {color: 'red', weight: 1, opacity: 0.5, smoothFactor: 1}).addTo(map);";
					*/
					}
			$checked = 'checked';
			} else 
			$checked = '';

		echo $this->render('helpers/switch.php', [
						'color'=> $color, 
						'checked'=>$checked, 
						'id'=>'map_checkbox_'.$mch, 
						'onChange'=> "results.maps.addPlaceRelatations('$wikiId')", 
						'label'=>$this->transEsc('Place of death of &quot;emigrants&quot;'), 
						'badge'=>count($migrated)
						]);
		}
	 
	$checked = ''; 
	$mch++; 
	echo $this->render('helpers/switch.php', [
						'color'	=> 'fuchsia', 
						'checked' =>$checked, 
						'id'=>'map_checkbox_'.$mch, 
						'onChange'=> "results.maps.addPlaceRelatations('$wikiId')", 
						'label'=>"<b>to do</b>: publication places of works of persons live-related (born/died/residance) with ".$baseName,
						'badge'=>count($arrivals)
						]);

	$placesOnMap[$base]['name'] = $baseName;
	$placesOnMap[$base]['headLink'] = $this->buildUrl('wiki/record/'.$wikiId);
	$placesOnMap[$base]['wikiName'] = $baseName;
											
	$placesOnMap[$base]['color'] = 'center';
	$placesOnMap[$base]['count'] = '<i class="ph-bold ph-house"></i>';
	$placesOnMap[$base]['roles'] = [];
	$placesOnMap[$base]['roles']['home']['count'] = '';
	$placesOnMap[$base]['roles']['home']['title'] = $this->transEsc('Viewed place');
	$t = explode(',', $base);
	$Tlat[$t[0]] = $t[0];
	$Tlon[$t[1]] = $t[1];


	unset($point);
	$mixed = 0;
	if (!empty($placesOnMap)) {
		$totalPoints = count($placesOnMap);
		foreach ($placesOnMap as $coor=>$place) {
			$pjs = [];
			$point['link'] = "<h3><a href='{$place['headLink']}'>{$place['wikiName']}</a></h3>";
			
			$key = uniqid();
			foreach ($place['roles'] as $index=>$role) 
			if (!empty($role['link']))
				$point['link'] .= "<a href='$role[link]'>{$role['title']} <span class='badge badge-$role[color]'>$role[count]</span></a><br/>";
				else
				$point['link'] .= "{$role['title']} <span class=badge>$role[count]</span><br/>";
			if (!empty($role['addText']))
				$point['link'] .= $role['addText'];
			
			if (($totalPoints>100) && ($place['count']<2)) {
				$pjs[] = "var smarker_$key = L.circle([$coor], {color: '$place[color]', fillColor: '$place[color]', fillOpacity: 0.5, radius:200 }).addTo(map); ";	
				} else {
				if (intval($place['count'])!==0)
					$place['count'] = $this->helper->badgeFormat($place['count']);	
				$pjs[] = "var smarker_$key = L.marker([$coor], {icon: CircleNone }).addTo(map); ";
				#$pjs[] = "var smarker_$key = L.circle([$coor], {color: '$place[color]', fillColor: '$place[color]', fillOpacity: 0.5, radius:100 }).addTo(map); ";	
				$pjs[] = "smarker_$key.bindTooltip('".$place['count']."' , {permanent: true, direction: 'center', className: 'relationLabel-{$place['color']}' });";
				
				}
			
			$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
			if ($place['color'] == 'mixed') 
				$mixed++;
			if ($place['color'] == 'mixed')
				$place['color'] = 'orange';
			$pjs[] = "var apolygon = L.polygon([[$base], [$coor]], {color: '{$place['color']}', weight: 1, opacity: 0.5, smoothFactor: 1}).addTo(map);";
		
			$js[] = implode("\n", $pjs);
			
			}
		}
	if ($mixed>0)
		echo '<br/><span class="relationLabel-mixed">'.$this->helper->badgeFormat($mixed).'</span> &nbsp;&nbsp; '.$this->transEsc('Mixed relation points').' <br/><br/>';		
			

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

	$this->addJS(implode("\n", $js));	

	// echo '<textarea style="width:100%">'.implode("\n", $js).'</textarea>';
	// echo $totalPoints;
	// echo $this->helper->pre($placesOnMap);
	}
?> 
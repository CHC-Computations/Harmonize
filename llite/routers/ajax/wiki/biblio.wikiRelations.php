<br/>
<?php 
$mch = 0;
$recId = $this->routeParam[0];
# echo $this->helper->pre($this->routeParam);
# echo '<button class="btn btn-success" OnClick="results.maps.addBiblioRecRelatations(\''.$recId.'\')">reload</button>';

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->buffer->setSql($this->sql);


################################################################################################################################### get persons and places from biblio record
$record = $this->solr->getRecord('biblio', $recId);
if (!empty($record->persons_wiki_str_mv))
	foreach ($record->persons_wiki_str_mv as $personWikiQ) {
		$Tpersons['Q'.$personWikiQ] = 'Q'.$personWikiQ;
		}
if (!empty($record->places_with_roles))
	foreach ($record->places_with_roles as $placeStr) {
		$place = explode("|",$placeStr);
		$wikiq = current($place);
		$role = end($place);
		if (is_numeric($wikiq))
			$Tcoor['Q'.$wikiq][$role]['str'] = $placeStr;
		}
		


################################################################################################################################### get IDs of places related with persons
$query['q'] = [
		'field' 	=> 'q',
		'value' 	=> 'id:('.implode(' OR ',$Tpersons).')'
		];
$query['fl'] = [
		'field' 	=> 'fl',
		'value' 	=> 'id,labels,biblio_labels,related_place'
		];
$results = $this->solr->getQuery('persons',$query); 
$results = $this->solr->resultsList();
if (!empty($results))
	foreach ($results as $person) {
		$Tpersons[$person->id] = $person;
		if (!empty($person->related_place) && is_array($person->related_place))
			foreach ($person->related_place as $placeStr) {
				#echo "placeStr: $placeStr<br>";
				$place = explode("|",$placeStr);
				$wikiid = current($place);
				$Tcoor[$wikiid]['person place']['str'] = $placeStr;
				$Tcoor[$wikiid]['person place']['persons'][$person->id] = $person;
				
				}
		}


################################################################################################################################### get places data		
$query = [];
$query['q'] = [
		'field' 	=> 'q',
		'value' 	=> 'id:('.implode(' OR ',array_keys($Tcoor)).')'
		];
$results = $this->solr->getQuery('places',$query); 
$results = $this->solr->resultsList();
if (!empty($results))
	foreach ($results as $place) {
		$Tplaces[$place->id] = $place;
		$Tplaces[$place->id]->roles = $Tcoor[$place->id];
		}


	


################################################################################################################################### preparing points

$Roles = [
	'publication place' => (object)[
			'color' => 'purple',
			'label' => 'Publication place',
			'core' => 'search',
			'index' => 'places_with_roles',
			'checkOrder' => 1
			],
	'subject place' => (object)[
			'color' => 'red',
			'label' => 'Subject place',
			'core' => 'search',
			'index' => 'places_with_roles',
			'checkOrder' => 2
			],
	'person place' => (object)[
			'color' => 'darkgreen',
			'label' => 'Person related place',
			'core' => 'persons',
			'index' => 'related_place',
			'checkOrder' => 3
			],
	'mixed' => (object)[
			'color' => 'orange',
			'label' => 'Mixed relation with place',
			'checkOrder' => null
			],
	];


if (!empty($Tplaces)) {
	$js = [];
	foreach ($Tplaces as $wikiid => $place) {
		$mch++;
		
		foreach ($place->roles as $role=>$roleRecord) {
			$Troles[$role] = $roleParams = $Roles[$role];
			if ($this->routeParam[$roleParams->checkOrder] == 'true') {
				
				$point = $place->latitiude.','.$place->longitiude;
				@$placesOnMap[$point]['count']++;
				$placesOnMap[$point]['name'] = $this->helper->formatMultiLangStr($roleRecord['str']);
				$placesOnMap[$point]['headLink'] = $this->buildUrl('wiki/record/'.$wikiid);
				if (!empty($placesOnMap[$point]['color']) && ($placesOnMap[$point]['color'] <> $roleParams->color))
					$placesOnMap[$point]['color'] = 'mixed';
					else 
					$placesOnMap[$point]['color'] = $roleParams->color;
				$placesOnMap[$point]['roles'][$role]['count'] = 1;
				$placesOnMap[$point]['roles'][$role]['title'] = $roleParams->label;
				$placesOnMap[$point]['roles'][$role]['link'] = $this->buildUri($roleParams->core.'/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["$roleParams->index:\"{$roleRecord['str']}\""]));
				$placesOnMap[$point]['roles'][$role]['color'] = $roleParams->color;
				
				$Tlat[$place->latitiude] = $place->latitiude;
				$Tlon[$place->longitiude] = $place->longitiude;
				$Troles[$role]->checked = 'checked';
				} else 
				$Troles[$role]->checked = '';
			
			@$Troles[$role]->count++;
			}

		}
		


	#########################################################################################################  drawing points

	
	unset($point);
	$mixed = 0;
	$js[] = "map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); "; // clear map
	if (!empty($placesOnMap)) {
		$totalPoints = count($placesOnMap);
		foreach ($placesOnMap as $coor=>$place) {
			$pjs = [];
			$point['link'] = "<h3><a href='{$place['headLink']}'>{$place['name']}</a></h3>";
			
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
				$pjs[] = "smarker_$key.bindTooltip('".$place['count']."' , {permanent: true, direction: 'center', className: 'relationLabel-{$place['color']}' });";
				
				}
			
			$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
			if ($place['color'] == 'mixed') 
				$mixed++;
			if ($place['color'] == 'mixed')
				$place['color'] = 'orange';
			
			$js[] = implode("\n", $pjs);
			
			}
		}
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
	
	if (!empty($Troles))
		foreach ($Troles as $role=>$roleParams) {
			echo $this->render('helpers/switch.php', [
						'color' 	=> $roleParams->color, 
						'checked' 	=> $roleParams->checked, 
						'id' 		=> 'map_checkbox_'.$roleParams->checkOrder, 
						'onChange' 	=> "results.maps.addBiblioRecRelatations('$recId')", 
						'label' 	=> $roleParams->label, 
						'badge' 	=> $roleParams->count
						]);
			
			}
	if ($mixed>0)
		echo '<br/><span class="relationLabel-mixed">'.$this->helper->badgeFormat($mixed).'</span> &nbsp;&nbsp; '.$this->transEsc('Mixed relation points').' <br/><br/>';		
	
	

	$js[] = "$('#mapRelationsAjaxArea').css('opacity', '1');";
	// map.removeLayer(marker)

	$this->addJS(implode("\n", $js));	
	}
	
	
# Echo 'Tcoor '.$this->helper->pre($this->routeParam);
# Echo 'Tplaces '.$this->helper->pre($Tplaces);
	
?>
<br/>
<?php 
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.wikiDataCores.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.solr.php');

$mch = 0;
$recId = $this->routeParam[0];

# echo '<button class="btn btn-success" OnClick="results.maps.addBiblioRecRelatations(\''.$recId.'\')">reload</button><br/><br/>';

$this->addClass('solr', new solr($this));
$this->addClass('buffer', new buffer()); 


################################################################################################################################### get persons and places from biblio record
$solrRecord = $this->solr->getRecord('biblio', $recId);
$this->addClass('record', new bibliographicRecord($solrRecord));



if (!empty($this->record->elbRecord->persons->all)) {
	$Tpersons = array_keys((array)$this->record->elbRecord->persons->all);
	$TpersonsFull = $this->record->elbRecord->persons->all;
	#echo $this->helper->pre($TpersonsFull);
	}
	
if (!empty($this->record->elbRecord->places))
	foreach ($this->record->elbRecord->places as $role=>$placesTable) 
		foreach ($placesTable as $wikiq => $placeStr) {
			$Tcoor[$wikiq][$role]['str'] = $placeStr;
			}
		


################################################################################################################################### 
##  get IDs of places related with persons & add person information to Tcoor (table of coordinates)
################################################################################################################################### 

if (!empty($Tpersons)) {
	$fieldsToCheck = [
			'birth_place' => 'was born here',
			'death_place' => 'died here',
			];
		
	$query['q'] = [
			'field' 	=> 'q',
			'value' 	=> 'id:('.implode(' OR ',$Tpersons).')'
			];
	$query['rows'] = [
			'field' 	=> 'rows',
			'value' 	=> '1000'
			];
	
	$currentCore = 'persons';
	$currentView = $this->getUserParam($currentCore.':view') ?? $this->configJson->$currentCore->summaryBarMenu->view->default;
	
	$results = $this->solr->getQuery($currentCore, $query); 
	$results = $this->solr->resultsList();
	
	
	echo '<div class="hidden"> <div id="resultsBox" class="results-list '.$currentCore.'-list '.$currentView.'-list">';
	if (!empty($results))
		foreach ($results as $result) {
				
			$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
			
			echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
			echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj, 'matchLevel' => $this->helper->matchLevel($TpersonsFull->{$result->wikiq}, $result)]);
			echo '</div>';
			$this->addJS("$('.personBox{$result->wikiq}').html($('#{$currentCore}_{$result->wikiq}').html())");
			
			$Tpersons[$result->id] = $result;
			foreach ($fieldsToCheck as $key => $label) 
				if (!empty($result->$key) && is_array($result->$key))
					foreach ($result->$key as $placeStr) {
						#echo "placeStr: $placeStr<br>";
						$place = explode("|", $placeStr);
						$wikiq = current($place);
						$result->roleInPlace[$key] = $label;
						$result->roleInRecord = $this->record->elbRecord->persons->{$result->id}->role ?? '';
						
						$Tcoor[$wikiq]['person place']['str'] = $placeStr;
						$Tcoor[$wikiq]['person place']['persons'][$result->id]['roleInPlace'][$key] = $this->transEsc($label);
						$Tcoor[$wikiq]['person place']['persons'][$result->id]['roleInRecord'] = $this->record->elbRecord->persons->{$result->id}->role ?? '';
						$Tcoor[$wikiq]['person place']['persons'][$result->id]['name'] = $resultObj->getStr('labels');
								
						#$Tcoor[$wikiq]['roles'][$label][] = $resultObj->getStr('labels').' ('.$personRole.')';
						}
			# echo $this->helper->pre($result);
			# echo $this->helper->pre($TpersonsFull->{$result->wikiq});
			}
	echo '</div></div>';
	}
	
	

################################################################################################################################### get places data		
$query = [];
if (!empty($Tcoor) && is_array($Tcoor)) {
	$query['q'] = [
			'field' 	=> 'q',
			'value' 	=> 'id:('.implode(' OR ',array_keys($Tcoor)).')'
			];
	$currentCore = 'places';
	$currentView = $this->getUserParam($currentCore.':view') ?? $this->configJson->$currentCore->summaryBarMenu->view->default;
	
	$results = $this->solr->getQuery($currentCore,$query); 
	$results = $this->solr->resultsList();
	
	
	#echo '<div class="results"> <div id="resultsBox" class="results-list '.$currentCore.'-list '.$currentView.'-list">';
	
	if (!empty($results))
		foreach ($results as $result) {
			
			$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
			/*
			echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
			echo $this->render('wikiResults/resultBoxes/'.$currentView.'.php',['result'=>$resultObj]);
			echo '</div>';
			*/
			$Tplaces[$result->id] = $result;
			$Tplaces[$result->id]->roles = $Tcoor[$result->id];
			}
	#echo '</div></div>';
	}
	


################################################################################################################################### preparing points

$Roles = [
	'publicationPlace' => (object)[
			'color' => 'purple',
			'label' => 'Publication place',
			'core' => 'biblio',
			'index' => 'places_with_roles',
			'checkOrder' => 1
			],
	'subjectPlace' => (object)[
			'color' => 'red',
			'label' => 'Subject place',
			'core' => 'biblio',
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

foreach ($Roles as $roleKey => $roleParams) {
	
	}


if (!empty($Tplaces)) {
	
	$js = [];
	foreach ($Tplaces as $wikiid => $place) {
		$mch++;
		
		foreach ($place->roles as $role=>$roleRecord) {
			if ($role != 'all')  {
				$Troles[$role] = $roleParams = $Roles[$role];
				if ($this->routeParam[$roleParams->checkOrder] == 'true') {
					
					$point = $place->latitiude.','.$place->longitiude;
					@$placesOnMap[$point]['count']++;
					
					/*
					if (!empty($roleRecord['str']->roles))
						unset($roleRecord['str']->roles);
					echo $this->helper->pre($roleRecord['str']);
					*/
					if (!empty($roleRecord['str']->nameML))
						$nameML = $roleRecord['str']->nameML;
						else 
						$nameML = $roleRecord['str'];	
					
					$placesOnMap[$point]['name'] = $this->helper->formatMultiLangStr($nameML);
					$placesOnMap[$point]['headLink'] = $this->buildUrl('wiki/record/'.$wikiid);
					$placesOnMap[$point]['wikiq'] = $wikiid;
					if (!empty($placesOnMap[$point]['color']) && ($placesOnMap[$point]['color'] <> $roleParams->color))
						$placesOnMap[$point]['color'] = 'mixed';
						else 
						$placesOnMap[$point]['color'] = $roleParams->color;
					$placesOnMap[$point]['roles'][$role]['count'] = 1;
					if ($role == 'person place') {
						$title = [];
						foreach ($roleRecord['persons'] as $tmpPersonWikiQ => $tmpPerson) {
							$title[] = "<a href='".$this->buildUrl('wiki/record/'.$tmpPersonWikiQ)."'>".$tmpPerson['name'].'</a> ('.implode('/',(array)$tmpPerson['roleInRecord']).')<br/>'.implode(' & ',$tmpPerson['roleInPlace']).'.';
							// $this->buildUrl('wiki/record/').$tmpPersonWikiQ
							}
						$placesOnMap[$point]['roles'][$role]['title'] = implode('<br/>',$title);
						} else {
						$placesOnMap[$point]['roles'][$role]['title'] = $roleParams->label;
						$placesOnMap[$point]['roles'][$role]['link'] = $this->buildUri('results', ['core' => $roleParams->core, 'facetsCode'=>$this->buffer->createFacetsCode(["$roleParams->index:\"{$nameML}\""])]);
						}
					$placesOnMap[$point]['roles'][$role]['color'] = $roleParams->color;
					
					$Tlat["{$place->latitiude}"] = $place->latitiude;
					$Tlon["{$place->longitiude}"] = $place->longitiude;
					$Troles[$role]->checked = 'checked';
					} else 
					$Troles[$role]->checked = '';
				@$Troles[$role]->count++;
				}
			}
		}
		


	#########################################################################################################  drawing points

	echo '<form id="mapDrowCheckboxes">';
	unset($point);
	$mixed = 0;
	$js[] = "map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); "; // clear map
	if (!empty($placesOnMap)) {
		
		
		
		$totalPoints = count($placesOnMap);
		foreach ($placesOnMap as $coor=>$place) {
			$pjs = [];
			$point['link'] = "<div id='placeBox_{$place['wikiq']}' class='mapPlaceBox'><h3><a href='{$place['headLink']}'>{$place['name']}</a></h3></div>";
			
			$key = uniqid();
			foreach ($place['roles'] as $index=>$role) 
			if (!empty($role['link']))
				$point['link'] .= "<p><a href='$role[link]'>{$role['title']} <span class='badge badge-$role[color]'>$role[count]</span></a></p>";
				else
				$point['link'] .= "<p>{$role['title']} <span class=badge>$role[count]</span></p>";
			if (!empty($role['addText']))
				$point['link'] .= $role['addText'];
			
			if (($totalPoints>100) && ($place['count']<2)) {
				$pjs[] = "var smarker_$key = L.circle([$coor], {color: '$place[color]', fillColor: '$place[color]', fillOpacity: 0.5, radius:200 }).addTo(map); ";	
				} else {
				if (intval($place['count'])!==0)
					$place['count'] = $this->helper->badgeFormat($place['count']);	
				$pjs[] = "var smarker_$key = L.marker([$coor], {icon: CircleNone }).addTo(map); ";
				$pjs[] = "smarker_$key.on({click: function () { results.maps.currentPlace('{$place['wikiq']}')}});";
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
	
	
	echo '</form>';
	echo '<div id = "mapPopupCurrentPlace" class="hidden"></div>';
	$js[] = "$('#mapRelationsAjaxArea').css('opacity', '1');";
	// map.removeLayer(marker)
	
	$this->addJS(implode("\n", $js));	
	}
	
# echo 'Tcoor '.$this->helper->pre($this->routeParam);
# echo 'Tplaces '.$this->helper->pre($Tplaces);
# echo "placesOnMap".$this->helper->pre($placesOnMap);
	
# echo $this->helper->pre($this->routeParam);
# echo $this->helper->pre($this->record->elbRecord);
# echo '<textarea>'.implode(";\n", $this->JS).'</textarea>';
 	
?>
<br/>
<script>$('#mapRelationsAjaxArea').css('opacity', '1');</script>
<?php 
require_once('functions/class.record.bibliographic.php');
require_once('functions/class.wikiDataCores.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.solr.php');
require_once('functions/class.wikidata.php');



$JS = [];
$mch = 0;
$recId = $this->routeParam[0];

# echo '<button class="btn btn-success" OnClick="results.maps.addBiblioRecRelatations(\''.$recId.'\')">reload</button><br/><br/>';

$this->addClass('solr', 	new solr($this));
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 


if (!isset($this->GET)) {
	echo '<br/><br/>'.$this->helper->alert('warning', '<div class="text-center">'.$this->transEsc('The session expired due to inactivity. Reload the page to rebuild the session data.').'<br/><br/><button type="button" onClick="location.reload();" class="btn btn-success"><i class="ph ph-repeat"></i> '.$this->transEsc('reload').'</button></div>');
	die();
	}
	




################################################################################################################################### get persons and places from biblio record
$solrRecord = $this->solr->getRecord('biblio', $recId);
$this->addClass('record', new bibliographicRecord($solrRecord));

if (empty($_SESSION[$recId]['data']) or ($this->routeParam[1] == 'undefined')) {
	/* I'm looking for a link to the source of the record (it's not related to the rest of the script, but we have the record loaded here and it's ajax .... so it seems to be the right time ;-) ;-) */
	
	$rawId = $this->record->elbRecord->rawId;
	if (!empty($this->record->solrRecord->source_db_str))
		switch ($this->record->solrRecord->source_db_str) {
			case 'Česká Literární Bibliografie' :
				$res = json_decode(@file_get_contents('https://vufind.ucl.cas.cz/api/v1/record?id='.$rawId.'&prettyPrint=false&lng=en'));
				#echo $this->helper->pre($res);
				if (!empty($res->resultCount)) {
					echo '<div class="hidden" id="linktosource">
						<dl class="detailsview-item">
						  <dt class="dv-label">'.$this->transEsc('Original record').':</dt>
						  <dd class="dv-value"><a href="https://vufind.ucl.cas.cz/Record/'.$rawId.'">https://vufind.ucl.cas.cz/Record/'.$rawId.'</a></dd>
						</dl>
						</div>';
					$this->addJS("
						var content = $('#linktosource').html();
						$('.detailsview').append(content);
						");
					}
				if (!empty($res->status) && ($res->status == 'ERROR')) {
					
					}
				break;
			}
	
	
	/* end looking for a link */
	
	$fields = ['persons', 'corporates', 'events', 'magazines', 'places'];

	echo '<div class="hidden"> 
			<div id="resultsBox" class="results-list showcase-list">';
				
	foreach ($fields as $currentCore) {
		if (!empty($this->record->elbRecord->$currentCore->all)) {
			$IDtable = array_keys((array)$this->record->elbRecord->$currentCore->all);
			$query['q'] = [
					'field' 	=> 'q',
					'value' 	=> 'id:('.implode(' OR ',$IDtable).')'
					];
			$query['rows'] = [
					'field' 	=> 'rows',
					'value' 	=> count($IDtable)
					];
			
			$results = $this->solr->getQuery($currentCore, $query); 
			$results = $this->solr->resultsList();
			if (!empty($results))
				foreach ($results as $result) {
					$placeFieldsToCheck = [];
					$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
					$currentItem = $this->record->elbRecord->$currentCore->all->{$result->wikiq};
					switch ($currentCore) {
						case 'persons' : 
								$placeFieldsToCheck = [
									'birth_place' => 'birth_place',
									'death_place' => 'death_place'
									];
								break;
						case 'magazines' : 
						case 'corporates' : 
								$placeFieldsToCheck = [
									'location' => 'location',
									'headquater_str_mv' => 'headquater',
									'country' => 'country'
									];
								break;
						case 'events' : 
								$placeFieldsToCheck = [
									'location' => 'location',
									'country' => 'country'
									];
								break;
						default: 
							echo $this->helper->pre($currentItem);
							echo $this->helper->pre($resultObj);
						}
					foreach ($placeFieldsToCheck as $field => $because) {
						if (!empty($resultObj->solrRecord->$field))
							foreach ($resultObj->solrRecord->$field as $placeString) {
								# echo "$placeWikiQ {$currentItem->role} {$currentItem->wikiQ} {$because} <br/>";
								
								$placeWikiQ = strtok($placeString, '|');
								if (!empty($placeWikiQ)) {
								
									$placesInBiblioRecord[$placeWikiQ][$currentItem->role][$currentItem->wikiQ] = $currentItem;
									@$isHereBecause[$placeWikiQ][$currentItem->wikiQ][$because]++;
									@$rolesToShow[$currentItem->role]++;
									}
								}
						}
					
					
					$matchLevel = false;
					if (!empty($currentItem->biblio_label)) {
						$biblio_label = $currentItem->biblio_label;
						$baseQUERY = "
								FROM matching_results a 
								LEFT JOIN matching_strings s ON a.string_id = s.id
								LEFT JOIN dic_rec_types rt ON a.rectype_id = rt.id
								";
						$rec = $this->psql->querySelect("SELECT *, a.id $baseQUERY WHERE s.string = {$this->psql->string($biblio_label)};");	
						# echo 'biblio_label: '.$biblio_label.'<br/>';
						if (is_Array($rec)) {
							#echo $this->helper->pre($rec);
							$matchLevel = current($rec)['match_level'];
							}
						}
					
					echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
					echo $this->render('wikiResults/resultBoxes/showcase.php',['result'=>$resultObj, 'matchLevel'=>$matchLevel] );
					echo '</div>';
					$this->addJS("$('.personBox{$result->wikiq}').html($('#{$currentCore}_{$result->wikiq}').html())");
					
					}
			}
		}
		
	echo '</div>
			</div>';
	
	$_SESSION[$recId]['data']['placesInBiblioRecord'] = $placesInBiblioRecord ?? [];
	$_SESSION[$recId]['data']['rolesToShow'] = $rolesToShow ?? [];
	$_SESSION[$recId]['data']['isHereBecause'] = $isHereBecause ?? [];

	} else {
	$placesInBiblioRecord = $_SESSION[$recId]['data']['placesInBiblioRecord'];
	$rolesToShow = $_SESSION[$recId]['data']['rolesToShow'];
	$isHereBecause = $_SESSION[$recId]['data']['isHereBecause'];
	}


$i = 0;

if (!empty($rolesToShow)) {
	#echo $this->helper->pre($rolesToShow);
	echo '<h4>'.$this->transEsc('Show points related with').':</h4>';
	echo '<form id="mapDrowCheckboxes">';
	foreach ($rolesToShow as $role=>$count) {
		$i++;
		if ($this->routeParam[1] == 'undefined') 
			$_SESSION[$recId]['switches'][$role] = true;
			else if ($this->routeParam[1] == $role)
			$_SESSION[$recId]['switches'][$role] = !$_SESSION[$recId]['switches'][$role] ?? true;	
		
		if ($_SESSION[$recId]['switches'][$role]) {
			$color = $this->helper->formatMajorRole($role)->color;
			$checked = 'checked';
			} else {
			$color = 'lightgray';	
			$checked = '';
			}
		echo $this->render('helpers/switch.php', [
					'color' 	=> $color, 
					'checked' 	=> $checked, 
					'id' 		=> 'map_checkbox_'.$i, 
					'onChange' 	=> "results.maps.addBiblioRecRelatations('$recId','$role')", 
					'label' 	=> '<span class="'.$this->helper->formatMajorRole($role)->ico.'"></span> '.$role, 
					'badge' 	=> $count
					]);
				
		}
	echo '</form>';
	}
	


if (!empty($placesInBiblioRecord)) {
	foreach ($placesInBiblioRecord as $placeWikiQ => $content) {
		foreach ($content as $role=>$items)
			if ($_SESSION[$recId]['switches'][$role])
				$placesToShow[$placeWikiQ][$role] = $items;
		}
	} else {
	echo $this->transEsc('Nothing to show on map').'.';	
	die();
	}




$js = [];
$js[] = "map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); "; // clear map
	

if (!empty($placesToShow)) {
	$copyPlacesToShow = $placesToShow;
	$currentCore = 'places';
	$placesIds = array_keys($placesToShow);
	$query['q'] = [
			'field' 	=> 'q',
			'value' 	=> 'id:('.implode(' OR ',$placesIds).')'
			];
	$query['rows'] = [
			'field' 	=> 'rows',
			'value' 	=> count($placesIds)
			];
	
	$results = $this->solr->getQuery($currentCore, $query); 
	$results = $this->solr->resultsList();
	
	$lat = [];
	$lon = [];
	if (!empty($results)) {
		foreach ($results as $pointOnMap) {
			$resultObj = new wikiLibri($this->user->lang['userLang'], $pointOnMap);
			$key = uniqid();
				
			$headLink = $this->buildURL('wiki/record/'.$resultObj->solrRecord->wikiq);
			$point['link'] = "<div id='placeBox_{$resultObj->solrRecord->wikiq}' class='mapPlaceBox'><h3><a href='{$headLink}'>{$resultObj->getStr('labels')}</a></h3></div>";
			$point['link'] .="<div id='placeBoxAppend_{$resultObj->solrRecord->wikiq}' class='placeBoxAppend'> link_{$key} </div>";
									
			$pointRoleSums = [];
			$listStr = [];
			$svgLinkParts = [];
			$subDataCount = 0;
			
			if (!empty($placesToShow[$resultObj->solrRecord->wikiq]))
				foreach ($placesToShow[$resultObj->solrRecord->wikiq] as $roleGroup=>$items) 
					if ($_SESSION[$recId]['switches'][$roleGroup]) 	
						foreach ($items as $itemWikiQ=>$item) {
							$pjs = [];
							$roleList = [];
							$roleListStr = [];
							$listStr[$itemWikiQ] = '<a href="'.$this->buildURL('wiki/record/'.$itemWikiQ).'" class="titleLink">'.$this->helper->formatMultiLangStr($item->nameML).'</a> <i class="'.$this->helper->formatMajorRole($roleGroup)->ico.'" title="'.$roleGroup.'"></i><br/> ';
							
							if (!empty($isHereBecause[$resultObj->solrRecord->wikiq][$itemWikiQ]))
								foreach ($isHereBecause[$resultObj->solrRecord->wikiq][$itemWikiQ] as $reason => $noImportantNumber)
									$listStr[$itemWikiQ] .= $this->transEsc($reason).' ';
							@$pointRoleSums[$roleGroup]++;
							}
						
			
			foreach ($pointRoleSums as $roleGroup => $roleCount) {
				$svgLinkParts[] = str_replace('#','',$this->helper->formatMajorRole($roleGroup)->color).'_'.$roleCount;
				}	
			$pngLink = $this->HOST.'_tools/png/'.implode('-',$svgLinkParts).'-pie.png';	
			$pngId = hash('crc32b', $pngLink);
			
			#  echo '<img src="'.$pngLink.'" alt="PieChart'.implode('-',$pointRoleSums).'" title="PieChart'.implode('-',$pointRoleSums).'" style="width:36px; margin:10px;"><br/>';
			
			$i = 0; 
			$max = 5;
			if (!empty($listStr)) {
				echo '<div class="hidden"><div id="content_'.$resultObj->solrRecord->wikiq.'">';
				foreach ($listStr as $listStrkey=>$string) {
					$i++;
					echo '<div class="placeBoxAppendItem">'.$i.'. '.$string.'</div>';
					}
				echo '</div></div>';
				}
			
			$subDataCount = array_sum($pointRoleSums);
			if (!empty($resultObj->solrRecord->latitiude)) {
				$coor = $resultObj->solrRecord->latitiude .','. $resultObj->solrRecord->longitiude;
				$lat[] = $resultObj->solrRecord->latitiude;
				$lon[] = $resultObj->solrRecord->longitiude;
					
				$pjs[] = 'var circle'.$pngId.' = L.icon({
							iconUrl: "'.$pngLink.'",
							iconSize: [36, 36],
							iconAnchor: [18, 18],
							popupAnchor: [0, -18]
						});';
				$pjs[] = "var smarker_$key = L.marker([$coor], {icon: circle{$pngId} }).addTo(map); ";
				$pjs[] = "smarker_$key.bindTooltip('".$subDataCount."' , {permanent: true, direction: 'center', className: 'relationLabel-onlyNumber' });";
				
				$pjs[] = "smarker_$key.on({click: function () { 
						tekst = $('#content_{$key}').html();
						results.maps.currentPlacePost('{$resultObj->solrRecord->wikiq}', tekst);
						$('#link_{$key}').html(tekst);
						$('#link_{$key}').css('background-color', 'red');	
						
						}});";
				$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
				
				$js[] = implode("\n", $pjs);
				}
			unset($copyPlacesToShow[$resultObj->solrRecord->wikiq]);
			}
			
			
		$qualityRange = round((count($results)/count($placesToShow))*100,0);
		if ($qualityRange<100) {
			
			# echo '<b>'.$this->transEsc('Notice').'!</b> ';
			# echo '<span class="pie" style="--p:'.$qualityRange.';--c:#5F3D8D;--b:7px;">'.$qualityRange.'%</span> '.$this->transEsc('note_1');
			
			
			
			### Add points not "active" in bibliography
			#echo $this->helper->pre($copyPlacesToShow); 
			
			foreach ($copyPlacesToShow as $placeWikiQ => $placeContain) {
				$key = uniqid();
				
				$this->wiki->loadRecord($placeWikiQ, false);
				$headLink = $this->buildURL('wiki/record/'.$placeWikiQ);
				$point['link'] = "<div id='placeBox_{$placeWikiQ}' class='mapPlaceBox'><h3><a href='{$headLink}'>{$this->wiki->get('labels')}</a></h3></div>";
				
				$pointRoleSums = [];
				$listStr = [];
				$svgLinkParts = [];
				$subDataCount = 0;
				
				$coordinates = $this->wiki->getCoordinates('P625');
				$pjs = [];
				# echo $this->helper->pre($coordinates);
				if (!empty($coordinates->latitude) && !empty($coordinates->longitude)) {
					$coor = implode(',', (array)$coordinates);
					$coor = $coordinates->latitude .','. $coordinates->longitude;
					$lat[] = $coordinates->latitude;
					$lon[] = $coordinates->longitude;
					
					$pointRoleSums = [];
					foreach ($placeContain as $roleGroup=>$items) 
						if ($_SESSION[$recId]['switches'][$roleGroup]) 	
							foreach ($items as $itemWikiQ=>$item) {
								$pjs = [];
								$roleList = [];
								$roleListStr = [];
								$listStr[$itemWikiQ] = '<a href="'.$this->buildURL('wiki/record/'.$itemWikiQ).'" class="titleLink">'.$this->helper->formatMultiLangStr($item->nameML).'</a> <i class="'.$this->helper->formatMajorRole($roleGroup)->ico.'" title="'.$roleGroup.'"></i><br/> ';
								
								if (!empty($isHereBecause[$placeWikiQ][$itemWikiQ]))
									foreach ($isHereBecause[$placeWikiQ][$itemWikiQ] as $reason => $noImportantNumber)
										$listStr[$itemWikiQ] .= $this->transEsc($reason).' ';
								@$pointRoleSums[$roleGroup]++;
								}
								
					$subDataCount = array_sum($pointRoleSums);
					foreach ($pointRoleSums as $roleGroup => $roleCount) {
						$svgLinkParts[] = str_replace('#','',$this->helper->formatMajorRole($roleGroup)->color).'_'.$roleCount;
						}	
					
					$i = 0; 
					$appendText = '';
					if (!empty($listStr)) {
						foreach ($listStr as $listStrkey=>$string) {
							$i++;
							$appendText .= '<div class="placeBoxAppendItem">'.$i.'. '.$string.'</div>';
							}
						}	
					$base64AppendText = base64_encode($appendText);	
						
					$pngLink = $this->HOST.'_tools/png/'.implode('-',$svgLinkParts).'-pie.png';	
					$pngId = hash('crc32b', $pngLink);

					
					$pjs[] = 'var circle'.$pngId.' = L.icon({
								iconUrl: "'.$pngLink.'",
								iconSize: [36, 36],
								iconAnchor: [18, 18],
								popupAnchor: [0, -18]
							});';
					$pjs[] = "var smarker_$key = L.marker([$coor], {icon: circle{$pngId} }).addTo(map); ";
					$pjs[] = "smarker_$key.bindTooltip('".$subDataCount."' , {permanent: true, direction: 'center', className: 'relationLabel-onlyNumber' });";
					$pjs[] = "smarker_$key.on({click: function () { 
							results.maps.currentPlaceWikiPost('{$placeWikiQ}', '{$base64AppendText}');
							}});";
					$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
					
					$js[] = implode("\n", $pjs);
					}
				}
			}
		
		if (!empty($lat) & !empty($lon))
			$js[] = 'map.fitBounds([['.max($lat).','.max($lon).'],['.min($lat).','.min($lon).']]);'; // center & zoom the map
		
		} else {
		echo $this->transEsc('Nothing to show on map');	
		} 
	echo '<div id = "mapPopupCurrentPlace" class="hidden"></div>';	
	} else 
	echo $this->transEsc('Nothing to show on map');		
$this->addJS(implode("\n", $js));
			

		 

	
		

echo '<div class="space"></div>';
# echo $this->helper->preCollapse('routeParam', $this->routeParam);
# echo $this->helper->preCollapse('switches', $_SESSION[$recId]['switches']);
# echo $this->helper->preCollapse('placesInBiblioRecord', $placesInBiblioRecord);
# echo $this->helper->preCollapse('rolesToShow', $rolesToShow);	
# echo $this->helper->preCollapse('placesToShow', $placesToShow);	
# echo $this->helper->preCollapse('isHereBecause', $isHereBecause);	
	
 	
?>
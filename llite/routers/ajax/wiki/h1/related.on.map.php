<?php
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 

$currentCore = $this->routeParam[1];
$wikiq = $this->routeParam[0];

$this->wiki->loadRecord($wikiq, false);
$prefix =  $wikiq.'|';
$recType = $this->wiki->recType();

#echo 'template for '.$recType;
echo '<br/>';
$templeFileName = 'routers/ajax/wiki/h1/related.on.map.'.$recType.'.php';



if (file_exists($templeFileName))
	include($templeFileName);
	else {
	echo $this->transEsc("There is no display scheme for ").$recType;
	die();
	}	


	
	
$cutOffValue = 1;	
if (!empty($buttons)) {
	if (empty($this->POST)) {
		$_SESSION['mapBasicRelationGraph'][$wikiq]['buttons'] = $buttons;
		} else {
		# echo $this->helper->pre($this->POST);	 
		$clickOn = $this->POST['pdata']['click'];
		$_SESSION['mapBasicRelationGraph'][$wikiq]['buttons'][$clickOn]->state = !$_SESSION['mapBasicRelationGraph'][$wikiq]['buttons'][$clickOn]->state;
		$buttons = $_SESSION['mapBasicRelationGraph'][$wikiq]['buttons'];
		
		}	
	echo 'Show on map: <div class="list-group" style="max-width:30vw">';
	foreach ($buttons as $buttonKey) {
		$buttonParameters = $this->helper->formatBasicRole($buttonKey->name);
		if ($buttonKey->state) 
			$additionalClass = '';
			else 
			$additionalClass = ' line-through';	
		$OC = "page.post('mapRelationsAjaxArea', 'wiki/h1/related.on.map/{$this->wiki->getID()}/{$recType}', {'click' : '{$buttonKey->name}'}); console.log('{$buttonKey->name}')";
		// <i style="margin:3px" class="'.$buttonParameters->ico.'"></i>
		echo '<button OnClick="'.$OC.'" 
				class="list-group-item'.$additionalClass.'" title="'.$this->transEsc('Click to switch').'">
				<span style="display:inline-block; min-width:20px; padding:3px; border-radius:3px; background-color:'.$buttonParameters->color.';" >&nbsp;</span> 
				'.$buttonParameters->title.' 
				<span class="badge">'.$buttonKey->count.'</span>
			</button>';
		}
	echo '</div>';
	}


if (!empty($placesToTake)) {
	#echo $this->helper->preCollapse('buttons', $buttons);
	# echo $this->helper->preCollapse('toShow', $toShow);
	# echo $this->helper->preCollapse('placesRoles', $placesRoles);
	# echo $this->helper->preCollapse('placesToTake', $placesToTake);
	$maxPointsAvaible = 512;
	$maxPoints = count($placesToTake);
	if ($maxPoints > $maxPointsAvaible) {
		echo $this->transEsc('Only the first '.$maxPointsAvaible.' related places are displayed on the map. Total number of related places found').': '.$this->helper->numberFormat($maxPoints).'<br/>';
		arsort($totalPointsCounts);
		$totalPointsCounts = array_slice($totalPointsCounts, 0, 500);
		$placesToTake = array_keys($totalPointsCounts);
		}
	
	
	## acquisition of place identifiers	
	$alert = '';
	$maxValue = 0;
	$query = [];			
	$query['q'] = [
			'field' 	=> 'q',
			'value' 	=> 'id:('.implode(' OR ',$placesToTake).')'
			];
	$query['rows'] = [
			'field' 	=> 'rows',
			'value' 	=> $maxPoints
			];
	$query['start'] = [
			'field' 	=> 'start',
			'value' 	=> 0
			];
				
	$results = $this->solr->getQuery('places', $query); 
	$results = $this->solr->resultsList();
	
	$js = [];
	$js[] = "map.eachLayer( function(layer) { if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); "; // clear map
	# echo $this->helper->preCollapse('query', $query);
	# echo $this->helper->preCollapse('results', $results);
	# echo $this->helper->preCollapse('solr alert', $this->solr->alert);
		

	if (!empty($results)) {
		$mapsPointsCount = 0;
		$mapsPointsCountTotal = 0;
		foreach ($results as $pointOnMap) {
			$pjs = [];
			$subDataCount = 0;		
			$svgLinkParts = [];
			
			$resultObj = new wikiLibri($this->user->lang['userLang'], $pointOnMap);
			#$pointRoleSums = $placesRoles[$pointOnMap->id];
			$headLink = $this->buildURL('wiki/record/'.$pointOnMap->id);
			$pointLink = "<div id='placeBox_{$pointOnMap->id}' class='mapPlaceBox'><h3><a href='{$headLink}'>{$resultObj->getStr('labels')}</a></h3>";
			$pointLink .= "<div style='padding:4px'>{$resultObj->getStr('descriptions')}</div>";
			if (!empty($placesRoles[$pointOnMap->id])) {
				$pointLink .= "<div style='padding:4px; border-top:solid 1px #ddd;'>";
				foreach ($placesRoles[$pointOnMap->id] as $roleName=>$roleCount) {
					if (empty($buttons[$roleName])) {
						$buttons[$roleName] = (object) ['state' => true];
						}
					if ($buttons[$roleName]->state) {
						if (!empty($replaceHeaders[$roleName]))
							$displayRoleName = $replaceHeaders[$roleName];
							else 
							$displayRoleName = $roleName;
						
						if (!empty($replaceStr[$pointOnMap->id][$roleName])) {
							$i = 0;
							$pointLink .= '<b>'.$displayRoleName."</b> <span class='badge'>$roleCount</span><br/>";
							foreach ($replaceStr[$pointOnMap->id][$roleName] as $putHere) {
								$pointLink .= '- '.$putHere.'<br/>';
								$i++;
								if ($i>3) break;
								}
							} else {
							$pointLink .= "<a><b>$displayRoleName</b> <span class='badge'>$roleCount</span></a><br/>";
							}
						
						$svgLinkParts[] = str_replace('#','',$this->helper->formatBasicRole($roleName)->color).'_'.$roleCount;
						} else {
						unset($placesRoles[$pointOnMap->id][$roleName]);	
						}
					}
				$pointLink .= "</div>";
				$subDataCount = array_sum($placesRoles[$pointOnMap->id]);
				}
			$pointLink = str_replace('"', '&quot;', $pointLink);
			$pngLink = $this->HOST.'_tools/png/'.implode('-',$svgLinkParts).'-pie.png';	
			$pngId = hash('crc32b', $pngLink);			
			
			
			if (!empty($resultObj->solrRecord->latitiude) & !empty($placesRoles[$pointOnMap->id])) {
				$mapsPointsCount ++;
					
				$key = uniqid();
				$coor = $resultObj->solrRecord->latitiude .','. $resultObj->solrRecord->longitiude;
				$tableOfCoordinates[$pointOnMap->id] = $coor;
				if ($subDataCount>$cutOffValue) {
					$pjs[] = 'var circle'.$pngId.' = L.icon({
							iconUrl: "'.$pngLink.'",
							iconSize: [36, 36],
							iconAnchor: [18, 18],
							popupAnchor: [0, -18]
						});';
					$pjs[] = "var smarker_$key = L.marker([$coor], {icon: circle{$pngId} }).addTo(map); ";
					$pjs[] = "smarker_$key.bindTooltip('".$subDataCount."' , {permanent: true, direction: 'center', className: 'relationLabel-onlyNumber' });";
					} else {
					$pjs[] = 'var circle'.$pngId.' = L.icon({
							iconUrl: "'.$pngLink.'",
							iconSize: [18, 18],
							iconAnchor: [9, 9],
							popupAnchor: [0, -9]
						});';
					$pjs[] = "var smarker_$key = L.marker([$coor], {icon: circle{$pngId} }).addTo(map); ";
					$pjs[] = "smarker_$key.bindTooltip('".$subDataCount."' , {permanent: false, direction: 'center', className: 'relationLabel-onlyNumber' });";
					//$('#link{$pngId}').html($('#content_{$pngId}').html());
					}
								
				
				$pjs[] = "smarker_$key.bindPopup(\"{$pointLink}\")";
				
				$js[] = implode("\n", $pjs);
				}
			}
		}
		
	if (!empty($linesToDraw)) {
		#echo 'linesToDraw'.$this->helper->pre($linesToDraw);
		foreach ($linesToDraw as $roleName=>$points) 
			if ($buttons[$roleName]->state) {
				$color = $this->helper->formatBasicRole($roleName)->color;
				foreach ($points as $from => $to) {
					#echo "$from => $to <br>";
					if (!empty($tableOfCoordinates[$from]) && !empty($tableOfCoordinates[$to])) {
						$lastPoint = $tableOfCoordinates[$from];
						$newPoint = $tableOfCoordinates[$to];
						$js[] = "var apolygon = L.polygon([[$lastPoint], [{$newPoint}]], {color: '$color', weight: 2, opacity: 0.6, smoothFactor: 1}).addTo(map);";
						}
					}
			}
		}

	/*
	$qualityRange = round(($mapsPointsCount/count($results))*100,0);
	if ($qualityRange<100) {
		echo '<b>'.$this->transEsc('Notice').'!</b> ';
		echo '<span class="pie" style="--p:'.$qualityRange.';--c:#5F3D8D;--b:7px;">'.$qualityRange.'%</span> '.$this->transEsc('note_1');
		}	
	*/	
	$this->addJS(implode("\n", $js));
	#echo $this->helper->preCollapse('JS', $js);
	
	}
	
			

?>
<script>$("#mapRelationsAjaxArea").css("opacity", "1");</script>
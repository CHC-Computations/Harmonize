<?php
require_once('functions/class.buffer.php');
require_once('functions/class.wikidata.php');
require_once('functions/class.wikidata.libri.php');

$this->addClass('solr', 	new solr($this)); 
$this->addClass('buffer', 	new buffer()); 
$this->addClass('wiki', 	new wikidata($this)); 

if (empty($this->POST))
	$_SESSION['mapRelationGraph'] = [];

$currentCore = $this->routeParam[1];
$wikiq = $this->routeParam[0];

$this->wiki->loadRecord($wikiq, false);
$prefix =  $wikiq.'|';

/*
if (!isset($this->GET)) {
	echo '<br/><br/>'.$this->helper->alert('warning', '<div class="text-center">'.$this->transEsc('The session expired due to inactivity. Reload the page to rebuild the session data.').'<br/><br/><button type="button" onClick="location.reload();" class="btn btn-success"><i class="ph ph-repeat"></i> '.$this->transEsc('reload').'</button></div>');
	die();
	}
*/

	######################################################################################################################################################################################################################################
	##
	##    first dimension
	##
	######################################################################################################################################################################################################################################

	
$query = [];
$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
$query['facet.prefix']	= ['field' => 'facet.prefix', 	'value' => $prefix ];
$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$allRoles = $this->solr->facetsList();	

$baseConditions = [];

if (!empty($allRoles['with_roles_wiki'])) {
	$objectRolesStr = '<div><strong>'.$this->wiki->get('labels').'</strong> '.$this->transEsc('appears in roles').':<br/><div class="list-group">';
	
	if (empty($_SESSION['mapRelationGraph']['role']))
		foreach ($allRoles['with_roles_wiki'] as $role=>$count) 
			$_SESSION['mapRelationGraph']['role'][$role] = true;
	$useForSearch = [];
	
	foreach ($allRoles['with_roles_wiki'] as $role=>$count) {
		$roleStr = str_replace($prefix, '', $role);
		
		if (!empty($this->POST['pdata']['field']) && ($this->POST['pdata']['field'] == $role)) {
			
			$_SESSION['mapRelationGraph']['role'][$role] = !$_SESSION['mapRelationGraph']['role'][$role] ?? true;
			}
		if (!empty($_SESSION['mapRelationGraph']['role'][$role]) && $_SESSION['mapRelationGraph']['role'][$role]) {
			$class = '';
			$useForSearch[] = $role;
			$baseConditions[] = '~with_roles_wiki:"'.$role.'"';
			} else 
			$class = 'line-through';
		$action = (object)[
			'field' => $role,
			'action' => 'change'
			];
			
		$objectRolesStr.='
			<button 
				class="list-group-item '.$class.'" 
				id="button_'.$roleStr.'"
				type="button" 
				onClick=\'page.postLT("mapRelationsAjaxArea", "wiki/related.on.map/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\' 
				title="'.$this->transEsc('Click to disable').'"> 
					'.$this->transEsc($roleStr).' <span class="badge">'.$this->helper->numberFormat($count).'</span>
			</button>
			';
		}
	$objectRolesStr.='</div></div>';
	
	 

	######################################################################################################################################################################################################################################
	##
	##    2-th dimension
	##
	######################################################################################################################################################################################################################################
	
	$statBoxes = $this->configJson->$currentCore->statBoxes ?? new stdClass;
	$query = [];
	$query['q'] = [
			'field' => 'q',
			'value' => '*:*'
			];
	$query['fq'] = [
			'field' => 'fq',
			'value' => 'with_roles_wiki:"'.implode('" OR with_roles_wiki:"', $useForSearch).'"'
			];
			
	$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];
	$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => 9999 // $statBoxes->maxResultsOnGraphs
			];
	$query['facet.mincount ']=[ 
			'field' => 'facet.mincount',
			'value' => 1
			];
	$query[]=[ 
			'field' => 'facet.field',
			'value' => 'with_roles_wiki'
			];
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 0
			];
	$query[]=[ 
			'field' => 'start',
			'value' => 0
			];		

	$results = $this->solr->getQuery('biblio', $query); 
	#echo implode('<br>',$this->solr->alert);
	$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();		
	$totalResults = $this->solr->totalResults();

	
	$conversionTable = [
		'subjectPerson' => 'Persons',
		'mainAuthor' => 'Persons',
		'coAuthor' => 'Persons',

		'subjectPlace' => 'Places',
		'publicationPlace' => 'Places',
		'eventPlace' => 'Places',	
		
		'subjectEvent' => 'Events',

		'subjectCorporate' => 'Corporates',
		];

	if (!empty($facets['with_roles_wiki'] ))
		foreach ($facets['with_roles_wiki'] as $resultStr=>$count) {
			$tmp = explode('|', $resultStr);
			$group = $tmp[1];
			$value = $tmp[0];
			$nGroup = $conversionTable[$group] ?? $group;
			@$relationGroups[$nGroup]+=$count;
			
			if ($value !== $wikiq) {
				@$recInGroups[$nGroup][$group] += $count;
				$recInGroupsHasRoles[$nGroup][$value][$group] = $count;
				}
			}

	if (empty($_SESSION['mapRelationGraph']['relatedWith']))
		foreach ($relationGroups as $group=>$countInGroup) 
			$_SESSION['mapRelationGraph']['relatedWith'][$group] = true;

	if (!empty($this->POST['pdata']['group'])) {
		$group = $this->POST['pdata']['group'];
		$_SESSION['mapRelationGraph']['relatedWith'][$group] = !$_SESSION['mapRelationGraph']['relatedWith'][$group];
		}

	if (!empty($relationGroups)) {
		$relatedWithStr = $this->transEsc('Show relations with').':<br/>';	
		$relatedWithStr.= '<div class="list-group">';	
		
			foreach ($relationGroups as $group=>$countInGroup) {
				
				$class = ($_SESSION['mapRelationGraph']['relatedWith'][$group]) ? '' : 'line-through';
				$action = (object)['group' => $group];
				$relatedWithStr.= '
					<button class="list-group-item '.$class.'" 
						id="button'.$group.'"
						onClick=\'page.postLT("mapRelationsAjaxArea", "wiki/related.on.map/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\'>
							<span class="map-related-legend '.strtolower($group).'">&nbsp;</span>
							'.$this->transEsc($group).' <span class="badge">'.$this->helper->numberFormat($countInGroup).'</span>
					</button> ';
				}
		$relatedWithStr.= '</div>';		
		} else 
		$relatedWithStr = '';

	######################################################################################################################################################################################################################################
	##
	##    preparing lines 1-2 
	##
	######################################################################################################################################################################################################################################
	
	if (!empty($useForSearch)) {
		foreach ($useForSearch as $searchRole) {
			$query = [];
			$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
			$query['rows']			= ['field' => 'rows',			'value' => 0];
			$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
			$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
			$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
			$query['facet.mincount']	= ['field' => 'facet.mincount',	'value' => 1 ];
			$query['fq']			= ['field' => 'fq', 			'value' => 'with_roles_wiki:"'.$searchRole.'"' ];
			$this->solr->getQuery('biblio', $query); 
			$results = $this->solr->resultsList();
			$facets = $this->solr->facetsList();
			
			$sr = explode('|', $searchRole);
			foreach ($facets['with_roles_wiki'] as $resultStr=>$count) {
				$tmp = explode('|', $resultStr);
				$group = $tmp[1];
				$value = $tmp[0];
				$nGroup = $conversionTable[$group] ?? $group;
				
				if ($value !== $wikiq) {
					@$linesToDraw[$sr[1]][$nGroup]+=$count;
					}
				}
			}
		# echo $searchRole.$this->helper->pre($linesToDraw);	
		if (!empty($linesToDraw)) {
			foreach ($linesToDraw as $from=>$toArr) 
				foreach ($toArr as $to=>$count) 
					$this->addJS("results.mapsMenu.drawLine('".uniqid()."','button_{$from}', 'button{$to}');");
			}	
		}
	
	######################################################################################################################################################################################################################################
	##
	##    3-th dimension
	##
	######################################################################################################################################################################################################################################
		
	if (!empty($_SESSION['mapRelationGraph']['relatedWith'])) {
		$relatedWithRolesStr = '';
		foreach ($_SESSION['mapRelationGraph']['relatedWith'] as $currentGroup=>$groupActive) {
			if ($_SESSION['mapRelationGraph']['relatedWith'][$currentGroup]) {
			
				$relatedWithRolesStr.= '<strong>'.$currentGroup.'</strong> '.$this->transEsc('appears in roles:').'<br/>';
				$relatedWithRolesStr.= '<div class="list-group">';
				if (!empty($recInGroups[$currentGroup])) {
					
					if (empty($_SESSION['mapRelationGraph']['subInRole'][$currentGroup]))
						foreach ($recInGroups[$currentGroup] as $role => $countInRole) 
							$_SESSION['mapRelationGraph']['subInRole'][$currentGroup][$role] = true;
					
					foreach ($recInGroups[$currentGroup] as $role => $countInRole) {
						if (!empty($this->POST['pdata']['subInRole']) && ($this->POST['pdata']['subInRole'] == $role))
							$_SESSION['mapRelationGraph']['subInRole'][$currentGroup][$role] = !$_SESSION['mapRelationGraph']['subInRole'][$currentGroup][$role];
						if (!empty($_SESSION['mapRelationGraph']['subInRole'][$currentGroup][$role]) && $_SESSION['mapRelationGraph']['subInRole'][$currentGroup][$role]) {
							$class = '';
							$useForDisplay[] = $role;
							} else 
							$class = 'line-through';
						$action = (object)[
							'subInRole' => $role,
							];
						
						$relatedWithRolesStr.= '
							<button class="list-group-item '.$class.'" id="button_'.$currentGroup.'_'.$role.'"
								onClick=\'page.postLT("mapRelationsAjaxArea", "wiki/related.on.map/'.$this->wiki->getID().'/'.$currentCore.'", '.json_encode($action).');\'>
									<span class="map-related-legend" style="background-color:'.$this->helper->formatMajorRole($role)->color.'">&nbsp;</span>
									<span class="'.$this->helper->formatMajorRole($role)->ico.'"></span> '.
									$this->helper->formatMajorRole($role)->title.' <span class="badge">'.$this->helper->numberFormat($countInRole).'</span>
							</button> ';
						$this->addJS("results.mapsMenu.drawLine('".uniqid()."', 'button{$currentGroup}', 'button_{$currentGroup}_{$role}');");	
						}
					}
				$relatedWithRolesStr.='</div>';
				$recInGroupsToShow[$currentGroup] = $recInGroupsHasRoles[$currentGroup] ?? [];
				}
			}
		} else {
		$relatedWithRolesStr = $this->transEsc('select something from the "show relations with"');
		}

	echo '<div style="position:relative;" id="mapRelationsBlock">';
	echo '<br/><p>'.$this->transEsc('Relation were created on the basis of entries in bibliographic records. Number of records in which the '.$this->wiki->get('labels').' was found').': <strong>'.$totalResults.'</strong>.</p>';	
	echo '<p>'.$this->transEsc('Relation means that they co-occur in the same bibliographic record').'.</p>';	
	echo '<div class="related-group">';
	echo '<div class="related-group-items">';
	echo $objectRolesStr;
	echo '</div>';
	echo '<div class="related-group-space">';
	echo '</div>';
	echo '<div class="related-group-items">';
	echo $relatedWithStr;
	echo '</div>';
	echo '<div class="related-group-space">';
	echo '</div>';
	echo '<div class="related-group-items">';
	echo $relatedWithRolesStr;
	echo '</div>';
	echo '</div>';
	echo '<div id="mapRopesBlock"></div>';
	echo '</div>';
	



	######################################################################################################################################################################################################################################
	##
	##    geting & drawing results
	##
	######################################################################################################################################################################################################################################
	
	
	# echo "<hr><h4>tmp tech part:</h4>";
	# echo $this->helper->pre($useForDisplay);
	
	## rejecting objects that should not be visible
	if (!empty($recInGroupsToShow) && !empty($useForDisplay)) {
		foreach ($recInGroupsToShow as $currentGroup => $currentGroupValues) {
			foreach ($currentGroupValues as $toCheckWikiq => $inRoles) {
				foreach ($inRoles as $roleKey=>$roleCount) 
					if (in_array($roleKey, $useForDisplay)) 
						$recInGroupsToMap[$currentGroup][$toCheckWikiq][$roleKey] = $roleCount;
				}
			}
		}
		
	## acquisition of place identifiers	
	$maxPoints = 500;
	$alert = '';
	$maxValue = 0;
	
	if (!empty($recInGroupsToMap)) {
		foreach ($recInGroupsToMap as $currentGroup => $currentGroupValues) {
			# echo '<div style="display: inline-block; padding:10px; background-color:#ddd; margin:10px; vertical-align:top; ">';
			# echo $currentGroup.' '.count($currentGroupValues);
			# echo '<hr>';
			#echo $this->helper->pre($currentGroupValues);	
			$i = 0;
			$query = [];
			if (count($currentGroupValues)>0) {
				
				$wikiqToShow = array_keys($currentGroupValues);
				$currentCore = strtolower($currentGroup);
				if (count($wikiqToShow) > $maxPoints) {
					$wikiqToShow = array_slice($wikiqToShow, 0, $maxPoints, true);
					$alert = $this->transEsc("Not all points are visible on map");
					}
				
				$query['q'] = [
						'field' 	=> 'q',
						'value' 	=> 'id:('.implode(' OR ',$wikiqToShow).')'
						];
				$query['rows'] = [
						'field' 	=> 'rows',
						'value' 	=> count($currentGroupValues)
						];
				$query['start'] = [
						'field' 	=> 'start',
						'value' 	=> 0
						];
				
				$results = $this->solr->getQuery($currentCore, $query); 
				$results = $this->solr->resultsList();
				
				# echo end($this->solr->alert).'<hr>';
				
				$i = 0;
				foreach ($results as $i=>$result) {
					$i++;
					$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
					$placeWikiQ = '';
					
					switch ($currentCore) {
						case 'persons' : {
							if (!empty($resultObj->getStr('birth_place'))) {
								$placeWikiQ = strstr($resultObj->getStr('birth_place'), '|', true);
								$pointsOnMap[$placeWikiQ][$result->wikiq]['pointRelations']['birth_place'] = 'birth_place';
								$pointsOnMap[$placeWikiQ][$result->wikiq]['biblioRelations'] = $currentGroupValues[$resultObj->solrRecord->wikiq];
								$pointsOnMap[$placeWikiQ][$result->wikiq]['label'] = $resultObj->getStr('labels');
								}
							if (!empty($resultObj->getStr('death_place'))) {
								$placeWikiQ = strstr($resultObj->getStr('death_place'), '|', true);
								$pointsOnMap[$placeWikiQ][$result->wikiq]['pointRelations']['death_place'] = 'death_place';
								$pointsOnMap[$placeWikiQ][$result->wikiq]['biblioRelations'] = $currentGroupValues[$resultObj->solrRecord->wikiq];
								$pointsOnMap[$placeWikiQ][$result->wikiq]['label'] = $resultObj->getStr('labels');
								}
							} 
							break;
						case 'places' : {
							if (!empty($resultObj->getStr('location'))) {
								$placeWikiQ = $resultObj->solrRecord->wikiq;	
								$pointsOnMap[$placeWikiQ][$currentGroup.'_location'][$resultObj->solrRecord->wikiq] = $currentGroupValues[$resultObj->solrRecord->wikiq];
								$pointsOnMap[$placeWikiQ][$currentGroup.'_location'][$resultObj->solrRecord->wikiq]['label'] = $resultObj->getStr('labels');
								$pointsOnMap[$placeWikiQ][$result->wikiq]['biblioRelations'] = $currentGroupValues[$resultObj->solrRecord->wikiq];
								$pointsOnMap[$placeWikiQ][$result->wikiq]['label'] = $resultObj->getStr('labels');
								}
							} 
							break;
						default : {
							if (!empty($resultObj->getStr('location')) && (!empty($currentGroupValues[$resultObj->solrRecord->wikiq]))) {
								
								$placeWikiQ = strstr($resultObj->getStr('location'), '|', true);
								$pointsOnMap[$placeWikiQ][$result->wikiq]['pointRelations'][$currentGroup.'_location'] = $currentGroup.'_location';
								
								$pointsOnMap[$placeWikiQ][$result->wikiq]['biblioRelations'] = $currentGroupValues[$resultObj->solrRecord->wikiq];
								$pointsOnMap[$placeWikiQ][$result->wikiq]['label'] = $resultObj->getStr('labels');
						
								}
							}	
						}
					# echo $i.'. '.$result->wikiq.'<br/>';
					if (!empty($placeWikiQ))
						@$tableOftotals[$placeWikiQ] += array_sum($currentGroupValues[$result->wikiq]);
					$results[$result->wikiq] = $result;
					
					unset($results[$i]);
					}
				}
			# echo '</div>';
				
			}
		
		
		if (!empty($pointsOnMap)) {
			$js = [];
			$js[] = "map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); "; // clear map
	
			# echo "<h3>Points to show on map</h3>";
			arsort($tableOftotals);
			# echo "max relations on one point:". max($tableOftotals).'<br/>';
			$i = 0;
			foreach ($tableOftotals as $q=>$cutOffValue) {
				$i++;
				#echo "$q => $v<br/>";
				if ($i>=20) break;
				}
			$pointsList = array_keys($pointsOnMap);
			$query = [];
			$query['q'] = [
					'field' 	=> 'q',
					'value' 	=> 'id:('.implode(' OR ',$pointsList).')'
					];
			$query['rows'] = [
					'field' 	=> 'rows',
					'value' 	=> count($pointsOnMap)
					];
			$query['start'] = [
					'field' 	=> 'start',
					'value' 	=> 0
					];
			
			$results = $this->solr->getQuery('places', $query); 
			$results = $this->solr->resultsList();
			
			/*
			echo '<div class="row">';
			echo '<div class="col-sm-6">';
			echo $this->helper->pre($pointsList);
			echo 'end pointsOnMap<br/>';
			echo '</div>';
			echo '<div class="col-sm-6">';
			echo $this->helper->pre($query);	
			echo end($this->solr->alert);
			echo '</div>';
			echo '</div>';
			*/
			
			if (!empty($results)) {
				foreach ($results as $pointOnMap) {
					
					$resultObj = new wikiLibri($this->user->lang['userLang'], $pointOnMap);
					if (!empty($pointsOnMap[$resultObj->solrRecord->wikiq])) {
						$pointObjectsToShow = $pointsOnMap[$resultObj->solrRecord->wikiq];
						
						# echo '<hr><b>'.$resultObj->getStr('labels').'</b> <small>'.$resultObj->solrRecord->wikiq.'</small>';

						$pointRoleSums = [];
						$listStr = [];
						foreach ($pointObjectsToShow as $objectWikiQ=>$resultRole) {
							$pjs = [];
							$headLink = $this->buildURL('wiki/record/'.$resultObj->solrRecord->wikiq);
							$point['link'] = "<div id='placeBox_{$resultObj->solrRecord->wikiq}' class='mapPlaceBox'><h3><a href='{$headLink}'>{$resultObj->getStr('labels')}</a></h3></div>";
							
							$roleList = [];
							$roleListStr = [];
							$key = uniqid();
							
							
							$listStr[$objectWikiQ] = '<a href="'.$this->buildURL('wiki/record/'.$objectWikiQ).'" class="titleLink"><b>'.$resultRole['label'].'</b></a> ';
							if (!empty($resultRole['biblioRelations'])) {
								$roleList = $resultRole['biblioRelations'];
								foreach ($resultRole['biblioRelations'] as $roleGroup=>$roleCount) {
									$withRoleStr = $objectWikiQ.'|'.$roleGroup;
									$stepArray = $baseConditions;
									$stepArray[] = 'with_roles2_wiki:"'.$withRoleStr.'"';
									$key = $this->buffer->createFacetsCode($stepArray);
						
									$roleListStr[] = ' <a href="'.$this->buildURL('results', ['core'=>'biblio', 'facetsCode'=>$key]).'" title="'.$this->helper->formatMajorRole($roleGroup)->title.'"><i class="'.$this->helper->formatMajorRole($roleGroup)->ico.'"></i> <span class="badge" style="background-color:'.$this->helper->formatMajorRole($roleGroup)->color.'">'.$roleCount.'</span></a>';
									@$pointRoleSums[$roleGroup] += $roleCount;
									}
								$listStr[$objectWikiQ] .= '<span class="links">'.implode($roleListStr).'</span>';
								$listOrder[$objectWikiQ] = array_sum($resultRole['biblioRelations']);
								}
							if (!empty($resultRole['pointRelations'])) {
								$listStr[$objectWikiQ] .= '<span class="tags">';
								foreach ($resultRole['pointRelations'] as $resultRoleV)
									$listStr[$objectWikiQ] .= ' <span class="badge">#'.$this->transESC($resultRoleV).'</span>';
								$listStr[$objectWikiQ] .= '</span>';
								}
							}
									
						if (!empty($pointRoleSums)) {
							$svgLinkParts = [];
							$subDataCount = 0;
							foreach ($pointRoleSums as $roleGroup => $roleCount) {
								$svgLinkParts[] = str_replace('#','',$this->helper->formatMajorRole($roleGroup)->color).'_'.$roleCount;
								}	
							$pngLink = $this->HOST.'_tools/png/'.implode('-',$svgLinkParts).'-pie.png';	
							$pngId = hash('crc32b', $pngLink);
							
							#  echo '<img src="'.$pngLink.'" alt="PieChart'.implode('-',$pointRoleSums).'" title="PieChart'.implode('-',$pointRoleSums).'" style="width:36px; margin:10px;"><br/>';
							
							uksort($listStr, function($a, $b) use ($listOrder) {return $listOrder[$b] - $listOrder[$a];	});
							$i = 0; 
							$max = 5;
							echo '<div class="hidden"><div id="content_'.$resultObj->solrRecord->wikiq.'">';
							foreach ($listStr as $listStrkey=>$string) {
								$i++;
								echo '<div class="placeBoxAppendItem">'.$i.'. '.$string.'</div>';
								if ($i>$max) break;
								}
							if ($max < count($listStr))
								echo '<div class="placeBoxAppendItem text-right"><a href="#">more '.count($listStr)-$max.' rec...</a></div>';
							echo '</div></div>';
							$point['link'] .="<div id='placeBoxAppend_{$resultObj->solrRecord->wikiq}' class='placeBoxAppend'> link_{$key} </div>";
							
							$subDataCount = array_sum($pointRoleSums);
							if (!empty($resultObj->solrRecord->latitiude)) {
								$coor = $resultObj->solrRecord->latitiude .','. $resultObj->solrRecord->longitiude;
									
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
								$pjs[] = "smarker_$key.on({click: function () { 
										results.maps.currentPlacePost('{$resultObj->solrRecord->wikiq}', 'test');
										tekst = $('#content_{$key}').html();
										$('#link_{$key}').html(tekst);
										$('#link_{$key}').css('background-color', 'red');	
										
										}});";
								$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
								
								$js[] = implode("\n", $pjs);
								}
							}
						
						#echo '<br>'.$objectWikiQ.$this->helper->pre($resultRole);
								
						}
					}
				$qualityRange = round((count($results)/count($pointsList))*100,0);
				if ($qualityRange<100) {
					echo '<b>'.$this->transEsc('Notice').'!</b> ';
					echo '<span class="pie" style="--p:'.$qualityRange.';--c:#5F3D8D;--b:7px;">'.$qualityRange.'%</span> '.$this->transEsc('note_1');
					}
				
				}
			echo '<div id = "mapPopupCurrentPlace" class="hidden"></div>';	
			
			$this->addJS(implode("\n", $js));
			/*
			if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
				echo '<textarea style="width:100%; height:150px; margin:30px;">'.implode("\n", $js).'</textarea>';
				}
			*/
			}
		
			
		} 
	} else {
	echo '<p style="margin:50px;">'.$this->transEsc('There is nothing to show here').'.</p>';
	}


# echo 'post'.$this->helper->pre($_POST);
# echo 'session'.$this->helper->pre($_SESSION['mapRelationGraph']);
# echo 'recInGroups'.$this->helper->pre($recInGroups);
$this->addJS('$("#mapRelationsAjaxArea").css("opacity", "1");');




?>
<script>$("#mapRelationsAjaxArea").css("opacity", "1");</script>
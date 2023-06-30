<br/>
<?php 
$mch = 0;
$recId = $this->routeParam[0];
# echo $this->helper->pre($this->routeParam);
# echo '<button class="btn btn-success" OnClick="results.maps.addBiblioRecRelatations(\''.$recId.'\')">reload</button>';

$this->addClass('solr', new solr($this->config));
$this->addClass('buffer', new marcBuffer()); 
$this->buffer->setSql($this->sql);

$record = $this->solr->getRecord('biblio', $recId);
if (!empty($record->geowiki_str_mv))
	foreach ($record->geowiki_str_mv as $place) {
		$Tcoor[$place] = $place;
		}

#echo 'all persons: persons_wiki_str_mv'.$this->helper->pre($record->persons_wiki_str_mv);
#Echo 'authors live places: all persons: author_search'.$this->helper->pre($record->author_search);
#Echo 'subject persons live places: all persons: subject_person_str_mv'.$this->helper->pre($record->subject_person_str_mv);

if (!empty($record->persons_wiki_str_mv)) {
	$t = $this->psql->querySelect("SELECT name, wikiq, viaf_id, year_born, year_death, place_born, place_death, solr_str FROM persons WHERE wikiq IN (".implode(', ', $record->persons_wiki_str_mv).");");
	if (is_array($t)) {
		foreach ($t as $person) {
			$Tpersons[$person['wikiq']] = $person;
			$Tcoor[$person['place_born']] = $person['place_born'];
			$Tcoor[$person['place_death']] = $person['place_death'];
			}
		}
	}

#echo $this->helper->pre($Tcoor);	
if (!empty($Tcoor)) {
	foreach ($Tcoor as $coor)
		if (!empty($coor) && is_array($coor))
			$Tin[$coor] = $coor;
			else 
			unset($Tcoor[$coor]);
	if (!empty($Tin)) {	
		$t = $this->psql->querySelect("SELECT wikiq,lat||','||lon as coor, lat, lon, name FROM places_on_map WHERE wikiq IN (".implode(',', $Tin).") AND lat IS NOT NULL AND lon IS NOT NULL;");
		if (is_Array($t))
			foreach ($t as $coor) {
				$Tcoor[$coor['wikiq']] = $coor;
				
				# echo $coor['wikiq'].$this->helper->pre($coor);
				$Tlat[$coor['lat']] = $coor['lat'];
				$Tlon[$coor['lon']] = $coor['lon'];
				}
		################################################################################################################################### preparing points
		$js = [];
		$solrIndexes = [
			'geographicpublication_str_mv' => [ 'color'=>'purple', 'label' => $this->transEsc('Publication place')],
			'geographic' => [ 'color'=>'red', 'label' => $this->transEsc('Subject place')],
			];

		foreach ($solrIndexes as $index=>$params) {
			$mch++;
			$color = $params['color'];
			$label = $params['label'];
			
			if (!empty($record->$index)) {
				$lp = 0;
				if ($this->routeParam[$mch] == 'true') {
					foreach ($record->$index as $place) {
						$t = explode('|',$place);	
						if (!empty($t[1]) && !empty($Tcoor[$t[1]])) {
							$lp++;
							$gpoint = $Tcoor[$t[1]];
							echo $this->helper->pre($Tcoor);
							$placesOnMap[$gpoint['coor']]['name'] 
								= $gpoint['name'];
							$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
							if (empty($placesOnMap[$gpoint['coor']]['color']))
								$placesOnMap[$gpoint['coor']]['color'] = $color;
								else 
								$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
							@$placesOnMap[$gpoint['coor']]['count']++;
							$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = 1;
							$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $label;
							$placesOnMap[$gpoint['coor']]['roles'][$index]['link'] = $this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["$index:\"$place\""]));
							$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $color;
							}	
						}
					$checked = 'checked';
					} else 
					$checked = '';	
				echo $this->render('helpers/switch.php', [
							'color'=>$color, 
							'checked'=>$checked, 
							'id'=>'map_checkbox_'.$mch, 
							'onChange'=> "results.maps.addBiblioRecRelatations('$recId')", 
							'label'=>$label, 
							'badge'=>$lp
							]);
				}
			}
			
			
		$solrIndexes = [
			'author_search' => [ 'color'=>'darkgreen', 'label' => $this->transEsc('Authors')],
			'subject_person_str_mv' => [ 'color'=>'mediumturquoise', 'label' => $this->transEsc('Subject persons')],
			];

		foreach ($solrIndexes as $index=>$params) {
			$mch++;
			$color = $params['color'];
			$label = $params['label'];
			
			if (!empty($record->$index)) {
				$lp = 0;
				if ($this->routeParam[$mch] == 'true') {
					foreach ($record->$index as $personstr) {
						$t = explode('|',$personstr);	
						if (!empty($t[4])) {
							$wikiQ = $t[4];
							if (!empty($Tpersons[$wikiQ])) {
								$person = $Tpersons[$wikiQ];
								
								$p1 = $p2 = '';
								if (!empty($person['place_born']) && !empty($Tcoor[$person['place_born']])) {
									$lp++;
									$gpoint = $Tcoor[$person['place_born']];
									
									$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
									$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
									if (empty($placesOnMap[$gpoint['coor']]['color']))
										$placesOnMap[$gpoint['coor']]['color'] = $color;
										else 
										$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
									@$placesOnMap[$gpoint['coor']]['count']++;
									$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = 1;
									$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $label.'<br/>'.$person['name'].' '.$this->transEsc('place of birth');
									$placesOnMap[$gpoint['coor']]['roles'][$index]['link'] = '';
									$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $color;
									$p1 = $gpoint['coor'];
									}	
								if (!empty($person['place_death']) && !empty($Tcoor[$person['place_death']])) {
									$lp++;
									$gpoint = $Tcoor[$person['place_death']];
									
									$placesOnMap[$gpoint['coor']]['name'] = $gpoint['name'];
									$placesOnMap[$gpoint['coor']]['headLink'] = $this->buildUrl('wiki/record/Q'.$gpoint['wikiq']);
									if (!empty($placesOnMap[$gpoint['coor']]['color']) && ($placesOnMap[$gpoint['coor']]['color']<>$color)) {
										$placesOnMap[$gpoint['coor']]['color'] = 'mixed';
										$placesOnMap[$gpoint['coor']]['roles'][$index]['count'] = 1;
										$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $label.'<br/>'.$person['name'].' '.$this->transEsc('place of death');
										} else if (!empty($placesOnMap[$gpoint['coor']]['color']) && ($placesOnMap[$gpoint['coor']]['color']==$color)) {
										@$placesOnMap[$gpoint['coor']]['roles'][$index]['count']++;
										$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] .= '<br/>'.$person['name'].' '.$this->transEsc('place of death');
										} else if (empty($placesOnMap[$gpoint['coor']]['color'])) {
										@$placesOnMap[$gpoint['coor']]['roles'][$index]['count']++;
										$placesOnMap[$gpoint['coor']]['color'] = $color;
										$placesOnMap[$gpoint['coor']]['roles'][$index]['title'] = $label.'<br/>'.$person['name'].' '.$this->transEsc('place of death');
										}
									@$placesOnMap[$gpoint['coor']]['count']++;
									$placesOnMap[$gpoint['coor']]['roles'][$index]['color'] = $color;
									$placesOnMap[$gpoint['coor']]['roles'][$index]['link'] = '';
									
									$p2 = $gpoint['coor'];
									}	
								if (!empty($p1) && !empty($p2))
									$js[] = "var apolygon = L.polygon([[$p1], [$p2]], {color: '$color', weight: 3, opacity: 0.4}).addTo(map);";
								}
							}
						}
					$checked = 'checked';
					} else 
					$checked = '';	
				echo $this->render('helpers/switch.php', [
							'color'=>$color, 
							'checked'=>$checked, 
							'id'=>'map_checkbox_'.$mch, 
							'onChange'=> "results.maps.addBiblioRecRelatations('$recId')", 
							'label'=>$label, 
							'badge'=>$lp
							]);
				}
			}





		#########################################################################################################  drawing points

		unset($point);
		$mixed = 0;

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
					#$pjs[] = "var smarker_$key = L.circle([$coor], {color: '$place[color]', fillColor: '$place[color]', fillOpacity: 0.5, radius:100 }).addTo(map); ";	
					$pjs[] = "smarker_$key.bindTooltip('".$place['count']."' , {permanent: true, direction: 'center', className: 'relationLabel-{$place['color']}' });";
					
					}
				
				$pjs[] = "smarker_$key.bindPopup(\"{$point['link']}\")";
				if ($place['color'] == 'mixed') 
					$mixed++;
				if ($place['color'] == 'mixed')
					$place['color'] = 'orange';
				#$pjs[] = "var apolygon = L.polygon([[$base], [$coor]], {color: '{$place['color']}', weight: 1, opacity: 0.5, smoothFactor: 1}).addTo(map);";
			
				$js[] = implode("\n", $pjs);
				
				}
			}
		if ($mixed>0)
			echo '<br/><span class="relationLabel-mixed">'.$this->helper->badgeFormat($mixed).'</span> &nbsp;&nbsp; '.$this->transEsc('Mixed relation points').' <br/><br/>';		
			
		$this->addJS ("map.eachLayer( function(layer) {	if(layer instanceof L.Marker) {map.removeLayer(layer)}; if(layer instanceof L.Circle) {map.removeLayer(layer)}; if(layer instanceof L.Polygon) {map.removeLayer(layer)}}); ");

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
		// map.removeLayer(marker)

		$this->addJS(implode("\n", $js));	
		}
	}
?>
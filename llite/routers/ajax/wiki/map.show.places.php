<?php 
if (empty($this)) die;
require_once('functions/class.helper.php');
require_once('functions/class.maps.php');

$marcRecord = false;
$this->addClass('buffer', new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this)); 


$currentCore = 'places';


if (!empty($this->configJson->$currentCore)) {
	if (!empty($this->configJson->$currentCore->title))
		$title = $this->configJson->$currentCore->title;
		else 
		$title = $this->configJson->$currentCore->title = UcFirst($currentCore);

	require_once('functions/class.wikidata.php');
	require_once('functions/class.wikidata.libri.php');
	
	$this->setTitle($this->transEsc($title));

	$this->addClass('buffer', 		new buffer()); 
	$this->addClass('helper', 		new helper()); 
	$this->addClass('solr', 		new solr($this)); 
	
	$this->configJson->$currentCore->default_view = $this->configJson->$currentCore->default_view ?? 'default-box';
	if (!empty($this->GET['view']))
		$this->saveUserParam('view',$this->GET['view']);
		else if (empty($this->getUserParam('view')))
		$this->saveUserParam('view', $this->configJson->$currentCore->default_view);
	
	if (!empty($this->GET['limit']))
		$this->saveUserParam('limit',$this->GET['limit']);
		else if (empty($this->getUserParam('limit')))
		$this->saveUserParam('limit', $this->configIni['search']['pagination']['default_rpp']);


	
	if (!empty($this->routeParam[3])) {
		$this->facetsCode = $this->routeParam[3];	
		$query[] = $this->buffer->getFacets( $this->facetsCode);	
		} else 
		$this->facetsCode = 'null';	

	$lookfor = $this->postParam('lookfor');
	if (empty($lookFor) && !empty($this->GET['lookfor'])) {
		$lookfor = $this->GET['lookfor'];
		$query['q']=[ 
				'field' => 'q',
				'value' => $lookfor
				];
		} else 
		$query['q']=[ 
				'field' => 'q',
				'value' => '*:*'
				];

	if (!empty($this->routeParam[2])) 
		$this->sortCode = $this->routeParam[2]; 
		else 
		$this->sortCode = 'bc';
	if (!empty($this->configJson->$currentCore->sorting->{$this->sortCode}->solrField)) {
		$query['sort']=[ 
			'field' => 'sort',
			'value' => $this->configJson->$currentCore->sorting->{$this->sortCode}->solrField
			];
		} else {
		$this->sortCode = 'bc';
		$query['sort']=[ 
			'field' => 'sort',
			'value' => 'biblio_count desc'
			];
		}


	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'true'
				];
	$query['facet.limit']=[ 
				'field' => 'facet.limit',
				'value' => 6
				];
	$query['facet.mincount ']=[ 
				'field' => 'facet.mincount',
				'value' => 1
				];

	foreach ($this->configJson->$currentCore->facets->facetsMenu as $facetField) {
		if (!empty($facetField->template) && ($facetField->template == 'timeGraph'))
			$query['facet.offset'.$facetField->solr_index]=[ 
					'field' => 'f.'.$facetField->solr_index.'.facet.limit', // keeping offset only on first field
					'value' => 9999
					];
		$query[]=[ 
					'field' => 'facet.field',
					'value' => $facetField->solr_index
					];
		}
	 
	$query['rows']=[ 
			'field' => 'rows',
			'value' => 50
			];
			
	if (!empty($this->getCurrentPage()>1))
		$query[]=[ 
			'field' => 'start',
			'value' => $this->getCurrentPage()*$this->getUserParam('limit') - $this->getUserParam('limit')
			];		

	$times = [];
	$results = $this->solr->getQuery($currentCore, $query); 
	$results = $this->solr->resultsList();
	$facets = $this->solr->facetsList();
	$totalResults = $recSum = $this->solr->totalResults();

	if ($recSum > 0) {
		$first = current($results);
		$recMax = $first->biblio_count;
		$last = end($results);
		$recMin = $last->biblio_count;
			
		###########################################################################################################################################
		##
		##										Drawing points 
		##
		###########################################################################################################################################
		
		$colorTop = '#f3984e';
		$colorMiddle = '#f0cc41';
		$colorBottom = '#88d167';
		
		$rolesFields = [
				'publication_place_count'=>'Publication place',
				'subject_place_count'=>'Subject place',
				'event_place_count'=>'Event place',
				'person_place_count' => 'Person place'
				];
		
		// meybe usefull? https://github.com/moravcik/Leaflet.TextIcon
		
		$PlaceCircleBasic = "color: '#76679B', fillColor: '#5F3D8D', weight: 2, fillOpacity: 0.5";
		$PlaceCircleHover = "color: 'red', fillColor: 'yellow', weight: 1, fillOpacity: 0.5";
		$emptyStr = ''; //$this->transEsc('Point on map to see details');
		
		$lp = 0;
		foreach ($results as $place) {
			$lp++;
			$pjs = [];
			
			$icon = 'SBottom';
			if ($lp<10) { $label = 'Bottom'; $icon = 'None'; }
			if ($lp<6) { $label = 'Middle'; $icon = 'None'; }
			if ($lp<3) { $label = 'Top'; $icon = 'None'; }
			
			$tlat[] = $place->latitiude;
			$tlon[] = $place->longitiude;
			
			$placeWiki = new wikiLibri($this->userLang, $place);
			$wikiName = $placeWiki->getStr('labels');
			
			if (!empty($placeWiki->solrRecord->picture))
				$place->link = "<div class='box-Image'><img div class='box-img-exists' style='background-image: url({$this->buffer->convertPicturePath(current($placeWiki->solrRecord->picture), 'small')});'></div></div>";
				else 
				$place->link = '';
			
			$place->link .= "<h3><a href='{$this->buildUrl('wiki/record/'.$place->wikiq)}'>{$wikiName}</a></h3>";
			$place->link .= '<p>'.$placeWiki->getStr('descriptions').'</p>';
			
			foreach ($rolesFields as $roleField => $roleName)
				if (!empty($place->$roleField)) $place->link .= $this->transEsc($roleName).': '.$place->$roleField.'<br/>';
			$key = $place->wikiq;
			
			$pjs[] = "var smarker_$key = L.marker([$place->latitiude, $place->longitiude], {icon: Circle$icon }); ";
			$pjs[] = "smarker_$key.addTo(map);";
			if ($lp<10) $pjs[] = "smarker_$key.bindTooltip('".$this->helper->badgeFormat($place->biblio_count)."' , {permanent: true, direction: 'center', className: 'label-{$label}' });";
			$pjs[] = "smarker_$key.on({click: function () { $('#poitedDet').html(\"$place->link\");	}});";
			$pjs[] = "smarker_$key.bindPopup(\"{$place->link}\")";
			$js[] = implode("\n", $pjs);
			}
		

		
		$addStr = http_build_query($this->GET);
		$lon['min'] = min($tlon);
		$lat['min'] = min($tlat);
		$lon['max'] = max($tlon);
		$lat['max'] = max($tlat);
		$js[] = "map.fitBounds([[$lat[max],$lon[max]],[$lat[min],$lon[min]]]);";
		$js[] = "$('#mapStartZoom').val(map.getZoom());";
		# $js[] = "map.on('zoomend', function() { $('#mapLastAction').html('zoom end'); });";
		$js[] = "map.on('moveend', function() { 
					//page.ajax('ajaxBox','wiki/maps.show.places?$addStr&N='+map.getBounds().getNorth()+'&S='+map.getBounds().getSouth()+'&W='+map.getBounds().getWest()+'&E='+map.getBounds().getEast());
					$('#mapBoundN').val(map.getBounds().getNorth());
					$('#mapBoundS').val(map.getBounds().getSouth());
					$('#mapBoundE').val(map.getBounds().getEast());
					$('#mapBoundW').val(map.getBounds().getWest());
					$('#mapZoom').val(map.getZoom());
					results.maps.moved('$recSum', '$lp');
					});";
		$this->addJS(implode("\n", $js));	

			
		
		$OMO = "$('#poitedDet').html(this.title);";
		$sums = '<div style="margin-top:10px; margin-bottom:10px; width:100%; text-align:center;"><div class="btn-group">';
		/*
		if (is_array($t)) {
			$res = current($t);
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Subject places shown on the map').'"><i class="ph-notebook-bold"></i> '.$this->helper->numberFormat($res['subjects']).'</button>';
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Publication places shown on the map').'"><i class="ph-house-line-bold"></i> '.$this->helper->numberFormat($res['pubplaces']).'</button>';
			$sums .= '<button class="btn btn-xs" OnMouseOver="'.$OMO.'" OnMouseOut="$(\'#poitedDet\').html(\'\');" title="'.$this->transEsc('Places of birth or death of persons appearing in the bibliography').'"><i class="ph-person-simple-bold"></i> '.$this->helper->numberFormat($res['personplaces']).'</button>';
			
			}
		*/	
		$sums.="</div></div>";
		
		echo '<div class="detailsview">';
		echo '<dl class="detailsview-item"><dt class="dv-label">'.$this->transEsc("Total results").':</dt><dd class="dv-value"><strong>'.$this->helper->numberFormat($totalResults).'</strong></dd></dl>';
		echo '<dl class="detailsview-item"><dt class="dv-label">'.$this->transEsc("Shown on map").':</dt><dd class="dv-value"><strong id="mapMovedActions">'.$this->helper->numberFormat($lp).'</strong></dd></dl>';
		echo '</div>';
		echo $sums;
		#echo $this->transEsc("Max publications/point").": <strong>".$this->helper->numberFormat($pmax).'</strong><br/>';
		echo "<div id='poitedDet'>".$emptyStr."</div>";
		
		echo '
			<div id="mapLastAction" class="text-center" >
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
	}
	
?>
<?php
$PRE = '';
$stats = '';
$extraTabs = [];

/* 
	
	#$place = $res = $this->buffer->getPlaceParams($this->wiki->get('labels'), 'extended');
	# $neighborhood = $this->buffer->getNeighborhoodPlaces($res);
	
	
	if (!empty($mapPoint = $this->wiki->getCoordinates('P625'))) {
		$point['lon'] = $mapPoint->longitude;
		$point['lat'] = $mapPoint->latitude;
		$point['name'] = $this->wiki->get('labels');
		$point['desc'] = $this->transEsc('Viewed place');
		$point['link'] = $this->buildURL('wiki/record/'.$this->wiki->record->id);
		$point['marker'] = 'marker';
		$point['color'] = 'blue';
		$this->maps->saveMapsPoint($point);
		}	
					
	if (count($this->maps->getMapsPoints())>0) {
		foreach ($this->maps->getMapsPoints() as $place) {
			$tlat[] = $place['lat'];
			$tlon[] = $place['lon'];
			}
		$lon['min'] = min($tlon);
		$lat['min'] = min($tlat);
		$lon['max'] = max($tlon);
		$lat['max'] = max($tlat);
		$this->addJS("map.fitBounds([[$lat[max],$lon[max]],[$lat[min],$lon[min]]]);");
		
		$mapDraw = '<div style="border:solid 1px lightgray;  margin-top:0px;">';
		$mapDraw.= $this->maps->drawWorldMap();
		$mapDraw.= "</div>";
		
		#$mapDraw.= '<button style="border:transparent; background-color: transparent;" OnClick="results.maps.addWikiRelatations(\''.$this->wiki->record->id.'\');">Draw Wiki relations</button>';
		$mapDraw.= ' <div id="mapRelationsAjaxArea">
					'.$this->helper->loader2().'
					
					</div>';
		#$this->addJS("results.maps.addPlaceRelatations('".$this->wiki->record->id."')"); // should I delete this method from libri.js ?
		
		$extraTabs['map'] = ['label' => $this->transEsc('Map'), 'content' => $mapDraw];
		} 
		


	if (!empty($stat->name) && is_array($stat->name) && count($stat->name)>1) {
		
		if (empty($this->routeParam[1])) {
			$agrBtn = '';		
			$agrInfo = '<br/>'.$this->transEsc('Below is a summary for all variants').'.';
			} else {
			$agrBtn = ' <small><a href="'.$this->buildUrl('wiki/record/'.$this->routeParam[0].'/agregated').'">'.$this->transESC('Aggregate all names').'</a></small>';
			$agrInfo = '<br/>'.$this->transEsc('Below is a summary for the name').' <strong>'.$this->wiki->get('labels').'</strong>. '.$agrBtn;
			}
		$stats .= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Probably the same place was typed down in bibliographic records in several different ways').':</h4>';
		foreach ($stat->name as $placeName) {
			$stats.= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
			}
		$stats .= $agrInfo;
		$stats .= '</div>';
		}
		
	if (!empty($stat->facets['geographic_facet']) && (count($stat->facets['geographic_facet'])>1)) {
		$stats.= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Other places that appear most frequently in bibliographic records, along with the place you are viewing').':</h4>';
		foreach ($stat->name['geographic_facet'] as $placeName=>$placeCount)
			if ($placeName!==$place['name'])
				$stats .= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
		$stats .= '</div>';
		
		}	
		
	if (!empty($neighborhood))  {
		$stats.= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Nearby places').':</h4>';
		foreach ($neighborhood as $placeName)
			if ($placeName!==$place['name'])
				$stats .= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
		$stats .= '</div>';
		}	

	if ($stats<>'') {
		$stats = '
			<div class="row">
			<div class="col-sm-12">
			<div class="panel panel-default">
				<div class="panel-body">
					'.$stats.'
				</div>
			</div>
			</div>
			</div>';
		}
		
	$statBoxes = $this->getIniArray('places', 'statBoxes');
	$formatters = $this->getIniArray('facets', 'formattedFacets');
	
	if (is_Array($statBoxes) && (count($statBoxes)>0)) {
		$stats .= '<div class="statBox">';
		$Llp = 0;
		foreach ($statBoxes as $facet=>$facetName) {
			$nstat = [];
			$lp = 0;
			if (!empty($stat->facets[$facet])) {
				foreach ($stat->facets[$facet] as $k=>$v) {
					$lp++;
					$index = $lp+$Llp;

					$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\"", "geowiki_str_mv:\"{$this->wiki->getIDint()}\""]);
					$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key); // , ['lookfor'=>$stat->name, 'type'=>'AllFields']
					
					$tk = $k;
					if (array_key_exists($facet, $formatters))
						$tk = str_replace('"', "'", $this->helper->{$formatters[$facet]}($k));
					if (!empty($stepSetting->formatter)) {
						$formatter = $stepSetting->formatter;
						$tk = $this->helper->$formatter($k);
						}
					if ($stepSetting->translated)
						$tk = $this->transEsc($k);
					
					
					if ($v>0)
						$nstat[$index] = [
							'label' => $this->transEsc($tk),
							'label_o' => $k,
							'count' => $v,
							'link' 	=> $link,
							'color' => $this->helper->getGraphColor($lp),
							'index' => $index,
							];
					}
				}
			$Llp = $Llp+$lp;
			$stats .= $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
			}
		$stats .="</div>";
		if ($lp>0)
			$extraTabs['bstats'] = ['label' => $this->transEsc('Bibliographical statistics'), 'content' => $stats ];	
		}
	
	$popPersonsStr = '';
	if (!empty($popPersons['count'])) {
		$popPersonsTotal = $popPersons['count']['count'];
		$wikiq = $popPersons['count']['wikiq'];
		unset($popPersons['count']);
		if ($popPersonsTotal>0) {
			$popPersonsList = '';
			$lp = 0;
			foreach ($popPersons as $popPerson) {
				$lp++;
				$activePerson = (object)$popPerson;
				$bornIcon = $dieIcon = '';
				if ($popPerson['place_born'] == $wikiq)
					$bornIcon = '<small class="label label-success" title="'.$this->transEsc('Was born here').'">'.$this->transEsc('Born here').'</small> ';
				if ($popPerson['place_death'] == $wikiq)
					$dieIcon = '<small class="label label-danger" title="'.$this->transEsc('Was died here').'">'.$this->transEsc('Died here').'</small> ';
				
				#$popPersonsList.= '<a href="'.$this->buildUrl('wiki/record/Q'.$popPerson['wikiq']).'">'.$popPerson['name'].' <small>'.$popPerson['year_born'].'-'.$popPerson['year_death'].'</small></a> '.$bornIcon.$dieIcon.' <span class="badge">'.$popPerson['rec_total'].'</span><br/>'; 
				#$popPersonsList.= $this->helper->pre($popPerson);
				

				$wikiId = 'Q'.$activePerson->wikiq;
				$activePerson->wiki = new wikidata($wikifile = json_decode($this->buffer->loadFromWikidata($wikiId))); 
				$activePerson->wiki->setUserLang($this->user->lang['userLang']);
				$personPhoto = $this->buffer->loadWikiMediaUrl($activePerson->wiki->getStrVal('P18'));
				if (!empty($personPhoto))
					$photoBox = '<div class="pi-Image"><div class="img-circle" style="background-image: url(\''.$personPhoto.'\');"></div></div>';
					else 
					$photoBox = '
						<div class="pi-Image empty">
							<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
						</div>';

				$popPersonsList.= '
					<div class="person-info">
						<div class="pi-Body">
							'. $photoBox .'
							<div class="pi-Desc">
								<div class="pi-linkPanel">'. $this->render('persons/linkPanel.php', ['AP' => $activePerson] ) .'</div>
								<div class="pi-head">
									<h4>
									  <a href="'. $this->buildUri('wiki/record/Q'.$activePerson->wikiq) .'" title="'. $this->transEsc('card of') .'...">
										'. $activePerson->wiki->get('labels') .' 
										<small>'. $this->render('persons/dateRange.php', ['b'=>$activePerson->wiki->getDate('P569'), 'd'=>$activePerson->wiki->getDate('P570')]) .'</small>
									  </a>
									</h4>
								</div>
								<p>'. $this->helper->setLength($activePerson->wiki->get('descriptions'),125) .'</p>
								<a class="pi-bottom-link" href="'. $this->buildUri('wiki/record/Q'.$activePerson->wikiq) .'" title="'. $this->transEsc('card of') .'...">'.$bornIcon.$dieIcon.'</a>
							</div>
						</div>
					</div>';
				
				
				}
			if ($popPersonsTotal>$lp) {	
				$popPersonsList.= '<div class="text-right">'.$this->transEsc('All persons').' <span class="badge">'.$this->helper->numberFormat($popPersonsTotal).'</span></div>';
				$popPersonsStr = '<p>'.$this->transESC('Most popular in bibliographic records').'</p>'.$popPersonsList;
				} else {
				$popPersonsStr = $popPersonsList;
				}
			}
		
		if ($popPersonsTotal>0) {
			$extraTabs['persons'] = ['label' => $this->transEsc('Related persons').' <span class="badge">'.$this->helper->numberFormat($popPersonsTotal).'</span>',		'content' => $popPersonsStr];
			}
		
		}
	
	
	
	P242 -> value - obraz z lokalizacją !
	P625 -> value => stdClass Object
	P1332 -> value => stdClass Object
										(
											[latitude] => 48.902156
											[longitude] => 2.3844292
										)
	P998[0] -> value :rozdzielane / - ścieżka dostępu - kontytent/kraj/region									
	P998[1] -> value :rozdzielane / - ścieżka dostępu - world/kontytent/kraj/region									
			

	if (!empty($dataQualityStr))
		$extraTabs['orginLabels'] = [
				'label' => $this->transEsc('Data quality').'', 
				'content' => $dataQualityStr
				];
	*/
	

?>

    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wiki.photo.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
		</div>
		<div class="record-main-panel">
			
			<p><?= $this->wiki->get('descriptions') ?></p>
			
			
			<ul class="detailsview">
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country'),  'value'=>$this->wiki->getHistoricalCountry(date("Y"))]) ?>
				<?= $this->render('wiki/row.place.php', ['label'=>$this->transEsc('Coordinates').' <small>(WGS-84)</small>',  'value'=>$this->wiki->getCoordinates('P625'), 'title'=>$title]) ?>
				<?= $this->render('wiki/time.line.countries.php', ['label'=>$this->transEsc('Country'),  'value'=>$this->wiki->getHistoricalCountries('P17')]) ?>
				<?= $this->render('wiki/time.line.php', ['label'=>$this->transEsc('Historical names'),  'value'=>$this->wiki->getHistoricalNames()]) ?>
			</ul>
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a>
			</small>
			</div>
			
		</div>
		
	</div>
	
	

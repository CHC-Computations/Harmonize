<?php
$PRE = '';
$stats = '';

switch ($this->wiki->recType()) {
	
	##################################################################################################################################################################################
	case 'person' :
			if (is_object($stat)) {
				$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\""]);
				$PRE = $stat->fullRecivedName."<pre>".print_R($stat,1)."</pre>";
				$this->addJS('page.ajax("drawPoints", "wiki/places.list?author='.urlencode($stat->fullRecivedName).'");');	 //$stat->fullRecivedName
				
				$facetCode = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->fullRecivedName\" OR topic_person_str_mv:\"$stat->fullRecivedName\""]);
				$stats = '<div class="statBox">';
				$stats .= '
						<div class="il-panel">
							<div class="il-panel-header"><h4>'.$this->transEsc('Roles summary') .'</h4></div>
							<div class="il-panel-bottom">
								<table class="list">
									<tbody>
										<tr> 
											<td colspan=3>'. $this->transEsc('We have').' <b><A href="'. $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$facetCode ) .'">'. $stat->numFound .'</a></b> '. $this->transEsc('bibliographic records').'.</td>
										</tr>';
				if (!empty($stat->as_author)) {
					$link = $this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$as_author_facet );
					$stats .='<tr>
									<td><a href="'.$link.'">'.$this->transEsc('As an author').': <b>'. $stat->as_author .'</b></a></td>
									<td>'. $this->helper->percentBox($stat->as_author_pr,100,'#AC9FC2') .'</td>
									<td>%</td>
								</tr>';				
					} 
				if (!empty($stat->as_topic_person)) {
					$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["topic_person_str_mv:\"$stat->topic_person_str_mv\""]) );
					$stats .= '<tr>
									<td><a href="'. $link .'">'. $this->transEsc('As a topic person').': <b>'. $stat->as_topic_person.'</b></a></td>
									<td>'. $this->helper->percentBox($stat->as_topic_person_pr,100,'#AC9FC2') .'</td>
									<td>%</td>
								</tr>';
					}
				$stats.= '				
									</tbody>
								</table>
								
							</div>
							<div class="il-panel-bottom" id="otherRolesBox">
								'.$this->helper->loader2().'
								'.$this->transESC('Searching for other roles (as translator, as illustrator, etc)').'...
								
							</div>
						</div>
				
						';
				
				
				$_SESSION['tr'] = [];
				$_SESSION['tid'] = [];
				$this->JS[] = "page.ajax('otherRolesBox','persons/person.roles.stat/{$stat->as_author}/{$as_author_facet}/');";
				
				$statBoxes = $this->getIniArray('person-card', 'statBoxes');
				$Llp = 0;
				foreach ($statBoxes as $facet=>$facetName) {
					$nstat = [];
					$lp = 0;
					if (!empty($stat->facets[$facet]))
						foreach ($stat->facets[$facet] as $k=>$v) {
							$lp++;
							$index = $lp+$Llp;

							# $key = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\"", "$facet:\"$k\""]);
							$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\""]);
							$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>'viaf/'.$this->person->record->viafid, 'type'=>'AllFields']);
							
							$nstat[$index] = [
								'label' => $this->transEsc($k),
								'label_o' => $k,
								'count' => $v,
								'link' 	=> $link,
								'color' => $this->helper->getGraphColor($lp),
								'index' => $index,
								];
							}
					$Llp = $Llp+$lp;
					$stats .= $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
					}
				$stats .="</div>";
				}
			break;
	##################################################################################################################################################################################
	case 'place' : 
			
			$place = $res = $this->buffer->getPlaceParams($this->wiki->get('labels'), 'extended');
			$neighborhood = $this->buffer->getNeighborhoodPlaces($res);
			$stats = '';
	

			if (!empty($place['other_names']) && is_array($place['other_names'])) {
				
				$stats .= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('The same place appears in bibliographic records under different names').':</h4>';
				foreach ($place['other_names'] as $placeName) {
					$stats.= $this->render('record/place-link-simple.php', ['place'=>$placeName]);
					}
				$stats .= '</div>';
				}
				
			if (!empty($stat->facets['geographic_facet']) && (count($stat->facets['geographic_facet'])>1)) {
				$stats.= '<div class="detailsview-pack"><h4 class="detailsview-h">'.$this->transEsc('Other places that appear most frequently in bibliographic records, along with the place you are viewing').':</h4>';
				foreach ($stat->facets['geographic_facet'] as $placeName=>$placeCount)
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
			if ($stats<>'') 
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
				
			$PRE = "<pre>".print_R($stat,1)."</pre>";
			
			$statBoxes = $this->getIniArray('place-card', 'statBoxes');
			$authorFormat = $this->getIniArray('facets', 'facetOptions','authorFormats');
			
			$stats .= '<div class="statBox">';
			$Llp = 0;
			foreach ($statBoxes as $facet=>$facetName) {
				$nstat = [];
				$lp = 0;
				if (!empty($stat->facets[$facet])) {
					foreach ($stat->facets[$facet] as $k=>$v) {
						$lp++;
						$index = $lp+$Llp;

						$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\""]);
						$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key, ['lookfor'=>$stat->name, 'type'=>'AllFields']);
						
						$tk = $k;
						if (in_array($facet, $authorFormat))
							$tk = $this->helper->authorFormat($k);
						
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
			
			/* 
			P242 -> value - obraz z lokalizacją !
			P625 -> value => stdClass Object
			P1332 -> value => stdClass Object
                                                (
                                                    [latitude] => 48.902156
                                                    [longitude] => 2.3844292
                                                )
			P998[0] -> value :rozdzielane / - ścieżka dostępu - kontytent/kraj/region									
			P998[1] -> value :rozdzielane / - ścieżka dostępu - world/kontytent/kraj/region									
			*/
			
			
			break;
	##################################################################################################################################################################################
	default :
			$stats = '';
			break;
	}


?>

<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->wiki->drawPicture($this->HOST, $photo, $title) ?>
				<?= $this->buffer->loadSoundFromWikimedia($this->wiki->getStrVal('P443')) ?>
			</div>
		</div>
		<div class="record-main-panel">
			
			<p><?= $this->wiki->get('descriptions') ?></p>
			
			
			<ul class="detailsview">
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Type of record'), 'value'=>$this->wiki->recType()]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country'),  'value'=>$this->wiki->getPropId('P17')]) ?>
				<?= $this->render('wiki/row.place.php', ['label'=>$this->transEsc('GPS'),  'value'=>$this->wiki->getCoordinates('P625'), 'title'=>$title]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Sex or Gender'),  'value'=>$this->wiki->getPropId('P21')]) ?>
				
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of Birth'),  'value'=>$this->wiki->getDate('P569')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of Birth'),  'value'=>$this->wiki->getPropId('P19'), 'time'=>$this->wiki->getClearDate('P569')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of Death'),  'value'=>$this->wiki->getDate('P570')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of Death'),  'value'=>$this->wiki->getPropId('P20'), 'time'=>$this->wiki->getClearDate('P570')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country of citizenship'),  'value'=>$this->wiki->getPropIds('P27')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Father'),  'value'=>$this->wiki->getPropId('P22')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Mother'),  'value'=>$this->wiki->getPropId('P25')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Spouse'),  'value'=>$this->wiki->getPropIds('P26')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Childrens'),  'value'=>$this->wiki->getPropIds('P40')]) ?>
				<?= $this->render('wiki/link.viaf.php', ['label'=>$this->transEsc('Viaf ID'),  'value'=>$this->wiki->getViafId()]) ?>
				
				
			</ul>
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a>
			</small>
			</div>
			
		</div>
		
	</div>
	<?= $stats ?>
	<?php 
	if (count($this->maps->getMapsPoints())>0) {
		echo "<script> 
		var IconMarkerBlue = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_fiolet.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
		var IconMarkerGreen = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_green.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
		var IconMarkerRed = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_red.svg',
				iconSize: [29, 33],
				iconAnchor: [15, 33],
				popupAnchor: [0, -33]
			});
		var IconSmallmarkerBlue = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_fiolet.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconSmallmarkerGreen = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_green.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconSmallmarkerRed = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/marker_red.svg',
				iconSize: [13, 20],
				iconAnchor: [8, 15],
				popupAnchor: [0, -15]
			});
		var IconPointBlue = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/po_blue.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		var IconPointGreen = L.icon({
				iconUrl: '".$this->HOST."themes/default/images/maps/po_green.png',
				iconSize: [7, 7],
				iconAnchor: [3, 3],
				popupAnchor: [0, -3]
			});
		</script>";
		echo '<div style="border:solid 1px lightgray;  margin-top:20px;">';
		echo $this->maps->drawWorldMap();
		echo "</div>";
		
		
		}
	
	?>
	<div id="drawPoints">

	</div> 
  </div>

  <div class="infopage">
	Stats: <?= $PRE ?>  
	Record: <pre><?= print_r($this->wiki->record) ?></pre>  
	Photos: <pre><?= print_r($photo) ?></pre>  
	Maps: <pre><?= print_r($this->maps->getMapsPoints()) ?></pre>  
  </div>
  
</div>
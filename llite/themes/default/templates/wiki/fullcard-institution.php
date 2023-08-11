<?php

$Llp = 0;
$statBoxes = $this->getIniParam('institutions','statBoxes');

#echo $this->helper->pre($stat);
#echo $this->helper->pre($compareStats);


$compareStatsStr = '<div class="compareStats">';
$compareStatsStr.= '
		<div class="compareRow">
			<div class="compareHeader"></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As author/co-author').'</h4></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As subject').'</h4></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As publisher').'</h4></div>
		</div>';	
foreach ($statBoxes as $facet=>$facetName) {
	$Llp++;
	$compareStatsStr.= '<div class="compareRow"><div class="rowHead"><span>'.$facetName.'</span></div>';
	foreach ($compareStats as $group=>$inGroupStats) {
		$nstat = [];
		$lp = 0;
		if (!empty($inGroupStats[$facet]))
			foreach ($inGroupStats[$facet] as $k=>$v) {
				$gresults = $inGroupStats[$facet];
				#echo $this->helper->pre($gresults);
				$lp++;
				$index = $lp+$Llp;

				$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\"", "$group:\"{$this->wiki->getIDint()}\""]);
				$link =$this->buildUri('search/results/1/r/'.$key );
				
				$label = $this->helper->convert($facet, $k);	
					
				$nstat[$index] = [
					'label' => $label,
					'label_o' => $k,
					'count' => $v,
					'link' 	=> $link,
					'color' => $this->helper->getGraphColor($lp),
					'index' => $index,
					];
				}
		$Llp = $Llp+$lp;
		@$c[$group] .= $str = $this->helper->drawStatBox($this->transEsc($facetName), $nstat);
		$compareStatsStr .= '<div class="dataCell">'.$str.'</div>';
		}
	$compareStatsStr.='</div>';
	}
$compareStatsStr .='</div>';
$compareStatsStr.= $this->transEsc('The charts show only the most popular options.');
$compareStatsStr.= $this->helper->pre($compareStats);


$PRE = '';
$stats = '';
if (!empty($stat)) {
	$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["persons_wiki_str_mv:\"{$this->wiki->getID()}\""]);
	$facetCode = $this->buffer->createFacetsCode($this->sql, ["persons_wiki_str_mv:\"{$this->wiki->getID()}\""]);
	
	$stats = '<h4>'.$this->transEsc('Summary for all the roles in which the viewed institution appears in the bibliography').'.</h4>';
	$stats .= '<div class="statBox">';
	$Llp = 100;
	foreach ($statBoxes as $facet=>$facetName) {
		$nstat = [];
		$lp = 0;
		if (!empty($stat->facets[$facet]))
			foreach ($stat->facets[$facet] as $k=>$v) {
				$lp++;
				$index = $lp+$Llp;

				# $key = $this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\" OR topic_person_str_mv:\"$stat->topic_person_str_mv\"", "$facet:\"$k\""]);
				$key = $this->buffer->createFacetsCode($this->sql, ["$facet:\"$k\"", "persons_wiki_str_mv:\"{$this->wiki->getIDint()}\""]);
				$link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$key );
				
				$nstat[$index] = [
					'label' => $this->helper->convert($facet,$k),
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
	} else {
	$stats = $this->transEsc('Institution not found in the bibliography').'.';
	}			

$this->addJS("results.maps.addInstitutionRelatations('".$this->wiki->record->id."')");

?>

<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/photo.php', ['photo'=>$photo, 'title'=>$title ]) ?>
				<?= $this->render('helpers/audio-player.php', ['audio' => $audio ]) ?>
			</div>
			
		</div>
		<div class="record-main-panel">
			<?= $this->render('persons/linkPanel.php', ['AP' => $activeInstitution] ) ?>
			<p><?= $this->wiki->get('descriptions') ?></p>
			
			
			<ul class="detailsview">
				
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of Inception'),  'value'=>$this->wiki->getDate('P571')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Headquarters location'),  'value'=>$this->wiki->getPropIds('P159'), 'time'=>$this->wiki->getClearDate('P571')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Countries'),  'value'=>$this->wiki->getPropIds('P27')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Other (old) names'),  'value'=>$this->wiki->getPropIds('P1365')]) ?> 
				
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Field of work'),  'value'=>$this->wiki->getPropIds('P101')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Commons category'),  'value'=>$this->wiki->getPropIds('P373')]) ?>
				<?= $this->render('wiki/link.out.php', ['label'=>$this->transEsc('Official website'),  'value'=>$this->wiki->getStrVal('P856')]) ?>
				<?= $this->render('wiki/link.isni.php', ['label'=>$this->transEsc('ISNI'),  'value'=>$this->wiki->getStrVal('P213')]) ?>
				<?= $this->render('wiki/link.viaf.php', ['label'=>$this->transEsc('Viaf'),  'value'=>$this->wiki->getViafId()]) ?>
				
				
			</ul>
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a><br/>
				<a href="https://www.entitree.com/en/affiliation/<?=$this->wiki->getID() ?>" class="text-right" target="_blank"><?= $this->transEsc('Explore with')?> EntiTree</a>
			</small>
			</div>
			
		</div>
		
	</div>
	
	<?php 
	
	$mapDraw = '<div style="border:solid 1px lightgray;  margin-top:20px;">';
	$mapDraw.= $this->maps->drawWorldMap();
	$mapDraw.= "</div>";
	$mapDraw.= '<div id="mapRelationsAjaxArea">'.$this->helper->loader2().'
			
			<input type="checkbox" checked id="map_checkbox_1" >
			<input type="checkbox" checked id="map_checkbox_2" >
			<input type="checkbox" checked id="map_checkbox_3" >

			</div>';

	
	$relatedPersonsStr = '';
	$elp = 0;
	foreach ($relatedPersons as $key=>$AP) {
		if (!empty($AP->wikiq)) {
			$wikiIDint = $AP->wikiq;
			$wikiId = 'Q'.$AP->wikiq;
			$wiki = new wikidata($wikiId);
			$activePerson = $wiki->getActivePersonValues();
			$activePerson->wiki = $wiki;
			$activePerson->wiki->setUserLang($this->user->lang['userLang']);

			$personPhoto = $this->buffer->loadWikiMediaUrl($activePerson->wiki->getStrVal('P18'));
			if (!empty($personPhoto))
				$photoBox = '<div class="pi-Image"><div class="img-circle" style="background-image: url(\''.$personPhoto.'\');"></div></div>';
				else 
				$photoBox = '
					<div class="pi-Image empty">
						<img src="'. $this->HOST .'themes/default/images/no_photo.svg" alt="no cover found" class="img img-responsive no-photo">
					</div>';

			$relatedPersonsStr.= '
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
								<a class="pi-bottom-link" href="'. $AP->bottomLink .'" title="'. $this->transEsc($AP->bottomStr) .'...">'.$AP->bottomStr.' <span class="badge">'.$AP->totalSharedRecords.'</span></a>
							</div>
						</div>
					</div>';
			
			} else {
			$TolnyNames[] = '<a href="'.$this->buildUrl('search/results/').'lookfor='.urlencode($AP->name).'&type=AllFields" title="'.$this->transEsc('look for').'">'.$AP->name.'</a> ';
			}
		}
	if (!empty($TolnyNames))
		$relatedPersonsStr.= '<br/>'.$this->transEsc('Also').':<br/>'.implode('<br/>',$TolnyNames);
	
	#$relatedPersonsStr .= $this->helper->pre($relatedPersons);





	$extraTabs['map'] = ['label' => $this->transEsc('Map'), 'content' => $mapDraw];
	$extraTabs['bstats'] = ['label' => $this->transEsc('Bibliographical statistics'), 'content' => $stats];
	$extraTabs['cStats'] = ['label' => $this->transEsc('Comparison of roles in bibliography'), 'content' => $compareStatsStr];
	$extraTabs['rPersons'] = ['label' => $this->transEsc('Related persons').' <span class="badge">'.$this->helper->numberFormat(count($relatedPersons)).'</span>', 'content' => $relatedPersonsStr];
	
	echo $this->helper->tabsCarousel( $extraTabs , 'map');
		
	?>
	<div id="drawPoints">

	</div> 
	
  </div>

<?php 
/*
  <div class="infopage">
	Stats: <?= $PRE ?>  
	Record: <pre><?= print_r($this->wiki->record) ?></pre>  
	Photos: <pre><?= print_r($photo) ?></pre>  
	Maps: <pre><?= print_r($this->maps->getMapsPoints()) ?></pre>  
  </div>
</div>

*/
?>


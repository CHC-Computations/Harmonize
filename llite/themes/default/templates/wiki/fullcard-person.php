<?php

$Llp = 0;
$statBoxes = $this->getIniParam('persons','statBoxes');

#echo $this->helper->pre($stat);
#echo $this->helper->pre($compareStats);


$compareStatsStr = '<div class="compareStats">';
$compareStatsStr.= '
		<div class="compareRow">
			<div class="compareHeader"></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As main author').'</h4></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As co-author').'</h4></div>
			<div class="compareHeader"><h4>'.$this->transEsc('As subject person').'</h4></div>
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


$PRE = '';
$stats = '';
if (!empty($stat)) {
	$as_author_facet = $this->buffer->createFacetsCode($this->sql, ["persons_wiki_str_mv:\"{$this->wiki->getID()}\""]);
	$facetCode = $this->buffer->createFacetsCode($this->sql, ["persons_wiki_str_mv:\"{$this->wiki->getID()}\""]);
	
	$stats = '<h4>'.$this->transEsc('Summary for all the roles in which the viewed person appears in the bibliography').'.</h4>';
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
	$stats = $this->transEsc('Person not found in the bibliography').'.';
	}			

$this->wiki->getPropIds('P19');
$this->addJS("results.maps.addPersonRelatations('".$this->wiki->record->id."')");

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
			<?= $this->render('persons/linkPanel.php', ['AP' => $activePerson] ) ?>
			<p><?= $this->wiki->get('descriptions') ?></p>
			
			
			<ul class="detailsview">
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Sex or Gender'),  'value'=>$this->wiki->getPropId('P21')]) ?>
				
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of Birth'),  'value'=>$this->wiki->getDate('P569')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of Birth'),  'value'=>$this->wiki->getPropIds('P19'), 'time'=>$this->wiki->getClearDate('P569')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of Death'),  'value'=>$this->wiki->getDate('P570')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of Death'),  'value'=>$this->wiki->getPropIds('P20'), 'time'=>$this->wiki->getClearDate('P570')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country of citizenship'),  'value'=>$this->wiki->getPropIds('P27')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Father'),  'value'=>$this->wiki->getPropIds('P22')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Mother'),  'value'=>$this->wiki->getPropIds('P25')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Spouse'),  'value'=>$this->wiki->getPropIds('P26')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Childrens'),  'value'=>$this->wiki->getPropIds('P40')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Occupation'),  'value'=>$this->wiki->getPropIds('P106')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Era'),  'value'=>$this->wiki->getPropIds('P135')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Genres'),  'value'=>$this->wiki->getPropIds('P136')]) ?>
				<?= $this->render('wiki/link.viaf.php', ['label'=>$this->transEsc('Viaf ID'),  'value'=>$this->wiki->getViafId()]) ?>
				
				
			</ul>
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a><br/>
				<a href="https://www.entitree.com/en/family_tree/<?=$this->wiki->getID() ?>" class="text-right" target="_blank"><?= $this->transEsc('Explore family tree with')?> EntiTree</a>
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

	$llp = $rlp = 0;
	
	$lp = 0;
	$theyWroteAboutStr = $theyWroteAboutStrOnlyNames = '';
	if (!empty($theyWroteAbout) && is_array($theyWroteAbout))
		foreach ($theyWroteAbout as $AP) {
			if (!empty($AP->wikiq)) {
				$lp++;
				if ($lp<=4) {
					$rlp++;
					$this->addJS("page.post('{$AP->field}', 'wiki/person/box/{$AP->wikiq}?', ".json_encode($AP).");");
					$theyWroteAboutStr.= '<div class="person-info '.$AP->field.'" id="'.$AP->field.'"></div>';
					} else {
					$theyWroteAboutStrOnlyNames.= '<div class="person-info-single-line"><a class="wikiLink" href="'.$this->buildUri('wiki/record/Q'.$AP->wikiq).'" title="'.$this->transEsc('Go to card of').'..."><b>'.$AP->name.'</b></a> '.$AP->date.' <a href="'.$AP->bottomLink.'" title="'.$AP->bottomStr.'" class="bibLink"><i class="ph-pen-nib-bold"></i> '.$AP->as_author.'</a></div>';	
					}
				} else 
				$theyWroteAboutStrOnlyNames.= '<div class="person-info-single-line"><b>'.$AP->name.'</b> '.$AP->date.' <a href="'.$AP->bottomLink.'" title="'.$AP->bottomStr.'" class="bibLink"><i class="ph-pen-nib-bold"></i> '.$AP->as_author.'</a></div>';	
			}
	$theyWroteAboutStr .= $theyWroteAboutStrOnlyNames;	
	
	$lp = 0;
	$heWroteAboutStr = $heWroteAboutStrOnlyNames = '';
	if (!empty($heWroteAbout) && is_array($heWroteAbout))
		foreach ($heWroteAbout as $AP) {
			if (!empty($AP->wikiq)) {
				$lp++;
				if ($lp<=4) {
					$llp++;
					$this->addJS("page.post('{$AP->field}R', 'wiki/person/box/{$AP->wikiq}?', ".json_encode($AP).");");
					$heWroteAboutStr.= '<div class="person-info '.$AP->field.'" id="'.$AP->field.'R">'.$AP->name.'</div>';
					} else {
					$heWroteAboutStrOnlyNames.= '<div class="person-info-single-line"><a class="wikiLink" href="'.$this->buildUri('wiki/record/Q'.$AP->wikiq).'" title="'.$this->transEsc('Go to card of').'..."><b>'.$AP->name.'</b></a> '.$AP->date.' <a href="'.$AP->bottomLink.'" title="'.$AP->bottomStr.'" class="bibLink"><i class="ph-user-focus-bold"></i> '.$AP->as_topic.'</a></div>';	
					}
				} else 
				$heWroteAboutStrOnlyNames.= '<div class="person-info-single-line"><b>'.$AP->name.'</b> '.$AP->date.' <a href="'.$AP->bottomLink.'" title="'.$AP->bottomStr.'" class="bibLink"><i class="ph-user-focus-bold"></i> '.$AP->as_topic.'</a></div>';	
			}
	$heWroteAboutStr .= $heWroteAboutStrOnlyNames;	
	
	
	$extraTabs['map'] = ['label' => $this->transEsc('Map'), 'content' => $mapDraw];
	$extraTabs['bstats'] = ['label' => $this->transEsc('Bibliographical statistics'), 'content' => $stats];
	$extraTabs['cStats'] = ['label' => $this->transEsc('Comparison of roles in bibliography'), 'content' => $compareStatsStr];
	
	
	
	if (($llp>0)or($rlp>0)) {
		$topMargin = 'margin-top: 264px; ';
		if (($llp<4)&($rlp<4)) $topMargin = 'margin-top: 83px; ';
		$relatedPersons = '
			<div class="relGraph">
				<div class="relGraph-cell">
				';
		
		// {{subject_person}} was the subject of publications in which the main author was:
		if (!empty($theyWroteAboutStr)) 
			$relatedPersons .='
					<a href="'.$this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["subject_person_str_mv:\"{$activePerson->solr_str}\""])).'" title="'.$this->transEsc('Go to bibliographic records').'"><b>'.$this->wiki->get('labels').' '.$this->transEsc('was the subject of publications in which the main author was').':</b></a>
					<br/>'.$theyWroteAboutStr;
		if (empty($photo)) 
			$centerImage = '<div class="relGraph-centerImage-empty" style="'.$topMargin.'"><span>'.$this->wiki->get('labels').'</span></div>';
			else 
			$centerImage = '<div class="relGraph-centerImage" style="'.$topMargin.'background-image: url('.$photo.')"><span>'.$this->wiki->get('labels').'</span></div>';
		$relatedPersons .='
				</div>
				<div class="relGraph-cell-middle" style="background-image: url('.$this->HOST.'/themes/default/images/extras/relationGraph'.$rlp.'-'.$llp.'.svg);">
					'.$centerImage.'
				</div>
				<div class="relGraph-cell">
				';
				
		// They were the subject of publications in which the main author was {{main_author}}
		if (!empty($heWroteAboutStr)) 				
			$relatedPersons .='<a href="'.$this->buildUri('search/results/1/r/'.$this->buffer->createFacetsCode($this->sql, ["author_facet:\"{$activePerson->solr_str}\""])).'" title="'.$this->transEsc('Go to bibliographic records').'"><b>'.$this->transEsc('They were the subject of publications in which the main author was').' '.$this->wiki->get('labels').'</b></a>
					<br/>'.$heWroteAboutStr;
		$relatedPersons .=' 
				</div>
			</div>';	
		$badge = ' <span class="badge">'.count($theyWroteAbout).' + '.count($heWroteAbout).'</span>';
		$extraTabs['rpersons'] = ['label' => $this->transEsc('Related persons').$badge, 'content' => $relatedPersons];
		}
	#$extraTabs['graph'] = ['label' => $this->transEsc('Some strange graph'), 'content' => 'Może powinniśmy tego użyć do "related persons"?<br/><div id="neo4jd3" style="height: 600px; overflow: hidden;"></div>	<small><a href="https://github.com/eisman/neo4jd3">neo4jd3 on GitHub</a></small>'];
	
	$relatedCorporatesStr = '';
	if (!empty($relatedCorporates) && is_array($relatedCorporates)) {
		foreach ($relatedCorporates as $AC) {
			# $relatedCorporatesStr.= $this->helper->pre($AP);
			
			$this->addJS("page.post('relatedCorporates{$AC->wikiq}', 'wiki/corporate/box/{$AC->wikiq}', ".json_encode($AC).");");
			$relatedCorporatesStr.= '<div class="person-info" id="relatedCorporates'.$AC->wikiq.'">'.$AC->wiki.'</div>';
			}
		$extraTabs['rCorpo'] = ['label' => $this->transEsc('Related corporations').' <span class="badge">'.count($relatedCorporates).'</span>', 'content' => $relatedCorporatesStr];
		}
	
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


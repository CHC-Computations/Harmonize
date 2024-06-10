<?php

$Llp = 0;


?>

<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->coreRecord->getStr('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		<?= $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $this->coreRecord->linkPanel()] ) ?>
		<p><?= $this->wiki->get('descriptions') ?></p>
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wikiphoto.php') ?>
				<?= $this->render('helpers/wiki.signature.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
			
		</div>
		<div class="record-main-panel">
			
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

	
	

	$extraTabs['map'] = ['label' => $this->transEsc('Map'), 'content' => $mapDraw];
	# $extraTabs['bstats'] = ['label' => $this->transEsc('Bibliographical statistics'), 'content' => $stats];
	# $extraTabs['cStats'] = ['label' => $this->transEsc('Comparison of roles in bibliography'), 'content' => $compareStatsStr];
	# $extraTabs['rPersons'] = ['label' => $this->transEsc('Related persons').' <span class="badge">'.$this->helper->numberFormat(count($relatedPersons)).'</span>', 'content' => $relatedPersonsStr];
	
	echo $this->helper->tabsCarousel( $extraTabs , 'map');
		
	?>
	<div id="drawPoints">

	</div> 
	
  </div>

coreRecord<?= $this->helper->pre($this->coreRecord) ?>
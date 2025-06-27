<?php #oldLinkPanel: $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $this->coreRecord->linkPanel()] ) ?>
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->getPersonYearsRange(' - ') ?></small></h1>
	</div>
	<div class="person-record">
		<div id="wikimediaDescription">
		<?php
			if (!empty($this->wiki->record->wikipediaDescription))
				echo $this->wiki->record->wikipediaDescription;
				else 
				echo $this->wiki->get('descriptions');
		?>
		</div>
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wiki.photo.php') ?>
				<?= $this->render('helpers/wiki.signature.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
			
		</div>
		<div class="record-main-panel">
			
			<ul class="detailsview">
				<?php /* $this->render('wiki/row.php', ['label'=>$this->transEsc('Aliases'),  'value'=>$this->wiki->get('aliases')]) */?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Pseudonym'),  'value'=>$this->wiki->getStrVal('P742')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Nickname'),  'value'=>$this->wiki->getTextVals('P1449')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Sex or gender'),  'value'=>$this->wiki->getPropId('P21')]) ?>
				
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of birth'),  'value'=>$this->wiki->getDate('P569')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of birth'),  'value'=>$this->wiki->getPropIds('P19'), 'time'=>$this->wiki->getClearDate('P569')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Date of death'),  'value'=>$this->wiki->getDate('P570')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Place of death'),  'value'=>$this->wiki->getPropIds('P20'), 'time'=>$this->wiki->getClearDate('P570')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country of citizenship'),  'value'=>$this->wiki->getPropIds('P27')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Father'),  'value'=>$this->wiki->getPropIds('P22')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Mother'),  'value'=>$this->wiki->getPropIds('P25')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Spouse'),  'value'=>$this->wiki->getPropIds('P26')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Children'),'value'=>$this->wiki->getPropIds('P40')]) ?>
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Occupation'),  'value'=>$this->wiki->getPropIds('P106')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Field of work'),  'value'=>$this->wiki->getPropIds('P101')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Genres'),  'value'=>$this->wiki->getPropIds('P136')]) ?>
				<?= $this->render('wiki/link.viaf.php', ['label'=>$this->transEsc('VIAF'),  'value'=>$this->wiki->getViafId()]) ?>
				
				
			</ul>
			
		</div>
		
	</div>


	





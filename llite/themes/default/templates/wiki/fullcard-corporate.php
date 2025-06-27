
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
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
		</div>
	</div>
	
	
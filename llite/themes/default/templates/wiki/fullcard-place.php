
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
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
		</div>
		<div class="record-main-panel">
			
			
			<ul class="detailsview">
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Country'),  'value'=>$this->wiki->getHistoricalCountry(date("Y"))]) ?>
				<?= $this->render('wiki/row.place.php', ['label'=>$this->transEsc('Coordinates').' <small>(WGS-84)</small>',  'value'=>$this->wiki->getCoordinates('P625'), 'title'=>$title]) ?>
				<?= $this->render('wiki/time.line.countries.php', ['label'=>$this->transEsc('Country'),  'value'=>$this->wiki->getHistoricalCountries('P17')]) ?>
				<?= $this->render('wiki/time.line.php', ['label'=>$this->transEsc('Historical names'),  'value'=>$this->wiki->getHistoricalNames()]) ?>
			</ul>
		</div>
	</div>
	
	

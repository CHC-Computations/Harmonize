
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->wiki->get('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
		
		<?= $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $this->coreRecord->linkPanel()] ) ?>
		<p><?= $this->wiki->get('descriptions') ?></p>
		
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wiki.photo.php') ?>
				<?= $this->render('helpers/wiki.signature.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
			
		</div>
		<div class="record-main-panel">
			
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
				<?= $this->render('wiki/link.viaf.php', ['label'=>$this->transEsc('VIAF'),  'value'=>$this->wiki->getViafId()]) ?>
				
				
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


	





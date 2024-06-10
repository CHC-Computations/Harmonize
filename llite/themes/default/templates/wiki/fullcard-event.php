
    <div class="infopage-header">
	   <h1 property="name"><?= $title = $this->coreRecord->getStr('labels') ?> <small><?= $this->wiki->get('aliases') ?></small></h1>
	</div>
	<div class="person-record">
	
		<?= $this->render('wikiResults/resultBoxes/linkPanel.php', ['AP' => $this->coreRecord->linkPanel()] ) ?>
		<p><?= $this->wiki->get('descriptions') ?? '&nbsp;'  ?></p>
		
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('helpers/wiki.photo.php') ?>
				<?= $this->render('helpers/wiki.signature.php') ?>
				<?= $this->render('helpers/wiki.audio.php') ?>
			</div>
			
		</div>
		<div class="record-main-panel">
			<ul class="detailsview">
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Type of'),  'value'=>$this->wiki->getPropIds('P31')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('First edition'),  'value'=>$this->wiki->getDate('P571')]) ?>
				<?= $this->render('wiki/row.php', ['label'=>$this->transEsc('Last edition'),  'value'=>$this->wiki->getDate('P582')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Founded by'),  'value'=>$this->wiki->getPropIds('P112')]) ?>
				<?= $this->render('wiki/link.place.php', ['label'=>$this->transEsc('Headquarters location'),  'value'=>$this->wiki->getPropIds('P159'), 'time'=>$this->wiki->getClearDate('P571')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Location'),  'value'=>$this->wiki->getPropIds('P276')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Countries'),  'value'=>$this->wiki->getPropIds('P17')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Other (old) names'),  'value'=>$this->wiki->getPropIds('P1365')]) ?> 
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Has part(s)'),  'value'=>$this->wiki->getPropIds('P527')]) ?> 
				
				
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Field of work'),  'value'=>$this->wiki->getPropIds('P101')]) ?>
				<?= $this->render('wiki/link.php', ['label'=>$this->transEsc('Commons category'),  'value'=>$this->wiki->getPropIds('P373')]) ?>
				<?= $this->render('wiki/link.out.php', ['label'=>$this->transEsc('Official website'),  'value'=>$this->wiki->getStrVal('P856')]) ?>
				
			</ul>
			<div class="text-right">
			<small>
				<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of information')?> Wikidata</a><br/>
				<a href="<?=$this->wiki->getSiteLink() ?>" class="text-right"><?= $this->transEsc('More information on')?> Wikipedia</a><br/>
				<a href="https://www.entitree.com/en/employer/<?=$this->wiki->getID() ?>" class="text-right" target="_blank"><?= $this->transEsc('Explore with')?> EntiTree</a>
			</small>
			</div>
			
		</div>
		
	</div>
	
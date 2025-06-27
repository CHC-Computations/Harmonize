
<div class="text-right" style="padding-right:20px"  id="ums">
	<small>
		<a href="https://www.wikidata.org/wiki/<?=$this->wiki->getID() ?>" class="text-right"><?= $this->transEsc('Source of media and information')?> Wikidata</a><br/>
		<a href="https://www.entitree.com/en/family_tree/<?=$this->wiki->getID() ?>" class="text-right" target="_blank"><?= $this->transEsc('Explore family tree with')?> EntiTree</a>
	</small>
</div>


<div class="person-record">
	<div class="record-left-panel">
	</div>
	<div class="record-main-panel">
			<ul class="detailsview">
				<?= $this->render('wiki/link.elb.php', ['label'=>$this->wiki->get('labels').' '.$this->transEsc('in bibliographic records'),  'facets'=>$facets]) ?>
			</ul>
	</div>
</div>



	 
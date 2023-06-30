

<i class="glyphicon glyphicon-info-sign"></i>
<div class="cloud-info">
	<h4><?= $this->person->getName() ?></h4>
	
	<div class="ci-Body">
		<div class="ci-Image">
			<?= $this->person->getImage() ?>
		</div>
		<div class="ci-Desc">
			
			<?= $this->person->getLinkPanel() ?>
			<?= $this->person->getDescription() ?>
			<div class="bottom-links">
				<a href='<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/', ['lookfor'=>'viaf/'.$this->person->record->viafid, 'type'=>'AllFields'] ) ?>' 
					class="stat-row"><b><?= $stat->numFound ?></b> <?=$this->transEsc('records in LiBRI')?>:</a>
				<a href='<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["author_facet_s:\"$stat->author_facet_s\""]) ); ?>' 
					class="stat-row"><?=$this->transEsc('As an author')?>: <b><?= $stat->as_author ?></b> (<?= number_format($stat->as_author_pr,1,'.','') ?>%)</a>
				<a href='<?= $link =$this->buildUri('search/results/1/'.$this->getUserParam('sort').'/'.$this->buffer->createFacetsCode($this->sql, ["topic_person_str_mv:\"$stat->topic_person_str_mv\""]) ); ?>' 
					class="stat-row"><?=$this->transEsc('As topic person')?>: <b><?= $stat->as_topic_person ?></b> (<?= number_format($stat->as_topic_person_pr,1,'.','') ?>%)</a>
			</div>
	
		
		</div>
	</div>
</div>
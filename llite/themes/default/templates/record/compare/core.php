	<h2 class="title"><?= $this->record->getTitle() ?></h2>
	<div class="record">
		<div class="record-left-panel">
			<div class="thumbnail">
				<?= $this->render('record/cover.php', ['rec' => $this->record, 'singleRecView'=>true]) ?>
			</div>
			<div id="stickyArea<?=$box_id?>"><?= $this->bookcart->resultStickyNote($this->record->getId()) ?></div>
		</div>
		<div class="record-main-panel">
			<?= $this->record->getRETpic() ?>
			<ul class="detailsview">
				<?= $this->render('record/fields/only-text.php', [
							'label'=>$this->transEsc('Statement of Responsibility'),  	
							'value'=>$this->record->get('StatmentOfResp')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Main author'),  					
							'value'=>$this->record->get('persons', 'mainAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Corporate as main author'),  	
							'value'=>$this->record->get('corporates', 'mainAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Other authors'),  				
							'value'=>$this->record->get('persons', 'coAuthor')
							]) ?>
				<?= $this->render('record/fields/authors-link.php', [
							'label'=>$this->transEsc('Corporate Author'),  			
							'value'=>$this->record->get('corporates', 'coAuthor')
							]) ?>
				
				<?= $this->render('record/fields/magazines-link.php', 	[
							'label'=>$this->transEsc('Source magazine'),  			
							'value'=>$this->record->get('magazines', 'sourceMagazine')
							]) ?>
				<?= $this->render('record/fields/magazines-link.php', 	[
							'label'=>$this->transEsc('Source document'),  			
							'value'=>$this->record->get('sourceDocument')
							]) ?>
				
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Format'), 				
							'facetField'=>'format_major', 		
							'translated'=>true,
							'value'=>$this->record->get('majorFormat')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Publication language'),  
							'facetField'=>'language', 
							'translated'=>true,
							'value'=>$this->record->get('language','publication')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Original language'),  	
							'facetField'=>'language_o_str_mv', 	
							'translated'=>true,
							'value'=>$this->record->get('language','original')
							]) ?>
				
				<?= $this->render('record/fields/places-link.php', 	[
							'label'=>$this->transEsc('Publication place'),  	
							'facetField'=>'geographicpublication_str_mv', 	
							'translated'=>true,
							'value'=>$this->record->get('places','publication')
							]) ?>
				
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('In'),  			
							'facetField'=>'magazines_str_mv', 	
							'value'=>$this->record->get('publishedIn')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Edition'),  		
							'facetField'=>'edition', 	
							'linkValue'=>$this->record->get('edition')->no ?? $this->record->get('edition'),
							'value'=>$this->record->get('edition')->str ?? $this->record->get('edition')
							]) ?>
				<?= $this->render('record/fields/refered-work.php', 	[
							'label'=>$this->transEsc('Referred work'), 
							'value'=>$this->record->get('referedWork')
							]) ?>
				<?= $this->render('record/fields/biblio-link.php', 	[
							'label'=>$this->transEsc('Seria'),  		
							'facetField'=>'series', 
							'value'=>$this->record->get('seria')
							]) ?>
				
				<?= $this->render('record/fields/formGenre-link.php', 	[
							'label'=>$this->transEsc('Form / Genre'),  
							'value'=>$this->record->get('subject','formGenre')
							]) ?>
				<?= $this->render('record/fields/facet-link.php', 		[
							'label'=>$this->transEsc('Universal Decimal Classification'),  
							'facetField'=>'udccode_str_mv', 
							'value'=>$this->record->get('subject','UDC', 0)]) 
							?>
				<?= $this->render('record/fields/facet-link.php', 		[
							'label'=>$this->transEsc('Literature by nationality'),  
							'facetField'=>'subject_nation_str_mv', 
							'value'=>$this->record->get('subject','elb', 'nations')]) 
							?>
				<?= $this->render('record/fields/facet-link.php', 		[
							'label'=>$this->transEsc('Literature by genre'),  
							'facetField'=>'subject_genre_str_mv', 
							'value'=>$this->record->get('subject','elb', 'genre')]) 
							?>
				<?= $this->render('record/fields/authors-link.php', 	[
							'label'=>$this->transEsc('Subject persons'), 
							'value'=>$this->record->get('persons', 'subjectPerson')
							]) ?>
				<?= $this->render('record/fields/subjects-link.php', 	[
							'label'=>$this->transEsc('Subjects'), 	
							'facetField'=>'subjects_str_mv', 
							'value'=>$this->record->get('subject','strings')
							]) ?>
				<?= $this->render('record/fields/only-text.php', [
							'label'=>$this->transEsc('Annotation'),  	
							'value'=>$this->record->get('description')
							]) ?>

			</ul>
			
			
		</div>
		
	</div>

<div class="text-right">
	<a href="<?=  $this->basicUri('results/biblio/record/'.$this->record->getId().'.html') ?>" class="btn btn-primary"><i class="ph ph-book-open"></i> <?= $this->transEsc('More about') ?>...</a>
</div>



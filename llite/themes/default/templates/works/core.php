<?php 
$current_view = 'table';
$publicationsTotal = count($results);
if (!empty($workRecord->isbn))
	$this->addJS("page.post('coversSlider', 'results/covers.slider/', ".json_encode($workRecord->isbn)." )");


			
?>
	
<div class="graybox">
  <div class="infopage">
    <div class="infopage-header">
	   <h1 property="title"><?= $workRecord->title ?></h1>
	</div>
	<div class="person-record">	
		<div class="record-left-panel">
			<div class="thumbnail" >
				<?php if (!empty($workRecord->isbn)): ?>
				<div id="coversSlider"></div>
				<?php else: ?>
				<div class="text-center">no isbn = no cover<br/><i style="font-size:4em;" class="ph ph-smiley-sad"></i></div>
				<?php endif ?>
				<div class="slider-vertical" id="work_covers"></div>
			</div>	
			<?php 
			$publicationsListLink = '<a href="'.$this->buildUri('results', ['core'=>'biblio', 'facetsCode'=>$this->buffer->createFacetsCode(["workkey_str:\"{$workRecord->id}\""])] ) .'">'.$this->transEsc('Go to publications list') .'</a>';
			echo '<h4>'.$publicationsListLink.'</h4>';
			?>
		</div>	
		<div class="record-main-panel">
			
				<?php 
				echo '<div class="detailsview">';
				echo $this->render('works/fields/authors-link.php', [
									'label'=>$this->transEsc('Main author'),  					
									'value'=>$this->record->get('persons', 'mainAuthor')
									]); 
				echo $this->render('works/fields/list.php', [
									'label'=>$this->transEsc('Year of creation'),  	
									'value'=>$workRecord->yearOfCreation ?? null,
									'publicationsTotal' => $publicationsTotal
									]);		
				echo $this->render('works/fields/list.php', [
									'label'=>$this->transEsc('Original language'),  	
									'value'=>$workRecord->originalLanguages,
									'publicationsTotal' => $publicationsTotal
									]);		
				echo $this->render('works/fields/list.php', [
									'label'=>$this->transEsc('Subjects'),  	
									'value'=>$workRecord->subjects,
									'publicationsTotal' => $publicationsTotal
									]);		
			
				echo $this->render('works/fields/list-titles.php', [
									'label'=>$this->transEsc('Titles in publication languages'),  	
									'value'=>$workRecord->titles,
									'publicationsTotal' => $publicationsTotal
									]);		
			
				echo '</div><br/>';
				
				if (!empty($workRecord->coAuthors)) {
					echo '<h4>'.$this->transEsc('co-Authors').'</h4>';
					echo '<div class="detailsview">';
					foreach ($workRecord->coAuthors as $role=>$persons)
						echo $this->render('works/fields/authors-link.php', [
									'label'=>$this->transEsc($this->record->creativeRolesSynonyms($role)),  					
									'value'=>$persons,
									'hideRole'=>$role,
									'workRecord'=>$workRecord
									]);
					echo '</div><br/>';
					}
					
				echo '<div class="detailsview">';					
				echo $this->render('works/fields/list.php', [
									'label'=>$this->transEsc('Publication languages'),  	
									'value'=>$workRecord->publicationLanguages,
									'publicationsTotal' => $publicationsTotal
									]);		
				echo $this->render('works/fields/time.line.php', [
									'label'=>$this->transEsc('Publication years'),  	
									'value'=>$workRecord->publicationYear,
									'publicationsTotal' => $publicationsTotal
									]);		
				
				
				if (!empty($workRecord->publicationPlace)) {
					echo $this->render('works/fields/publication-places.php', [
									'label'=>$this->transEsc('Publication places'),  					
									'value'=>$workRecord->publicationPlace,
									'publicationsTotal' => $publicationsTotal,
									'workRecord'=>$workRecord
									]);
					echo '</div><br/>';
					}
				
				echo '</div>';
				?>
		
		</div>
		<p class="text-right"><?= $publicationsListLink ?></p>
	</div>
	<div class="results hidden">
	  
	<?php 
		#print_r($workRecord);
		
		
		$listRelated = $this->configJson->settings->homePage->coresNames;
		unset($listRelated->$currentCore);
		foreach ($listRelated as $key=>$related) {
			$extraTabs[$key] = [
						'label'=> $this->transEsc($related->name).' <span class="badge">'.count($this->buffer->getBottomList($key)).'</span>', 
						'content'=> '<div class="tabPanelWhiteCart" id="related_'.$key.'">'.$this->helper->loader2().'</div>'
						];
			$this->addJS('page.post("related_'.$key.'", "results/related/'.$key.'/", '.json_encode($this->buffer->getBottomList($key)).');');
			}
		echo '<h4>'.$this->transEsc('Related to the above results').'<h4>';
		echo $this->helper->tabsCarousel( $extraTabs , current(array_keys((array)$listRelated)) );
		echo '<br/><br/>';
		
		
		?>
	
	
	</div>
  </div>
</div>



<script>
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});
</script>
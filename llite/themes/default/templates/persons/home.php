<div class='main'>
	<div class='sidebar'>
		<?= $this->render('persons/facets-sidebar.php', ['labels'=>$facets]) ?>
		<?= $this->helper->pre($facets) ?>
		
	</div>
	<div class='mainbody' id='content'>
		<?= $this->render('persons/summary.php', ['resultsCount'=>$totalResults]) ?>
		<div class="results">
		  <?php if (!empty($results) && is_array($results)): ?>
		  <div id="resultsBox" class="results-list">
				<?php 
				foreach ($results as $result) {
					#echo $this->helper->pre($result);
					
					$result->wiki = new wikiLibri($this->user->lang['userLang'], $result);
					$result->activePerson = $result->wiki->getActivePersonValues();
					
					$photo = $this->buffer->loadWikiMediaUrl($result->wiki->getStrVal('P18'));

					echo '<div class="person-info" id="person_'.$result->id.'">';
					echo $this->render('persons/results/list-wiki-solr.php',['activePerson'=>$result->activePerson, 'photo'=>$photo, 'result'=>$result]);
					echo '</div>';
					
					
					}
				?>
			  <br/>
		  </div>
		  <?= $this->render('persons/paggination.php') ?>
		  <?php else: ?>
		  <h1><?=$this->transEsc('No results')?></h1>
		  <?php endif; ?>
		</div>
		
		
	</div>
</div>


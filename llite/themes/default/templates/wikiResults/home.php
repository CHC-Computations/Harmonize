<div class='main'>
	<div class='sidebar'>
		<?= $this->render('wikiResults/facets-sidebar.php', ['facets'=>$facets, 'currentCore'=>$currentCore]) ?>
	</div>
	<div class='mainbody' id='content'>
		<?= $this->render('search/summary.php', ['resultsCount'=>$totalResults, 'currentCore'=>$currentCore]) ?>
		<div class="results">
		  <?php if (!empty($results) && is_array($results)): ?>
		  <div id="resultsBox" class="results-list <?= $currentCore ?>-list <?= $this->getUserParam($currentCore.':view') ?>-list">
				<?php 
				foreach ($results as $result) {
					#echo $this->helper->pre($result);
					
					$resultObj = new wikiLibri($this->user->lang['userLang'], $result);
					
					echo '<div class="result-box '.$currentCore.'-result " id="'.$currentCore.'_'.$result->wikiq.'">';
					echo $this->render('wikiResults/resultBoxes/'.$this->getUserParam($currentCore.':view').'.php',['result'=>$resultObj]);
					echo '</div>';
					
					
					}
				?>
			  <br/>
		  </div>
		  <?= $this->render('search/paggination.php', ['currentCore'=>$currentCore]) ?>
		  <?php else: ?>
		  <h1><?=$this->transEsc('No results')?></h1>
		  <?php endif; ?>
		</div>
		
	</div>
</div>


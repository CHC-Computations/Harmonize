<?php 


#echo "<pre>".print_R($this,1)."</pre>";
$current_view = $this->getUserParam($currentCore.':view') ?? 'default-box';

#$this->JS[] = "results.saveList();";
#echo implode(' ', $this->solr->alert);
?>

<?php if (!empty($results->exception)): ?>
	<div class="main">
		<div class="cms_box">
			<div class="container" id="content" >

				<h2><?= $results->exception ?></h2>
				<a href="<?= $this->buildUrl() ?>"><?= $this->transEsc('Go back to the first page') ?></a>
			</div>
		</div>
	</div>
<?php else: ?>

	<?php 

	if ($this->solr->totalResults()>0):
	?>
		<div class='main'>
			<div class='sidebar'>
				<?= $this->render('search/facet-sidebar.php', ['currentCore'=>$currentCore]) ?>
				
			</div>
			<div class='mainbody' id='content'>
				<?= $this->render('search/summary.php', ['currentCore'=>$currentCore]) ?>
				<div class="results">
				<?= $this->render('search/results/bulk-actions.php') ?>
				
				  <div class="results-<?= $current_view ?>">
					<?php 
					
					foreach ($results as $result) {
						
						# $marcJson = $this->buffer->getJsonRecord($result->id, $result->fullrecord);
						# $marcJson = $this->convert->mrk2json($result->fullrecord);
						# $this->addClass('marc', new marc21($marcJson, $result)); 
						# $this->marc->setBasicUri($this->basicUri());
						# $this->marc->getCoreFields();
						# $auth = $this->marc->getMainAuthor();
						# $auth = $this->marc->getMainAuthorLink();
						
						$this->addClass('record', new bibliographicRecord($result, $this->convert->mrk2json($result->fullrecord)));
						
						echo $this->render('search/results/'.$current_view.'.php', ['result'=>$result, 'record'=>json_decode($result->relations)] );
						
						$this->buffer->addToBottomSummary(json_decode($result->relations));
						}
					?>
				  </div>	
				<?= $this->render('search/results/bulk-actions.php') ?>
				<?= $this->render('search/paggination.php', ['currentCore'=>$currentCore]) ?>
				
				<?php 
				
					$listRelated = $this->configJson->settings->homePage->coresNames;
					unset($listRelated->$currentCore);
					foreach ($listRelated as $key=>$related) {
						$extraTabs[$key] = [
									'label'=> $this->transEsc($related->name).' <span class="badge">'.count($this->buffer->getBottomList($key)).'</span>', 
									'content'=> '<div class="tabPanelWhiteCart" id="related_'.$key.'">'.$this->helper->loader2().'</div>'
									];
						$this->addJS('page.post("related_'.$key.'", "results/related/'.$key.'/", '.json_encode($this->buffer->getBottomList($key)).');');
						}
					echo '<h4>'.$this->transEsc('Related to the resutations above').'<h4>';
					echo $this->helper->tabsCarousel( $extraTabs , current(array_keys((array)$listRelated)) );
					echo '<br/><br/>';
					
					
					
					?>
				
				<div id="sessionBox"></div>
				</div>
			</div>
		</div>


	<?php else: ?>
				
		<div class="main">
			<div class='sidebar'>
				<?= $this->render('search/facet-sidebar.php', ['currentCore'=>$currentCore]) ?>
				
			</div>
			<div class='mainbody' id='content'>
			<?= $this->render('search/summary.php', ['currentCore'=>$currentCore]) ?>
			<?php if (!empty($this->solr->error)): ?>
				<h1><?= $this->transEsc($this->solr->error) ?></h1>
					
				<?= $this->transEsc("We are working on the solution") ?>.
				<?= $this->transEsc("Please, try again later") ?>.
			<?php else: ?>
				<h1><?= $this->transEsc('No results')?></h1>
				<?= $this->transEsc("The query returned an empty result list") ?>.</br>
				<?= implode('<br>',$this->solr->alert) ?>
			<?php endif; ?>
			</div>
		</div>
		

		
	<?php endif; ?>
<?php endif; ?>
	
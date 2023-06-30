<?php 


#echo "<pre>".print_R($this,1)."</pre>";
$current_view = $this->getUserParam('view');
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
				<?= $this->render('search/facet-sidebar.php') ?>
				
			</div>
			<div class='mainbody' id='content'>
				<?= $this->render('search/summary.php') ?>
				<div class="results">
				<?= $this->render('search/results/bulk-actions.php') ?>
				
				  <div class="results-<?= $current_view ?>">
					<?php 
					
					foreach ($results as $result) {
						
						#$marcJson = $this->buffer->getJsonRecord($result->id, $result->fullrecord);
						$marcJson = $this->convert->mrk2json($result->fullrecord);
						$this->addClass('marc', new marc21($marcJson, $result)); 
						$this->marc->setBasicUri($this->basicUri());
						#$this->marc->getCoreFields();
						$auth = $this->marc->getMainAuthor();
						# $auth = $this->marc->getMainAuthorLink();

						echo $this->render('search/results/'.$current_view.'.php', ['result'=>$result, 'auth'=>$auth] );
						#echo "<pre>".print_R($result,1).'</pre>';
						$this->buffer->addToBottomSummary($result);
						}
					?>
				  </div>	
				<?= $this->render('search/results/bulk-actions.php') ?>
				<?= $this->render('search/paggination.php') ?>
				
				<?php 
					# echo $this->render('search/bottomLists/personsBoxes.php');
					# echo "<hr/>work in progress";
					$list = $this->buffer->getBottomList('persons');
					if (!empty($list)) {
						echo '<div id="relatedPersons">'.$this->helper->loader2().'</div>';
						if (!empty($this->buffer->usedFacetsValues)) 
							foreach ($list as $key=>$value) {
								$skey = substr($key,1);
								if (in_array($skey, $this->buffer->usedFacetsValues) or in_array($value, $this->buffer->usedFacetsValues)) {
									if (substr($key,0,1) == '.')
										$list['!'.$skey] = $value;
										else 
										$list['0!'.$skey] = $value;	
									unset($list[$key]);
									} 
								}
						ksort($list);		
						
						#echo $this->helper->pre($this->buffer->usedFacetsValues);
						#echo $this->helper->pre($list);
						
						$this->addJS("setTimeout(results.relatedPersons(".json_encode($list)."), 500);");
						}
					?>
				<?= $this->render('search/bottomLists/placesBoxes.php') ?>
				
				
				<div id="sessionBox"></div>
				</div>
			</div>
		</div>


	<?php else: ?>
				
		<div class="main">
			<div class='sidebar'>
				<?= $this->render('search/facet-sidebar.php') ?>
				
			</div>
			<div class='mainbody' id='content'>
			<?= $this->render('search/summary.php') ?>
			<?php if (!empty($this->solr->error)): ?>
				<h1><?= $this->transEsc($this->solr->error) ?></h1>
					
				<?= $this->transEsc("We are working on the solution") ?>.
				<?= $this->transEsc("Please, try again later") ?>.
			<?php else: ?>
				<h1><?= $this->transEsc('No results')?></h1>
				<?= $this->transEsc("The query returned an empty result list") ?>.</br>
				
			<?php endif; ?>
			</div>
		</div>
		

		
	<?php endif; ?>
<?php endif; ?>
	
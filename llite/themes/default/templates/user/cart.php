<?php
$current_view = $this->getUserParam($currentCore.':view') ?? 'default-box';
?>

<div class='main'>
	<div class='sidebar'>
		<div class="facets-header">
			<span type="button" class="ico-btn" id="slideoutbtn" onclick="facets.SlideOut(); " title="<?= $this->transEsc('Hide facet panel') ?>"><i class="fa fa-angle-left"></i></span>

			<span type="button" class="ico-btn" id="collapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('hide'); " title="<?= $this->transEsc('Collapse all') ?>"><i class="fa fa-angle-double-up"></i></span>
			<span type="button" class="ico-btn" id="uncollapse_all_button" onclick="$('.collapse'+'.sidefl').collapse('show'); " title="<?= $this->transEsc('Show all') ?>"><i class="fa fa-angle-double-down"></i></span>

			<h4><?= $this->transEsc('User cart') ?></h4>
		</div>
		<div id="loadbox_all_facets" class="facets-body">
			<?= $this->helper->alert('info', $this->transEsc('I need to better understand what users want to use this functionality for in order to do it better :-) ')) ?>	
			<?= $this->helper->alert('success', $this->transEsc('Here, you may find filters similar to those for bibliographic records. ')) ?>	
		</div>
  
		
	</div>
	<div class='mainbody' id='content'>
		<div class="search-header hidden-print">
			
			<div class="sidebar-buttons">
				<button type="button" id="slideinbtn" class="ico-btn" OnClick="facets.SlideIn();" title="<?= $this->transEsc('Show side panel')?>"><i class="ph-sidebar-simple-bold"></i></button>
			</div>
			<div class="search-stats">
				<span><?= $this->transEsc('Total results')?> <b><?= $this->helper->numberFormat($this->buffer->myListCount()) ?></b>, </span>
				<span><?= $this->transEsc('showing')?>: <?= $this->helper->numberFormat($this->buffer->myListCount()) ?> </span>
			</div>
		</div>
		<div class="results">
		
		
		
		
		
		
		
		<div class="results">
				<?= $this->render('user/myCart/bulk-actions.php') ?>
				
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
				<?= $this->render('user/myCart/bulk-actions.php') ?>
		
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
					echo '<h4>'.$this->transEsc('Related to the above results').'<h4>';
					echo $this->helper->tabsCarousel( $extraTabs , current(array_keys((array)$listRelated)) );
					echo '<br/><br/>';
					
					
					
					?>
				
				<div id="sessionBox"></div>
				</div>
		
		
		
		
		
		
		
		
		<?= $this->render('user/myCart/bulk-actions.php') ?>
		<?= $this->render('search/paggination.php', ['currentCore'=>'biblio']) ?>
		<div id="sessionBox"></div>
		</div>
	</div>
</div>





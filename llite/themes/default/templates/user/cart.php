<?php

$myListCount = $this->buffer->myListCount();
$results = $this->buffer->myListResults();
$current_view = $this->getUserParam('view');

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
				<?= $this->alert('info', "Może tutaj powinna być jakaś instrukcja do czego lista podręczna służy?") ?>
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
		
		<?= $this->render('user/myCart/bulk-actions.php') ?>
		
		  <div class="results-<?= $current_view ?>">
			<?php 
			$lp = 1;
			if (!empty($results))
				foreach ($results as $recId) {
					$result = new stdClass;
					$result->id = $recId;
					$result->lp = $lp++;
					$result->fullrecord = 'test';
					
					$marcJson = $this->buffer->getJsonRecord($result->id, $result->fullrecord);
					$this->addClass('marc', new marc21($marcJson));
					echo $this->render('search/results/'.$current_view.'.php', ['result'=>$result] );
					}
			?>
		  </div>	
		
		<?= $this->render('user/myCart/bulk-actions.php') ?>
		<?= $this->render('search/paggination.php') ?>
		<div id="sessionBox"></div>
		</div>
	</div>
</div>
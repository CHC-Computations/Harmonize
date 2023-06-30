<?php 


# echo "<pre>".print_r(get_defined_vars(),1)."</pre>";

#$results = $this->solr->getFacets('biblio', array_keys($facets['facetList']));
 
?>


<div class='main'>
	<div class='sidebar'>
		<?= $this->render('search/facet-sidebar.php') ?>
		
	</div>
	<div class='mainbody' id='content'>
			
		<?= $this->render('search/summary.php') ?>
		
		<div class="results">
		<?= $this->render('search/results/bulk-actions.php') ?>
		
		  <div class="results-list">
			<?php 
			foreach ($this->solr->resultsList() as $result) {
				echo $this->render('search/results/list.php', ['result'=>$result] );
				}
			?>
		  </div>	
		
		<?= $this->render('search/results/bulk-actions.php') ?>
		<?= $this->render('search/paggination.php') ?>
		</div>
		
		
	</div>
</div>
		
<button type="button" id="slideinbtn" OnClick="facets.SlideIn();"><i class='fa fa-filter'></i></button>
	
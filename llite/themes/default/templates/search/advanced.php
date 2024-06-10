<?php 
$this->addJS("advancedSearch.refresh(".json_encode($this->GET).");");
$this->addJS("advancedSearch.facets(".json_encode($this->GET).");");
$this->addJS("advancedSearch.sortby(".json_encode($this->GET).");");
$this->addJS("advancedSearch.summary();");
?>

<div class="container-fluid">
	<div class="main">
		<br/>
		<div class="row">
			<div class="col-sm-8"> 
				<div id="formBox">
					<?= $this->helper->loader2() ?>
				</div>
				<div id="facetsBox">
					<?= $this->helper->loader2() ?>
				</div>
				<div id="sortbyBox">
					<?= $this->helper->loader2() ?>
				</div>
				
			</div>
			<div class="col-sm-4">
				<div class="panel panel-default">
					<div class="panel-heading"><?= $this->transEsc("Search summary") ?></div>
					<div class="panel-body">
						<div id="querySummary"></div>
						
					</div>
				</div>
			</div>
			
		</div>
	</div>
</div>


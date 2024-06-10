<?php

if (in_array('wiki', $this->linkParts) and in_Array('record', $this->linkParts)) {
	} else {

	$cleanLink = '';
	if (!empty($this->GET['lookfor']))
		$cleanLink = '<div class="searchRemoveBtn"><a href="'.$this->selfUrl($_SERVER['QUERY_STRING'], '').'" title="'.$this->transEsc('Clean up').'"><i class="glyphicon glyphicon-remove"></i></a></div>';

	$this->addJS("search.start();");
$this->addJS("search.autocomplete();"); 
	}

?>


<?php if (in_array('wiki', $this->linkParts) and in_Array('record', $this->linkParts)): ?>
	<!-- h1 style="font-size:1.2em"><?= $this->transEsc('WikiData record')?></h1-->
<?php else: ?>	
	
	<nav class="searchbox hidden-print" >
		<form id="searchForm" class="searchForm" method="get" action="<?= $this->selfUrl()?>" name="searchForm" autocomplete="off">
			<div class="searchInput" id="searchInput">
				<div class="searchInputMain">
					<input id="searchForm_lookfor" class="search-query autocomplete ac-auto-submit" required type="text" 
							name="lookfor" 
							value="<?= $this->getParam('GET','lookfor')?>" 
							placeholder="<?= $this->transEsc('Search for') ?>..." 
							OnClick="search.start();"/>
				</div>
				<?= $cleanLink ?>
				<div class="serachSubmitBtn">
					<button type="submit" class="btn btn-primary"><i class="ph-magnifying-glass-bold" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> <?= $this->transEsc('Search') ?></span></button>
				</div>
			</div>
		<div id="searchInput-ac" class="searchInput-ac"></div>
		</form>
		<input type="hidden" id="search_core" name="search_core" value="<?= $currentCore ?>">
	</nav>
<?php endif; ?>


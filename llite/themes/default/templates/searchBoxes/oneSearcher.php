<?php
$currentCore = 'biblio';
$this->addJS('search.oneSearcher();');

$searchForm = '
	<div class="searchBoxHome">
	<h4>'.$this->transEsc('Search in bibliografic records').':</h4>
	<nav class="searchbox hidden-print" >
		<form id="searchForm" class="searchForm" method="get" action="'.$this->buildUrl('results/biblio').'" name="searchForm" autocomplete="off">
			<input type="hidden" name="type" value="allfields" />
			<div class="searchInput rounded" id="searchInput">
				<div class="searchInputMain">
					<input id="searchForm_lookfor" class="search-query autocomplete ac-auto-submit" required type="text" name="lookfor" value="'.$this->getParam('GET','lookfor').'" placeholder="'.$this->transEsc('Search for').'..." OnClick="search.start();"/>
				</div>
				<div class="serachSubmitBtn">
					<button type="submit" class="btn btn-primary"><i class="ph-magnifying-glass-bold" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> '.$this->transEsc('Search').'</span></button>
				</div>
			</div>
		</form>
		<input type="hidden" id="search_core" name="search_core" value="biblio">
		
	</nav>
	<div id="oneSearcherArea"></div>
	</div>
	
	';

echo $searchForm;


?>

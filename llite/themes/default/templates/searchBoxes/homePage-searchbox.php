<?php
$currentCore = 'biblio';
$facets = $this->getConfig('search');
foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
	$opt[$k] = $this->transEsc( $v );
	}

$searchForm = '
	<div class="searchBoxHome">
	<h4>'.$this->transEsc('Search in bibliografic records').':</h4>
	<nav class="searchbox hidden-print" >
		<form id="searchForm" class="searchForm" method="get" action="'.$this->buildUrl('results/biblio').'" name="searchForm" autocomplete="off">
			<div class="searchInput" id="searchInput">
				<div>'.$this->forms->select('type', $opt , ['id'=>'searchForm'], 'OnChange="search.start();"').'</div>
				<div class="searchInputMain">
					<input id="searchForm_lookfor" class="search-query autocomplete ac-auto-submit" required type="text" name="lookfor" value="'.$this->getParam('GET','lookfor').'" placeholder="'.$this->transEsc('Search for').'..." OnClick="search.start();"/>
				</div>
				<div class="serachSubmitBtn">
					<button type="submit" class="btn btn-primary"><i class="ph-magnifying-glass-bold" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> '.$this->transEsc('Search').'</span></button>
				</div>
				<div class="serachSubmitBtn">
					<a href="'.$this->buildUri('/search/advanced').'" rel="nofollow" class="btn btn-default"><i class="ph-sliders-bold" aria-hidden="true"></i><span class="hidden-xs hidden-sd"> '.$this->transEsc('Advanced search').'</span></a>
				</div>
			</div>
			<div id="searchInput-ac" class="searchInput-ac"></div>
		</form>
		<input type="hidden" id="search_core" name="search_core" value="biblio">
	</nav>
	</div>
	';

echo $searchForm;


?>

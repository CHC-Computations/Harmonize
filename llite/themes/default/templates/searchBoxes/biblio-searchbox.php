<?php
$currentCore = 'biblio';
$facets = $this->getConfig('search');
foreach ($this->getIniParam('search', 'basicSearches') as $k=>$v) {
	$opt[$k] = $this->transEsc( $v );
	}
$cleanLink = '';
if (!empty($this->GET['lookfor']))
	$cleanLink = '<div class="searchRemoveBtn"><a href="'.$this->selfUrl($_SERVER['QUERY_STRING'], '').'" title="'.$this->transEsc('Clean up').'"><i class="glyphicon glyphicon-remove"></i></a></div>';


$searchForm = '
	<nav class="searchbox hidden-print" >
		<form id="searchForm" class="searchForm" method="get" action="'.$this->selfUrl().'" name="searchForm" autocomplete="off">
			<div class="searchInput" id="searchInput">
				<div>'.$this->forms->select('type', $opt , ['id'=>'searchForm'], 'OnChange="search.start();"').'</div>
				<div class="searchInputMain">
					<input id="searchForm_lookfor" class="search-query autocomplete ac-auto-submit" required type="text" name="lookfor" value="'.$this->getParam('GET','lookfor').'" placeholder="'.$this->transEsc('Search for').'..." OnClick="search.start();"/>
				</div>
				'.$cleanLink.'
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
	';


if (!empty($this->linkParts[3]) && ($this->linkParts[3] == 'advanced'))
	echo '<h1>'.$this->transEsc('Advanced search').'</h1>';
	else if (empty($this->GET['sk'])) {
		echo $searchForm;
		$this->addJS("search.start();");
		$this->addJS("search.autocomplete();");
		} else {
		echo '<nav class="searchbox hidden-print" >';
		echo '<div id="advancedSearch" class="searchInput">';
		echo '<div>';
		echo $this->transEsc('Advanced search query').'';
		echo '</div>';
		echo '<div class="serachSubmitBtn">';
		echo '<a href="'.$this->buildUri('/search/advanced').'">'.$this->transEsc('Edit query').'</a>';
		echo '</div>';
		echo '<div class="serachSubmitBtn">';
		echo '<a class="btn btn-default" OnClick="$(\'#simpleSearch\').toggle(); $(\'#advancedSearch\').toggle();" >'.$this->transEsc('Back to simple search').'</a>';
		echo '</div>';
		echo '</div>';
		echo '</nav>';
		echo '<div id="simpleSearch" class="collapse">'.$searchForm.'</div>';
		}



?>

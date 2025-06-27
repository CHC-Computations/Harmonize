<?php 



$CONDITIONS = '';
$ORDER = '';

$t = $this->psql->querySelect("SELECT * FROM matching_manual $CONDITIONS $ORDER;");

if (is_Array($t)) {
	
	
	echo '<div id="filterField">'.$this->helper->loader().'</div>';
	echo '<div id="resultsField">'.$this->helper->loader().'</div>';
	
	$this->addJS('
	page.phpResults = "/service/data/manual.matching.results";
	page.phpFilters = "/service/data/manual.matching.filters";
	page.phpAction = "/service/data/manual.matching.edit";
	page.results("1", "value");
	page.filters();
	');
	}



?>

<?php
if (empty($this)) die;
$limit = 16;

if (!empty($this->GET['q'])) {
	$queryString = explode(' ', $this->urlName2($this->GET['q']));
	
	$WHERE = "name_search ILIKE '%".implode("%' AND name_search ILIKE '%", $queryString)."%'";
	$t = $this->psql->querySelect("SELECT name,year_born,year_death FROM persons WHERE {$WHERE} ORDER BY rec_total DESC LIMIT $limit;");
	if (is_array($t)) {
		foreach ($t as $row)
			$Tauto[] = $row;
		}
	
	if (empty($Tauto))
		$Tauto[] = "no results";
	echo '{"searchResults":'.json_encode($Tauto).'}';	
	} 

?>
<?php
if (empty($this)) die;
$limit = 16;

if (!empty($this->GET['q'])) {
	$queryString = $this->urlName2($this->GET['q']);
	$t = $this->psql->querySelect("SELECT name, names FROM places_on_map WHERE sstring ILIKE '%{$queryString}%' ORDER BY subjecthits+pubplacehits+personhits DESC LIMIT $limit;");
	if (is_array($t)) {
		foreach ($t as $row)
			$Tauto[] = $row;
		}
	
	if (empty($Tauto))
		$Tauto[] = "no results";
	echo '{"searchResults":'.json_encode($Tauto).'}';	
	} 

?>
<?php 
$core = $this->routeParam[0];


echo '<div id="pairingContentBox"  style="color:#000">';
echo '<p>http://viaf.org/viaf/* are omitted</p><br/><br/>';
$t = $this->psql->querySelect("SELECT other_id, count(*) as cnt FROM elb_{$core}s_raw_ids GROUP BY other_id HAVING count(*)>2 AND other_id NOT ILIKE 'http://viaf.org/viaf/%' ORDER BY cnt DESC;");
if (is_array($t)) {
	echo '<table class="table table-hover">';
	foreach ($t as $row)
		echo "<tr OnClick=\"page.post('pairingContentBox', 'service/data/pre.harmonisation.ids.values/$core/', '{$row['other_id']}');\"><td>"
			.$row['other_id'].'</td><td>'.$row['cnt'].'</td></tr>';
	
	echo '</table>';
	}
echo '</div>';	
#echo $this->helper->pre($t);

?>
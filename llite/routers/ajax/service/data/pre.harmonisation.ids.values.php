<?php 
$core = $this->routeParam[0];
$id = $this->POST['pdata'];

echo "<button class=\"btn btn-success\" onClick=\"page.post('pairingContentBox', 'service/data/pre.harmonisation.ids.pairing/$core/');\">Back to the list</button>";

$t = $this->psql->querySelect("SELECT * FROM elb_{$core}s_raw_ids a JOIN elb_{$core}s_raw b ON a.id_{$core}_raw = b.id WHERE other_id='{$id}';");

if (is_Array($t)) {
	echo '<table class="table table-hover" style="color:#000">';
				
	switch ($core) {
		case 'person': 
				#echo $this->helper->pre($t);
				foreach ($t as $row)
					echo '<tr title="'.$row['id_person_raw'].'">
						<td>'.$row['name'].'</td>
						<td>'.$row['date_range'].'</td>
						<td>'.$row['other_id'].'</td>
						<td>'.$row['clear_str'].'</td>
						</tr>';
				break;
		case 'subject' :
				# echo $this->helper->pre($t);
				foreach ($t as $row)
					echo '<tr title="'.$row['id_subject_raw'].'">
						<td>'.$row['string'].'</td>
						<td>'.$row['id_name'].'</td>
						<td>'.$row['other_id'].'</td>
						<td style="font-size:0.8em">'.$row['clear_str'].'<br/>'.$row['value'].'</td>
						</tr>';
				break;
		default: 
				echo $this->helper->pre($t);
				break;
		}
	echo '</table>';		
	}

echo "<button class=\"btn btn-success\" onClick=\"page.post('pairingContentBox', 'service/data/pre.harmonisation.ids.pairing/$core/');\">Back to the list</button>";

?>
<?php 


$t = $this->psql->querySelect("SELECT creator_role, count(*) FROM elb_publication_corporates_roles GROUP BY creator_role ORDER BY count(*) DESC;");
if (is_array($t)) {
	foreach ($t as $row) {
		foreach($t as $row) {
			$Tused[$row['creator_role']] = $row['count'];
			}
		}
	}
$t = $this->psql->querySelect("SELECT creator_role, count(*) FROM elb_publication_persons_roles GROUP BY creator_role ORDER BY count(*) DESC;");
if (is_array($t)) {
	foreach ($t as $row) {
		foreach($t as $row) {
			@$Tused[$row['creator_role']] += $row['count'];
			}
		}
	}


$order = ['wikiq', 'ingroup', 'code', 'label_en', 'pl', 'cs', 'fi', 'es'];


echo '<div class="row">
	<div class="col-sm-9" style="height:90vh; overflow:auto">';
$t = $this->psql->querySelect("SELECT * FROM dic_creative_roles ORDER BY cs;");
if (is_array($t)) {
	echo '<table class="table table-hover" style="font-size:0.7em;">';
	echo '<thead><tr> 
			<td style="width:12%">wikiq</td>
			<td style="width:2%">ingroup</td>
			<td style="width:14%">code</td>
			<td style="width:14%">en</td>
			<td style="width:14%">pl</td>
			<td style="width:14%">cs</td>
			<td style="width:14%">fi</td>
			<td style="width:14%">es</td>
		</tr>';
	echo '</thead><tbody>';	
	foreach ($t as $row) {
		echo '<tr>';
		
		foreach ($order as $key) {
			$value = $row[$key];
			if ($key == 'ingroup')
				echo '<td >'.$value.'</td>';	
				else 
				if (!empty($Tused[$value])) {
					echo '<td class="success">'.$value.' <span class="badge">'.$Tused[$value].'</span></td>';	
					unset($Tused[$value]);
					} else if (!empty($value))
					echo '<td >'.$value.'</td>';	
					else 
					echo '<td class="warning">&nbsp;</td>';	
			}
		echo '
			</tr>
			';
		}
	
	echo '</tbody></table>';
	}
	
echo '</div>
		<div class="col-sm-3" style="height:90vh; overflow:auto">';
echo '<div class="list-group">';
foreach ($Tused as $string=>$stringCount) {
	echo '<a class="list-group-item">'.$string.' <span class="badge">'.$stringCount.'</span></a>';
	}
echo '</div>';
echo '		
		</div>
	</div>';	
<?php 


$t = $this->psql->querySelect("SELECT language_string, count(*) FROM elb_languages_unrecognized GROUP BY language_string ORDER BY count(*) DESC;");
if (is_array($t)) {
	foreach ($t as $row) {
		foreach($t as $row) {
			$Tused[$row['language_string']] = $row['count'];
			}
		}
	}


$order = ['wikiq', 'iso639_2', 'iso639_1', 'label_en', 'pl', 'cs', 'fi', 'es'];


echo '<div class="row">
	<div class="col-sm-10" style="height:80vh; overflow:auto">';
$t = $this->psql->querySelect("SELECT * FROM dic_languages ORDER BY cs;");
if (is_array($t)) {
	echo '<table class="table table-hover" style="font-size:0.7em;">';
	echo '<thead><tr>';
	foreach ($order as $key) {
		echo '<td style="width:12.5%" >'.$key.'</td>';	
		}
	echo '</tr></thead><tbody>';	
	foreach ($t as $row) {
		echo '<tr title="'.$row['id'].'">';
		
		foreach ($order as $key) {
			$value = $row[$key];
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
		<div class="col-sm-2" style="height:80vh; overflow:auto">';
echo '<div class="list-group">';
foreach ($Tused as $string=>$stringCount) {
	echo '<a class="list-group-item">'.$string.' <span class="badge">'.$stringCount.'</span></a>';
	}
echo '</div>';
echo '		
		</div>
	</div>';	
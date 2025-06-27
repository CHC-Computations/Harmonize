<?php 

$cores = [
		'person' => true, 
		'corporate' => true, 
		'place' => false, 
		'event' => true, 
		'magazine' => false,
		'subject' => false,
		];
foreach ($cores as $core => $hasRoles) {
	$t = $this->psql->querySelect("SELECT count(*) FROM elb_{$core}s_raw;");
	if (is_array($t))
		$dataPSQL[$core]['raw strings'] = current($t)['count'];
	
	$t = $this->psql->querySelect("SELECT count(*) FROM elb_{$core}s_raw_ids;");
	if (is_array($t))
		$dataPSQL[$core]['IDs'] = current($t)['count'];
	
	#$t = $this->psql->querySelect("SELECT other_id, count(*) as cnt FROM elb_persons_raw_ids GROUP BY other_id HAVING count(*)>2 ORDER BY cnt DESC;");
	
	
	$t = $this->psql->querySelect("SELECT COUNT(*) AS num_ids
				FROM (
				  SELECT other_id
				  FROM elb_{$core}s_raw_ids
				  GROUP BY other_id
				  HAVING COUNT(*) > 2
				) AS frequent_ids;
				");
	
	if (is_array($t)) {
		$dataPSQL[$core]['ID indicates 3 or more strings'] = current($t)['num_ids'];
		$onClick[$core]['ID indicates 3 or more strings'] = "page.postInModal('$core: ID indicates 3 or more strings', 'service/data/pre.harmonisation.ids.pairing/$core/', '', false);"; 
		} else 
		$dataPSQL[$core]['ID indicates 3 or more strings'] = 0;
	
	
	$t = $this->psql->querySelect("SELECT COUNT(*) AS num_ids
				FROM (
				  SELECT other_id
				  FROM elb_{$core}s_raw_ids
				  GROUP BY other_id
				  HAVING COUNT(*) = 2
				) AS frequent_ids;
				");
	
	if (is_array($t))
		$dataPSQL[$core]['ID indicates 2 strings'] = current($t)['num_ids'];
		else 
		$dataPSQL[$core]['ID indicates 2 strings'] = 0;
	
	# to check items list = "SELECT * FROM elb_persons_raw_ids a JOIN elb_persons_raw b ON a.id_person_raw = b.id WHERE other_id='http://viaf.org/viaf/102227101';"
	# to check items list = "SELECT * FROM elb_corporates_raw_ids a JOIN elb_corporates_raw b ON a.id_corporate_raw = b.id WHERE other_id='http://viaf.org/viaf/151818712';"
	$inBox = '<h4><strong>'.$core.'s</strong></h4>';
	$inBox.= '<table class="table table-hover">';
	foreach ($dataPSQL[$core] as $key=>$value) {
		$OC = '';
		if ($onClick[$core]['ID indicates 3 or more strings'])
			$OC = ' onClick="'.$onClick[$core]['ID indicates 3 or more strings'].'" style="cursor:pointer"';
		$inBox .= '<tr'.$OC.'><td>'.$key.':</td><td class="text-right"><b>'.$value.'</b></td></tr>';
		}
	$inBox.= '</table>';
	
	$boxes[$core] = '<div class="service-panel-box" style="display:inline-block; width:400px; margin-right:20px;">';
	$boxes[$core] .= $this->helper->panelSimple($inBox);
	$boxes[$core] .= '</div>';
	
	$toClear ['elb_publication_'.$core.'s'] = 'elb_publication_'.$core.'s_id_seq';
	if ($hasRoles) $toClear ['elb_publication_'.$core.'s_roles'] = '';
	}


$t = $this->psql->querySelect("SELECT a.id,msg,count(*) FROM elb_errors a JOIN elb_publication_error b ON (a.id = b.id_error) GROUP BY a.id,msg ORDER BY msg;");
if (is_array($t)) {
	$errorsMsg = "<h4><strong>errors & warrnings:</strong></h4>";
	$errorsMsg.= '<table class="table table-hover">';
	$errorsMsg.= '<thead><tr><td>ErrorID</td><td>Message</td><td>Count</td></tr></thead><tbody>';
	foreach ($t as $row)
		$errorsMsg .='<tr><td>'.$row['id'].'</td><td>'.$row['msg'].'</td><td class="text-right"><b>'.$row['count'].'</b></td></tr>';
	$errorsMsg.= '</tbody></table>';
	$errorsMsg = $this->helper->panelSimple($errorsMsg);

	# to check items list = SELECT * FROM elb_publication a JOIN elb_publication_error b ON b.id_publication = a.id WHERE b.id_error = 2 
	} else {
	$errorsMsg = '';	
	}


$summary = '';	
$t = $this->psql->querySelect("SELECT count(*) FROM elb_publication;");
if (is_array($t)) {
	$totalRecords = 4700000;
	$recordsDone = current($t)['count'];
	$percentDone = round(($recordsDone/$totalRecords)*100,1);
	if ($recordsDone<$totalRecords) {
		$summary = 'This report covers <b>'.$percentDone.'</b>% of all records. Work in progress.';
		$summary.= $this->helper->progressThin($recordsDone, $totalRecords);
		$summary.= '<br/>';
		$summary.= '<br/>';
		}
	} 


?>

<div class='main'>
	<div class="container">
		<br/><br/>
		<?= $summary ?>
		<?= implode('', $boxes) ?>
		<hr/>
		<?= $errorsMsg ?>
	</div>
</div>
<?php
require_once ('./functions/class.lists.php');
require_once ('./functions/class.wikidata.php');
require_once ('./functions/class.solr.php');

$this->addClass('lists', new lists());
$this->addClass('solr', new solr($this));
$this->addClass('wiki', new wikidata($this));
$this->addJS("$('#'+page.resultsField).css('opacity','1');");

$baseQUERY = "
		FROM matching_results a 
		LEFT JOIN matching_strings s ON a.string_id = s.id
		LEFT JOIN dic_rec_types rt ON a.rectype_id = rt.id
		";
# echo $this->helper->pre($_POST);
# echo $this->helper->pre($this->lists->controlFields);

if ($this->user->isLoggedIn() && $this->user->hasPower('editor')) {
	
	$ConditionsSTR = '';
	$CONDITIONS = $this->lists->getConditions('matching.results');
	$ORDER = '';
	
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',										'title' => $this->transEsc('No.') ),
			array( 'class' => 'technical center',	'field' => 'rec_type_name', 'orderField' => 'rt.rec_type_name',	'title' => $this->transEsc('Record type') ), 
			array( 'class' => 'major',				'field' => 'string', 		'orderField' => 's.clearstring',	'title' => $this->transEsc('String given') ), 
			array( 'class' => 'technical',			'field' => 'match_type', 	'orderField' => 'match_type',		'title' => $this->transEsc('Match method') ), 
			array( 'class' => 'major bold',			'field' => 'match_source', 	'orderField' => 'match_source', 	'title' => $this->transEsc('Source') ), 
			array( 'class' => 'useful', 			'field' => 'match_result', 	'orderField' => 'match_result',		'title' => $this->transEsc('Match to') ), 
			array( 'class' => 'technical right',	'field' => 'mlevel', 		'orderField' => 'match_level',		'title' => $this->transEsc('Similarity level') ), 
			array( 'class' => 'actions', 			'field' => 'actions',											'title' => $this->transEsc('Actions') )
			);	 
		 
	if (count($CONDITIONS))
		$ConditionsSTR = 'WHERE '.implode(' AND ', $CONDITIONS);
	$table = $this->psql->querySelect("SELECT count(*) $baseQUERY $ConditionsSTR $ORDER;");
	if (is_Array($table)) {
		$total = current($table)['count'];
		}
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;

	if (!empty($sort))
		$ORDER = "ORDER BY $sort";
	
	$sql			= "SELECT *, a.id $baseQUERY
							$ConditionsSTR $ORDER
							LIMIT $max OFFSET $lp";
	$tableItems		= $this->psql->querySelect("$sql");
	
	if (is_array($tableItems))
		foreach ($tableItems as $k=>$row) {
			$i++;
			if (!empty($row['string']))
				$tmp = explode('|', $row['string']);
				else 
				$tmp = [];
			$string = '<strong>'.$tmp[0].'</strong> ';
			unset($tmp[0]);
			$string .= implode(' ',$tmp);
			$row['string'] = '<span title="'.$row['string_id'].'">'.$string.'</span>';
			if ($row['match_level']<1) 
				$row['match_level'] = round($row['match_level']*100);
			$row['mlevel'] = $row['match_level'].'%';
			$rowOnClick = "page.postInModal('{$this->transEsc('Matching summary')}', 'service/data/matching.results.edit/$row[id]', '$row[id]');";
			$row['actions'] ='
					<button class="table-list-btn" onClick="'.$rowOnClick.'" title="'.$this->transEsc('Create rule').'"><i class="ph ph-pencil"></i></button>
					';
			$tableItems[$k] = $row;
			}
	$this->lists->onmouseover = '';	// we can add for row on mouse over function 
	$this->lists->onclick = ''; 	// we can add for row on mouse click function 
	$tableContent	= $this->lists -> content($tableItems);
	
	
	echo "
		$subPages
		<table class=\"table table-hover table-lists\">
		$tableHeaders
		$tableContent
		</table>
		$subPages
		";
	#echo $this->helper->pre($tableItems);
	}
	


?>
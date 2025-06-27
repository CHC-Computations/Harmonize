<?php
require_once ('./functions/class.lists.php');

$this->addClass('lists', new lists());
$this->addJS("$('#'+page.resultsField).css('opacity','1');");


if ($this->user->isLoggedIn() && $this->user->hasPower('editor')) {
	
	$ConditionsSTR = '';
	$CONDITIONS = $this->lists->getConditions('post');
	$ORDER = '';
	
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',							'title' => $this->transEsc('No.') ),
			array( 'class' => 'technical',		'field' => 'time', 		'orderField' => 'time',			'title' => $this->transEsc('Last modification') ), 
			array( 'class' => 'technical',		'field' => 'mail', 		'orderField' => 'mail',			'title' => $this->transEsc('e-mail') ), 
			array( 'class' => 'useful', 		'field' => 'url', 		'orderField' => 'url',			'title' => $this->transEsc('URL') ), 
			array( 'class' => 'major',			'field' => 'message', 	'orderField' => 'message',		'title' => $this->transEsc('Raport') ), 
			array( 'class' => 'major',			'field' => 'type', 		'orderField' => 'type',			'title' => $this->transEsc('Type') ), 
			array( 'class' => 'technical', 		'field' => 'status',	'orderField' => 'status', 		'title' => $this->transEsc('Status') ),
			array( 'class' => 'actions', 		'field' => 'actions',									'title' => $this->transEsc('Actions') )
			);	 
	
	if (count($CONDITIONS))
		$ConditionsSTR = 'WHERE '.implode(' AND ', $CONDITIONS);	
	$table = $this->psql->querySelect("SELECT count(*) FROM error_report $ConditionsSTR $ORDER;");
	if (is_Array($table)) {
		$total = current($table)['count'];
		}
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;
	if ($sort == '') $sort='time DESC';
	
	if (!empty($sort))
		$ORDER = "ORDER BY $sort";
	
	$sql			= "SELECT * FROM error_report
							$ConditionsSTR $ORDER
							LIMIT $max OFFSET $lp";
	$tableItems		= $this->psql->querySelect("$sql");
	if (is_array($tableItems))
		foreach ($tableItems as $k=>$row) {
			$i++;
			$row['message'] = $this->helper->setLength($row['message'],255);
			$rowOnClick = "page.postInModal('{$this->transEsc('Edit content')}', 'service/admin/error.edit/{$row['id']}', '{$row['id']}');";
			$row['actions'] ='
					<button class="table-list-btn" onClick="'.$rowOnClick.'" title="'.$this->transEsc('Edit').'"><i class="ph ph-pencil"></i></button>
					<button class="table-list-btn" onClick="" title="'.$this->transEsc('Delete').'"><i class="ph ph-trash"></i></button>
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

}

?>
<?php
if (empty($this))
	die();
if ($this->user->isLoggedIn()) {

	$tableItems = $this->psql->querySelect($Q = "SELECT * FROM users_lists WHERE user_id = {$this->psql->string($this->user->full()->email)} ORDER BY list_name");
	
	require_once ('./functions/class.lists.php');
	require_once ('./functions/class.solr.php');

	$this->addClass('lists', new lists());
	$this->addJS("$('#'+page.resultsField).css('opacity','1');");

	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',	'title' => $this->transEsc('No.') ),
			array( 'class' => 'major',				'field' => 'list_name', 	'title' => $this->transEsc('List name') ), 
			array( 'class' => 'technical',			'field' => 'list_ico', 		'title' => $this->transEsc('Icon') ), 
			array( 'class' => 'technical',			'field' => 'list_description', 	'title' => $this->transEsc('Description') ), 
			array( 'class' => 'major bold',			'field' => 'is_public', 	'title' => $this->transEsc('Is public?') ), 
			array( 'class' => 'actions', 			'field' => 'actions',		'title' => $this->transEsc('Actions') )
			);	 
		 
	$table = $this->psql->querySelect("SELECT count(*) FROM users_lists WHERE user_id = {$this->psql->string($this->user->full()->email)};");
	if (is_Array($table)) {
		$total = current($table)['count'];
		}
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;

	if (is_array($tableItems))
		foreach ($tableItems as $k=>$row) {
			$i++;
			$ico = $row['list_ico'];
			$row['list_ico'] = '<i class="ph '.$ico.'"></i>';
			$row['list_description'] = $this->helper->setLength($row['list_description'], 68);
			$rowOnClick = "page.post('user_lists_editor', 'user/lists/editor', '$row[id]');";
			$row['actions'] ='
					<button class="table-list-btn" onClick="'.$rowOnClick.'" title="'.$this->transEsc('Edit').'"><i class="ph ph-pencil"></i></button>
					';
			$tableItems[$k] = $row;
			}
	$this->lists->onmouseover = '';	// we can add for row on mouse over function 
	$this->lists->onclick = ''; 	// we can add for row on mouse click function 
	$tableContent	= $this->lists -> content($tableItems);
	
	
	echo "
		<table class=\"table table-hover table-lists\">
		$tableHeaders
		$tableContent
		</table>
		";
	#echo $this->helper->pre($tableItems);
	}
	


?>



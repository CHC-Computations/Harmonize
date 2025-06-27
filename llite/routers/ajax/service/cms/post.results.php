<?php
require_once ('./functions/class.lists.php');

$this->addClass('lists', new lists());
$this->addJS("$('#'+page.resultsField).css('opacity','1');");


#echo $this->helper->pre($_POST);
#echo $this->helper->pre($this->lists->controlFields);

if ($this->user->isLoggedIn() && $this->user->hasPower('editor')) {
	
	$ConditionsSTR = '';
	$CONDITIONS = $this->lists->getConditions('post');
	$ORDER = '';
	
	$this->lists->dataColumns = array (
			'1'	=> array( 'class' => 'technical right',	'function' => 'counter',								'title' => $this->transEsc('No.') ),
			#'3' => array( 'class' => 'useful',			'field' => 'id', 		'orderField' => 'id',			'title' => $this->transEsc('ID') ), 
			'2' => array( 'class' => 'useful',			'field' => 'parent_id', 'orderField' => 'parent_id,p_order', 	'title' => $this->transEsc('Order') ), 
			'4' => array( 'class' => 'technical center','field' => 'lang', 		'orderField' => 'lang',			'title' => $this->transEsc('Language') ), 
			'5' => array( 'class' => 'useful', 			'field' => 'url', 		'orderField' => 'url',			'title' => $this->transEsc('Url name') ), 
			'6' => array( 'class' => 'major',			'field' => 'title', 	'orderField' => 'title',		'title' => $this->transEsc('Title') ), 
			'7' => array( 'class' => 'technical',		'field' => 'date_m', 	'orderField' => 'date_m',		'title' => $this->transEsc('Last modification') ), 
			'8' => array( 'class' => 'major',			'field' => 'author', 	'orderField' => 'author',		'title' => $this->transEsc('Author') ), 
			'9'=> array( 'class' => 'technical', 		'field' => 'operator',	'orderField' => 'operator', 	'title' => $this->transEsc('Operator') ),
			'10'=> array( 'class' => 'actions', 		'field' => 'actions',									'title' => $this->transEsc('Actions') )
			);	 
	
	if (count($CONDITIONS))
		$ConditionsSTR = 'WHERE '.implode(' AND ', $CONDITIONS);	
	$table = $this->psql->querySelect("SELECT count(*) FROM cms_posts $ConditionsSTR $ORDER;");
	if (is_Array($table)) {
		$total = current($table)['count'];
		}
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;
	if ($sort == '') $sort='parent_id,p_order';
	
	if (!empty($sort))
		$ORDER = "ORDER BY $sort";
	
	$sql			= "SELECT * FROM cms_posts
							$ConditionsSTR $ORDER
							LIMIT $max OFFSET $lp";
	$tableItems		= $this->psql->querySelect("$sql");
	if (is_array($tableItems))
		foreach ($tableItems as $k=>$row) {
			$i++;
			$row['parent_id'] .='.'.$row['p_order'];
			$rowOnClick = "page.postInModal('{$this->transEsc('Edit post/page content')}', 'service/cms/post.edit/{$row['id']}', '{$row['id']}');";
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
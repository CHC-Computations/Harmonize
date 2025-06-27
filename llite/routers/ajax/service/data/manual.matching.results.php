<?php
require_once ('./functions/class.lists.php');
require_once ('./functions/class.wikidata.php');
require_once ('./functions/class.solr.php');

$this->addClass('lists', new lists());
$this->addClass('solr', new solr($this));
$this->addClass('wiki', new wikidata($this));
$this->addJS("$('#'+page.resultsField).css('opacity','1');");

$baseQUERY = "
		FROM matching_manual a 
		LEFT JOIN matching_fields f ON a.field = f.id
		LEFT JOIN dic_rec_types rt ON a.rectype = rt.id
		LEFT JOIN dic_values_types v ON a.valuetype = v.id
		";
#echo $this->helper->pre($_POST);
#echo $this->helper->pre($this->lists->controlFields);

if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
	
	$CONDITIONS = '';
	$ORDER = '';
	
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter',										'title' => $this->transEsc('No.') ),
			array( 'class' => 'technical center',	'field' => 'rec_type_name', 'orderField' => 'rt.rec_type_name',	'title' => $this->transEsc('Record type') ), 
			array( 'class' => 'useful',				'field' => 'fieldname', 	'orderField' => 'f.fieldname',		'title' => $this->transEsc('Field type') ), 
			array( 'class' => 'technical center',	'field' => 'value_type', 	'orderField' => 'v.value_type',		'title' => $this->transEsc('Value type') ), 
			array( 'class' => 'major bold',			'field' => 'value', 		'orderField' => 'a.value', 			'title' => $this->transEsc('Value') ), 
			array( 'class' => 'useful', 			'field' => 'target', 		'orderField' => 'a.target',			'title' => $this->transEsc('Match to') ), 
			array( 'class' => 'technical',			'field' => 'data', 			'orderField' => 'data',				'title' => $this->transEsc('Input time') ), 
			array( 'class' => 'technical', 			'field' => 'operator',		'orderField' => 'operator', 		'title' => $this->transEsc('Operator') ),
			array( 'class' => 'actions', 			'field' => 'actions',											'title' => $this->transEsc('Actions') )
			);	 
		 
	$table = $this->psql->querySelect("SELECT count(*) $baseQUERY $CONDITIONS $ORDER;");
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
							$CONDITIONS $ORDER
							LIMIT $max OFFSET $lp";
	$tableItems		= $this->psql->querySelect("$sql");
	
	if (is_array($tableItems))
		foreach ($tableItems as $k=>$row) {
			$i++;
			if ($row['target'] == 0)
				$row['target'] = '<small class="text-warning">'.$this->transEsc('do not match').'</small>';
				else 
				$row['target'] = '<strong title="'.$this->helper->formatWiki($row['target']).'">'.$row['target'].'</strong>';
			
			$row['data'] = explode(' ',$row['data'])[0].' <small>'.substr(explode(' ',$row['data'])[1],0,5).'</small>';
			$rowOnClick = "page.postInModal('{$this->transEsc('Edit manual matching rule')}', 'service/data/manual.matching.edit/{$row['id']}', '{$row['id']}');";
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
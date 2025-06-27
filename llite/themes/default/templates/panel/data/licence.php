<?php 
if (empty($this)) die();

if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
	$this->addClass('buffer', new buffer()); 
	$this->addClass('helper', new helper()); 
	$this->addClass('solr', new solr($this)); 

	require_once ('./functions/class.lists.php');
	$this->addClass('lists', new lists());

	
	
	
	$tableGroupsItems = [];
	$descriptionsDirectory = './themes/default/templates/cms/sources/';
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter','title' => $this->transEsc('No.') ),
			array( 'class' => 'major',			'field' => 'sourceDB',  	'title' => $this->transEsc('Source Database') ), 
			array( 'class' => 'technical ',		'field' => 'code', 			'title' => $this->transEsc('Licence type') ), 
			array( 'class' => 'technical',		'field' => 'description', 	'title' => $this->transEsc('Description') ), 
			array( 'class' => 'actions', 		'field' => 'actions',		'title' => $this->transEsc('Actions') )
			);	 
	
	
	$licenceTable = $this->helper->getLicence();
	# echo $this->helper->pre($licenceTable);
	foreach ($licenceTable as $sourceDB => $licence) {
		$row = (array)$licence;
		$row['sourceDB'] = $sourceDB;
		$row['description'] = '<span style="max-width:400px; display:block;">'.$licence->description.'</span>';
		$row['code'] = '<a href="'.$licence->link.'">'.$licence->code.'</a>';
		$row['actions'] ='
					<button class="table-list-btn" onClick="" title="'.$this->transEsc('Edit').'"><i class="ph ph-pencil"></i></button>
					<button class="table-list-btn" onClick="" title="'.$this->transEsc('Delete').'"><i class="ph ph-trash"></i></button>
					';
		$tableGroupsItems[] = $row;
		}
	
	$groupsTotal = count($tableGroupsItems);		
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($groupsTotal);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;
	$tableContent	= $this->lists -> content($tableGroupsItems);
	echo '<br/><br/>';
	echo "<table class=\"table table-hover table-lists\">
					$tableHeaders
					$tableContent
					</table>
					";
	
	
	}
?>
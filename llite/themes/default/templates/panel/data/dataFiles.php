<?php
if (empty($this)) die();

if ($this->user->isLoggedIn() && $this->user->hasPower('admin')) {
	$this->addClass('buffer', new buffer()); 
	$this->addClass('helper', new helper()); 
	$this->addClass('solr', new solr($this)); 

	require_once ('./functions/class.lists.php');
	$this->addClass('lists', new lists());

		
	$source_path = $this->configJson->import->dataFolder ?? './import/data/';
	
	
	$fileList = glob ($source_path.'*.mrk');
	foreach ($fileList as $file) {
		$fileName = str_replace($source_path, '', $file);
		$fileSize = filesize($file);
		$filesNamesList[$fileName] = $fileSize;
		$tmp = explode('_', $fileName); 
		@$filesGroups[$tmp[0]]++;
		}


	$solrField = 'source_file';
	$query=[];
	$query['q']=[ 
					'field' => 'q',
					'value' => '*:*'
					];
	$query['rows']=[ 
					'field' => 'rows',
					'value' => 0
					];
	$query['facet']=[ 
					'field' => 'facet',
					'value' => 'true'
					];
	$query['facet.field']=[ 
					'field' => 'facet.field',
					'value' => $solrField
					];
	$query['stats']=[ 
					'field' => 'stats',
					'value' => 'true'
					];
	$query['stats.field']=[ 
					'field' => 'stats.field',
					'value' => 'last_indexed'
					];
	$query['stats.facet']=[ 
					'field' => 'stats.facet',
					'value' => $solrField
					];

	$results = $this->solr->getQuery('biblio', $query);
	$stats = $this->solr->stats->stats_fields->last_indexed->facets->$solrField;
	
	$recInFile = $this->solr->facetsList()[$solrField];
	
	$tableItems = [];
	foreach ($filesNamesList as $fileName => $fileSize) {
		$row['fileName'] = $fileName;
		$row['fileSize'] = $this->helper->fileSize($fileSize);
		$row['recTotal'] = $this->helper->numberFormat($recInFile[$fileName] ?? 0);
		
		$lastIndexMax = strtotime($stats->$fileName->max);
		$lastIndexMin = strtotime($stats->$fileName->min);
		$lastIndexDuration = $lastIndexMax - $lastIndexMin;
		$row['lastIndex'] = date("Y-m-d", $lastIndexMax).' <br/><small>'.date("H:i", $lastIndexMax).'</small>';
		$row['indexDuration'] = date("H:i's", $lastIndexDuration);
		$row['actions'] ='
					<button class="table-list-btn" onClick="" title="'.$this->transEsc('Edit').'"><i class="ph ph-pencil"></i></button>
					<button class="table-list-btn" onClick="" title="'.$this->transEsc('Delete').'"><i class="ph ph-trash"></i></button>
					';
		$tableItems[] = $row;
		}
	
	$this->lists->dataColumns = array (
			array( 'class' => 'technical right',	'function' => 'counter','title' => $this->transEsc('No.') ),
			array( 'class' => 'major',			'field' => 'fileName',  	'title' => $this->transEsc('File name') ), 
			array( 'class' => 'technical right','field' => 'fileSize', 		'title' => $this->transEsc('File size') ), 
			array( 'class' => 'technical right','field' => 'recTotal', 		'title' => $this->transEsc('Number of records') ), 
			array( 'class' => 'useful', 		'field' => 'lastIndex', 	'title' => $this->transEsc('Last index date') ), 
			array( 'class' => 'useful',			'field' => 'indexDuration', 'title' => $this->transEsc('Last index duration') ), 
			#array( 'class' => 'technical',		'field' => 'licence', 		'title' => $this->transEsc('Licence') ), 
			array( 'class' => 'actions', 		'field' => 'actions',		'title' => $this->transEsc('Actions') )
			);	 
	$total = count($filesNamesList);		
	$max 			= $this->lists -> SetMax(50);
	$subPages 		= $this->lists -> subPages ($total);
	$tableHeaders 	= $this->lists -> headers ();
	$lp				= $this->lists -> startPoint;
	$sort			= $this->lists -> sorting;
	$tableContent	= $this->lists -> content($tableItems);
	

	echo "<table class=\"table table-hover table-lists\">
		$tableHeaders
		$tableContent
		</table>
		";
	
	
	
	}
?>
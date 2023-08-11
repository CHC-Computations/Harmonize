<?php 
require_once('./config/db.php');
require_once('./functions/klasa.importer.3.php');
require_once('./functions/klasa.buffer.php');
require_once('./functions/klasa.wikidata.php');
require_once('./functions/klasa.wikidata.libri.php');
require_once('./functions/klasa.solr.php');


$conFiles = glob ('./config/*.ini'); 
if (is_array($conFiles))
	foreach ($conFiles as $fullFileName) {
		$confName = str_replace(['./config/', '.ini'], '', $fullFileName);
		$config[$confName] = parse_ini_file($fullFileName, true);
		}

$settings = json_decode(@file_get_contents('./config/settings.json'));
if (empty($settings)) {
	die ("settings.json file not found");
	}

$step = 1;
$solr = new solr($config);

$query['q']=[ 
		'field' => 'q',
		'value' => '*:*'
		];
$query['fq']=[ 
		'field' => 'fq',
		'value' => 'record_type:person'
		];
$query['fl']=[ 
		'field' => 'fl',
		'value' => 'id,fullrecord'
		];
$query['sort']=[ 
		'field' => 'sort',
		'value' => 'last_indexed asc'
		];
$query['facet']=[ 
		'field' => 'facet',
		'value' => 'false'
		];
$query['rows']=[ 
		'field' => 'rows',
		'value' => $step
		];
$query['start']=[ 
		'field' => 'start',
		'value' => 0
		];	
$LP = 0;		
echo "retriving id list\n";
$results = $solr->getQuery('wikidata',$query); 
$results = $solr->resultsList();
$total = $solr->totalResults();
echo "total records: $total\n\n";
for ($i = 0; $i <= $total; $i+=$step) {
	$query['start']=[ 
		'field' => 'start',
		'value' => $i
		];
	$results = $solr->getQuery('wikidata',$query); 
	$results = $solr->resultsList();
	foreach ($results as $result) {
		$LP++;
		$id = $result->id;
		$wiki = new wikiLibri('en', $result);
		
		echo "\rstep: $i     $id   ".substr($wiki->get('labels'),0,20);
		
		$newWiki = new wikidata($result->id, true);
		
		}
	
	}

echo "\n";
?>
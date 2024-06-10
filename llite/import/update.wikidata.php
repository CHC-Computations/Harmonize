<?php 
$maxRows = 9999999;
$commitAfter = 5000;
$currentCore = 'wikidata';
$uniqid = 'id';
$source_path = './import/data';




require_once('./config/db.php');
require_once('./functions/class.importer.php');
require_once('./functions/class.solr.php');
require_once('./functions/class.wikiSearcher.php');
require_once('./functions/class.viafSearcher.php');
require_once('./functions/class.wikidata.php');
require_once('./functions/class.helper.php');
require_once('./functions/class.buffer.php');
require_once('./functions/class.pgsql.php');


$imp = new importer();
$imp->addClass('solr', new solr($imp));
$imp->addClass('psql', new postgresql($psqldb));
$imp->addClass('wikiData', new wikidata($imp));
$imp->addClass('helper', new helper($imp));
$imp->addClass('wikiSearcher', new wikiSearcher($imp));
$imp->addClass('viafSearcher', new viafSearcher($imp));



## cleaning search files
$list = glob ($imp->outPutFolder.'*.csv');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file); 

$imp->buffer->bufferTime = 86400*530; // saving time. we want to accept even very old wikidata files  


$query['q']=[ 
			'field' => 'q',
			'value' => '*:*'
			];
$query['fl']=[ 
			'field' => 'fl',
			'value' => $uniqid
			];

$query['start']=[ 
			'field' => 'start',
			'value' => '0'
			];	
$query['rows']=[ 
			'field' => 'rows',
			'value' => 0
			];	
			
$query['sort']=[ 
			'field' => 'sort',
			'value' => 'id asc'
			];			
$query['facet']=[ 
			'field' => 'facet',
			'value' => 'true'
			];			
$query['facet.field']=[ 
			'field' => 'facet.field',
			'value' => $uniqid
			];			
$query['facet.limit']=[ 
			'field' => 'facet.limit',
			'value' => $maxRows
			];
			

echo "start quering: ".$imp->workTime()."\n";
$results = $imp->solr->getQuery($currentCore, $query); 
$results = $imp->solr->resultsList();
$facets = $imp->solr->facetsList();
echo "ID list ready: ".$imp->workTime()."\n\n";

$viafCounter = 0;
if (!empty($facets[$uniqid])) {
	$totalResults = count($facets[$uniqid]);
	file_put_contents($imp->outPutFolder.'wikiIDs.json', json_encode($facets[$uniqid]));
	$i = 0;
	foreach ($facets[$uniqid] as $id => $notImportantNumber) {
		$imp->wikiData->loadRecord($id, false);
		$i++;
		$proc = round(($i/$totalResults)*100,1);
		@$Tsource[substr($imp->wikiData->localRecord,0,2)]++;
		file_put_contents($imp->outPutFolder.$imp->wikiData->localRecord.'.csv', $id."\n", FILE_APPEND);
		$sourceTab = '';
		foreach ($Tsource as $k=>$v)
			$sourceTab .= "$k:$v, ";
		$work = '-- ';	
		if (!empty($imp->wikiData->getViafId())) {
			$viaf = $imp->wikiData->getViafId();
			$labels = $imp->viafSearcher->getLabels($viaf);
			if ($imp->viafSearcher->dataOrigin == 'external') 
				$imp->addLabelsToViafRecord($viaf, $labels);
			$viafCounter++;
			if ($viafCounter % $commitAfter == 0)
				$imp->solr->curlCommit('viaf');
			$work = 'ok ';
			
			}	

		$recType = $imp->wikiData->recType();
		$currentRecType = $imp->wikiData->solrRecord->record_type ?? 'undefined';
		if (empty($imp->wikiData->solrRecord->record_type) or ($recType !== $imp->wikiData->solrRecord->record_type)) {
			$data = (object)['id' =>$id];
			$data->record_type =  (object) ["set" => $recType];
			if ($recType == 'magazine')
				$data->eids_issn = (object) ["set" => $imp->wikiData->getStrVal('P236') ?? null];
			
			
			if (!$imp->solr->curlSaveData('wiki', $data)) {
				file_put_contents($imp->outPutFolder.'toSave.wikiData.json', $imp->solr->curlSavePostData);
				echo "\nfatal error while wikiData ($id) updating\n";
				#die();
				}
			file_put_contents($imp->outPutFolder.'toSave.wikiData.json', $imp->solr->curlSavePostData);
			}
	
		if ($i % $commitAfter == 0) {
			echo "\rcommiting to solr    ";
			$imp->solr->curlCommit('wiki');
			}
		
		$work.=$viafCounter;	
		echo "\r".$imp->helper->numberFormat($i).' ('.$proc.'%)   '.$imp->workTime().' '.$sourceTab.' '.$id."  $work                        ";
		}
	}



echo "\nrequresive done: ".$imp->workTime()."\n";
#echo "results";
#print_r($facets);
#echo "alerts:";
#print_r($imp->solr->alert);
$totalResults = $imp->solr->totalResults();

$imp->solr->curlCommit('wiki');
$imp->solr->curlCommit('viaf');


?>
all done!

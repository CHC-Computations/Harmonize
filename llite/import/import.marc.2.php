<?php
$source_path = '/var/www/html/harmonize/import/data';
$sepLine = "________________________________________________________________________\n";

require_once('./config/db.php');
require_once('./functions/class.importer.php');
require_once('./functions/class.solr.php');
require_once('./functions/class.wikiSearcher.php');
require_once('./functions/class.viafSearcher.php');
require_once('./functions/class.localSearcher.php');
require_once('./functions/class.wikidata.php');
require_once('./functions/class.helper.php');
require_once('./functions/class.buffer.php');
require_once('./functions/class.pgsql.php');


$imp = new importer();
$imp->addClass('solr', new solr($imp));
$imp->addClass('psql', new postgresql($psqldb));
$imp->addClass('buffer', new buffer());
$imp->addClass('wikiData', new wikidata($imp));
$imp->addClass('wikiDataSub', new wikidata($imp));
$imp->addClass('marcBuffer', new buffer($imp));
$imp->addClass('helper', new helper($imp));
$imp->addClass('wikiSearcher', new wikiSearcher($imp));
$imp->addClass('viafSearcher', new viafSearcher($imp));
$imp->addClass('localSearcher', new localSearcher($imp));

$imp->buffer->bufferTime = 86400*530; // saving time. we want to accept even very old wikidata files  


$imp->psql->query("TRUNCATE TABLE matching_strings_best_label;"); 
$imp->workingStep = 2;

$destination_path = $imp->setDestinationPath('./files');

$errorFile = './import/errors/recWithError.mrk';

$lp = $wikiFounded = 0;
$lpp = 1;

$imp->setFilesToImport(glob($imp->outPutFolder.'t.buffer.*.json'));
$imp->lp = 0;
	
foreach ($imp->bufferAreas as $area) {	
	$fileList = glob($imp->outPutFolder.'t.buffer.'.$area.'.*.json');
	echo $sepLine;
	echo $area."\n";
	foreach ($fileList as $bufferFile) {
		$i = 0;
		
		$imp->setFileName($bufferFile);
		$imp->setFileNo($lpp++);
		
		$extraData = json_decode(file_get_contents($bufferFile));	
		$imp->fullFileSize = count((array)$extraData);
		$type = str_replace([$imp->outPutFolder.'t.buffer.'.$area.'.', '.json'], '', $bufferFile);
		#echo $bufferFile."\n";
		$block = "$area.$type";
		$block .=str_repeat(' ', 30 - strlen($block));
		
		$lineText = $block." records to save: ". $imp->helper->numberFormat($toSave = count((array)$extraData));
		echo $lineText."\r";
		
		switch ($area) {
			case ('wikiQ') :
				foreach ($extraData as $wikiQ => $data) {
					$i++;
					echo $lineText .' '.$imp->workTime().' ('.round(($i/$toSave)*100).'%)';
					#echo $imp->helper->numberFormat($lp++);
					echo ' '.$wikiQ;
					#$imp->wikiData->loadRecord($wikiQ);
					#$saveFunction = 'save'.ucfirst($type);
					$imp->saveCoreRecord($type, $wikiQ, $data);
					$imp->saveImportStatus();
						
					echo "\r";
					}
				break;	
			case ('issn'):  {
				foreach ($extraData as $issn => $data) {
					$i++;
					echo $lineText .' ('.round(($i/$toSave)*100).'%)';
					$issnArray = (array)$data->issn;
					arsort($issnArray);
					$issnStr = key($issnArray);
					
					$labelsArray = (array)$data->biblio_labels;
					arsort($labelsArray);
					$name = key($labelsArray);
					if (stristr($name, '(')) {
						$tmp = explode('(', $name);
						$name = trim($tmp[0]);
						}
					#echo $imp->helper->numberFormat($lp++);
					echo ' '.$issn.' '.$wikiFounded;
					#$imp->wikiData->loadRecord($wikiQ);
					// P7363  = issn-L property
					// https://portal.issn.org/resource/ISSN/1210-9118?format=json
					$wikiQ = $imp->wikiSearcher->query('magazine', $name, []); 
					if (!empty($wikiQ) && ($wikiQ !== 'not found')) {
						$wikiFounded++;
						$imp->saveCoreRecord($type, $wikiQ, $data);
						$imp->saveImportStatus();
					
						} 
					echo "\r";
					}
				} break;
			case 'str' : 
				foreach ($extraData as $key => $data) {
					$i++;
					echo $lineText .' ('.round(($i/$toSave)*100).'%)';
					echo ' '.substr($key,0,30).' ';
					$imp->saveOrphansRecord($type, $key, $data);
					$imp->saveImportStatus();
					
					echo "\r";
					}
				$imp->solr->curlCommit('orphans');	
				break;
			}	
		$imp->solr->curlCommit($type.'s');	
		echo $lineText." - done                                          \n";
			
		}
	}
$imp->saveNoBiblioRelatedPlaces();
$imp->solr->curlCommit('places');	

echo $sepLine;
echo number_format($imp->totalRec,0,'','.').' records saved to solr in '.$imp->WorkTime()."                                          \n\n";




if (file_exists($imp->outPutFolder.'counter.txt'))
	unlink ($imp->outPutFolder.'counter.txt');




?>
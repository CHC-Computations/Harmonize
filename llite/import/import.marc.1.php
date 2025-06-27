<?php
$source_path = './import/data';


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
$imp->addClass('wikiData', new wikidata($imp));
$imp->addClass('helper', new helper($imp));
$imp->addClass('wikiSearcher', new wikiSearcher($imp));
$imp->addClass('viafSearcher', new viafSearcher($imp));
$imp->addClass('localSearcher', new localSearcher($imp));

$imp->buffer->bufferTime = 86400*530; // saving time. we want to accept even very old wikidata files  

echo "cleaning facets_queries table\n";
$imp->psql->query("TRUNCATE TABLE facets_queries;");  // jakiś problem tutaj - zadanie zajeło PSQL niezwykle dużo czasu 
echo "cleaning matching_results table\n";
$imp->psql->query("TRUNCATE TABLE matching_results;"); 
$imp->psql->query("COMMIT;");  // jakiś problem tutaj - zadanie zajeło PSQL niezwykle dużo czasu 

$destination_path = $imp->setDestinationPath('./files');

$errorFile = './import/errors/recWithError.mrk';

echo "########################\n\n";


## cleaning old tmp files 
echo "cleaning old tmp files \n";
$list = glob ($imp->outPutFolder.'*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file);
## cleaning errors files 
$list = glob ('./import/errors/*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file); 
		
		
$imp->workingStep = 1;

## creating tmp files with indexes
echo "Searching for a files in $source_path\n";
$lp = 0;
$lpp = 0;
$list = glob ($source_path.'/*.mrk');
echo "files found: \e[94m".count($list)."\e[0m\n";
$imp->setFilesToImport($list);

if (is_array($list))
	foreach ($list as $file) {
		$lpp++;
		$lp = 0;
		$fileName = str_Replace($source_path.'/', '', $file);
		echo "\n$lpp. reading: \e[94m$fileName\e[0m                             \n";
		$imp->setFileName($fileName);
		$imp->setFileNo($lpp);
		
		$results = [];
		$record = '';
		$MRK = '';
		$imp->fileSize($file);
		
		$fp = @fopen($file, "r");
		if ($fp) {
			while (($buffer = fgets($fp, 8192)) !== false) {
				$MRK .= $buffer;
				if (empty(trim($buffer)) or (($imp->buffSize+strlen($MRK)) >= $imp->fullFileSize)) {
					$lp++;
					$json = $imp->mrk2json($MRK);
					echo $imp->createRelations();
					
					$imp->saveImportStatus();
					
					# $imp->saveFieldsContent();
					# $imp->saveAllFields();
					$isOK = true;
					#$isOK = $imp->saveRecord();
					if ($isOK == 'error')
						file_put_contents($errorFile, $MRK, FILE_APPEND);
						else 
						echo $isOK."\r";
					$MRK = '';
					}
				 
				}
			fclose($fp);
			
			foreach ($imp->bufferAreas as $area)
				if (!empty($imp->buffer->$area))
					foreach ($imp->buffer->$area as $type=>$list)
						file_put_contents($imp->outPutFolder.'t.buffer.'.$area.'.'.$type.'.json', json_encode($list, JSON_INVALID_UTF8_SUBSTITUTE));
			}
		}
		
				

echo "___________________________________________________________\n";
echo number_format($imp->totalRec,0,'','.').' records saved to solr in '.$imp->WorkTime()."                                          \n\n";

// echo "Reindexing spellcheck step 1\n";
// file_get_contents("http://localhost:8983/solr/lite.biblio/select?q=*:*&spellcheck=true&spellcheck.build=true");
// echo "Reindexing spellcheck step 2\n";
// file_get_contents("http://localhost:8983/solr/lite.biblio/select?q=*:*&spellcheck.dictionary=basicSpell&spellcheck=true&spellcheck.build=true");

if (file_exists($imp->outPutFolder.'counter.txt'))
	unlink ($imp->outPutFolder.'counter.txt');






?>
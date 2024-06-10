<?php
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

$imp->buffer->bufferTime = 86400*530; // saving time. we want to accept even very old wikidata files  

#$imp->psql->query("TRUNCATE TABLE facets_queries;");  // jakiś problem tutaj - zadanie zajeło PSQL niezwykle dużo czasu 

$destination_path = $imp->setDestinationPath('./files');

$errorFile = './import/errors/recWithError.mrk';

echo "########################\n\n";

## cleaning errors files 
$list = glob ('./import/errors/*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file); 
	
## cleaning temporary files	
$extToRemove = ['csv', 'log'];
foreach ($extToRemove as $ext) {
	$list = glob ($imp->outPutFolder.'*.'.$ext);
	if (is_array($list))
		foreach ($list as $file)
			unlink ($file); 
	}
		
		
		


echo "Searching for a files in $source_path\n";
$lp = 0;
$lpp = 0;
$list = glob ($source_path.'/*.mrk');
echo "files found: \e[94m".count($list)."\e[0m\n\n";

if (is_array($list))
	foreach ($list as $file) {
		$lpp++;
		$lp = 0;
		$fileName = str_Replace($source_path.'/', '', $file);
		echo "\n$lpp. reading: \e[94m$fileName\e[0m                             \n";
		$imp->setFileName($fileName);
		
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
					$imp->createRelations();
					
					# $imp->saveFieldsContent();
					# $imp->saveAllFields();
					# print_r($imp->record);
					#die();
					
					$isOK = $imp->saveRecord();
					if ($isOK == 'error')
						file_put_contents($errorFile, $MRK, FILE_APPEND);
						else 
						echo "\r".$isOK;
					$MRK = '';
					}
				
				}
			fclose($fp);
			
			if (!empty($imp->buffer))
				file_put_contents($imp->outPutFolder.'t.buffer.json', json_encode($imp->buffer, JSON_INVALID_UTF8_SUBSTITUTE));
			foreach ($imp->bufferAreas as $area)
				if (!empty($imp->buffer->$area))
					foreach ($imp->buffer->$area as $type=>$list)
						file_put_contents($imp->outPutFolder.'t.buffer.'.$area.'.'.$type.'.json', json_encode($list, JSON_INVALID_UTF8_SUBSTITUTE));
			}
		}
		
				

echo "\n___________________________________________________________\n";
echo number_format($imp->totalRec,0,'','.').' records saved to solr in '.$imp->WorkTime()."                                          \n\n";


if (file_exists($imp->outPutFolder.'counter.txt'))
	unlink ($imp->outPutFolder.'counter.txt');






?>
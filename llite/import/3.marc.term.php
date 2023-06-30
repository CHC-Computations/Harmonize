<?php
require_once('./config/db.php');
require_once('./functions/klasa.importer.3.php');
require_once('./functions/klasa.buffer.php');
require_once('./functions/klasa.wikidata.php');
require_once('./functions/klasa.pgsql.php');

include('./import/config.php');
$imp = new importer();
$imp->mdb($mdb); 
$imp->register('psql', new postgresql($psqldb));
$imp->register('buffer', new marcbuffer());
$imp->buffer->bufferTime = 86400*530; // saving time. we want to accept even very old wikidata files  

$destination_path = $imp->setDestinationPath('./files');

$errorFile = './import/errors/recWithError.mrk';
$imp->setTable('INDEXES', $INDEXES);
$imp->setTable('TEXT_INDEXES', $TEXT_INDEXES);
$Tmap 	= $imp->getConfig('language_map2');
$Trole	= $imp->getConfig('creative_roles_map');

echo "Languages Map: ".count($Tmap)."\n";
echo "Creative roles Map: ".count($Trole)."\n";
echo "########################\n\n";


## cleaning old tmp files 
$list = glob ($imp->outPutFolder.'*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file);
## cleaning errors files 
$list = glob ('./import/errors/*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file); 
		
		

## creating tmp files with indexes
echo "Searching for a files in $source_path\n";
$lp = 0;
$list = glob ($source_path.'/*.mrk');

if (is_array($list))
	foreach ($list as $file) {
		echo "\nreading: \e[94m$file\e[0m                             \n";
		
		$fname = str_Replace($source_path, '', $file);
		$imp->setFileName($fname);
		
		$results = [];
		$record = '';
		$MRK = '';
		$imp->fileSize($file);
		
		$fp = @fopen($file, "r");
		if ($fp) {
			while (($buffer = fgets($fp, 8192)) !== false) {
				$MRK .= $buffer;
				if (empty(trim($buffer))) {
					$lp++;
					$json = $imp->mrk2json($MRK);
					#print_r($imp->record);
					#die();
					$isOK = $imp->saveRecord($record);
					if ($isOK == 'error')
						file_put_contents($errorFile, $MRK, FILE_APPEND);
						else 
						echo $isOK."\r";
					$MRK = '';
					}
				
				}
			fclose($fp);
			}
		}
		
	

$workTime = $imp->startTime - time();	
echo "___________________________________________________________\n";
echo number_format($imp->totalRec,0,'','.').' records saved to solr in '.$imp->WorkTime($workTime)."                                          \n\n";

// echo "Reindexing spellcheck step 1\n";
// file_get_contents("http://localhost:8983/solr/lite.biblio/select?q=*:*&spellcheck=true&spellcheck.build=true");
// echo "Reindexing spellcheck step 2\n";
// file_get_contents("http://localhost:8983/solr/lite.biblio/select?q=*:*&spellcheck.dictionary=basicSpell&spellcheck=true&spellcheck.build=true");

if (file_exists($imp->outPutFolder.'counter.txt'))
	unlink ($imp->outPutFolder.'counter.txt');






?>
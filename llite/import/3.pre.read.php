<?php
require_once('./config/db.php');
require_once('./functions/klasa.importer.3.php');
require_once('./functions/klasa.buffer.php');
require_once('./functions/klasa.wikidata.php');
require_once('./functions/klasa.pgsql.php');
include('./import/config.php');

$imp = new importer();
$imp->register('psql', new postgresql($psqldb));
#$imp->register('buffer', new marcbuffer());

$destination_path = $imp->setDestinationPath('./files');

$errorFile = './import/errors/recWithError.mrk';
$imp->workingMode = 'checking';

$Tmap 			= $imp->getConfig('language_map2');
$Trole			= $imp->getConfig('creative_roles_map');

$imp->fileToConfig('donotgeocode','csv');

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
$total = count($list);

if (is_array($list))
	foreach ($list as $file) {
		$lp++;
		$dname = str_replace($source_path,'',$file);
		echo "\n$lp/$total reading: \e[94m$dfile\e[0m                             \n";
		
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
					$isOK = $imp->checkRecord($record);
					echo $isOK."\r";
					$MRK = '';
					}
				
				}
			fclose($fp);
			}
		}
echo "\n\n";
$imp->saveCheckingResults();

$workTime = $imp->startTime - time();	
echo "___________________________________________________________\n";
echo number_format($imp->totalRec,0,'','.').' records readed in '.$imp->WorkTime($workTime)."                                          \n\n";







?>
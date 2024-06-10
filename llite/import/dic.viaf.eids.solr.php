<?php
$lineSeparators = ['|', '@'];

$commitStep = 100000;

require_once('functions/class.pgsql.php');
require_once('functions/class.importer.php');
require_once('functions/class.viafSearcher.php');
require_once('functions/class.solr.php');
include('config/db.php');

$imp = new importer();
# $imp->register('psql', new postgresql($psqldb));
$imp->register('solr', new solr($imp));
$imp->register('viafSearcher', new viafSearcher($imp));

$lp = 0;
$glob = glob("./import/tests/viaf-*links.txt");
if (is_array($glob)) {
	$file = end($glob);
}
echo "reading: $file\n";

$viaf = $lastviaf = '';
$row = [];
$Tmatch = [];

$fp = @fopen($file, "r");
$fs = filesize($file);
$buffSize = 0;

if ($fp) {
	 
	while (($buffer = fgets($fp, 8192)) !== false) {
		$buffSize += strlen($buffer);
		$lp++;

		$line = explode("\t", $buffer);
		$viaf = $imp->viafFromStr($line[0]);
		if (empty($lastviaf)) $lastviaf = $viaf;
		
		if ($viaf !== $lastviaf) {
			$imp->viafSearcher->saveIdsToSolr($lastviaf, $row, false);
			$lastviaf = $viaf;
			$row = [];
			}
		
		foreach ($lineSeparators as $sep) {
			$idKey = $id = '';
			if (stristr($line[1], $sep)) {
				$id_tmp = explode($sep, $line[1]);
				$idKey = $id_tmp[0];
				$id = trim($id_tmp[1]);
				break;
				}
			}
		$row[$idKey][$id] = $id;
					
		$workTime = time() - $imp->startTime;
		echo $lp.' ('.round(($buffSize/$fs)*100).'%)  '.$imp->WorkTime($workTime).'  '.$viaf."   \t\t\t\t\r";	
		if ($lp % $commitStep == 0) {
			$imp->viafSearcher->client->commit();
			echo "commiting to solr\r";
			}
		}
	$imp->viafSearcher->saveIdsToSolr($lastviaf, $row, $imp);
	$imp->viafSearcher->client->commit();
	$imp->viafSearcher->client->optimize();
	fclose($fp);
	}




	
?>

all done!


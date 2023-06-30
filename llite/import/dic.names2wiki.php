<?php 

/*
autocheck for updates:
https://viaf.org/viaf/data/viaf-20230206-links.txt.gz

*/

require_once('./config/db.php');
require_once('./functions/klasa.importer.psql.php');
require_once('./functions/klasa.pgsql.php');


$psql = new postgresql($psqldb2);
$imp = new importer();

$lp = 0;
$file = './import/data/placeNames2wiki.csv';
$fp = @fopen($file, "r");
$fs = filesize($file);
$buffSize = 0;
$savedList = [];

if ($fp) {
	echo "starting... \n";
	$psql->query("TRUNCATE TABLE lib_dic_places2wiki;");
	while (($buffer = fgets($fp, 8192)) !== false) {
		$buffSize += strLen($buffer);
		
		$lp++;
		$line = str_getcsv($buffer);
		$placeName = $imp->clearName($line[0]);
		$wikiq = $line[3];
		echo $lp.' ('.round(($buffSize/$fs)*100).'%)  '.$placeName.' -> '.$wikiq."                     \r";
		if (!empty($wikiq) && ($wikiq<>'\N') && empty($savedList[$placeName])) {
			$savedList[$placeName] = $wikiq;
			$Q = "INSERT INTO lib_dic_places2wiki (name, wikiq) VALUES ({$psql->isNull($placeName)}, {$psql->isNull($wikiq)});";
			$psql->query($Q);
			}
		if ($lp % 1000 == 0)
			$psql->query("COMMIT;");
		
		}
	fclose($fp);
	}


?>

ALL DONE!

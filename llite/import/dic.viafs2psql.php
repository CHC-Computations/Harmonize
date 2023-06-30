<?php 

/*
autocheck for updates:
https://viaf.org/viaf/data/viaf-20230206-links.txt.gz

*/


require_once('functions/klasa.pgsql.php');

include('config/db.php');
$psql = new postgresql($psqldb2);

if (file_exists('import/tests/viaf-wiki.csv'))
	unlink ('import/tests/viaf-wiki.csv');

$lp = 0;
$file = 'import/tests/viaf-links.txt';
$fp = @fopen($file, "r");
$fs = filesize($file);
$buffSize = 0;;
if ($fp) {
	$psql->query("TRUNCATE TABLE lib_dic_viaf2wiki;");
	while (($buffer = fgets($fp, 8192)) !== false) {
		$buffSize += strLen($buffer);
		if (stristr($buffer, 'WKP|Q')) {
			$lp++;
			$line = explode('WKP|Q',$buffer);
			$wikiId = trim($line[1]);
			$viafId = str_replacE('http://viaf.org/viaf/', '', trim($line[0]));
			echo $lp.' ('.round(($buffSize/$fs)*100).'%)  '.$viafId.' -> '.$wikiId."                     \r";
			file_put_contents('import/tests/viaf-wiki.csv', $viafId.';'.$wikiId."\n", FILE_APPEND);
			
			$Q = "INSERT INTO lib_dic_viaf2wiki (viaf, wikiq, lastcheck) VALUES ({$psql->isNull($viafId)}, {$psql->isNull($wikiId)}, now());";
			$psql->query($Q);
			#echo $Q."\n";
			if ($lp % 1000 == 0)
				$psql->query("COMMIT;");
			}
			
		}
	fclose($fp);
	}


?>

ALL DONE!

<?php 
require_once('./functions/klasa.wikidata.php');

$fileName = './import/outputfiles/country_years.csv';

$file = file($fileName);
foreach ($file as $line) {
	if (!empty($buffor[$line])) {
		#echo 'Q'.$in[0].' + '.$in[1].' = '.$buffor[$line]." buffor\n";
		} else {
		$in = explode('|', $line);
		echo 'Q'.$in[0].' + '.$in[1].' = ';
		$wiki = new wikidata('Q'.$in[0]);
		$country = $wiki->getHistoricalCountry($in[1]);
		if (is_array($country))
			$res = current($country);
			else 
			$res = $country;
		$buffor[$line] = $res;
		echo $res."\n";
		file_put_contents('./import/outputfiles/country_years_filed.csv', 'Q'.$in[0].'|'.$in[1].'|'.$res."|\n", FILE_APPEND);
		}
	}
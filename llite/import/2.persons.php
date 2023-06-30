<?php 
require_once('./config/db.php');
require_once('./functions/klasa.importer.2.php');
require_once('./functions/klasa.pgsql.php');
require_once('./functions/klasa.helper.php');
require_once('./functions/klasa.wikidata.php');
require_once('./functions/klasa.buffer.php');


include('config.php');
$imp = new importer();
$imp->mdb($mdb); 
$imp->register('psql', new postgresql($psqldb));
$imp->register('buffer', new marcbuffer());
$imp->register('helper', new helper());

echo "start at: ".date("H:i:s")."\n";
$res = $imp->psql->query("TRUNCATE TABLE persons;");
$res = $imp->psql->query("UPDATE places_on_map SET personhits=0;");
#$res = $imp->psql->query("TRUNCATE TABLE lite_persons_croles;");

$lp = 0;

	$file = 'import/outputfiles/persons.csv';
	
	$fp = @fopen($file, "r");
	$fs = filesize($file);
	$buffSize = 0;
	#echo "Total Results: ".count($fp)."\n";
	if ($fp) {
		while (($buffer = fgets($fp, 8192)) !== false) {
			$lp++;
			$buffSize += strLen($buffer);
			$part = str_getcsv ($buffer, ';');
			$p = new stdclass;
			$p->key 		= $part[0];
			#$p->solr_str	= $part[1];
			$p->name 		= $part[1];
			$p->name_sort 	= $imp->str2url($p->name);
			$p->year_born 	= intval($part[2]);
			$p->year_death 	= intval($part[3]);
			$p->viaf_id 	= substr($part[4],0,30);
			$p->wikiq 		= str_replace('Q','',$part[5]);
			$p->date 		= $part[6];
			$p->role 		= $part[7];
			$p->solr_str 	= $part[1].'|'.$part[2].'|'.$part[3].'|'.$part[4].'|'.$part[5].'|'.$part[6];
			/* 
				'skey' 		=> $skey,					#0
				'name'		=> $ndesc['name'],			#1
				'year_born'	=> $ndesc['year_born'],		#2
				'year_death'=> $ndesc['year_death'],	#3
				'viaf_id'	=> $ndesc['viaf_id'],		#4
				'wiki_id'	=> $ndesc['wiki_id'],		#5
				'date'		=> $ndesc['date'],			#6
				'field'		=> $as,						#7
				'role'		=> $roles					#8
			*/
			
			if ($p->year_born == 0) 
				$p->year_born = '';
			if ($p->year_death == 0) 
				$p->year_death = '';
			if (!empty($part[8]))
				$croles = explode(',',trim($part[8]));
				else 
				$croles = ['Unknown'];
			foreach ($croles as $role) {
				if (empty($Trol[$p->key][$role]))
					$Trol[$p->key][$role] = 1;
					else 
					$Trol[$p->key][$role]++;
				}
				
			if (stristr($p->name, '>>')) {
				$tmp = explode('>>', $p->name);
				$p->name = trim($tmp[1]);
				}
			
			$p->rec_total = 1;
			$p->as_author = $p->as_author2 = $p->as_topic = 0;
			switch ($p->role) {
				case 'author':
					$p->as_author = 1;
					break;
				case 'author2':
					$p->as_author2 = 1;
					break;
				case 'topic' :
					$p->as_topic = 1;
					break;
				}
			
			
			if (empty($Tres[$p->key]))
				$Tres[$p->key] = $p;
				else {
				$Tres[$p->key]->as_author += $p->as_author;
				$Tres[$p->key]->as_author2 += $p->as_author2;
				$Tres[$p->key]->as_topic += $p->as_topic;
				$Tres[$p->key]->rec_total += $p->rec_total;
				}
			$p->c_date = date("Y-m-d H:i:s");	
				
			echo 'counting records: '.round(($buffSize/$fs)*100).'%  recNo:'.$imp->helper->numberFormat($lp).".          \r";
			}
		fclose($fp);
		 
		
		echo "\n saving to sql \n";
		echo "records to save: ".count($Tres)."\n";
		$lp = 0;
		foreach ($Tres as $k=>$p) 
			if (($k<>'00000000') && !empty($p->viaf_id)) {
				if (!empty($p->wikiq)) {
					$wiki = new wikidata('Q'.$p->wikiq);
					$p->place_born = str_replace('Q', '', $wiki->getPropId('P19'));  
					$p->place_death = str_replace('Q', '', $wiki->getPropId('P20')); 
					
					if (!empty($p->place_born))
						$imp->psql->query(checkWiki($imp, $p->place_born));
					if (!empty($p->place_death))
						$imp->psql->query(checkWiki($imp, $p->place_death));
					
					$p->name_search = $wiki->getSearchString();
					
					} else 
					$p->name_search = $p->name_sort;
				
				
				$lp++;
				$proc = round(($lp/count($Tres))*100);
				echo $imp->helper->numberFormat($lp).". ({$proc}%) ".substr($p->name.str_repeat(' ',50),0,50)."         \r";
				
				$fields = ['viaf_id', 'wikiq', 'name', 'name_sort', 'year_born', 'year_death', 'place_born', 'place_death', 'rec_total', 'as_author', 'as_author2', 'as_topic', 'solr_str', 'name_search', 'c_date', 'm_date'];
				$Tset = $Tk = $Tv = [];
				
				$t = $imp->psql->querySelect("SELECT * FROM persons WHERE viaf_id = {$imp->psql->isNull($p->viaf_id)};");
				if (is_array($t)) {
					$old = current($t);
					$p->rec_total += $old['rec_total'];
					$p->as_author += $old['as_author'];
					$p->as_author2 += $old['as_author2'];
					$p->as_topic += $old['as_topic'];
					$tmp = explode('|', $old['solr_str']);
					$tmp[] = $p->solr_str;
					$p->solr_str = implode('|', array_unique($tmp));
					
					$tmp = explode(', ', $old['name_search']);
					$tmp2 = explode(', ',$p->name_search);
					$p->name_search = implode(',', array_unique(array_merge($tmp,$tmp2)));
					
					foreach ($fields as $field) {
						if (!empty($p->$field)) {
							$Tset[$field] = $field.'='.$imp->psql->isNull($p->$field);
							}
						}
					$q = 'UPDATE persons SET '.implode(', ',$Tset).' WHERE viaf_id = '.$imp->psql->isNull($p->viaf_id).';';
					$imp->psql->query($q);	
					} else {
					foreach ($fields as $field) {
						if (!empty($p->$field)) {
							$Tk[$field] = $field;
							$Tv[$field] = $imp->psql->isNull($p->$field);
							}
						}
					$q = "INSERT INTO persons (".implode(', ',$Tk).") VALUES (".implode(', ',$Tv).");";
					$imp->psql->query($q);
					}
				#echo implode("\n", $q);
				}
		
		/*
		$lp = 0;
		echo "\n saving to croles \n";
		foreach ($Trol as $key=>$arr)
			foreach ($arr as $role=>$counter) {
				$lp++;
				echo "$lp.          \r";
				$imp->psql->query($Q = "INSERT INTO lite_persons_croles (skey, role, counter) VALUES ({$imp->isNULL($key)}, {$imp->isNULL($role)}, {$imp->isNULL($counter)});");
				#file_put_contents('saveRoles.csv', $Q."\n", FILE_APPEND);
				}
		
		*/
		
		}

echo "\nfinished at: ".date("H:i:s")."\n";


function checkWiki($imp, $wikiCheck) {
	$wikiCheck = intval($wikiCheck);
	$t = $imp->psql->querySelect("SELECT * FROM places_on_map WHERE wikiq='{$wikiCheck}';");
	if (is_array($t))
		return "UPDATE places_on_map SET personhits=personhits+1 WHERE wikiq='{$wikiCheck}';";
		else {
		$wikiPlace = new wikidata('Q'.$wikiCheck);	
		$pfields = ['wikiq', 'lon', 'lat', 'personhits', 'name', 'names', 'sstring'];
		$newPlace = new stdclass;
		$newPlace->wikiq = $wikiCheck;
		$coor = $wikiPlace->getCoordinates('P625');
		if (!empty($coor)) {
			$newPlace->lon = $coor->longitude;
			$newPlace->lat = $coor->latitude;
			$newPlace->personhits = 1;
			$newPlace->name = $wikiPlace->get('labels') ;
			$newPlace->names = $wikiPlace->getAllNamesStr();
			$newPlace->sstring = $wikiPlace->getSearchString();
			foreach ($newPlace as $k=>$v) {
				$Tk[$k] = $k;
				$Tv[$k] = $imp->psql->isNull($v);
				}
			return  "INSERT INTO places_on_map (".implode(', ',$Tk).") VALUES (".implode(', ',$Tv).");";
			}
		}
	return "COMMIT;";
	}


?>
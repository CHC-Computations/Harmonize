<?php
echo "\n";
require_once('./config/db.php');
require_once('./functions/klasa.importer.psql.php');
require_once('./functions/klasa.pgsql.php');

$imp = new importer();
$imp->register('psql', new postgresql($psqldb2));
$source_path = '/var/www/html/lite/import/data';

$destination_path = $imp->setDestinationPath('./files');

##################################################################
### cleaning db - the order matters!
 
$idxTables = [
		'biblio_swords', 
		'biblio_places', 
		'genre_major', 
		'languages',
		'genre_sub',
		'genre_subject',
		'literature_nation_subject',
		'subjects',
		'subject_centureis',
		'udccode',
		'source_doc',
		'source_db_sub'
		]; 
					
		
foreach ($idxTables as $tname) {
	$test = false;
	$t = $imp->psql->querySelect("SELECT EXISTS (SELECT FROM pg_tables WHERE schemaname = 'public' AND tablename  = 'lib_idx_{$tname}');");
	if (is_Array($t))
		$test = current($t)['exists'];
	
	if ($test=='f') {
		$q[] = "CREATE SEQUENCE public.lib_dic_{$tname}_id_seq AS integer START WITH 1 INCREMENT BY 1 NO MINVALUE NO MAXVALUE CACHE 1;";
		$q[] = "CREATE TABLE public.lib_dic_{$tname} (id integer DEFAULT nextval('public.lib_dic_{$tname}_id_seq'::regclass) NOT NULL, name name);";
		$q[] = "ALTER TABLE public.lib_dic_{$tname} OWNER TO {$psqldb2['user']};";
		$q[] = "ALTER TABLE ONLY public.lib_dic_{$tname} ADD CONSTRAINT lib_dic_{$tname}_name_key UNIQUE (name);";
		$q[] = "ALTER TABLE ONLY public.lib_dic_{$tname} ADD CONSTRAINT lib_dic_{$tname}_pkey PRIMARY KEY (id);";
		$q[] = "CREATE TABLE public.lib_idx_{$tname} (id_biblio name NOT NULL, id_dic integer NOT NULL);";
		$q[] = "ALTER TABLE public.lib_idx_{$tname} OWNER TO {$psqldb2['user']}; ";
		$q[] = "ALTER TABLE ONLY public.lib_idx_{$tname} ADD CONSTRAINT id_idx_genre_{$tname} PRIMARY KEY (id_biblio, id_dic);";
		echo implode("\n", $q)."\n\n" ;
		die;
		$res = $imp->psql->query(implode("\n", $q));
		} else 
		$res = $imp->psql->query("TRUNCATE TABLE lib_idx_{$tname};");
	}
	
$lstTables = [
		'persons_functions', 
		'persons_roles', 
		'persons',
		'places_relations', 
		'events', 
		'biblio'
		];  
foreach ($lstTables as $tname)
	$res = $imp->psql->query("TRUNCATE TABLE lib_lst_{$tname};");

### end cleaning db 
##################################################################



$Tmap 	= $imp->getConfig('language_map2');
$Trole	= $imp->getConfig('creative_roles_map');

echo "Languages Map: ".count($Tmap)."\n";
echo "Creative roles Map: ".count($Trole)."\n";
echo "____________________________________\n\n";

## cleaning old tmp files 
$list = glob ($imp->outPutFolder.'*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file);
## cleaning errors files 
$list = glob ('errors/*.*');
if (is_array($list))
	foreach ($list as $file)
		unlink ($file); 



## creating tmp files with indexes
echo "Searching for a files in $source_path\n";
$list = glob ($source_path.'/*.mrk');
$imp->fullFileSize = 0;
$imp->buffSize = 0;
			
if (is_array($list))
	foreach ($list as $file) 
		$imp->fullFileSize+=filesize($file);
echo "Files to import: ".count($list)."\n";
$FLP = 0;
if (is_array($list))
	foreach ($list as $file) {
		$FLP++;
		echo "$FLP. reading: \e[94m$file\e[0m                            \n";
		$imp->saveError("reading file; $file");
		
		$fname = str_Replace($source_path, '', $file);
		$imp->setFileName($fname);
		
		$results = [];
		$record = '';
		$errMRK = '';
		
		$fp = @fopen($file, "r");
		if ($fp) {
			while (($buffer = fgets($fp, 8192)) !== false) {
				$part = $imp->mrkLine($buffer);
				$imp->buffSize +=strlen($buffer);
				$errMRK .= $buffer;
				if (is_array($part)) {
					if (key($part) == 'LDR') {
						$record = $imp->newRecord($part);
						} else if (key($part) == '001') 
							$id = $imp->recordId($record, $part);
							else 
							$imp->recordAddValue($record, $part);				
							
					} else if (is_array($record)) {
						######### zapisz gdy trafisz na pustą linijkę
						echo $imp->savePsqlRecord($record);
						$json = $imp->saveJsonFile($record);  
						# $imp->saveAllFields($record);  
						# $mkr  = $imp->saveMKRFile($errMRK);  
						if ($imp->saveStatus == 'error')
							file_put_contents('errors/recWithError.mrk', $errMRK, FILE_APPEND);
						$errMRK = '';
						unset($record);
						}
					
				}
			fclose($fp);
			}
		
		######### zapisz gdy kończysz z plikiem 	 
		if (!empty($record) && is_array($record)) {
			echo $imp->savePsqlRecord($record);
			$json = $imp->saveJsonFile($record);  
			# $mkr  = $imp->saveMKRFile($errMRK);  
			if ($imp->saveStatus == 'error')
				file_put_contents('errors/recWithError.mrk', $errMRK, FILE_APPEND);
			$errMRK = '';
			}
		}
		
	

$workTime = $imp->startTime - time();	
echo "All done in ".$imp->WorkTime($workTime)."                                          \n\n";


if (!empty($imp->psql->errors)) {
	file_put_contents('errors/psql.txt', implode("\n", $imp->psql->errors));
	echo "\n\nerrors:\n".implode("\n", $imp->psql->errors);
	}

?>



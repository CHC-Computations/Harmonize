<?php

class importer {
	
	
	
	public function __construct() {
		$this->config = new stdClass;
		$this->config->maxErrorFiles = 10;
		$this->startTime = time();
		
		$this->solrUrl = 'http://localhost:8983/solr/lite.biblio/update';
		$this->config->commitStep = 2000;  
		
		$this->lp = 0;
		$this->blp = 0;
		$this->lastLen = 0;
		
		$this->config->ini_folder = "./config/import/";
		$this->outPutFolder = './import/outputfiles/';
		$this->recFormat = 'unknown';
		
		setlocale(LC_ALL, 'pl_PL');
		}
	
	public function workTime() {
		return date("H:i:s", (time() - $this->startTime)+82800).' ';
		}
	
	public function mdb($o = []) {
		$dsn = 'mdsql:dbname=vufind;host=loacalhost;port=3306;charset=utf8';
		$connection = mysqli_connect($o['host'], $o['user'], $o['password'], $o['dbname']);
		$this->sql = $connection;
		}
	
	function leadingZeros($count, $length) {
		$strlen = strlen($count);
		if ($length>$strlen)
			return str_repeat('0', $length-$strlen).$count;
		return $count;
		}
	
	function setDestinationPath($path) {
		return $this->destinationPath = $path;
		}
		
	function setFileName($name) {
		$this->currentFileName = $name;
		}
 
 
	function setRecord($json) {
		$this->record = new stdClass;
		$this->record->marcFields = $json;
		}
	
	
	public function getConfig($iniFile) {
		if (!empty($this->config->$iniFile)) {
			return $this->config->$iniFile;
			}
		
		$fullFileName = $this->config->ini_folder.$iniFile.'.ini';
		if (file_exists($fullFileName)) {
			$this->config->$iniFile = parse_ini_file($fullFileName, true);
			
			return $this->config->$iniFile;
			} else 
			return [];
		}	
	
	function register($name, $var) {
		$this->$name = $var;
		}
	
	function setTable($tablename, $tablevalues) {
		$this->$tablename = $tablevalues;
		}
	
	function myList(&$res, $indexName, $input) {
		if (array_key_exists($indexName, $this->TEXT_INDEXES)){
			if (is_array($input))
				foreach ($input as $val)
					$res[] = $val;	
				else 
				$res[] = $input;
			}
		}
	
	
	function setCurrentRecord($record) {
		$this->record = new stdClass;
		$this->record->marcFields = $record;
		}
	
	function psqlDicSimple($dicName, $value) {
		if (empty($value))
			return null;
		$key = str_replace("'",'',$this->psql->isNull($value));
		if (empty($this->dictionary[$dicName][$key])) {
			$t = $this->psql->querySelect("SELECT id FROM lib_dic_{$dicName} WHERE name={$this->psql->isNull($value)};");
			if (is_array($t)) {
				$this->dictionary[$dicName][$key] = current($t)['id'];
				} else {
				$id = $this->psql->nextVal('lib_dic_'.$dicName.'_id_seq');	
				$this->work->psqlQueries[] = "INSERT INTO lib_dic_{$dicName} (id, name) VALUES ($id, {$this->psql->isNull($value)});";
				$this->dictionary[$dicName][$key] = $id;
				}
			}  
		return $this->dictionary[$dicName][$key];
		}
	
	function addSimleIndex($indexName, $function) {
		$id = $this->currentId;
		$res = $this->$function();
		if (is_string($res)) {
			$id_dic = $this->psqlDicSimple($indexName, $res);
			if (!empty($id_dic) && empty($this->work->idx[$indexName][$id_dic])) {
				$this->work->psqlQueries[] = "INSERT INTO lib_idx_{$indexName} (id_biblio, id_dic) VALUES ({$this->psql->isNull($id)}, {$this->psql->isNull($id_dic)});";
				$this->work->idx[$indexName][$id_dic] = $id_dic;
				}
			}
		if (is_array($res) && (count($res)>0)) {
			$res = array_unique($res);
			foreach ($res as $value) {
				$id_dic = $this->psqlDicSimple($indexName, $value);
				if (!empty($id_dic) && empty($this->work->idx[$indexName][$id_dic])) {
					$this->work->psqlQueries[] = "INSERT INTO lib_idx_{$indexName} (id_biblio, id_dic) VALUES ({$this->psql->isNull($id)}, {$this->psql->isNull($id_dic)});";
					$this->work->idx[$indexName][$id_dic] = $id_dic;
					}
				}
			}
		}
	
	
	function savePsqlRecord($record) {
		$this->lp++;
		$this->work->psqlQueries = [];
		if (!empty($record['001']))
			$id = $record['001'][0];
			else 
			return $this->lp++.' error\n';
		
		$this->setCurrentRecord($record);
		$this->currentId = $id;
		$isOK = 'error';
		$isOK = 'ok';
		
		file_put_contents($this->outPutFolder.'counter.csv', $this->lp."\n".$id."\n" );
		
		
		if (empty($this->savedID[$id])) {
			$this->work->psqlQueries[] = "INSERT INTO lib_lst_biblio (id, title_short, title_search, source_db, major_format, pub_year) VALUES ({$this->psql->isNull($id)}, {$this->psql->isNull($this->getTitleShort())}, {$this->psql->isNull($this->getTitleSort())}, {$this->psql->isNull($this->psqlDicSimple('source_db', $this->getMarcFirstStr(995,'a')))}, {$this->psql->isNull($this->psqlDicSimple('major_format', $this->getMajorFormat()))}, {$this->psql->isNull($this->getPublishDate())} );";
			$this->savedID[$id] = $id;	
			
			
			$this->addSimleIndex('genre_major', 'getGenreMajor');
			$this->addSimleIndex('genre_sub', 'getGenreSub');
			$this->addSimleIndex('genre_subject', 'getSubjectELBg');
			$this->addSimleIndex('literature_nation_subject', 'getSubjectELBn');
			$this->addSimleIndex('subject_centureis', 'getCenturies');
			$this->addSimleIndex('source_doc', 'getSourceDocument');
			$this->addSimleIndex('source_db_sub', 'getSourceDBsub');
			$this->addSimleIndex('udccode', 'getUDC');
			
				
			$this->addLanguages();	
			$this->addPersons();	
			$this->addSubjects();
			$this->addPlaces();
			$this->addCorporates();
			
			
			$this->addSWords();
			
			if ($this->psql->query(implode('',$this->work->psqlQueries)) == 0) {
				echo "\nerror saving: ".$this->currentId."\n";
				$this->saveError($id.';error saving;'.implode(";", $this->work->psqlQueries).';');
				}
			#file_put_contents($this->outPutFolder.$this->leadingZeros($this->blp,6).'psqlPart.sql', implode("\n",$this->work->psqlQueries)."\n", FILE_APPEND);
			} else 
			$this->saveError($id.';ID already exists;');	
		
		if ($this->lp % $this->config->commitStep == 0) {
				$this->blp++;
				echo "Commmiting updates to PSQL                                          \r";
				$this->psql->query('COMMIT;'); 
				}
		
		
		$workTime = time()-$this->startTime;
		$this->lastLen = strlen($id);
		$this->saveStatus = $isOK;
		
		$returnStr = number_format($this->lp,0,'','.').". \e[92m".round(($this->buffSize/$this->fullFileSize)*100)."%\e[0m  rec: (".$id.")    ".$this->WorkTime($workTime)." s. ";
		if (strlen($returnStr)<70)
			return $returnStr.str_repeat(' ',70-strlen($returnStr))."\r";
			else 
			return $returnStr."\r";	
			
		}
	
		
	###########################################################  field gettin
	
	function mrkLine($line) {
		$line = trim(chop($line));
		if (substr($line,0,1) == '=') {
			$field = substr($line,1,3);
			$data = substr($line, 6);
			if (($field == 'LDR')or(substr($field,0,2) == '00'))
				return [$field => $data];
				else {
				$tmp = explode('$',$data);
				$ind1 = substr($data, 0, 1);
				$ind2 = substr($data, 1, 1);
				unset ($tmp[0]);
				$arr = [];
				foreach ($tmp as $part) {
					$subfield = substr($part, 0, 1);
					$value = substr($part, 1);
					if (!array_key_exists($subfield, $arr)) 
						$arr[$subfield] = $value;
						else if (!is_array($arr[$subfield])){
							$oldval = $arr[$subfield];
							$arr[$subfield] = [];
							$arr[$subfield][]=$oldval;
							$arr[$subfield][]=$value;
							} else 
							$arr[$subfield][]=$value;	
					}
				
				return [$field => [
					'ind1' => $ind1,
					'ind2' => $ind2,
					'code' => $arr
					]];	
				}
			} else 
			return null;
		}
	
	
	
	function newRecord($part) {
		$this->work = new stdClass;
		return ['LEADER' => $part['LDR']];
		}
	
	function recordId(&$record, $part) {
		$val = current($part);
		#$record['ID'] = $val;
		$record[key($part)][] = $val;
		return $val;
		}
	
	function recordAddValue(&$record, $part) {
		$val = current($part);
		$record[key($part)][] = $val;
		}
	
	
	function saveIndex($indexname, $id, $value) {
		$fp = fopen($this->outPutFolder.$indexname.'.csv', 'a');
		if (is_array($value)) {
			foreach ($value as $val) {
				fputcsv($fp, [$id,$val]);
				}
			} else if ($value<>'') {
				fputcsv($fp, [$id,$value]);
				}
		}
	
	function saveAllFields($record) {
		$id = $record['001'][0];
		foreach ($record as $field=>$arr) 
			if (!is_Array($arr))
				$this->saveIndex($field,$id,$arr);
				else 
				foreach ($arr as $content) 
					if (!is_array($content)) 
						$this->saveIndex($field,$id,$content);
						else 
						if (!empty($content['code']))
							foreach ($content['code'] as $subF=>$value)
								if (!is_array($value))
									$this->saveIndex($field.'-'.$subF,$id,$value);
									else 
									foreach ($value as $val)
										$this->saveIndex($field.'-'.$subF,$id,$val);
		}
	
	function saveJsonFile($record, $fname = '') {
		$this->recFormat = 'json';
		$destination_path = $this->destinationPath;
		$fname = $this->currentFileName;
		
		$id = $record['001'][0];
		$record['hiddenfield']['sourceFile'] = $fname;
		$record['hiddenfield']['dataEdited'] = date("Y-m-d H:i:s");
		$json = json_encode($record, JSON_INVALID_UTF8_SUBSTITUTE);
		
		$sk = substr($id,0,5);
		if (!is_dir("$destination_path/json/$sk")) {
			mkdir("$destination_path/json/$sk");
			chmod("$destination_path/json/$sk", 0775);
			} 

		$fj = "$destination_path/json/$sk/$id.json";
		file_put_contents($fj, $json);
		chmod("$fj", 0775);
		return $json;
		}
		
	function saveMRKFile($id, $record) {
		$this->recFormat = 'mrk';
		$destination_path = $this->destinationPath;
		
		$sk = substr($id,0,5);
		if (!is_dir("$destination_path/mrk/$sk")) {
			mkdir("$destination_path/mrk/$sk");
			chmod("$destination_path/mrk/$sk", 0775);
			} 

		$fj = "$destination_path/mrk/$sk/$id.mrk";
		$status = file_put_contents($fj, $record);
		chmod("$fj", 0775);
		return json_decode($status);
		}
	
	function getMRKFile($id, $record) {
		$this->recFormat = 'mrk';
		return $record;
		}
	
	function saveSolrUpdateFile($record, $fname = '', $postdata) {
		$destination_path = $this->destinationPath;
		$id = $record['001'][0];
		
		$sk = substr($id,0,5);
		if (!is_dir("$destination_path/solr/$sk")) {
			mkdir("$destination_path/solr/$sk");
			chmod("$destination_path/solr/$sk", 0775);
			} 

		$fj = "$destination_path/solr/$sk/$id.json";
		file_put_contents($fj, $postdata);
		chmod("$fj", 0775);
		return json_decode($postdata);
		}
	
	
	
	###########################################################################################################################################################################
	###########################################################################################################################################################################
	###########################################################################################################################################################################
	###########################################################################################################################################################################
	###########################################################################################################################################################################
	###########################################################################################################################################################################
	
	

	
	public function getMarcLine($field, $subfields=array(), $sep=' ', $sepLn='<br/>') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			$result = '';
			
			foreach ($line as $row) {
				$codes = array();
				if (!empty($row['code'])) {
					foreach ($row['code'] as $code=>$val) {
						if (count($subfields)>0) {
							if (in_array($code,$subfields))
								$codes[] = $val;
							} else 
							$codes[] = $val;
						}
					$result .= implode($sep, $codes).$sepLn;
					} 
				if (!is_array($row))
					$result .= $row.$sepLn;
				}
				
			return $result;	
			} else 
			return null;
		}
	
	public function getMarcFirst($field, $subfields=array(), $sep=' ') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			$result = '';
			
			$row = (array)current($line);
			
			$codes = array();
			if (!empty($row['code'])) {
				foreach ($row['code'] as $code=>$val) {
					if (count($subfields)>0) {
						if (in_array($code,$subfields))
							$codes[$code] = $val;
						} else 
						$codes[$code] = $val;
					}
				} 
			if (!is_array($row))
				$codes = $row;
			
				
			return $codes;	
			} else 
			return null;
		} 	
		
	public function getMarcFirstStr($field, $subfields=array(), $sep=' ') {
		if (!is_array($subfields))
			$subfields = (array)$subfields;
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			$result = '';
			
			$row = (array)current($line);
			
			$codes = array();
			if (!empty($row['code'])) {
				foreach ($row['code'] as $code=>$val) {
					if (count($subfields)>0) {
						if (in_array($code,$subfields))
							$codes[] = $val;
						} else 
						$codes[] = $val;
					}
				foreach ($codes as $k=>$v)
					if (is_Array($v))
						$codes[$k] = implode($sep, $v);
					
				$result .= implode($sep, $codes);
				} 
			if (!is_array($row))
				$result .= $row;
			
			$result = trim(chop($result));	
			if ($result=='')
				return null;
				else 
				return $result;	
			} else 
			return null;
		} 
	
	public function getMarcArr($field, $subfields) {
		
		}
	
	public function getLeader() {
		return $this->record->marcFields['LEADER'];
		}

	public function getTitle() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b']));
		
		
		if ($title == '[Název textu k dispozici na připojeném lístku]') {
			$title = "[Title on the picture (retrobi record)]"; 
			}
		
		if (!empty($title))
			return $title;
			// return mb_convert_encoding($title, 'UTF-8', 'ISO-8859-1');
			else 
			return "[no title]";
		}
		
	public function getTitleFull() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b', 'c']));
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
	public function getTitleSort() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b', 'c']));
		if (!empty($title))
			return $this->str2url($title);
			else 
			return "[no title]";
		}
		
	public function getTitleSub() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['b']));
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
		
	public function getTitleShort() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a']));
		if (!empty($title)) {
			if ($title == '[Název textu k dispozici na připojeném lístku]') {
				$title = "[Title on the picture (retrobi record)]"; 
				}
			return $title;
			} else 
			return "[no title]";
		}

		
	public function getSourceDBsub() {
		$desc = $this->getMarcFirstStr('995', ['b']);
		if (!empty($desc))
			return [$desc];
			else 
			return null;
		}
		
	public function getUDC() {
		$Tres = [];
		$field = '080';
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['a'])) {
					if (!is_array($sf['code']['a'])) {
						$code = $this->onlyNumbers($sf['code']['a']);
						$l = substr($code,0,1);
						if ($l==5) {
							$k = substr($code,1,1);
							if ($k==1)
								$Tres['udc_51'] = 'udc_51';
								else 
								$Tres['udc_5x'] = 'udc_5x';
							} else if (is_numeric($l) && ($l<>4))
								$Tres['udc_'.$l] = 'udc_'.$l;
						} else {
						foreach ($sf['code']['a'] as $code) {
							$code = $this->onlyNumbers($code);
							$l = substr($code,0,1);
							if ($l==5) {
								$k = substr($code,1,1);
								if ($k==1)
									$Tres['udc_51'] = 'udc_51';
									else 
									$Tres['udc_5x'] = 'udc_5x';
								} else if (is_numeric($l) && ($l<>4))
									$Tres['udc_'.$l] = 'udc_'.$l;
							}		
						}
					}
				}
			}
		
		if (count($Tres) == 0)
			return ['Undefined'];
			else 
			return $this->removeArrayKeys($Tres);
		}
	
	private function yearToCentury($year) {
		$year = floatval($year);
		return floor(($year+99)/100);
		}
	
	private function yearToCenturyF($year) {
		$year = floatval($year);
		return floor(($year+100)/100);
		}
	
	private function isBC($str) {
		$bcStrings = ['pne', 'přkr', 'př kr', 'bc', 'ekr'];
		foreach ($bcStrings as $needle)
			if (stristr($str, $needle)) return true;
		return false;
		}
		
	
	public function getCenturies() {
		$finCent = ['yso/fin'];
		$errorText = -99999;
		$strToRemove = [
					'od ',
					'-luku',
					'-talet',
					'-luvut',
					'.'
					];

		$field = '648';
		$id = $this->record->marcFields['001']['0'];
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['a']) && is_string($sf['code']['a'])) {
						$era = str_replace($strToRemove, '', strtolower($sf['code']['a']));
					
					
					
					if (!empty($sf['code']['2'])) 
						$format = $sf['code']['2'];
						else
						$format = '';
					$centFunction = 'yearToCentury'; // default method of counting century
					if (in_array($format, $finCent))  
						$centFunction = 'yearToCenturyF'; // suomi method of counting century
							
					if (substr($era,0,1)=='-')
						$era = substr($era,1);
					$per = explode('-', $era);
					
					if (count($per)>1) {
						if (stristr($era, 'století')) {
							$cent1 =  floatval($per[0]);
							$cent2 =  floatval($per[1]);
							} else {
							$cent1 = $this->$centFunction($per[0]);
							$cent2 = $this->$centFunction($per[1]);
							}
						
						if (($cent1>99)or($cent2>99)) return $errorText; 
						
						if ($this->isBC($per[1])) {
							$cent1 = -$cent1;
							$cent2 = -$cent2;
							}
						if ($this->isBC($per[0])) {
							$cent1 = -$cent1;
							}
						
						// expections
						if (($cent1 > $cent2) & ($cent2<100))
							$res[$cent1] = $cent1;
							else 
						if ($cent1 > $cent2)
							$res[$errorText] = $errorText;
							else 
						if ($cent2-$cent1>21) // remove if range too large
							$res[$errorText] = $errorText;
							else 
						if ($cent1 == $cent2)
							$res[$cent1] = $cent1;
							else 
						// regular return
						for ($i = $cent1; $i <= $cent2; $i++) 
							if ($i<>0)
								$res[$i] = $i;
						} else {
						$cent = $this->$centFunction($era);
						if ($this->isBC($era)) {
							$cent = -$cent;
							}
						if (($cent>99)or($cent==0)) return $errorText;
						$res[$cent] = $cent;
						}
					}
				}
			}
		if (!empty($res)) {
			
			if ((count($res)>1) & array_key_exists($errorText, $res))
				unset ($res[$errorText]);
			
			return array_unique($res);
			} else 
			return [$errorText];
		}
	
	
	public function preparePerson($desc, $field = null, $as = null) {
		
		$personLine['croles'] = [];
		$personLine['functions'] = [];
		$personLine['name'] = 
		$personLine['date_range'] = 
		$personLine['year_born'] = 
		$personLine['year_death'] =
		$personLine['viaf'] = 
		$personLine['wikiq'] = '';
		
		if (!empty($desc['a'])) {
			if (is_Array($desc['a'])) {
				$personLine['name'] = $desc['a'][0];
				if (($field == 100)or($field==700)) 
					$personLine['functions'][$desc['a'][1]] = $desc['a'][1]; 
				} else 
				$personLine['name'] = $desc['a'];
			
			if (!empty($desc['d'])) {
				if (is_array($desc['d']))
					$desc['d']=implode(' ',$desc['d']);
				$personLine['date_range'] = $desc['d'];
				$tmp = explode('-', str_replace(['(',')'], '', $desc['d']));
				
				if (!empty($tmp[0]))
					$personLine['year_born'] = intval($tmp[0]);
					else 
					$personLine['year_born'] = '';	
				if (!empty($tmp[1]))
					$personLine['year_death'] = intval($tmp[1]);
					else 
					$personLine['year_death'] = '';	
				if ($personLine['year_death'] == 0) 
					$personLine['year_death'] = '';
				if ($personLine['year_born'] == 0) 
					$personLine['year_born'] = '';
				}
			
			if (!empty($desc['1'])) {
				if (is_array($desc['1']))
					$desc['1'] = implode(' ',$desc['1']);
				$personLine['viaf'] = $this->viafFromStr( $desc['1'] ); 
				if (!empty($ndesc['viaf_id']))
					$personLine['wikiq'] = $this->viaf2wiki( $personLine['viaf'] );
				}
			
			if (!empty($desc['4'])) {
				if (is_Array($desc['4'])) {
					foreach ($desc['4'] as $role) {
						$personLine['croles'][$this->transleteCrativeRoles($role)] = $this->transleteCrativeRoles($role);
						}
					} else {
					$personLine['croles'][$this->transleteCrativeRoles($desc['4'])] = $this->transleteCrativeRoles($desc['4']);
					}
				}  
			if (!empty($desc['e'])) {
				if (is_Array($desc['e'])) {
					foreach ($desc['e'] as $role) {
						$personLine['croles'][$this->transleteCrativeRoles($role)] = $this->transleteCrativeRoles($role);
						}
					} else {
					$personLine['croles'][$this->transleteCrativeRoles($desc['e'])] = $this->transleteCrativeRoles($desc['e']);
					}
				}
			if (empty($personLine['croles']))
				$personLine['croles'][] = 'Unknown';
			
			
			
			if (!empty($personLine['viaf']))
				$skey = $this->shortHash($personLine['viaf']);
				else 
				$skey = $this->shortHash($personLine['name'].$personLine['date_range']);
			$personLine['pkey'] = $skey;
			
			if (stristr($personLine['name'], '>>')) {
				$tmp = explode('>>', $personLine['name']);
				$personLine['name'] = trim($tmp[1]);
				}
			
			$this->work->persons[$skey][$as] = $personLine;
			return $personLine;	
			}
		}	
	
	
	public function bufferPlace($name, $relation) {
		$sname = $this->str2url($name);
		if (empty($this->work->places[$sname])) {
			$this->work->places[$sname]['name'] = $name;
			$this->work->places[$sname]['wikiq'] = $this->getWiki4Name($name);
			} 
		$this->work->places[$sname]['relations'][$relation] = $relation;	
		}
	
	public function addPlaces() {
		$fields = [
			260 => 'publication',
			264 => 'publication',
			651	=> 'subject'
			];
		// + events places colleted in addEvents	
		
		// data colletion 
		foreach ($fields as $field => $relation) {
			if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					if (!empty($sf['code']['a'])) {
						if (is_Array($sf['code']['a'])) {
							foreach ($sf['code']['a'] as $z) 
								$this->bufferPlace($z, $relation);
							} else 
							$this->bufferPlace($sf['code']['a'], $relation);
						}
					}
				}
			}
		
		// data saving (collected now and in other functions) 
		if (!empty($this->work->places) && (count($this->work->places)>0)) {
			foreach ($this->work->places as $placeKey => $placeParams) {
				$placeName = $placeParams['name'];
				$this->work->psqlQueries[] = "INSERT INTO lib_idx_biblio_places (id_biblio, name) VALUES ('{$this->currentId}', {$this->isNull($placeName)});";
				if (!empty($placeParams['wikiq']) && !empty($praceParams['relations'])) {
					foreach ($placeParams['relations'] as $relation) 
						$this->work->psqlQueries[] = "INSERT INTO lib_lst_places_relations (wikiq, relation, table, ext_id) VALUES ({$this->isNull($placeParams['wikiq'])}, {$this->isNull($this->psqlDicSimple('places_relations', $relation))}, 'biblio', '{$this->currentId}');";
					}
				}	
			}
		}


	public function addSWords() {
		if (!empty($this->work->swords) && (count($this->work->swords)>0)) {
			foreach ($this->work->swords as $word => $weight) {
				}	
			}
		}
	
	public function addPersons() {

		$fields = [
			100 => 'Main author',
			700 => 'Co-author',
			600 => 'Subject person'
			];
		
		foreach ($fields as $field => $role)
			if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					##################### working with person ###################################
					$desc = (array)$sf['code'];
					$authors[] = $this->preparePerson($desc, $field, $role);
					}
				}
		if (!empty($this->work->persons)) 
			foreach ($this->work->persons as $pkey=>$persons)
				foreach ($persons as $role=>$personLine) {
					$croles = $personLine['croles'];
					$functions = $personLine['functions'];
					unset ($personLine['croles']);
					unset ($personLine['functions']);
					$pkey = $personLine['pkey'];
					
					
					if (empty($this->savedPersons[$pkey])) {
						$personLine['name_sort'] = $this->str2url($personLine['name']);
						foreach ($personLine as $k=>$v) {
							if (is_array($v)) {
								file_put_contents($this->outPutFolder.'personsError.csv', $this->currentId."\n".print_r($personLine,1) );
								}
							$Tk[$k] = $k;
							$Tv[$k] = $this->psql->isNull($v); // error! array given
							}
						$this->work->psqlQueries[] = "INSERT INTO lib_lst_persons (".implode(', ',$Tk).") VALUES (".implode(', ',$Tv).");";
						$this->savedPersons[$pkey] = $pkey;
						}
						
					if (count($croles)>0) 
						foreach ($croles as $crole)
							$this->work->psqlQueries[] = "INSERT INTO lib_lst_persons_roles (id_biblio, pkey, role, crole) VALUES ('{$this->currentId}', '$pkey', {$this->psql->isNull($this->psqlDicSimple('person_roles', $role))}, {$this->psql->isNull($crole)});";
					if (count($functions)>0) 
						foreach ($functions as $function)
							$this->work->psqlQueries[] = "INSERT INTO lib_lst_persons_functions (id_biblio, pkey, function) VALUES ('{$this->currentId}', '$pkey', {$this->psql->isNull($function)});";
					}
		}
	
	
	
	public function transleteCrativeRoles($code) {
		$Tmap = $this->config->creative_roles_map;
		if (!empty($Tmap[$code])) { 
			#echo $Tmap[$code].".";
			return $Tmap[$code];
			} else {
			#echo $code.".";
			return $code;
			}
		}
	

	public function isNULL ($val) {
		$val=chop(trim($val));
		$val=str_replace("'",'',$val);
		if ($val=='')
			return 'NULL';
			else 
			return "'$val'";
		}
	
	public function shortHash($str) {
		return hash('crc32b', $str);
		}
	
	function viafFromStr($str) {
		if (stristr($str, 'viaf'))
			return $this->onlyNumbers($str); 
		}
	
	function viaf2wiki($viafId) {
		if (empty($this->viafs[$viafId])) {
			$t = $this->psql->querySelect("SELECT wikiq FROM lib_dic_viaf2wiki WHERE viaf='$viafId';");
			if (is_array($t)) {
				$res = current($t);
				$this->viafs[$viafId] = $res['wikiq'];
				return $res['wikiq'];
				} 
			} else 
			return $this->viafs[$viafId];
		}
	
	function getWiki4Name($name) {
		$name = $this->clearName($name);
		if (empty($this->wikiNames[$name])) {
			$t = $this->psql->querySelect("SELECT wikiq FROM lib_dic_places2wiki WHERE name={$this->psql->isNull($name)};");
			if (is_array($t)) {
				$res = current($t);
				$this->wikiNames[$name] = $res['wikiq'];
				$return = $res['wikiq'];
				}
			} else 
			$return = $this->wikiNames[$name];
		
		if (!empty($return)) 
			return ['name' => $name, 'wikiq' => $return];
		}
	
	
	public function personInitials($desc) {
			
		$ndesc['name'] = '';
		if (!empty($desc['a'])) {
			if (is_Array($desc['a']))
				$name = implode(' ',$desc['a']);
				else 
				$name = $desc['a'];
			$name = str_replace('-', ' ', $name);
			$init = explode(' ', $name);
			foreach ($init as $str)
				$nt[]=strtolower(substr($str,0,1));
			krsort($nt);
			if (is_array($ndesc))	
				return implode(' ',$nt).' '.implode('',$nt);
				else 
				return $init;
			}
		}
	
	
	public function addCorporates() {
		$fields = [
			110 => 'main author',
			710 => 'co-author',
			610 => 'as subject',
			260 => 'as publisher',
			264 => 'as publisher'
			];
		$lp = 0;
		foreach ($fields as $field => $relation ) {
			if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					$lp++;
					foreach ($sf['code'] as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								file_put_contents($this->outPutFolder.'corporates.txt', $this->currentId.' '.$field.$k.' '.print_r($z,1)."\n", FILE_APPEND);
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] =  $this->setLength($sz,200);
										}
									} else {	
									$uri[] = $this->setLength($z,200);
									} 
							}
						
						}	
					}
				}
			}
		
								
		}
	
	public function getCorporateAuthor() {
		#$fields = [110,111,710,711];
		$fields = [
			110 => 'main author',
			710 => 'co-author',
			610 => 'as subject',
			260 => 'as publisher',
			264 => 'as publisher'
			];
		// dodaj też miejsca 
		$fields = [110,610,710];
		
		$cauthors = [];
		$lp = 0;
		foreach ($fields as $field) {
			$desc = $this->getMarcFirst($field);
			
			if (!empty($desc['a']) && is_string($desc['a'])) {
				$corp = [
					'name' => '',
					'viaf' => '',
					'wiki' => ''
					]; 
				$corp['name'] = $desc['a'];
				if (!empty($desc['1'])) {
					$corp['viaf'] = $this->viafFromStr( $desc['1'] );
					$corp['wiki'] = $this->viaf2wiki($corp['viaf']);
					}
				$cauthors[] = implode('|', $corp); 
				}
			if (!empty($desc['a']) && is_array($desc['a'])) 
				foreach ($desc['a'] as $k=>$v) {
					$corp = [
						'name' => '',
						'viaf' => '',
						'wiki' => ''
						];
				
					$corp['name'] = $v;
					if (!empty($desc['1'][$k])) {
						$corp['viaf'] = $this->viafFromStr( $desc['1'][$k] );
						$corp['wiki'] = $this->viaf2wiki($corp['viaf']);
						}
					$cauthors[] = implode('|', $corp); 
					}
			}
		return $cauthors;
		}
	

	public function getMajorFormat() {
		$formats = [
			'a' => 'Book chapter',
			'b' => 'Journal article',
			'm' => 'Book'
			];
		$code = substr($this->getLeader(), 7, 1);	
		if (array_key_exists($code, $formats))
			return $formats[$code];
			else 
			return 'Other';
		}
	
	public function getFormat() {
		$formats = [
			'ab' => 'Article',
			'am' => 'Book',
			'aa' => 'Book Chapter',
			'cm' => 'Musical Score', 
			'km' => 'Photo', 
			'as' => 'Serial', 
			'em' => 'Map',
			'gb' => 'Slide',
			
			'mb' => 'Computer file (Serial component part)',
			'im' => 'Nonmusical sound recording (Monograph/Item)',
			'om' => 'Kit (Monograph/Item)',
			'gm' => 'Projected medium (Monograph/Item)',
			];
		$code = substr($this->getLeader(), 6, 2);	
		if (!empty($this->GetMarcFirst(111)))
			$ret[] ='Conference Proceeding';
		if (array_key_exists($code, $formats))
			$ret[] = $formats[$code];
			else 
			$ret[] = "unknown ($code)";
		if (count($ret)==1)
			return current($ret);
			else 
			return $ret;
		}

	public function translateLangCode($code) {
		$code = substr($code, 0, 3);
		#$code = $this->str2url($code);
		#echo "LangCode: $code - ".ord(substr($code,0,1))."\n\n";
			
		$Tmap = $this->config->language_map2;
		if (!empty($Tmap[$code])) { 
			#echo $Tmap[$code].".";
			#file_put_contents($this->outPutFolder.'langCodesOK.csv', $code."\t".$Tmap[$code]."\n", FILE_APPEND);
			return $Tmap[$code];
			} else {
			file_put_contents($this->outPutFolder.'langCodesEmpty.csv', $code."\n", FILE_APPEND);
			return 'undefined';	
			if (substr($code,0,1) == chr(92))
				return 'undefined';
				else 
				return $code;
			}
		}
	
	public function isAlfa($str) {
		return preg_match('/^[a-ząćęłńóśźż]+$/ui', $str);
		}
	
	public function addLanguages() {
		$field = '008';
		$langsP = [];
		$langsO = [];
		if (is_array($this->record->marcFields)) {
			$marcFields = $this->record->marcFields;
			if (!empty($this->record->marcFields[$field][0]))  {
				$z = substr($this->record->marcFields[$field][0],35,3);
				if ($this->isAlfa($z))	
					$langsP[$z] = $z;	
				}
			
			$field = '041';
			if (!empty($marcFields[$field]))  {
				
				foreach ($marcFields[$field] as $sf) {
					if (!empty($sf['code']['a']) && is_Array($sf['code']['a']))
						foreach ($sf['code']['a'] as $z) {
							if ($this->isAlfa($z))	
								$langsP[$z] = $z;	
							}
					if (!empty($sf['code']['a']) && is_string($sf['code']['a'])) {
						$z = $sf['code']['a'];
						if ($this->isAlfa($z))	
							$langsP[$z] = $z;	
						}
					
					if (!empty($sf['code']['h']) && is_Array($sf['code']['h']))
						foreach ($sf['code']['h'] as $z) {
							if ($this->isAlfa($z))	
								$langsO[$z] = $z;	
							}
					if (!empty($sf['code']['h']) && is_string($sf['code']['h'])) {
						$z = $sf['code']['h'];
						if ($this->isAlfa($z))	
							$langsO[$z] = $z;	
						}
					
					}
				}
			
			$field = '040';
			if (!empty($marcFields[$field]))  {
				
				foreach ($this->record->marcFields[$field] as $sf) {
					if (!empty($sf['code']['b']) && is_Array($sf['code']['b']))
						foreach ($sf['code']['b'] as $z) {
							if ($this->isAlfa($z))	
								$langsP[$z] = $z;	
							}
					if (!empty($sf['code']['b']) && is_string($sf['code']['b'])) {
						$z = $sf['code']['b'];
						if ($this->isAlfa($z))	
							$langsP[$z] = $z;	
						}
					}
				}
			
			if (count($langsO)>0) 
				foreach ($langsO as $lang)
					if (!empty($lang))
						$this->work->psqlQueries[] = "INSERT INTO lib_idx_languages (id_biblio, value, role) VALUES ('{$this->currentId}', '$lang', {$this->psql->isNull($this->psqlDicSimple('lang_roles', 'Original language'))});";
			if (count($langsP)>0) 
				foreach ($langsP as $lang)
					if (!empty($lang))
						$this->work->psqlQueries[] = "INSERT INTO lib_idx_languages (id_biblio, value, role) VALUES ('{$this->currentId}', '$lang', {$this->psql->isNull($this->psqlDicSimple('lang_roles', 'Publication language'))});";
			}
		}
		
		
	public function getPublishDate() {
		$field = '008';
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field][0])) ) {
			$res = $this->onlyNumbers(substr($this->record->marcFields[$field][0],7,4));
			if (!empty($res)) return $res;
			}
				
		$field = '264';
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['c'])) {
					if (is_Array($sf['code']['c'])) {
						foreach ($sf['code']['c'] as $c) {
							$res = $this->onlyNumbers($c);
							if (!empty($res)) return $res;
							}									
						} else {
						$res = $this->onlyNumbers($sf['code']['c']);
						if (!empty($res)) return $res;
						}
					}
				}
			}	
			
		$field = '260';
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['c'])) {
					if (is_Array($sf['code']['c'])) {
						foreach ($sf['code']['c'] as $c) 
							if (!empty($this->onlyNumbers($c)))
								return $this->onlyNumbers($c);	
						}
						else if (!empty($this->onlyNumbers($sf['code']['c'])))
							return $this->onlyNumbers($sf['code']['c']);
					}
				}
			}
		
		#$this->saveError( $this->currentId.";publishing date not found;" );
		return null;	
		}
		
	function saveError($msg) {
		file_put_contents($this->outPutFolder.'errors.csv', $msg."\n", FILE_APPEND);
		}	
		
		
	public function getGenreMajor() { 
		$field = '380';
		$res = [];
		
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['i'])&&($sf['code']['i'] == 'Major genre')&& !empty($sf['code']['a']))
					if (is_Array($sf['code']['a'])) {
						foreach ($sf['code']['a'] as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf['code']['a'];
				}
			}
		if (count($res)==0)
			$res[]='Undefined';
		return $res;	
		}	
		
		
		
	public function getGenreSub() { 
		$field = '381';
		$res = [];
		
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['i'])&&($sf['code']['i'] == 'Major genre')&& !empty($sf['code']['a']))
					if (is_Array($sf['code']['a'])) {
						foreach ($sf['code']['a'] as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf['code']['a'];
				}
			}
			
		return $res;	
		}	
	
	public function getGenre() { 
		$field = '655';
		$res = [];
		
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['a']))
					if (is_Array($sf['code']['a'])) {
						foreach ($sf['code']['a'] as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf['code']['a'];
				}
			}
			
		return $res;	
		}	
	
	public function clearName($z) {
		if (is_string($z))
			return trim(str_replace(['[',']',':',';','(',')'], '', $z));
		if (is_array($z)) {
			foreach ($z as $v)
				$Tr[] = trim(str_replace(['[',']',':',';','(',')'], '', $v));
			return implode(', ',$Tr);	
			}
		return $z;
		}
	

	
	public function prepareEvent($code, $relation) {
		$event = new stdClass;
		$event->relation = $relation;
		if (!empty($code['a'])) $event->name = $code['a'];
		if (!empty($code['d'])) $event->date = $this->getYearsFromStr($code['d']); // array
		if (!empty($code['c'])) {
			$event->place = $this->removeLonelyBrackets($code['c']); 
			$this->bufferPlace($event->place, 'event');
			}
		if (!empty($code['n'])) $event->edition = $this->onlyNumbers($code['n']); // integer
		return $event;
		}
	

	
	public function addEvents() {
		$fields = [
			111 => 'author',
			611 => 'subject',
			711	=> 'co-author'
			];
		$events = [];
		foreach ($fields as $field=>$relation)
			if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
				foreach ($this->record->marcFields[$field] as $sf) 
					if (!empty($sf['code'])) {
						$code = $sf['code'];
						$events[] = $this->prepareEvent($code, $relation);
						}
				}
		if (count($events)>0) {
			// jeszcze myśle jak to sesnownie zapisać 
			}		
		}
	
	

	public function getPublished() {
		$field = '260';
		$res = [];
		$lp = 0;
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				$res[$lp] = '';
				foreach ($sf['code'] as $z) {
					if (is_array($z))
						$res[$lp] .= ' '.implode(' ',$z);
						else 
						$res[$lp] .= ' '.$z;
					}	
				}
			$lp++;
			}
			
		return $res;	
		}
	
	
	
	public function getEdition() {
		$field = '250';
		$res = [];
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) 
			if (is_array($this->record->marcFields[$field]))
				foreach ($this->record->marcFields[$field] as $sf) 
					if (!empty($sf['code']) && is_array($sf['code']))
						foreach ($sf['code'] as $z) 
							$res[] = $z;
			
		return implode(' ',$res);	
		}
	
		
		
	public function getIn() {
		$field = '773';
		$res = [];
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				$sres = [];
				foreach ($sf['code'] as $k=>$z) {
					if (is_Array($z))
						$z = implode(', ', $z);
					switch ($k) {
						
						case 'x' : 
							$sres[]= 'ISSN '.$z; 
							$this->record->ISSN = $z;
							break;
						case 'q':
						case 'w':
						case '7':
						case 's':
						case 'i':
						case '9':
							break;
						default: $sres[]= $z; break;
						}
					
					}	
				$res[] = implode(' ',$sres);	
				}
			}
		return implode(' ',$res);	
		}
	
	public function getMagazines() { 
		$res = null;
		if ($this->getMajorFormat() == 'Journal article') {
			$field = '773';
			if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					$sres = [];
					if (!empty($sf['code']['s']))
						$sres['name'] = $sf['code']['s'];
						else if (!empty($sf['code']['t']))
							$sres['name'] = $sf['code']['t'];
					
					if (!empty($sf['code']['x']))
						$sres['issn'] = $sf['code']['x'];
							
					if (!empty($sres['name']))
						$res[] = $sres;	 
					}
				}
			}
		return $res;	
		
		}
		

	
	

	
	public function getRefferedWork() {
		$field = 787;
		$rec = [];
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($sf['code']['a'])) {
					$rec[] = $sf['code']['a'];
					} 
			return $rec;		
			}
		}
	
	public function getSeria() {
		$field = 490;
		$rec = [];
		$lp = 0;
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			
			$row = (array)current($line);
			foreach ($line as $row) {
				$lp++;
				$ln = [];
				if (!empty($row['code']['a']) && is_array($row['code']['a'])) 
					$ln[] = implode(' ',$row['code']['a']);
				if (!empty($row['code']['a']) && !is_array($row['code']['a'])) 
					$ln[] = $row['code']['a'];
				
				if (!empty($row['code']['v']) && is_array($row['code']['v']))  
					$ln[] = implode(' ', $row['code']['v']);
				if (!empty($row['code']['v']) && !is_array($row['code']['v']))  
					$ln[] = $row['code']['v'];
				if (is_array($ln))
					$rec[] = implode(' ', $ln);
					else 
					$rec[] = $ln;
				} 
				
			return $rec;		
			}
		}
	


	public function getYearsFromStr($str) {
		/*
		function return array of years
		if field contain place name it's given to $this->work->places table 
		Types of ways of writing the date solved by the function
					
			-2016
			(2010-2013)
			(2009 :
			(2017-2018 :
			(2016- :
			1977 :
			23. 10. - 10. 11. 1956
			2007/2008
			10.-12. 9. 1955

			2005  Warszawa
			2006, Wrocław
			2016-2017  Paryż

			11. - 13. září 2019
			December 17 - 19, 1982

		*/
		$years = [];
		$pattern = '/[\'\/~`\!@#\$%\^&\*\(\)_\-\+=\{\}\[\]\|;: "\<\>,\.\?\\\]/';
		$tmp = preg_replace($pattern, '|', trim($str));
		$array = explode('|', $tmp);
		foreach ($array as $word) {
			if (!empty($word)) {
				if (preg_match('/^[0-9]{4}$/', $word))
					$years[] = $word;
				}
			}
		// after foreach $word contain lastWord	
		// if the lastword is place adding to record places list
		if (preg_match('/[^\da-zA-Z]/', $word))	
			$this->bufferPlace($word, 'event');
	
		
		sort($years);
		if ((count($years)==2) && ($years[1] - $years[0] > 1)) {
			for ($i = $years[0]; $i <= $years[1]; $i++)
				$nyears[] = $i;
			$years = $nyears;
			}
		return $years;
		}

		
	public function addSubjects() {
		$res = [];
		$min = 601;
		$max = 699;
		$exceptions = [
			611, // in getEvents
			648, // in getCenturies
			651, // in getPlaces
			];
		$subFieldsIgnored = [
			'0', '2', '7'
			];
		$lp = 0;
		$subjects = [];
		for ($field = $min; $field<=$max; $field++) {
			if (!in_array($field,$exceptions) && is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					if (is_array($sf['code'])) {
						foreach ($sf['code'] as $k=>$z) {
							if (!in_array($k, $subFieldsIgnored)) {
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$subjects[] =  $this->setLength($sz, 59);
										}
									} else {	
									$subjects[] = $this->setLength($z, 59);
									} 
								}
							}
						}
					}
				}
			}
			
		if (count($subjects)>0)	{
			$subjects = array_unique($subjects);
			foreach ($subjects as $subject) 
				if (!empty($subject))
					$this->work->psqlQueries[] = "INSERT INTO lib_idx_subjects (id_biblio, id_dic) VALUES ('{$this->currentId}', {$this->psql->isNull($this->psqlDicSimple('subjects', $subject))});";
			}
		}
		
	
	public function getSubjects() {
		$res = [];
		$min = 601;
		$max = 699;
		$lp = 0;
		$uri = [];
		for ($field = $min; $field<=$max; $field++) {
			if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
				foreach ($this->record->marcFields[$field] as $sf) {
					$lp++;
					foreach ($sf['code'] as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								file_put_contents($this->outPutFolder.'subjects.txt', $this->currentId.' '.$field.$k.' '.print_r($z,1)."\n", FILE_APPEND);
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] =  $this->setLength($sz,200);
										}
									} else {	
									$uri[] = $this->setLength($z,200);
									} 
							}
						
						}	
					}
				}
			}
		$uri = array_unique($uri);
		#return $uri;	
		}
	
		
	public function getSubjectELB() {
		$field = 650;
		$res = [];
		
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty ($sf['code']['2']) && is_string ($sf['code']['2']) && (substr($sf['code']['2'],0,3) == 'ELB')) {
					$res[] = $sf['code']['a'];
					}
				}
			} 
		
		return $res;	
		}
	
	public function getSubjectELBg() {
		$field = 650;
		$res = [];
		
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['2']) && is_string ($sf['code']['2']) && (trim($sf['code']['2']) == 'ELB-g')) {
					$res[] = $sf['code']['a'];
					}
				}
			}
		
		return $res;	
		}
	
	public function getSubjectELBn() {
		$field = 650;
		$res = [];
		
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field])) ) {
			foreach ($this->record->marcFields[$field] as $sf) {
				if (!empty($sf['code']['2']) && is_string ($sf['code']['2']) && (trim($sf['code']['2']) == 'ELB-n')) {
					$res[] = str_replace(' literature', '', $sf['code']['a']);
					}
				}
			}
		
		return $res;
		}
	
	public function getISSN() {
		$fields = [
			'022'=>'a',
			'440'=>'x',
			'490'=>'x',
			'730'=>'x',
			'773'=>'x', 
			'776'=>'x',
			'780'=>'x',
			'785'=>'x'
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getArticleISSN() {
		$fields = [
			'773'=>'x',
			];
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getISBN() {
		// isbn = 020a:773z
		$fields = [
			'020'=>'a',
			'773'=>'z',
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return $res;
		
		}
	
	public function getSpellingShingle() {
		// author
		// title 
		// topic 
		
		}
		
		
	public function getSourceDocument() {
		$fields = [
			'773'=>'s',
			'773'=>'t',
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'',''))) {
				 str_replace('. -', '', $res);
				}
		
		}
	
	public function getOclcNum() {
		$field = '035';
		if (is_Array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row['code']['a'])) {
					$rec = $row['code']['a'];
					if (stristr($rec, '(OCoLC)'))
						return str_replace('(OCoLC)', '', $rec);
					} 
			}
		return null;
		}
	
	public function getCtrlNum() {
		$field = '035';
		$rec = [];
		if (is_array($this->record->marcFields) && (!empty($this->record->marcFields[$field]))) {
			
			$line = $this->record->marcFields[$field];
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row['code']['a'])) {
					$rec[] = $row['code']['a'];
					
					} 
			return $rec;		
			}
		}
	
	public function getWorkKey() {
		
		$author = $this->getMainAuthorSort();
		$title = $this->str2url($this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b'])));
		
		
		$author = preg_replace("/[^a-z]+/", "", strtolower($author));
		$title = preg_replace("/[^a-z]+/", "", strtolower($title));
	
		return "AT $author $title";
		}
	
	
	
	########################################   
	
	
	
	public function getCurrentTime() {
		return date("Y-m-d").'T'.date("H:i:s").'Z';
		}
		
	
	public function getFullMrc($id) {
		$file = file_get_contents('http://localhost/lite/import/marc21/getMRC.php?id='.$id);
		$this->recFormat = 'mrc';
		return $file;
		}
			
	public function getRecFormat() {
		return $this->recFormat;
		}
		
	public function registerMrk($rec) {
		$this->mrkFullRecord = $rec;
		}
	
	public function getSourceMrk() {
		$this->recFormat = 'mrk';
		if (!empty($this->mrkFullRecord))
			return $this->mrkFullRecord;
		}
		
	public function drawTextMarc() {
		$this->recFormat = 'mrk';
		if (!empty($this->record->marcFields)) {
			$result = 'LDR  '.$this->record->LEADER."\n";
			foreach ($this->record->marcFields as $field=>$subarr) {
				if (is_Array($subarr))
				foreach ($subarr as $row) {
					$codes = array();
					$value = $ind = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind = $row['ind1'];
						if (!empty($row['ind2']))
							$ind .= $row['ind2'];
							else 
							$ind .= ' ';
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]='$'.$code.implode('$'.$code, $val);
								else
								$codes[]='$'.$code.$val;
						$value = implode('', $codes);
						if ($ind=='')
							$ind = '  ';
						} 
					$result.="$field  $ind$value\n";
					}
				}
			
			return $result;	
			} else {
			return "no record loaded";	
			}
		}
		

	public function drawMarc() {
		if (!empty($this->record->marcFields)) {
			$result = '<table class="table table-striped">
					<thead><tr><td style="text-align:right"><b>LEADER</b></td><td colspan=3>'.$this->record->LEADER.'</td></tr></thead>
					<tbody>
					';
			foreach ($this->record->marcFields as $field=>$subarr) {
				if (is_Array($subarr))
				foreach ($subarr as $row) {
					$codes = array();
					$value = $ind = ''; 
					$row = (array)$row;
					if (!empty($row['ind1'])) {
						$ind = "<td>$row[ind1]</td>";
						if (!empty($row['ind2']))
							$ind .= "<td>$row[ind2]</td>";
							else 
							$ind .= "<td></td>";
						}
					if (!empty($row['code'])) {
						foreach ($row['code'] as $code=>$val) 
							if (is_array($val))
								$codes[]="<b>|$code</b> ".implode(" <b>|$code</b> ", $val);
								else
								$codes[]="<b>|$code</b> $val ";
						$value = "<td>".implode(' ', $codes)."</td>";
						if ($ind=='')
							$ind = "<td></td><td></td>";
						} 
					if (count($row)==1)
						$value="<td colspan=3>$row[0]</td>";
					if ($value=='')
						$value = '<td></td>';
					$result.="<tr>	
						<td style='text-align:right'><b>$field</b></td>
						$ind
						$value
						</tr>";
					}
				}
			$result.="</tbody></table>";
			
			
			return $result;	
			} else {
			return "no record loaded";	
			}
		}
	
	
	## function from:
	## https://gist.github.com/mayconbordin/2860547
	
	function progress_bar($done, $total, $info="", $width=50) {
		$perc = round(($done * 100) / $total);
		$bar = round(($width * $perc) / 100);
		return sprintf("%s%%[%s>%s]%s\r", $perc, str_repeat("=", $bar), str_repeat(" ", $width-$bar), $info);
		}
	
	
	public function currentDate() {
		return date("Y-m-d");
		}	
	public function currentTime() {
		return date("Y-m-d H:i:s");
		}
	
	private function removeLonelyBrackets($str) {
		if (!stristr($str, '(') & stristr($str, ')'))
			return str_replace(')', '', $str);
		if (stristr($str, '(') & !stristr($str, ')'))
			return str_replace('(', '', $str);
		}
	
	private function removeLastSlash($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,'/');
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
	
	private function removeLastComa($t1) {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1,',');
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
	
	function removeArrayKeys($array) {
		if (is_Array($array)) {
			$n_array = [];
			foreach ($array as $k=>$v)
				$n_array[] = $v;
			return $n_array;	
			}
		}
	
	function onlyNumbers($string) {
		#return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
		return preg_replace("/[^0-9]/", '', $string);
		}
	
	public function setLength($str, $len) {
		$wstr = $str;
		$wlen = strlen($str);
		
		if ($wlen>$len) {
			$tmp = explode(' ', $str);
			$z = count($tmp);
			for ($i = 0; $i<=$z; $i++) {
				$step = $z - $i;
				unset($tmp[$step]);
				$nstr = implode(' ', $tmp);
				if (strlen($nstr)<$len) {
					$str = $nstr;
					break;
					}
				}
			
			$str.='(...)';
			// $str = '<span title="'.$wstr.'">'.$str.'</span>';
			return $str;
			}
		return $str;
		}
	
	
	function str2url( $str, $replace = " " ){
        setlocale(LC_ALL, 'pl_PL.UTF8');
		$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str); // TRANSLIT
        $charsArr = array( '^', "'", '"', '`', '~');
        $str = str_replace( $charsArr, '', $str );
        $return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
        return str_replace(' ', $replace, $return);
        }	
	
	}



?>
<?php

class importer {
	
	
	
	public function __construct() {
		$this->config = new stdClass;
		$this->config->maxErrorFiles = 10;
		$this->startTime = time();
		
		$this->settings = json_decode(@file_get_contents('./config/settings.json'));
		if (empty($this->settings)) {
			return "settings.json file not found";
			}
		
		$this->buffer = new stdclass;
		$this->buffer->wikiq = [];
		$solr = $this->settings->solr;
		$this->solrUrl = $solr->hostname.':'.$solr->port.'/solr/'.$solr->cores->biblio.'/update';
		$this->solrWikiUrl = $solr->hostname.':'.$solr->port.'/solr/'.$solr->cores->wiki.'/update';
		$this->config->commitStep = 5000;  
		
		$this->lp = 0;
		$this->totalRec = 0;
		$this->lastLen = 0;
		
		$this->config->ini_folder = "./config/import/";
		$this->outPutFolder = './import/outputfiles/';
		$this->recFormat = 'unknown';
		}
	
	public function workTime() {
		return date("H:i:s", (time() - $this->startTime)+82800).' ';
		}
	
	public function mdb($o = []) {
		$dsn = 'mdsql:dbname=vufind;host=loacalhost;port=3306;charset=utf8';
		$connection = mysqli_connect($o['host'], $o['user'], $o['password'], $o['dbname']);
		$this->sql = $connection;
		}
		
	public function fileToConfig($fileName, $format) {
		$file = file($this->config->ini_folder.$fileName.'.'.$format);
		$this->config->fileName = $file;
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
	
	
	function setDestinationPath($path) {
		return $this->destinationPath = $path;
		}
		
	function setFileName($name) {
		$this->lp = 0;
		$this->currentFileName = $name;
		}
 
	function getSourceFile() {
		return $this->currentFileName;
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
	
	
	function psqlDicSimple($dicName, $value) {
		if (empty($value))
			return null;
		if (empty($this->dictionary[$dicName][$value])) {
			$t = $this->psql->querySelect("SELECT id FROM lib_dic_{$dicName} WHERE name='$value';");
			if (is_array($t)) {
				$this->dictionary[$dicName][$value] = current($t)['id'];
				} else {
				$id = $this->psql->nextVal('lib_dic_'.$dicName.'_id_seq');	
				$this->psqlQueries[] = "INSERT INTO lib_dic_{$dicName} (id, name) VALUES ($id, '$value')";
				$this->dictionary[$dicName][$value] = $id;
				}
			}  
		return $this->dictionary[$dicName][$value];
		}
		
	
	function savePsqlRecord($record) {
		$this->lp++;
		$this->psqlQueries = [];
		if (!empty($record['001']))
			$id = $record['001'][0];
			else 
			return $this->lp.' error\n';
		
		$this->setCurrentRecord($record);
		$isOK = 'error';
		$isOK = 'ok';
		
		/*
		$this->getMainAuthor();
		$this->getOtherAuthors();
		$this->getSubjectPersons();
		*/
		
		#$t = $this->psql->querySelect("SELECT id FROM biblio WHERE id = '$id';");
		#if (!is_array($t))
		$this->psqlQueries[] = "INSERT INTO lib_lst_biblio (id, title_sort, title_search, source_db, major_format, major_genre, pub_year) 
					VALUES (
						{$this->psql->isNull($id)}, 
						{$this->psql->isNull($this->getTitleShort())}, {$this->psql->isNull($this->getTitleSort())}, 
						{$this->psql->isNull($this->psqlDicSimple('source_db', $this->getMarcFirstStr(995,'a')))},  
						{$this->psql->isNull($this->psqlDicSimple('major_format', $this->getMajorFormat()))},  
						{$this->psql->isNull($this->psqlDicSimple('major_genre', $this->getGenreM()))},  
						{$this->psql->isNull($this->getPublishDate())}  
						);";
		/*
		if (!empty($this->work->personsObject))
			foreach ($this->work->personsObject as $skey=>$line) {
				foreach ($line->role as $role)
					$q[] = "INSERT INTO biblio_persons_roles (id_biblio, skey, role, crole) 
								VALUES ({$this->psql->isNull($id)}, {$this->psql->isNull($line->skey)}, {$this->psql->isNull($line->field)}, {$this->psql->isNull($role)});";
				}
		*/
		$this->psql->query(implode(";\n",$this->psqlQueries));
		if ($this->lp % $this->config->commitStep == 0) {
				echo "Commmiting updates to PSQL                                          \r";
				$this->psql->query('COMMIT;'); 
				}
		
		
		$workTime = time()-$this->startTime;
		file_put_contents("outputfiles/counter.txt", $this->lp);
		$this->lastLen = strlen($id);
		$this->saveStatus = $isOK;
		return number_format($this->lp,0,'','.').". ".round(($this->buffSize/$this->fullFileSize)*100)."% rec: (".$id.")    ".$this->WorkTime($workTime)." s. ".str_repeat(' ',$this->lastLen)."  \r";
			
		}
	
	function saveCheckingResults() {
		
		if (!empty($this->buffer->wikiq) && count($this->buffer->wikiq)>0) {
			$lp = 0;		
			$max = count($this->buffer->wikiq);
			foreach ($this->buffer->wikiq as $wikiq=>$values) {
				$wikiId = 'Q'.$wikiq;
				$wiki = new wikidata($wikiId);
				$lp++;
				$solr_strings = $values['biblio_labels'];
				asort($solr_strings);
				$data = (object) ["id" => 'Q'.$wikiq];
				// $sss = [];
				// foreach ($solr_strings as $string=>$count)
					// $sss[] = $string;
				$sss = array_keys($solr_strings);
				$data->biblio_labels = (object) ["set" => $sss];	
				
				if (!empty($values['roles'])) 
					foreach ($values['roles'] as $as=>$biblioIds) {
						$fieldName = str_replace(' ','_',$as).'_count';
						$data->$fieldName = count($biblioIds); 
						switch ($as) {
							case 'total' : $data->biblio_count = count($biblioIds); break;
							case 'author' : $data->as_author = count($biblioIds); break;
							case 'author2' : $data->as_coauthor = count($biblioIds); break;
							case 'subject place' : 
							case 'topic' : $data->as_subject = count($biblioIds); break;
							case 'publication place' : $data->as_pub_place = count($biblioIds); break;
							case 'event place' : $data->as_event = count($biblioIds); break;
							}
						}
								
				$postdata = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
			
				file_put_contents($this->outPutFolder.'jsonupdates.json', $postdata."\n"); // , FILE_APPEND
				#  $json = $imp->saveSolrUpdateFile($destination_path, $record, $fname, $postdata);  // zapisz plik buffora  - może ta funkcja powinna trafić do klasy buffer?
				
				$ch = curl_init($this->solrWikiUrl); 
				curl_setopt($ch, CURLOPT_POST, 1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, '['.$postdata.']');
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
				curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
				$result = curl_exec($ch);
				$resDecoded = json_decode($result);
				if ($resDecoded->responseHeader->status == 0) {
					#echo "ok ";
					} else {
					echo "error ";	
					$g = glob ("./import/errors/*.*");
					if (count($g)<$this->config->maxErrorFiles) {
						#file_put_contents("./import/errors/$id.json", json_encode($record) );
						file_put_contents("./import/errors/{$id}_send.json", $postdata );
						file_put_contents("./import/errors/{$id}_res.json", $result );
						$isOK = 'error';
						}
					}
				curl_close($ch);
				
				$workTime = time()-$this->startTime;
				echo "Saving to solr: ".round(($lp/$max)*100.0)."% ".$this->WorkTime($workTime)." s. wiki: Q$wikiq             \r";
				
				if ($lp % $this->config->commitStep == 0) 
					file_get_contents($this->solrWikiUrl.'?commit=true');
				}
			}	
		file_get_contents($this->solrWikiUrl.'?commit=true');
		}
	
	function checkRecord() {
		
		$this->lp++;
		$this->totalRec++;
		$id = $this->id;
		
		
		if (!empty($this->record['LEADER'])) {
			$isOK = 'reading';
			################################ UPDATING SOLR - START
			
			$data = (object) ["id" => $id];	
			
			foreach ($this->settings->pretest->list as $indexName) {
				$indexSettings = $this->settings->facets->solrIndexes->$indexName;
				unset ($val);
				if (!empty($indexSettings->importFunction)) {
					$functionName = $indexSettings->importFunction;
					if (!empty($indexSettings->params))
						$val = $this->$functionName($indexSettings->params);
						else 
						$val = $this->$functionName();
					}
				if (!empty($indexSettings->importField)) {
					$field = substr($indexSettings->importField, 0, 3);
					$subfield = str_replace($field, '', $indexSettings->importField);
					$sub = [];
					$len = strlen($subfield)-1;
					for ($i = 0; $i<=$len; $i++)
						$sub[] = $subfield[$i];
					$val = $this->getMarcFirstStr($field, $sub);
					}
				if (!empty($val))
					$data->$indexName = (object) ["set" => $val];
				}
			
			
			} else {
			return "Some error with rec: $id.\n";
			file_put_contents("./import/errors/$id.json", json_encode($record) );
			$isOK = 'error';
			}
		
		$workTime = time()-$this->startTime;
		$returnStr = number_format($this->lp,0,'','.').". \e[92m".round(($this->buffSize/$this->fullFileSize)*100)."%\e[0m   ".$this->WorkTime($workTime)." s.  wikisIDs: ".count($this->buffer->wikiq);
					
		file_put_contents($this->outPutFolder."counter.txt", $this->lp."\n".$id);
		$this->lastLen = strlen($id);
		
		return $returnStr;
		
		}
		
		
	function saveRecord() {
		
		$this->lp++;
		$this->totalRec++;
		$id = $this->id;
		
		
		if (!empty($this->record['LEADER'])) {
			$isOK = 'reading';
			################################ UPDATING SOLR - START
			
			$data = (object) ["id" => $id];	
			
			foreach ($this->settings->facets->solrIndexes as $indexName=>$indexSettings) {
				unset ($val);
				if (!empty($indexSettings->importFunction)) {
					$functionName = $indexSettings->importFunction;
					if (!empty($indexSettings->params))
						$val = $this->$functionName($indexSettings->params);
						else 
						$val = $this->$functionName();
					}
				if (!empty($indexSettings->importField)) {
					$field = substr($indexSettings->importField, 0, 3);
					$subfield = str_replace($field, '', $indexSettings->importField);
					$sub = [];
					$len = strlen($subfield)-1;
					for ($i = 0; $i<=$len; $i++)
						$sub[] = $subfield[$i];
					$val = $this->getMarcFirstStr($field, $sub);
					}
				if (!empty($val))
					$data->$indexName = (object) ["set" => $val];
				}
			
			$postdata = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
			
			file_put_contents($this->outPutFolder.'jsonupdates.json', $postdata."\n"); // , FILE_APPEND
			#  $json = $imp->saveSolrUpdateFile($destination_path, $record, $fname, $postdata);  // zapisz plik buffora  - może ta funkcja powinna trafić do klasy buffer?
			
			$ch = curl_init($this->solrUrl); 
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, '['.$postdata.']');
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			$result = curl_exec($ch);
			$resDecoded = json_decode($result);
			if (!isset($resDecoded->responseHeader->status)) {
				echo "Solr not responding. Try to restart Solr and type Y to continue? (Y/N) - ";

				$stdin = fopen('php://stdin', 'r');
				$response = fgetc($stdin);
				if ($response != 'Y') {
				   echo "Aborted.\n";
				   exit;
					}
				} elseif ($resDecoded->responseHeader->status == 0) {
				#echo "ok ";
				} else {
				echo "error ";	
				$g = glob ("./import/errors/*.*");
				if (count($g)<$this->config->maxErrorFiles) {
					#file_put_contents("./import/errors/$id.json", json_encode($record) );
					file_put_contents("./import/errors/{$id}_send.json", $postdata );
					file_put_contents("./import/errors/{$id}_res.json", $result );
					$isOK = 'error';
					}
				}
			curl_close($ch);

			
			################################ UPDATING SOLR - END;
				
			if ($this->lp % $this->config->commitStep == 0) {
				echo "Commmiting updates to Solr                                          \r";
				file_get_contents($this->solrUrl.'?commit=true');
				}
			} else {
			return "Some error with rec: $id.\n";
			file_put_contents("./import/errors/$id.json", json_encode($record) );
			$isOK = 'error';
			}
		
		$workTime = time()-$this->startTime;
		$returnStr = number_format($this->lp,0,'','.').". \e[92m".round(($this->buffSize/$this->fullFileSize)*100)."%\e[0m  rec: (".$this->setLen($id,20).")    ".$this->WorkTime($workTime)." s. ";
					
		file_put_contents($this->outPutFolder."counter.txt", $this->lp."\n".$id);
		$this->lastLen = strlen($id);
		
		return $returnStr;
		
		}
	
	function setLen($str, $elen) {
		$len = $str;
		if ($len >= $elen)
			return substr($str, 0, $elen);
		return $str.str_repeat(' ',$elen-$len);
		}
	
	function fileSize($file) {
		$this->buffSize = 0;
		$this->fullFileSize = filesize($file);
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
		$this->record = ['LEADER' => $part['LDR']];
		}
	
	function recordId($part) {
		$val = current($part);
		#$record['ID'] = $val;
		$this->record[key($part)][] = $val;
		return $val;
		}
	
	function recordAddValue($part) {
		$val = current($part);
		$this->record[key($part)][] = $val;
		}
	
	public function mrk2json($mrk) {
		$this->buffSize += strlen($mrk);
		$this->mrk = $mrk;
		$Tmrk = explode("\n", $mrk);
		foreach ($Tmrk as $line) {
			$part = $this->mrkLine($line);
			if (is_array($part)) {
				if (key($part) == 'LDR') {
					$this->newRecord($part);
					} else if (key($part) == '001') 
						$this->id = $this->recordId($part);
						else 
						$this->recordAddValue($part);				
				} 
			}
		return json_encode($this->record, JSON_INVALID_UTF8_SUBSTITUTE);
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
	
	function saveAllFields() {
		$id = $this->record['001'][0];
		foreach ($this->record as $field=>$arr) 
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
	
	function saveJsonFile($fname = '') {
		$this->recFormat = 'json';
		$destination_path = $this->destinationPath;
		$fname = $this->currentFileName;
		
		$id = $this->record['001'][0];
		$this->record['hiddenfield']['sourceFile'] = $fname;
		$this->record['hiddenfield']['dataEdited'] = date("Y-m-d H:i:s");
		$json = json_encode($this->record, JSON_INVALID_UTF8_SUBSTITUTE);
		
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
	
	function getMRKFile() {
		$this->recFormat = 'mrk';
		return $this->mrk;
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
		
		if (is_array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
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
		if (is_array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
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
		if (is_array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
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
		return $this->record['LEADER'];
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
		if (!empty($title))
			return $title;
			else 
			return "[no title]";
		}
		
		
	public function getTitleAlt() {
		$field = '700';
		$roles = [];
		
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				if (!empty($sf['code']['t']) && is_Array($sf['code']['t']))
					foreach ($sf['code']['t'] as $z) {
						$roles[] = $z;	
						}
				}
			}
		return $roles;	
		}
		
		
	
	public function getDescription () {
		$desc = $this->getMarcLine('520', ['a']);
		
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	
	public function getStatmentOfResp() {
		$desc = $this->getMarcFirstStr('245', ['c']);
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	public function getSourceDocument() {
		$desc = $this->getMarcFirstStr('995', ['a']);
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
		
	public function getUDC() {
		$Tres = [];
		$field = '080';
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		$errorText = 'Undectectable';
		$strToRemove = [
					'od ',
					'-luku',
					'-talet',
					'-luvut',
					'.'
					];

		$field = '648';
		$id = $this->record['001']['0'];
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
					file_put_contents($this->outPutFolder.'centuries.csv', "$era | ".implode($res)."\n", FILE_APPEND); 	
					}
				}
			}
		if (!empty($res)) {
			if ((count($res)>1) & array_key_exists($errorText, $res))
				unset ($res[$errorText]);
			return $this->removeArrayKeys($res);
			} else 
			return ['Undefined'];
		}
	
	
	public function preparePerson($desc, $as = null) {
		
		$ndesc['role'] = 
		$ndesc['name'] = 
		$ndesc['date'] = 
		$ndesc['year_born'] = 
		$ndesc['year_death'] =
		$ndesc['viaf_id'] = 
		$ndesc['wiki_q'] = '';
		
		if (!empty($desc['a'])) 
			if (is_Array($desc['a']))
				$ndesc['name'] = implode(' ',$desc['a']);
				else 
				$ndesc['name'] = $desc['a'];
		$sdesc['name'] = $ndesc['name'];
		
		
		if (!empty($desc['d'])) {
			if (is_array($desc['d']))
				$desc['d']=implode(' ',$desc['d']);
			$sdesc['date'] = $desc['d'];
			$ndesc['date'] = $desc['d'];
			$tmp = explode('-', str_replace(['(',')'], '', $desc['d']));
			$ndesc['year_born'] = floatval($tmp[0]);
			if (!empty($tmp[1]))
				$ndesc['year_death'] = floatval($tmp[1]);
				else 
				$ndesc['year_death'] = '';	
			if ($ndesc['year_death'] == 0) 
				$ndesc['year_death'] = '';
			if ($ndesc['year_born'] == 0) 
				$ndesc['year_born'] = '';
			}
		
		$ndesc['viaf_id'] = '';
		if (!empty($desc['1'])) {
			if (is_array($desc['1']))
				$desc['1']=implode(' ',$desc['1']);
			$ndesc['viaf_id'] = $this->viafFromStr( $desc['1'] ); 
			$sdesc['viaf_id'] = $desc['1'];
			}
		
		$ndesc['role'] = '';
		if (!empty($desc['4'])) {
			if (is_Array($desc['4'])) {
				foreach ($desc['4'] as $role) {
					$desc['role'][$this->transleteCrativeRoles($role)] = $this->transleteCrativeRoles($role);
					}
				} else {
				$desc['role'][$this->transleteCrativeRoles($desc['4'])] = $this->transleteCrativeRoles($desc['4']);
				}
			}  
		if (!empty($desc['e'])) {
			if (is_Array($desc['e'])) {
				foreach ($desc['e'] as $role) {
					$desc['role'][$this->transleteCrativeRoles($role)] = $this->transleteCrativeRoles($role);
					}
				} else {
				$desc['role'][$this->transleteCrativeRoles($desc['e'])] = $this->transleteCrativeRoles($desc['e']);
				}
			}
		if (empty($desc['role']))
			$desc['role'][] = 'Unknown';
		$roles = implode(', ',$desc['role']);

		
		if (is_array($ndesc)) {	
			$solrStr = implode('|',$sdesc);
			if (!empty($ndesc['viaf_id']))
				$skey = $this->shortHash($ndesc['viaf_id']);
				else 
				$skey = $this->shortHash($ndesc['name'].$ndesc['date']);
			
			if (stristr($ndesc['name'], '>>')) {
				$tmp = explode('>>', $ndesc['name']);
				$ndesc['name'] = trim($tmp[1]);
				}
			$ndesc['wiki_id'] = $this->viaf2wiki($ndesc['viaf_id']);
			
			$solr_str = implode('|',[
				'name'		=> $ndesc['name'],
				'year_born'	=> $ndesc['year_born'],
				'year_death'=> $ndesc['year_death'],
				'viaf_id'	=> $ndesc['viaf_id'],
				'wiki_id'	=> $ndesc['wiki_id'],
				'date'		=> $ndesc['date'],
				]);
			
			$csvLine = [
				'skey' 		=> $skey,
				'name'		=> $ndesc['name'],
				'year_born'	=> $ndesc['year_born'],
				'year_death'=> $ndesc['year_death'],
				'viaf_id'	=> $ndesc['viaf_id'],
				'wiki_id'	=> $ndesc['wiki_id'],
				'date'		=> $ndesc['date'],
				'field'		=> $as,
				'role'		=> $roles
				];
				
			$personLine = implode('|',[
				#'skey' 		=> $skey,
				'name'		=> $ndesc['name'],
				'year_born'	=> $ndesc['year_born'],
				'year_death'=> $ndesc['year_death'],
				'viaf_id'	=> $ndesc['viaf_id'],
				'wiki_id'	=> $ndesc['wiki_id'],
				'date'		=> $ndesc['date']
				]);	
				
			$fp = fopen($this->outPutFolder.'persons.csv', 'a');
			fputcsv($fp, $csvLine, ';');
			fclose ($fp);
			
			$this->work->persons[$skey] = $personLine;
			if (!empty($ndesc['viaf_id']))
				$this->work->personsViaf[$ndesc['viaf_id']] = $ndesc['viaf_id'];
			if (!empty($ndesc['wiki_id'])) {
				$this->work->personsWiki[$ndesc['wiki_id']] = $ndesc['wiki_id'];
				if (!empty($as) && is_string($as))
					$this->work->onlyWiki[$as][$ndesc['wiki_id']] = $ndesc['wiki_id'];
				if (!empty($this->workingMode) && ($this->workingMode == 'checking')) {
					@$this->buffer->wikiq[$ndesc['wiki_id']]['biblio_labels'][$solr_str]++;
					@$this->buffer->wikiq[$ndesc['wiki_id']]['roles'][$as][$this->id] = $this->id;
					@$this->buffer->wikiq[$ndesc['wiki_id']]['roles']['total'][$this->id] = $this->id;
					
					} else {
					if (!empty($this->buffer->wikiq[$ndesc['wiki_id']]['biblio_label']))
						return $this->buffer->wikiq[$ndesc['wiki_id']]['biblio_label'];
					$wiki = new wikidata('Q'.$ndesc['wiki_id']);
					if (!empty($wiki->getBiblioLabel())) {
						$this->buffer->wikiq[$ndesc['wiki_id']]['biblio_label'] = $wiki->getBiblioLabel();
						return $wiki->getBiblioLabel();
						}
					}
				}
			
			return $solr_str;	
			}
		}	
	
	public function savePerson($desc, $as = '') {

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
		if (is_string($str) && stristr($str, 'viaf'))
			return $this->onlyNumbers($str); 
		}
	
	function viaf2wiki($viafId) {
		if (empty($this->viafs[$viafId])) {
			$t = $this->psql->querySelect("SELECT wikiid FROM viaf2wiki WHERE viafid='$viafId';");
			if (is_array($t)) {
				$res = current($t);
				$this->viafs[$viafId] = $res['wikiid'];
				return $res['wikiid'];
				}
			} else 
			return $this->viafs[$viafId];
		}
	
	function wikiq4name($name) {
		if (!empty($this->config->donotgeocode) && in_array($name, $this->config->donotgeocode)) 
			return null;
		if (!empty($this->wikiNames[$name]))
			return $this->wikiNames[$name];
			else {
			$solr = $this->settings->solr;
			// look for in solr buffer
			$fres = @file_get_contents($Q = $solr->hostname.':'.$solr->port.'/solr/'.$solr->cores->wiki.'/select?indent=true&q.op=OR&q=biblio_labels%3A'.urlencode('"'.$name.'"').'+OR+labels%3A'.urlencode('"'.$name.'"'));
			
			if (!empty($fres)) {
				$res = json_decode($fres);
				if (!empty($res->response->docs)) {
					$result = current($res->response->docs);
					$wikiId = $result->id;
					$wikiq = substr($wikiId,1);
					if (!empty($result->biblio_labels)) {
						$labels = $result->biblio_labels;
						foreach ($labels as $label)
							$this->wikiNames[$label] = $wikiq;
						} else {
						// update elementy 
						// <delete><query>id:Q46</query></delete>
						}
					file_put_contents($this->outPutFolder.'geoSolrQueries.csv', "$name;$wikiq;\n", FILE_APPEND);
					$this->wikiNames[$name] = $wikiq;
					return $wikiq;
					}
				}
				
			// geocode via wikidata	
			$sstring = 'inlabel:'.urlencode( $name = $this->clearName($name) );
			$res = json_decode(file_get_contents($F = $this->settings->externalHosts->wikidata.'/w/api.php?action=query&format=json&list=search&srlimit=6&srprop=size&formatversion=2&srsearch='.$sstring));
			$hit = false;
			file_put_contents($this->outPutFolder.'geoWikiQueries.csv', "$sstring;$name;\n", FILE_APPEND);
			if (!empty($res->batchcomplete) && ($res->query->searchinfo->totalhits>0)) {
				foreach ($res->query->search as $key=>$result) {
					$wikiClass  = new wikidata($result->title); 
					if (!empty($value = $wikiClass->getCoordinates('P625'))) { // perhaps more conditions should be added?
						$wikiq = substr($result->title,1);
						$this->wikiNames[$name] = $wikiq;
						return $wikiq;
						}
					}
				} 
			$this->wikiNames[$name] = 'not found';
			file_put_contents($this->outPutFolder.'geoNotFound.csv', "$name;\n", FILE_APPEND);
			}
		return null;
		}
	
	
	function getWiki4Name($name, $role = '') {
		$name = $this->clearName($name);
		$wikiq = $this->wikiq4name($name); 
		
		if (!empty($wikiq) && ($wikiq<>'not found')) {
			if (!empty($this->workingMode) && ($this->workingMode == 'checking')) {
				@$this->buffer->wikiq[$wikiq]['biblio_labels'][$name]++;
				$this->buffer->wikiq[$wikiq]['roles'][$role][$this->id] = $this->id;
				$this->buffer->wikiq[$wikiq]['roles']['total'][$this->id] = $this->id;
				
				} else {
				
				if (!empty($this->buffer->wikiq[$wikiq]['ml_label'])) {
					$name = $this->buffer->wikiq[$wikiq]['ml_label'];
					} else {
					$wiki = new wikidata('Q'.$wikiq);
					$this->buffer->wikiq[$wikiq]['ml_label'] = $name =  $wiki->getML('labels', $this->settings->multiLanguage->order);
					}
				}
			
			$this->work->geoWikiFull[$name] = "$wikiq|$name";
			$this->work->geoWiki[$wikiq] = $wikiq;
			}
		return "$wikiq|$name";
		}
	
	function convertName2Wiki($name) {
		$name = $this->clearName($name);
		return $this->wikiq4name($name); 
		}

	
	public function getGeoWikiFull() {
		if (!empty($this->work->geoWikiFull))
			return $this->removeArrayKeys($this->work->geoWikiFull);
		return null;
		}
	
	
	public function getGeoWiki() {
		if (!empty($this->work->geoWiki))
			return $this->removeArrayKeys($this->work->geoWiki);
		return null;
		}
	
		
	public function getPersonsWiki() {
		if (!empty($this->work->personsWiki))
			return $this->removeArrayKeys($this->work->personsWiki);
		return null;
		}
	
	public function getPersonsViaf() {
		if (!empty($this->work->personsViaf))
			return $this->removeArrayKeys($this->work->personsViaf);
		return null;
		}
	
	public function getPersons() {
		if (!empty($this->work->persons))
			return $this->removeArrayKeys($this->work->persons);
		return null;
		}
	
	public function savePersonFromStr($solrStr, $role, $count) {
		$n['viaf_id'] = '';
		$id_str = 'http://viaf.org/viaf/';
		
		$tmp = explode($id_str, $solrStr);
		if (count($tmp) == 2) {
			$n['viaf_id'] = $tmp[1];
			$nstr = trim(str_replace($id_str.$tmp[1], '', $solrStr));
			} else 
			$nstr = $solrStr;
		
		$tmp = explode('(', $nstr);
		$isdate = end($tmp);
		$n['year_born'] = $n['year_death'] = '';
		$tmp = explode('-', str_replace(['(',')'], '', $isdate));	
		if (count($tmp) == 2) { 
			$n['year_born'] = floatval($tmp[0]);
			$n['year_death'] = floatval($tmp[1]);
			$nstr = trim(str_replace('('.$isdate, '', $nstr));
			if ($n['year_death'] == 0) 
				$n['year_death'] = '';
			if ($n['year_born'] == 0) 
				$n['year_born'] = '';
			}

		$n['name'] = $nstr;
		$ndesc = $n;
		$as_author = $as_topic = 0;
		if ($role == 'Authors') {
			$as_author = $count;
			$SET = "as_author = '$as_author'";
			} else { #if ($role == 'Subject persons') 
	
			$as_topic = $count; 
			$SET = "as_topic = '$as_topic'";
			}
		
		if (is_array($ndesc)) {	
			$res = $this->sql->query($Q = "SELECT * FROM libri_persons_full WHERE solr_str = '$solrStr';");
			if (empty($res->num_rows) OR ($res->num_rows=='0')) {
				$this->sql->query($Q = "INSERT INTO libri_persons_full (name, year_born, year_death, viaf_id, as_author, as_topic, solr_str) 
					VALUES (
						{$this->isNULL($ndesc['name'])}, 
						{$this->isNULL($ndesc['year_born'])}, 
						{$this->isNULL($ndesc['year_death'])}, 
						{$this->isNULL($ndesc['viaf_id'])}, 
						{$this->isNULL($as_author)}, 
						{$this->isNULL($as_topic)}, 
						{$this->isNULL($solrStr)}
						)");
				} else {
				$row = mysqli_fetch_assoc($res);
				$this->sql->query($Q = "UPDATE libri_persons_full SET $SET WHERE solr_str = '$solrStr';");	
				}
			return $ndesc;
			}
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
	
	
	public function getMainAuthor() {
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			#$this->savePerson($desc, 'author');
			return $this->preparePerson($desc, 'author');
			} else 
			return null;
		}
	
	public function getMainAuthorWiki() {
		
		if (!empty($this->work->onlyWiki['author']))
			return $this->removeArrayKeys($this->work->onlyWiki['author']);
		}
	
	public function getCoAuthorWiki() {
		if (!empty($this->work->onlyWiki['author2']))
			return $this->removeArrayKeys($this->work->onlyWiki['author2']);
		}
	
	public function getSubjectPersonsWiki() {
		if (!empty($this->work->onlyWiki['topic']))
			return $this->removeArrayKeys($this->work->onlyWiki['topic']);
		}	
	
	
	public function getMainAuthorW() {
		// inicjały dla: Takala, Jukka-Pekka = j p t jpt
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			return $this->personInitials($desc);
			} else 
			return null;
		}
	
	public function getMainAuthorSort() {
		// Name + date without special chars. check:  $str = preg_replace('/[[:cntrl:]]/', '', $str);
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			return $this->str2url($this->preparePerson($desc));
			} else 
			return null;
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
	
	
	
	public function getMainAuthorRole() {
		$field = '100';
		$roles = [];
		
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				if (!empty($sf['code']['e']) && is_Array($sf['code']['e']))
					foreach ($sf['code']['e'] as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['e']) && !is_Array($sf['code']['e'])) {
						$z = $sf['code']['e'];
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['4']) && is_Array($sf['code']['4']))
					foreach ($sf['code']['4'] as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['4']) && !is_Array($sf['code']['4'])) {
						$z = $sf['code']['4'];
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				}
			}
		if (empty($roles))
			$roles[] = 'Unknown';
		return $this->removeArrayKeys($roles);	
		}
		
	public function getMainOtherAuthorsRoles() {
		$field = '700';
		$roles = [];
		
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				if (!empty($sf['code']['e']) && is_Array($sf['code']['e']))
					foreach ($sf['code']['e'] as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['e']) && !is_Array($sf['code']['e'])) {
						$z = $sf['code']['e'];
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['4']) && is_Array($sf['code']['4']))
					foreach ($sf['code']['4'] as $z) {
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				if (!empty($sf['code']['4']) && !is_Array($sf['code']['4'])) {
						$z = $sf['code']['4'];
						$roles[$z] = $this->transleteCrativeRoles($z);	
						}
				}
			}
		if (empty($roles))
			$roles[] = 'Unknown';
		return $this->removeArrayKeys($roles);	
		}

		
	public function getCorporateAuthor() {
		$fields = [110,111,710,711];
		$cauthors = [];
		foreach ($fields as $field) {
			$desc = $this->getMarcFirst($field);
			if (!empty($desc['a'])) 
				$cauthors[] = $desc['a'];
			}
		return $cauthors;
		}
	
	public function getCorporateAuthorFull() {
		#$fields = [110,111,710,711];
		$fields = [
			110 => 'main author',
			710 => 'co-author',
			610 => 'as subject',
			260 => 'as publisher',
			264 => 'as publisher'
			];
		
		$cauthors = [];
		$lp = 0;
		foreach ($fields as $field=>$role) {
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
	
	public function getCorporateAuthorWiki() {
		#$fields = [110,111,710,711];
		$fields = [
			110 => 'main author',
			710 => 'co-author',
			610 => 'as subject',
			260 => 'as publisher',
			264 => 'as publisher'
			];
		$return = [];
		$lp = 0;
		foreach ($fields as $field=>$role) {
			$desc = $this->getMarcFirst($field);
			
			if (!empty($desc['a']) && is_string($desc['a'])) {
				if (!empty($desc['1'])) {
					$corp['viaf'] = $this->viafFromStr( $desc['1'] );
					$return[] = $this->viaf2wiki($corp['viaf']);
					}
				}
			if (!empty($desc['a']) && is_array($desc['a'])) 
				foreach ($desc['a'] as $k=>$v) {
					if (!empty($desc['1'][$k])) {
						$corp['viaf'] = $this->viafFromStr( $desc['1'][$k] );
						$return[] = $this->viaf2wiki($corp['viaf']);
						}
					}
			}
		return $return;
		}
	
	
	public function getAuthorViaf() {
		$fields = [100,700];
		$viafs = [];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				
				$line = $this->record[$field];
				foreach ($line as $author) {
					$desc = (array)$author['code'];
					if (!empty($desc['1']) && is_string($desc['1'])) {
						$viaf = $this->viafFromStr($desc['1']);
						if (is_string($viaf) && strlen($viaf)>0)
							$viafs[$viaf] = $viaf;
						}
						
					}
				}
		return $this->removeArrayKeys($viafs);		
				
		}
		
	public function getSubjectPersonsViaf() {
		$fields = [600];
		$viafs = [];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				
				$line = $this->record[$field];
				foreach ($line as $author) {
					$desc = (array)$author['code'];
					if (!empty($desc['1'])) {
						if (is_string($desc['1'])) {
							$viaf = $this->viafFromStr($desc['1']);
							$viafs[$viaf] = $viaf;
							} else if (is_Array($desc['1'])) {
							file_put_contents($this->outPutFolder.'multiViaf.csv', $this->id."\t".print_r($desc['1'],1)."\n", FILE_APPEND);
							foreach ($desc['1'] as $str) {
								$viaf = $this->viafFromStr($str);
								$viafs[$viaf] = $viaf;
								}
							}
						}
					}
				}
		return $this->removeArrayKeys($viafs);		
				
		}
	

	
	public function getAllAuthors() {
		$authors = [];
		
		$fields = [100,700];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				
				$line = $this->record[$field];
				$result = '';
				$authors = [];
				foreach ($line as $author) {
					$desc = (array)$author['code'];
					$authors[] = $this->preparePerson($desc);
					}
				} 
		return $authors;	
		}
		
		
	public function getOtherAuthors() {
		$field = 700;
		
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author['code'];
				$authors[] = $this->preparePerson($desc, 'author2');
				}
				
			return $authors;	
			} else 
			return null;
		}
		
	

	
		
	public function getOtherAuthorsW() {
		$field = 700;
		
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author['code'];
				$authors[] = $this->personInitials($desc);
				}
				
			return $authors;	
			} else 
			return null;
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
	
		
	public function getLanguageHierarchical() {
		$field = '008';
		$langs = [];
		if (is_array($this->record)) {
			$marcFields = $this->record;
			if (!empty($this->record[$field][0]))  {
				$z = 'publication/'.$this->translateLangCode(substr($this->record[$field][0],35,3));
				$langs[$z] = $z;	
				}
			
			$field = '041';
			if (!empty($marcFields[$field]))  {
				
				foreach ($marcFields[$field] as $sf) {
					if (!empty($sf['code']['a']) && is_Array($sf['code']['a']))
						foreach ($sf['code']['a'] as $z) {
							$z = 'publication/'.$this->translateLangCode($z);
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['a']) && is_string($sf['code']['a'])) {
						$z = 'publication/'.$this->translateLangCode($sf['code']['a']);
						$langs[$z] = $z;	
						}
					
					if (!empty($sf['code']['h']) && is_Array($sf['code']['h']))
						foreach ($sf['code']['h'] as $z) {
							$z = 'publication/'.$this->translateLangCode($z);
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['h']) && is_string($sf['code']['h'])) {
						$z = 'publication/'.$this->translateLangCode($sf['code']['h']);
						$langs[$z] = $z;	
						}
					
					}
				}
			
			$field = '040';
			if (!empty($marcFields[$field]))  {
				
				foreach ($this->record[$field] as $sf) {
					if (!empty($sf['code']['b']) && is_Array($sf['code']['b']))
						foreach ($sf['code']['b'] as $z) {
							$z = 'original/'.$this->translateLangCode($z);
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['b']) && is_string($sf['code']['b'])) {
						$z = 'original/'.$this->translateLangCode($sf['code']['b']);
						$langs[$z] = $z;	
						}
					}
				}
			}
		
		if (count($langs)>0)
			return $this->removeArrayKeys($langs);	
			else 
			return ['undefined'];
		}
		
	public function getLanguageP() {
		$field = '008';
		$langs = [];
		if (is_array($this->record)) {
			$marcFields = $this->record;
			if (!empty($this->record[$field][0]))  {
				$z = substr($this->record[$field][0],35,3);
				$langs[$z] = $z;	
				}
			
			$field = '041';
			if (!empty($marcFields[$field]))  {
				
				foreach ($marcFields[$field] as $sf) {
					if (!empty($sf['code']['a']) && is_Array($sf['code']['a']))
						foreach ($sf['code']['a'] as $z) {
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['a']) && is_string($sf['code']['a'])) {
						$z = $sf['code']['a'];
						$langs[$z] = $z;	
						}
					}
				}
			
			$field = '040';
			if (!empty($marcFields[$field]))  {
				
				foreach ($this->record[$field] as $sf) {
					if (!empty($sf['code']['b']) && is_Array($sf['code']['b']))
						foreach ($sf['code']['b'] as $z) {
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['b']) && is_string($sf['code']['b'])) {
						$z = $sf['code']['b'];
						$langs[$z] = $z;	
						}
					}
				}
			}
		
		$TNlangs = [];
		foreach ($langs as $k=>$lang) {
			$nlang = $this->translateLangCode($lang);
			if ($nlang<>'undefined') {
				$TNlangs[$nlang] = $nlang;
				}
			}
		if (count($TNlangs)>0)
			return $this->removeArrayKeys($TNlangs);	
			else 
			return ['undefined'];
		}
		
	public function getLanguageO() {
		$langs = [];
			
		$field = '041';
		if (is_array($this->record)) {
			$marcFields = $this->record;
			if (!empty($marcFields[$field]))  {
				foreach ($marcFields[$field] as $sf) {
					if (!empty($sf['code']['h']) && is_Array($sf['code']['h']))
						foreach ($sf['code']['h'] as $z) {
							$langs[$z] = $z;	
							}
					if (!empty($sf['code']['h']) && is_string($sf['code']['h'])) {
						$z = $sf['code']['h'];
						$langs[$z] = $z;	
						}
					}
				}
			if (count($langs)>0) {	
				foreach ($langs as $k=>$lang) 
					$langs[$k] = $this->translateLangCode($lang);
					
				return $this->removeArrayKeys($langs);	
				}
			}
		}
		
		
	public function getPublishDate() {
		$field = '008';
		if (is_array($this->record) && (!empty($this->record[$field][0])) ) {
			$res = $this->onlyNumbers(substr($this->record[$field][0],7,4));
			if (!empty($res)) return $res;
			}
				
		$field = '264';
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
		return null;	
		}
		
		
	public function getGenreM() { 
		$field = '380';
		$res = [];
		
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
	public function getGenreS() { 
		$field = '381';
		$res = [];
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
		if (is_array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
	
	
	public function getAuthorEvents() {
		$fields = [111,711];
		$res = [];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				
				$line = $this->record[$field];
				foreach ($line as $event) {
					$desc = (array)$event['code'];
					$resEvent['1name'] = 
					$resEvent['2year'] = 
					$resEvent['3place'] = 
					$resEvent['4edition'] = '';
					 
					if (!empty($desc['a']) & is_string($desc['a'])) {
						$resEvent['1name'] = $desc['a'];
						}
					if (!empty($desc['a']) & is_array($desc['a'])) {
						$resEvent['1name'] = implode(', ',$desc['a']);
						}
					if (!empty($desc['d'])) {
						$resEvent['2year'] = $this->clearName($desc['d']);
						} 
					if (!empty($desc['c'])) {
						$resEvent['3place'] = $this->clearName($desc['c']);
						} 
					if (!empty($desc['n'])) {
						$resEvent['4edition'] = $this->clearName($desc['n']);
						}
						
					ksort($resEvent);	
					$res[] = implode('|', $resEvent);	
					}
				}
		return $this->removeArrayKeys($res);		
		
		}
	
	public function getSubjectEvents() {
		$fields = [611];
		$res = [];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				
				$line = $this->record[$field];
				foreach ($line as $event) {
					$desc = (array)$event['code'];
					$resEvent['1name'] = 
					$resEvent['2year'] = 
					$resEvent['3place'] = 
					$resEvent['4edition'] = '';
					 
					if (!empty($desc['a']) & is_string($desc['a'])) {
						$resEvent['1name'] = $desc['a'];
						}
					if (!empty($desc['a']) & is_array($desc['a'])) {
						$resEvent['1name'] = implode(', ',$desc['a']);
						}
					if (!empty($desc['d'])) {
						$resEvent['2year'] = $this->clearName($desc['d']);
						} 
					if (!empty($desc['c'])) {
						$resEvent['3place'] = $this->clearName($desc['c']);
						} 
					if (!empty($desc['n'])) {
						$resEvent['4edition'] = $this->clearName($desc['n']);
						}
						
					ksort($resEvent);	
					$res[] = implode('|', $resEvent);	
					}
				}
		return $this->removeArrayKeys($res);		
		
		}
	
	
	public function getEventsPlace() {
		$fields = [111,611,711];
		$res = [];
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field]))) {
				$line = $this->record[$field];
				foreach ($line as $event) {
					$desc = (array)$event['code'];
					if (!empty($desc['c']) && is_string($desc['c'])) {
						$place['name'] = 
						$place['wikiq'] = '';
						
						$place['name'] = $this->clearName($desc['c']);
						$res[] = $this->getWiki4Name($place['name'], 'event place');
						}
					if (!empty($desc['c']) && is_array($desc['c'])) 
						foreach ($desc['c'] as $c) {
						
							$place['name'] = 
							$place['wikiq'] = '';
							
							$place['name'] = $this->clearName($c);
							$res[] = $this->getWiki4Name($place['name'], 'event place');
							}
						
					}
				}
		return $this->removeArrayKeys(array_unique($res));		
		
		}
	
	
	public function getPublicationPlaces() {
		$fields = ['260', '264'];
		$res = [];
		
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
					if (!empty($sf['code']['a']))
						if (is_Array($sf['code']['a'])) {
							foreach ($sf['code']['a'] as $z) 
								$res[] = $this->getWiki4Name($z, 'publication place');	
							}
							else 
							$res[] = $this->getWiki4Name($sf['code']['a'], 'publication place');
					} 
				}		
		return array_unique($res);	
		}
	
	
	public function getPublicationPlacesWiki() {
		$fields = ['260', '264'];
		$res = [];
		
		foreach ($fields as $field)
			if (is_Array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
					if (!empty($sf['code']['a']))
						if (is_Array($sf['code']['a'])) {
							foreach ($sf['code']['a'] as $z) 
								$res[] = $this->convertName2Wiki($z);	
							}
							else 
							$res[] = $this->convertName2Wiki($sf['code']['a']);
					} 
				}	
		$this->work->pub_places = $res; 		
		return $res;	
		}
	
	public function getSubjectPlacesWiki() {
		$field = '651';
		$res = [];
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				if (!empty($sf['code']['a']))
					if (is_Array($sf['code']['a'])) {
						foreach ($sf['code']['a'] as $z) 
							$res[] = $this->convertName2Wiki($z);	
						}
						else 
						$res[] = $this->convertName2Wiki($sf['code']['a']);
				}
			}
			
		return $res;	
		}
	
	
	public function getCountry($wikiq, $year) {
		$wikiId = 'Q'.$wikiq;
		if (!empty($this->buffer->countryYears[$wikiId][$year]))
			return $this->buffer->countryYears[$wikiId][$year];
		
		$wiki = new wikidata($wikiId);
		$country = $wiki->getHistoricalCountry($year);
		if (is_array($country))
			$res = current($country);
			else 
			$res = $country;
		
		$this->buffer->countryYears[$wikiId][$year] = $res;
		file_put_contents($this->outPutFolder.'country_years.csv', "$wikiq|$year|$res|\n", FILE_APPEND); 
		return $country;
		}
	
	public function getPublicationCountryWiki() {
		$res = [];
		
		$year = $this->getPublishDate();
		if (!empty($year) && !empty($this->work->pub_places))
			foreach ($this->work->pub_places as $placeWiki) {
				if ($placeWiki<>'') {
					$pubCountry = $this->getCountry($placeWiki, $year);
					if (!empty($pubCountry) && is_string($pubCountry))
						$res[$pubCountry] = $pubCountry;
						else 
						$res[$placeWiki] = $placeWiki;
					}
				
				}
		
		return $this->removeArrayKeys($res);	
		
		}
	
	
	
	public function getRegion() {
		$field = '651';
		$res = [];
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				if (!empty($sf['code']['a']))
					if (is_Array($sf['code']['a'])) {
						foreach ($sf['code']['a'] as $z) 
							$res[] = $this->getWiki4Name($z, 'subject place');	
						}
						else 
						$res[] = $this->getWiki4Name($sf['code']['a'], 'subject place');
				}
			}
			
		return array_unique($res);	
		}
	
	public function getPublished() {
		$fields = ['260','264'];
		$res = [];
		$lp = 0;
		foreach ($fields as $field)
			if (is_array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
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
		if (is_Array($this->record) && (!empty($this->record[$field])) ) 
			if (is_array($this->record[$field]))
				foreach ($this->record[$field] as $sf) 
					if (!empty($sf['code']) && is_array($sf['code']))
						foreach ($sf['code'] as $z) 
							$res[] = $z;
			
		return implode(' ',$res);	
		}
	
		
		
	public function getIn() {
		$field = '773';
		$res = [];
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
				$sres = [];
				foreach ($sf['code'] as $k=>$z) {
					if (is_Array($z))
						$z = implode(', ', $z);
					switch ($k) {
						
						case 'x' : 
							$sres[]= 'ISSN '.$z; 
							$this->work->ISSN = $z;
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
			if (is_Array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
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
		

	
	
	public function getSubjectPersons() {
		$field = 600;
		
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			$line = $this->record[$field];
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author['code'];
				#$this->savePerson($desc, 'topic');
				$authors[] = $this->preparePerson($desc, 'topic');
				} 
			return $authors;	
			} else 
			return null;
		}
	
	public function getRefferedWork() {
		$field = 787;
		$rec = [];
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			
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
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			
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
	
	
	
	public function getSubjects() {
		$res = [];
		$min = 601;
		$max = 699;
		$lp = 0;
		$uri = [];
		for ($field = $min; $field<=$max; $field++) {
			if (is_array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
					$lp++;
					foreach ($sf['code'] as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = $sz;
										}
									} else {	
									$uri[] = $z;
									}
							}
						
						}	
					}
				}
			}
		
		return $uri;	
		}
	
	public function getSubjectsFull() {
		$res = [];
		$min = 600;
		$max = 699;
		$lp = 0;
		$uri = [];
		for ($field = $min; $field<=$max; $field++) {
			if (is_Array($this->record) && (!empty($this->record[$field])) ) {
				foreach ($this->record[$field] as $sf) {
					$lp++;
					foreach ($sf['code'] as $k=>$z) {
						switch ($k) {
							case '0': 
							case '2': 
							case '7': break;
							default: 
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = $sz;
										}
									} else {	
									$uri[] = $z;
									}
							}
						
						}	
					}
				}
			}
		
		return $uri;	
		}
	
	public function getSubjectELB() {
		$field = 650;
		$res = [];
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
		if (is_Array($this->record) && (!empty($this->record[$field])) ) {
			foreach ($this->record[$field] as $sf) {
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
		
		
	public function getSourcePublication() {
		$fields = [
			'773'=>'s',
			'773'=>'t',
			];
		
		foreach ($fields as $field=>$subfield) 
			if (!empty($res = $this->getMarcFirstStr($field,[$subfield],'','')))
				return str_replace('. -', '', $res);
		
		}
	
	public function getOclcNum() {
		$field = '035';
		if (is_Array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			
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
		if (is_array($this->record) && (!empty($this->record[$field]))) {
			
			$line = $this->record[$field];
			
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
		
	
	public function getFullMrc() {
		$id = $this->id;
		$file = file_get_contents('http://localhost/lite/import/marc21/getMRC.php?id='.$id);
		$this->recFormat = 'mrc';
		return $file;
		}
			
	public function getRecFormat() {
		return $this->recFormat;
		}
		
	public function getSourceMrk() {
		$this->recFormat = 'mrk';
		if (!empty($this->mrk))
			return $this->mrk;
		}
		
	public function drawTextMarc() {
		$this->recFormat = 'mrk';
		if (!empty($this->record)) {
			$result = 'LDR  '.$this->record->LEADER."\n";
			foreach ($this->record as $field=>$subarr) {
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
		if (!empty($this->record)) {
			$result = '<table class="table table-striped">
					<thead><tr><td style="text-align:right"><b>LEADER</b></td><td colspan=3>'.$this->record->LEADER.'</td></tr></thead>
					<tbody>
					';
			foreach ($this->record as $field=>$subarr) {
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
	

	
	
	public function currentDate() {
		return date("Y-m-d");
		}	
	public function currentTime() {
		return date("Y-m-d H:i:s");
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
		return array_unique(array_values($array));
		}
	
	function onlyNumbers($string) {
		#return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
		return preg_replace("/[^0-9]/", '', $string);
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
<?php
#[AllowDynamicProperties]

class importer {
	public $settings;
	private $configPath = './config/';
	public $bufferAreas = ['wikiQ', 'issn', 'str'];
	public $oftenReapeted; 
	public $confingJson;
	public $confingIni;
	public $startTime;
	public $startHRTime;
	
	public $currentFile;
	public $currentGroup;
	public $currentUpdates;
	public $filesToImport = [];
	public $totalFilesSize = 0;
	public $buffFullSize = 0;
	public $licences = [];
	public $groupDefaults;
	public $udcMeaning;
	public $placesToSave = [];
	public $DDkeys = [];
	public $mainRoles = [];
	
	public $topicValues;
	public $topicCounts;
	
	public $workingStep = 0;
	
	public function __construct() {
		$jsonFiles = glob ($this->configPath.'*.json');
		$this->configJson = new stdClass;
		foreach ($jsonFiles as $jsonFile) {
			$confName = str_replace([$this->configPath, '.json'], '', $jsonFile);
			$this->configJson->$confName = json_decode(@file_get_contents($jsonFile));
			if (empty($this->configJson->$confName)) {
				die ($confName.".json file not found or json error\n");
				}
			}
		$this->config = new stdClass;
		$this->config->maxErrorFiles = 10;
		$this->startTime = time();
		$this->startHRTime = hrtime();
		
		$this->defaultLanguage = $this->configJson->settings->www->defaultLanguage ?? 'en';
		
		$this->oftenReapeted = new stdClass;
		$this->buffer = new stdclass;
		$this->buffer->wikiq = [];
		$solr = $this->configJson->settings->solr;
		
		$this->solrUrl = $solr->host.':'.$solr->port.'/solr/'.$solr->cores->biblio.'/update';
		$this->solrWikiUrl = $solr->hostname.':'.$solr->port.'/solr/'.$solr->cores->wiki.'/update';
		$this->config->commitStep = 5000;  
		
		$this->lp = 0; // saveRecord counter
		$this->noCR = 0; //createRelations counter
		$this->totalRec = 0;
		$this->lastLen = 0;
		
		$this->config->ini_folder = "./config/import/";
		$this->outPutFolder = './import/outputfiles/';
		$this->recFormat = 'unknown';
		
		$iniFiles = glob ($this->config->ini_folder.'*.ini');
		foreach ($iniFiles as $fullFileName) {
			$iniFile = str_replace([$this->config->ini_folder, '.ini'], '', $fullFileName);
			$this->config->$iniFile = parse_ini_file($fullFileName, true);
			}
		}
	
	public function formatTime($workTime) {
		$days = '';
		$workDays = floor($workTime/86400);
		if ($workDays>0)
			$days =$workDays.'d ';
		
		return $days.date("H:i:s", (int)$workTime).' '; 
		}
	
	public function workTime() {
		$days = '';
		$workTime = time() - $this->startTime;
		$workDays = floor($workTime/86400);
		if ($workDays>0)
			$days =$workDays.'d ';
		
		return $days.date("H:i:s", $workTime).' '; 
		}
	
	function register($name, $var) {
		$this->$name = $var;
		}
	
	public function addClass($className, $res) {
		if (method_exists($res,'register'))
			$res->register('cms', $this);
		$this->$className = $res;
		}
	
	
	function bashColor(string $colorName): string {
		$colors = [
			"ColorOff" => "\033[0m",
			"Black" => "\033[0;30m", "Red" => "\033[0;31m", "Green" => "\033[0;32m", "Yellow" => "\033[0;33m", "Blue" => "\033[0;34m", "LightBlue"=>"\e[94m", "Purple" => "\033[0;35m", "Cyan" => "\033[0;36m", "White" => "\033[0;37m",
			"BBlack" => "\033[1;30m", "BRed" => "\033[1;31m", "BGreen" => "\033[1;32m", "BYellow" => "\033[1;33m", "BBlue" => "\033[1;34m", "BPurple" => "\033[1;35m", "BCyan" => "\033[1;36m", "BWhite" => "\033[1;37m",
			"UBlack" => "\033[4;30m", "URed" => "\033[4;31m", "UGreen" => "\033[4;32m", "UYellow" => "\033[4;33m", "UBlue" => "\033[4;34m", "UPurple" => "\033[4;35m", "UCyan" => "\033[4;36m", "UWhite" => "\033[4;37m",
			"On_Black" => "\033[40m", "On_Red" => "\033[41m", "On_Green" => "\033[42m", "On_Yellow" => "\033[43m", "On_Blue" => "\033[44m", "On_Purple" => "\033[45m", "On_Cyan" => "\033[46m", "On_White" => "\033[47m",
			"IBlack" => "\033[0;90m", "IRed" => "\033[0;91m", "IGreen" => "\033[0;92m", "IYellow" => "\033[0;93m", "IBlue" => "\033[0;94m", "IPurple" => "\033[0;95m", "ICyan" => "\033[0;96m", "IWhite" => "\033[0;97m",
			"BIBlack" => "\033[1;90m", "BIRed" => "\033[1;91m", "BIGreen" => "\033[1;92m", "BIYellow" => "\033[1;93m", "BIBlue" => "\033[1;94m", "BIPurple" => "\033[1;95m", "BICyan" => "\033[1;96m", "BIWhite" => "\033[1;97m",
			"On_IBlack" => "\033[0;100m", "On_IRed" => "\033[0;101m", "On_IGreen" => "\033[0;102m", "On_IYellow" => "\033[0;103m", "On_IBlue" => "\033[0;104m", "On_IPurple" => "\033[0;105m", "On_ICyan" => "\033[0;106m", "On_IWhite" => "\033[0;107m",
			];
    
		return $colors[$colorName] ?? "\033[0m";
		}
	
	
	public function logTime() {
		return hrtime()[1] - $this->startHRTime[1];
		}
	
	public function saveLogTime($str) {
		file_put_contents($this->outPutFolder.'timeLog.csv', $this->logTime().';'.$str.";\n", FILE_APPEND);
		}
	
	function setDestinationPath($path) {
		return $this->destinationPath = $path;
		}
		
	function setCurrentFile($name) {
		$this->lp = 0;
		$this->currentFile['name'] = $name;
		$this->currentFile['number'] = empty($this->currentFile['number']) ? 1 : $this->currentFile['number'] + 1;
		$this->currentFile['group'] = $sourceGroupCode = $this->getFileGroupFromName($name);
		$this->currentFile['groupDefaults'] = $this->groupDefaults[$sourceGroupCode];
		$this->currentFile['licence'] = $this->licences[$sourceGroupCode];
		
		$path_info = pathinfo($name);
		$this->currentFile['format'] = $path_info['extension'];
		
		
		$updateFileName = $this->configJson->import->extentionsFolder.$name.'.json';
		if (file_exists($updateFileName)) {
			$this->currentFile['updates'] = json_decode(file_get_contents($updateFileName));
			$addMsg = "\t\t".$this->bashColor('Yellow').'Data correction file exists.';
			# print_r($this->currentFile['updates']);
			} else {
			$this->currentFile['updates']  = new stdClass; 
			$addMsg = '';
			}

		if (!empty($this->filesToImport['db_id'][$name])) {
			$this->currentFile['db_id'] = $this->filesToImport['db_id'][$name];
			} else {
			$t = $this->psql->querySelect("
					INSERT INTO elb_source_files (file_name, file_group, file_format) 
					VALUES ({$this->psql->string($name)}, {$this->psql->string($sourceGroupCode)}, {$this->psql->string($this->currentFile['format'])})
					ON CONFLICT (file_name)
					DO UPDATE SET file_name = elb_source_files.file_name
					RETURNING id;
					");
			if (is_array($t)) {
				$dbRow = current($t);
				$this->currentFile['db_id'] = $dbRow['id'];
				} 
			}
		
		return "\n".$this->currentFile['number'].". reading: ".$this->bashColor('LightBlue').$this->currentFile['name'].$addMsg.$this->bashColor('ColorOff')."  \n";
		}
	
	function setFileNo($number) {
		$this->currentFile['number'] = $number;
		}
	
	function setFilesToImport($array) {
		$this->totalFilesSize = 0;
		foreach ($array as $fileName) {
			$this->filesToImport['files'][] = $currentFile = str_replace($this->configJson->import->dataFolder, '', $fileName);
			$this->totalFilesSize += filesize($fileName);
			$path_info = pathinfo($fileName);
			$group = current(explode('_', $currentFile));
			@$this->filesToImport['groups'][$group]++; 
			@$this->filesToImport['formats'][$path_info['extension']]++; 
			}
		foreach ($this->filesToImport['groups'] as $value => $count)	
			$groupsStr[] = $this->bashColor('Blue').$value.': '.$this->bashColor('Green').$count.$this->bashColor('ColorOff');
		foreach ($this->filesToImport['formats'] as $value => $count)	
			$formatsStr[] = $this->bashColor('Blue').$value.': '.$this->bashColor('Green').$count.$this->bashColor('ColorOff');
		
		$return[] = "files to import: ".$this->bashColor('Green').count($array).$this->bashColor('ColorOff');
		$return[] = "      formats: ".implode("\n               ", $formatsStr);
		$return[] = "      groups:  ".implode("\n               ", $groupsStr);
		
		return implode("\n", $return)."\n";
		}
 
	function getFileGroupFromName($fileName) {
		$tmp = explode('_', $fileName);
		return $tmp[0];
		}
 
	function checkLicences() {
		$errors = [];
		foreach ($this->filesToImport['files'] as $fileName) {
			$sourceGroupCode = $this->getFileGroupFromName($fileName);
			$this->groupDefaults[$sourceGroupCode] = $this->configJson->import->source_db->$sourceGroupCode ?? new stdClass;
			
			if (empty($this->licences[$sourceGroupCode])) {
				$jsonFileName = $this->configJson->import->licenceFolder.$sourceGroupCode.'.json';
				if (file_exists($jsonFileName)) {
					$licenceInfo = json_decode(file_get_contents($jsonFileName));
					$this->licences[$sourceGroupCode] = $licenceInfo;
					} else {
					$errors[] = "No licence info for \e[94m{$fileName}\e[0m. File \e[94m$jsonFileName\e[0m not exists.";
					}
				}
			}
		
		
		
		return implode("\n", $errors);	
		}
 

	function saveImportStatus($id = '') {
		$workTime = time()-$this->startTime;
		if (empty($this->buffSize))
			$this->buffSize = 0;
		if (empty($this->fullFileSize))
			$this->fullFileSize = 1;
		if (empty($this->currentFile['name']))
			$this->currentFile['name'] = 'no data';
		if (!empty($this->relRec->id))
			$id = $this->relRec->id;
		if (empty($id))	$id = 0;
		$persentDone = round(($this->buffSize/$this->fullFileSize)*100);
		file_put_contents($this->outPutFolder."counter.txt", 
				'step:'.$this->workingStep.
				"\ncount:".$this->lp.
				"\npersent done:".$persentDone.
				"\nstart time:".$this->startTime.
				"\nwork time:".$this->WorkTime($workTime = time()-$this->startTime).
				"\ncurrent file name:".$this->currentFile['name'].
				"\ncurrent file number:".$this->currentFile['number'].
				"\ntotal files:".count($this->filesToImport).
				"\ncurrent id:".$id);
		
		}
	
	
	function fileSize($file) {
		$this->buffSize = 0;
		$this->fullFileSize = @filesize($file);
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
							$arr[$subfield][] = str_replace('{dollar}', '$', $oldval);	
							$arr[$subfield][] = str_replace('{dollar}', '$', $value);	
							} else 
							$arr[$subfield][] = str_replace('{dollar}', '$', $value);	
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
	
	/* start: mrk2json parts */
	
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
		$this->buffFullSize += strlen($mrk);
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
	
	/* end: mrk2json parts */
	
	function saveIndex($indexname, $id, $value) {
		$indexname = $this->cms->helper->clearStr($indexname);
		$fp = fopen($this->outPutFolder.'fields/'.$indexname.'.csv', 'a');
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
	
	function saveFieldsContent() {
		$id = $this->record['001'][0];
		foreach ($this->record as $field=>$arr) {
			file_put_contents($this->outPutFolder.'fields/F.'.$field.'.txt', $id."\n".print_r($arr,1), FILE_APPEND);
			}
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
	
	function saveSolrUpdateFile($record, $fname = '', $postdata = '') {
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
	
	
	public function getLeader() {
		return $this->relRec->LEADER ?? null;
		}

	public function getTitle() {
		if (!empty($this->relRec->title))
			return $this->relRec->title;
			else 
			return "[no title]";
		}
	
	public function getTitleFull() {
		if (!empty($this->relRec->title))
			return $this->relRec->title;
			else 
			return "[no title]";
		}
	
	public function getTitleSort() {
		if (!empty($this->relRec->title))
			return $this->helper->clearStr($this->relRec->title);
			else 
			return "no title";
		}
	
	public function getDDkey() {
		$titleDrop = [
			'[Title on the picture (retrobi record)]',
			'title on the picture retrobi record',
			'[no title]',
			'no title'
			];
		if (empty($this->relRec->title) or in_array($this->relRec->title, $titleDrop))
			return '';
		
		
		$keyArray = [];
		$keyArray[] = $this->getRelValue('majorFormat');
		if (!empty($this->relRec->title))
			$keyArray[] = $this->helper->clearStr($this->relRec->title);
		$keyArray[] = $this->helper->clearStr($this->getMainAuthor());
		$keyArray[] = $this->getPublisher();
		$keyArray[] = $this->getRelValue('publicationYear');
		foreach ($keyArray as $k=>$v)
			if (is_array($v))
				$keyArray[$k] = implode(' ', $v);
			
		$ddkey = str_replace('  ', ' ', trim(implode(' ', $keyArray)));
		if (isset($this->DDkeys[$ddkey])) {
			$this->relRec->hasDuplicate = true;
			$this->DDkeys[$ddkey]++;
			} else 
			$this->DDkeys[$ddkey] = 1;
		return $ddkey;
		}

	public function getTitleSub() {
		if (!empty($this->relRec->titleSub))
			return $this->relRec->titleSub;
			else 
			return "[no title]";
		}
	
	public function getTitleShort() {
		if (!empty($this->relRec->titleShort))
			return $this->relRec->titleShort;
			else 
			return "[no title]";
		}
	
	public function getTitleAlt() {
		if (!empty($this->relRec->titleAlt))
			return $this->relRec->titleAlt;
		}
	
	public function getDescription () {
		if (!empty($this->relRec->description))
			return $this->returnValue($this->relRec->description);
		}
	
	public function getStatmentOfResp() {
		if (!empty($this->relRec->StatmentOfResp))
			return $this->returnValue($this->relRec->StatmentOfResp);
		}
	
	public function getSourceDocument() {
		if (!empty($this->relRec->sourceDB->name))
			return $this->returnValue($this->relRec->sourceDB->name);
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
		
	public function shortHash($str) {
		return hash('crc32b', $str);
		}
	
	function viafFromStr($str) {
		if (is_string($str) && stristr($str, 'viaf'))
			return $this->onlyNumbers($str); 
		}
	

	public function getWiki() {
		if (!empty($this->relRec->wikiQ)) {
			foreach ($this->relRec->wikiQ as $wikiQ=>$roles) {
				$roles = (array)$roles;
				foreach ($roles as $role) 
					$res[] = $wikiQ;
				}
			return $this->removeArrayKeys($res);
			}
		}
	
	
	public function getWikiWithRoles() {
		if (!empty($this->relRec->wikiQ)) {
			foreach ($this->relRec->wikiQ as $wikiQ=>$roles) {
				$roles = (array)$roles;
				foreach ($roles as $role=>$count) 
					$res[] = $wikiQ.'|'.$role;
				}
			return $this->removeArrayKeys($res);
			}
		}
	
	
	function convertName2Wiki($name) {
		$name = $this->helper->clearName($name);
		return $this->wikiq4name($name); 
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
		if (!empty($this->relRec->persons->mainAuthor)) 
			return $this->helper->createPersonStr(current ($this->relRec->persons->mainAuthor));
		}
	
	public function getMainAuthorW() {
		// inicjały dla: Takala, Jukka-Pekka = j p t jpt
		if (isset($this->relRec->persons->mainAuthor)) {
			$author = current($this->relRec->persons->mainAuthor);
			return $this->personInitials($author['name']);
			}
		}
	
	public function getMainAuthorSort() {
		if (isset($this->relRec->persons->mainAuthor)) {
			$author = (object)current($this->relRec->persons->mainAuthor);
			return $this->helper->clearStr($author->name.' '.$author->dates);
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
	
	
	
	public function getMainAuthorRole() {
		if (isset($this->relRec->persons->mainAuthor)) {
			$author = current($this->relRec->persons->mainAuthor);
			if (!empty($author->roles))
				foreach ($author->roles as $role)
					$roles[$role] = $this->transleteCrativeRoles($role);
			}
		if (empty($roles))
			$roles[] = 'Unknown';
		return $this->removeArrayKeys($roles);	
		}
		
	public function getCorporate($as) {
		$res = [];
		if (!empty($this->relRec->corporates->$as)) 
			foreach ($this->relRec->corporates->$as as $corporate)
				$res[] = $this->helper->createCorporateStr($corporate);
		return $res;
		}

	public function getCorporateFull($as) {
		$res = [];
		if (!empty($this->relRec->corporates->$as))
			foreach ($this->relRec->corporates->$as as $corporate)
				$res[] = implode('|', [
						$corporate['name'] ?? null,
						$corporate['wikiQ'] ?? null,
						$corporate['role'] ?? null
						]);
		return $res;
		}
	
	public function getOtherAuthors() {
		if (!empty($this->relRec->persons->coAuthor)) {
			foreach ($this->relRec->persons->coAuthor as $person)
				$coAuthors[] = $this->helper->createPersonStr($person);
			return $this->removeArrayKeys($coAuthors);	
			}
		}
		
	public function getOtherAuthorsW() {
		if (!empty($this->relRec->persons->coAuthor)) {
			foreach ($this->relRec->persons->coAuthor as $person)
				$coAuthors[] = $this->personInitials($person);
			return $this->removeArrayKeys($coAuthors);	
			} 
		}
	

	
	
	public function getAuthorEvents() {
		$res = [];
		if (!empty($this->relRec->events->mainAuthor))
			$res[] = $this->helper->createEventStr(current ($this->relRec->events->mainAuthor));
		if (!empty($this->relRec->events->coAuthor))
			foreach ($this->relRec->events->coAuthor as $event)
				$res[] = $this->helper->createEventStr($event);
		return $this->removeArrayKeys($res);
		}
	
	public function getSubjectEvents() {
		$res = [];
		if (!empty($this->relRec->subject->events))
			foreach ($this->relRec->subject->events as $event)
				$res[] = $this->helper->createEventStr($event);
		return $this->removeArrayKeys($res);
		}
	
	
	
	public function getPublisher() {
		$res = [];
		if (!empty($this->relRec->publisher->corporates))
			foreach ($this->relRec->publisher->corporates as $publisher)
				$res[] = $this->helper->createCorporateStr($publisher);
		return $this->removeArrayKeys($res);
		}

		
	public function getSourcePublication() {
		$res = [];
		if (!empty($this->relRec->sourceDocument))
			foreach ($this->relRec->sourceDocument as $event)
				$res[] = $this->helper->createMagazineStr($event);
		return $this->removeArrayKeys($res);
		}
	
	public function getPersons($as) {
		if (!empty($this->relRec->persons->$as)) {
			$res = [];
			foreach ($this->relRec->persons->$as as $person)
				$res[] = $this->helper->createPersonStr($person);
			return $res;
			}
		}
	
	public function getPlaces($as) {
		if (!empty($this->relRec->places->$as)) {
			$res = [];
			foreach ($this->relRec->places->$as as $place)
				$res[] = $this->helper->createPlaceStr($place);
			return $res;
			}
		}
	
	public function getEvents($as) {
		if (!empty($this->relRec->events->$as)) {
			$res = [];
			foreach ($this->relRec->events->$as as $event)
				$res[] = $this->helper->createEventStr($event);
			return $res;
			}
		}
		
	public function getMagazines($as) { 
		$res = [];
		if (!empty($this->relRec->magazine->$as))
			foreach ($this->relRec->magazine->$as as $magazine)
				$res[] = $this->helper->createMagazineStr($magazine);
		return $this->removeArrayKeys($res);
		}

	
	
	public function getSubjects() {
		if (!empty($this->relRec->subject->strings))
			return $this->flattenArray($this->relRec->subject->strings);		
		}
	
	public function getSubjectsRows() {
		$res = [];
		if (!empty($this->relRec->subject->strings))
			foreach ($this->relRec->subject->strings as $subject)
				$res[] = implode('|',$subject);
		return $res;		
		}
	
	public function getSubjectsFull() {
		if (!empty($this->relRec->subject))
			return $this->flattenArray($this->relRec->subject);		
		}
	
	public function getSubjectELB($context = null) {
		$res = [];
		if ($context == null) {
			if (!empty($this->relRec->subject->elb))
				return $this->flattenArray($this->relRec->subject->elb);
			} elseif (!empty($this->relRec->subject->elb[$context]))
				return $this->flattenArray($this->relRec->subject->elb[$context]);		
		}
	

	public function getWorkKey() {
		$author = $this->getMainAuthorSort();
		$title = $this->helper->clearStr($this->relRec->title);
		
		$author = preg_replace("/[^a-z]+/", "", strtolower($author));
		$title = preg_replace("/[^a-z]+/", "", strtolower($title));
	
		return "AT $author $title";
		}
	
	/* to rebuild */
	public function getAutocomplete($source) { 
		$res = null;
		switch ($source) {
			case 'title' : 
				$res = $this->getTitleShort();
				$this->relRec->autocomplete[] = $res;
				break;
			case 'author' : 
				if (!empty($this->work->personsOnlyName['author'])) {
					$res = $this->removeArrayKeys($this->work->personsOnlyName['author']);
					foreach ($this->work->personsOnlyName['author'] as $name)
						$this->work->autocomplete[] = $name;
					}
				break;
			case 'subject' : 
				$res = $this->getSubjects();
				break;
			case 'linkedResource' : 
				if (!empty($this->work->linkedResources))
					$res = $this->removeArrayKeys($this->work->linkedResources);
				break;
			
			}
		
		return $res;
		}
		
		
	function getJsonRelations() {
		if (!empty($this->relRec))
			return json_encode($this->relRec);
		}	
	
	function getRelations() {
		if (!empty($this->relRec))
			return $this->relRec;
		}	
		
	
	
	
	
	
	
	
	
	
	
	
	public function getExternalValue($key) {
		// $this->buffer->externalSource[$key]->{this->relRec->rawId}
		
		return $this->relRec->exteralTags[$key] ?? null;
		}
	
	
	public function returnValue($value) {
		if (is_array($value))
			if (count($value) == 1)
				return current($value);
				else 
				return $value;
		if (is_string($value))
		return $value;
		}
		
	public function getRelValue($keys) {
		$keys = (array)$keys;
		$value = (array)$this->relRec;
		
		foreach ($keys as $key) {
			if (is_object($value))
				$value = (array)$value;
			if (!isset($value[$key])) {
				return null;
				}
			$value = $value[$key];
			}
		return $this->returnValue($value);
		}
		

	public function placeHasDescription($name) {
		if (is_string($name) && stristr($name, '(')) {
			$tmp = explode('(', $name);
			$searchPlace['name'] = $this->helper->clearName($tmp[0]);
			$searchPlace['desc'] = $this->helper->clearName(str_replace(')', '', $tmp[1]));
			} else 
			$searchPlace['name'] = $this->helper->clearName($name);
		return $searchPlace;
		}
		
	
	public function relPreparePlace($searchPlace, $as) {
		$place = $searchPlace;
		if (!empty($place['name'])) {
			if (stristr($place['name'], ',')) {
				// This part needs attention. We lose some of the data doing this
				$tmp = explode(',', $place['name']);
				$place['name'] = $tmp[0];
				}
			
			if (!empty($searchPlace['ids']) && count($searchPlace['ids'])>0) {
				foreach ($searchPlace['ids'] as $keyType => $key)
					if (substr($keyType,0,3) == 'yso') {
						$searchPlace['ids']['yso'] = $this->onlyNumbers($key);
						}	
				}
			$place['ids'] = $searchPlace['ids'] ?? [];
			
			
			$skey = $this->addMoreIds($place, 'place', $as);
			
			if (empty($this->relRec->places))
				$this->relRec->places = new stdClass;
			@$this->relRec->places->$as[$skey] = $place;
			@$this->relRec->places->all[$skey] = $place;
			}	
		#file_put_contents($this->outPutFolder.'relPreparePlace.log', "\n$as: ".print_r($place,1), FILE_APPEND);
		}
		
	public function registerException($function, $field, $desc) {
		file_put_contents($this->outPutFolder.'exeptions.txt', $this->relRec->id.';'.$function.';'.$field.';'.$desc."\n", FILE_APPEND);
		}
	
	public function relAddLanguage($field, $input) {
		$this->relRec->language[$field][] = $language;
		}
	
	public function relPreparePerson($line, $as = null) {
		$beforeChristStrings = ['př. Kr', 'p.n.e.'];
		
		
		if (!empty($line['code'])) {
		
			$person['name'] = 
			$person['dates'] = 
			$person['year_born'] = 
			$person['year_death'] =
			$person['viaf'] = 
			$person['wikiQ'] = '';
			$person['ids'] = [];
			$person['role'] = $as;
			$person['roles'] = [];
			
			$cv = $line['code'];
			
			// names
			if (!empty($cv['a'])) {
				if (is_Array($cv['a'])) {
					$person['name'] = implode(' ', $cv['a']);
					$this->registerException('relPreparePerson', $as, 'array in "a"');
					} else 
					$person['name'] = $cv['a'];
				}
			
			// dates
			if (!empty($cv['d'])) {
				if (is_array($cv['d'])) {
					$cv['d'] = implode(' ', $cv['d']);
					$this->registerException('relPreparePerson', $as, 'array in "d"');
					}
				
				$person['dates'] = $cv['d'];
				$tmp = explode('-', str_replace(['(',')'], '', $cv['d']));
				$person['year_born'] = floatval($tmp[0]);
				if (!empty($tmp[1]))
					$person['year_death'] = floatval($tmp[1]);
					else 
					$person['year_death'] = '';	
				if ($person['year_death'] == 0) 
					$person['year_death'] = '';
				if ($person['year_born'] == 0) 
					$person['year_born'] = '';
				
				if (($person['year_born']>0) & ($person['year_death']>0) & ($person['year_born']>$person['year_death'])) {
					$person['year_born'] = -$person['year_born'];
					$person['year_death'] = -$person['year_death'];
					}
				}
			
			// viaf
			if (!empty($cv['1'])) {
				if (is_array($cv['1']))
					$cv['1']=implode(' ',$cv['1']);
				$person['viaf'] = $this->viafFromStr( $cv['1'] ); 
				if (!empty($person['viaf']))
					$person['ids']['viaf'] = $person['viaf'];
				}
			
			// role strings	(this is first to overwrite with 4 if exists the same role)
			if (!empty($cv['e'])) {
				$testValue = (array)$cv['e'];
				foreach ($testValue as $role) 
					$person['roles'][$role] = $role;
				}
			// role codes
			if (!empty($cv['4'])) {
				$testValue = (array)$cv['4'];
				foreach ($testValue as $roleCode) 
					$person['roles'][$this->transleteCrativeRoles($roleCode)] = $this->transleteCrativeRoles($roleCode);
				}  
			if (empty($person['roles']) && stristr(strtolower($as), 'author'))
				$persons['roles'][] = 'unknown';
			
			// ids
			if (!empty($cv['7'])) {
				$testValue = (array)$cv['7'];
				foreach ($testValue as $id) 
					$person['ids'][] = $id;
				}  

			$skey = $this->addMoreIds($person, 'person', $as); // skey = wikiQ if exists
			if (empty($person['wikiQ']))
				$skey = $this->shortHash($person['name'].$person['dates']);
				
			if (empty($this->relRec->persons))
				$this->relRec->persons = new stdClass;
			@$this->relRec->persons->$as[$skey] = $person;
			@$this->relRec->persons->all[$skey] = $person;
			}
		}
	
	
	/*
	=110  2\$aRedakce$c[Vaše Literatura]$cwt$4aut
=110  2\$aRedakce$c[Vaše Literatura]$4aut
=110  2\$aČeská tisková kancelář$7ko2003196051$4aut
=110  2\$aRedakce$c[Fantasy Planet]$4aut
=110  2\$aRedakce$c[Zlatý máj]$4aut
=110  2\$aiDNES.cz$4aut
*/
	
	public function relPrepareCorporate($line, $as = null) {
		if (!empty($line['code'])) {
		
			$corporate['name'] =
			$corporate['nameML'] = '';
			$corporate['sub-name'] = []; 
			$corporate['viaf'] = '';
			$corporate['wikiQ'] = '';
			$corporate['role'] = $as;
			$corporate['ids'] = [];
			$corporate['roles'] = [];
			
			$cv = $line['code'];
			
			if (!empty($cv['c']) && is_array($cv['c'])) { // because rec: cz.002304612 has: =110  2\$aRedakce$c[Vaše Literatura]$cwt$4aut
				$test = current($cv['c']);
				if (substr($test,0,1) == '[') 
					$cv['c'] = $test;
				}
			
			if (!empty($cv['c']) && substr($cv['c'],0,1) == '[') {
				$corporate['name'] = str_replace(['[',']'], '' ,$cv['c']);
				
				if (!empty($cv['a'])) {
					$testValue = (array)$cv['a'];
					foreach ($testValue as $roleStr)
						$corporate['roles'][$roleStr] = $roleStr;
					}
				} else {
				// names
				if (!empty($cv['a'])) 
					if (is_Array($cv['a'])) {
						$corporate['name'] = implode(' ', $cv['a']);
						$this->registerException('relPrepareCorporate', $as, 'array in "a"');
						} else 
						$corporate['name'] = $this->helper->clearName($cv['a']);
				
				if (!empty($cv['b'])) {
					$testValue = (array)$cv['b'];
					foreach ($testValue as $name)
						$corporate['sub-name'][] = $name;
					}
				if (!empty($cv['c'])) {
					$testValue = (array)$cv['c'];
					foreach ($testValue as $name)
						$corporate['description'][] = $name;
					}
				} 
			// viaf
			if (!empty($cv['1'])) {
				if (is_array($cv['1']))
					$cv['1']=implode(' ',$cv['1']);
				$corporate['viaf'] = $this->viafFromStr( $cv['1'] ); 
				}
			// role strings	(this is first to overwrite with 4 if exists the same role)
			if (!empty($cv['e'])) {
				$testValue = (array)$cv['e'];
				foreach ($testValue as $role) 
					$corporate['roles'][$role] = $role;
				}
			// role codes
			if (!empty($cv['4'])) {
				$testValue = (array)$cv['4'];
				foreach ($testValue as $roleCode) 
					$corporate['roles'][$this->transleteCrativeRoles($roleCode)] = $this->transleteCrativeRoles($roleCode);
				}  
			if (empty($corporate['roles']) && stristr(strtolower($as), 'author'))
				$corporate['roles'][] = 'unknown';	
			
			// ids
			if (!empty($cv['7'])) {
				$testValue = (array)$cv['7'];
				foreach ($testValue as $id) 
					$corporate['ids'][] = $id;
				}  
			
			$skey = $this->addMoreIds($corporate, 'corporate', $as);

			if (empty($this->relRec->corporates))
				$this->relRec->corporates = new stdClass;
			
			@$this->relRec->corporates->all[$skey] = $corporate;
			@$this->relRec->corporates->$as[$skey] = $corporate;
			}
		}
		
	public function addMoreIds(&$someThing, $type, $as) {
		/*
		$bl['name'] =  $someThing['name'];
		if ($type == 'person') $bl['dates'] =  $someThing['dates'] ?? null;
		$bl['viaf'] = $someThing['ids']['viaf'] ?? null;
		$other_id = null;
		if (!empty($someThing['ids']) && empty($someThing['wikiQ'])) 
			foreach( $someThing['ids'] as $idType => $idValue) 
				if (($idType!=='viaf')&($idType!=='wikiQ')&(empty($other_id)))
					$other_id = $idValue;
		$bl['other_id'] = $other_id ?? null;
		
		$someThing['biblio_label'] = $biblio_label = implode('|', $bl);		
		
		#$someThing['wikiQ'] = $this->localSearcher->getWikiQ($someThing, $type);
		$someThing['wikiQ'] = $this->localSearcher->getWikiQLabelMethod($biblio_label, $type);
		$someThing['bestLabel'] = $this->localSearcher->getBestLabel($biblio_label, $type);
		
		// nie szukaj jeśli krok 3 - dodać tutaj warunek i dodać odczytywnie viaf w zlisty psql poprzedniego szukania. 
		if (!empty($someThing['ids'])) {
			if ($this->workingStep !== 3)
				foreach( $someThing['ids'] as $idType => $idValue) {
					if (empty($someThing['viaf']) or empty($someThing['wikiQ'])) {
						$response = $this->viafSearcher->getIdByOtherId($idValue, $idType);
						if (empty($someThing['viaf']) & !empty($response['viaf']))
							$someThing['viaf'] = $response['viaf'];
						if (empty($someThing['wikiQ']) & !empty($response['wikiQ'])) {
							$someThing['wikiQ'] = $response['wikiQ'] ?? null;
							$this->localSearcher->saveMatching($someThing['biblio_label'], $type, 'by id', 'viaf', $someThing['wikiQ'], 100);
							}
						}
					}
			} else {
			$someThing['ids'] = [];	
			}
			
		if ($this->workingStep !== 3)
		if (empty($someThing['wikiQ']) && !empty($someThing['ids'])) {
			$someThing['wikiQ'] = $this->wikiSearcher->getIdByOtherId($someThing['ids'], $type); 
			if (!empty($someThing['wikiQ']))
				$this->localSearcher->saveMatching($someThing['biblio_label'], $type, 'by id', 'wiki', $someThing['wikiQ'], 100);
			}
		
		$stringToFind = $someThing['name'];
		if (!empty($someThing['dates']))
			$stringToFind .= ' '.$someThing['dates'];
		
		if ($this->workingStep !== 3)
		if (empty($someThing['viaf'])) {
			$response = $this->viafSearcher->getIdByLabel($stringToFind); 
			if (empty($someThing['viaf']) & !empty($response['viaf']))
				$someThing['viaf'] = $response['viaf'];
			if (empty($someThing['wikiQ']) & !empty($response['wikiQ'])) {
				$someThing['wikiQ'] = $response['wikiQ'] ?? null;
				$this->localSearcher->saveMatching($someThing['biblio_label'], $type, 'by label', 'viaf', $someThing['wikiQ'], $response['matchLevel']);
				}
			}
			
		if (empty($someThing['wikiQ'])) {
			$response = $this->wikiSearcher->getIdByLabel($type, $stringToFind, $someThing); 
			if (!empty($response['wikiQ'])) {
				$someThing['wikiQ'] = $response['wikiQ']; 
				$this->localSearcher->saveMatching($someThing['biblio_label'], $type, 'by label', 'wiki', $someThing['wikiQ'], $response['matchLevel']);
				}
			}
			
		if (!empty($someThing['wikiQ']) && ($someThing['wikiQ'] !== 'not found')) {
			$skey = $someThing['wikiQ'];
			
			$this->bufferWikiAdd($someThing['wikiQ'], $biblio_label, $as, $type);
			$someThing['nameML'] = $this->buffer->wikiQ[$type][$someThing['wikiQ']]['nameML'];
			} else {
			$skey = $this->shortHash($someThing['name']);
			$this->bufferStrAdd($someThing['name'], $as, $type);
			
			if ($this->workingStep !== 3)
				$this->localSearcher->saveMatching($someThing['biblio_label'], $type, 'any', 'any', 'not found', 0);
			}
		
		return $skey;
		*/
		}	
		
		
		
	public function relCorporateFromStr($string, $as) {
		$array = (array)$string;
		foreach ($array as $string) {
			$skey = $this->shortHash($string);
			$corporate['name'] = $string;
			$corporate['role'] = $as;
			
			if (empty($this->relRec->corporates))
				$this->relRec->corporates = new stdClass;
			
			@$this->relRec->corporates->all[$skey] = $corporate;
			@$this->relRec->corporates->$as[$skey] = $corporate;
			
			$this->bufferStrAdd($string, $as, 'corporate');
			}
		}
	
	public function relPrepareEvent($line, $as = null) {
		if (!empty($line['code'])) {
		
			$event['name'] = 
			$event['type'] = 
			$event['year'] = 
			$event['edition'] = 
			$event['viaf'] = 
			$event['wikiQ'] = '';
			$event['role'] = $as;
			$event['ids'] = [];
			$event['roles'] = [];
			
			$cv = $line['code'];
			
			if (!empty($cv['a'])) {
				if (is_array($cv['a']))
					$event['name'] = current($cv['a']); // it's because of errors in PBL records. Probably should be changed! TO DO!
					else 
					$event['name'] = $cv['a'];
				}
			if (!empty($cv['b'])) 
				$event['type'] = $this->helper->clearName($cv['b']);
			if (!empty($cv['c'])) {
				$event['place'] = $this->helper->clearName($cv['c']);
				$searchPlace = $this->placeHasDescription($event['place']);
				$this->relPreparePlace($searchPlace, 'eventPlace');
				}
			if (!empty($cv['d'])) 
				$event['year'] = $this->helper->clearName($cv['d']);
			if (!empty($cv['n'])) 
				$event['edition'] = $this->helper->clearName($cv['n']);
				
			if ($as == 'mainAuthor') 
				$this->relRec->format[] = 'Conference Proceeding';
				
				
			// viaf
			if (!empty($cv['1'])) {
				if (is_array($cv['1']))
					$cv['1']=implode(' ',$cv['1']);
				$event['viaf'] = $this->viafFromStr( $cv['1'] ); 
				}
			// role strings	(this is first to overwrite with 4 if exists the same role)
			if (!empty($cv['e'])) {
				$testValue = (array)$cv['e'];
				foreach ($testValue as $role) 
					$event['roles'][$role] = $role;
				}
			// role codes
			if (!empty($cv['4'])) {
				$testValue = (array)$cv['4'];
				foreach ($testValue as $roleCode) 
					$event['roles'][$this->transleteCrativeRoles($roleCode)] = $this->transleteCrativeRoles($roleCode);
				}  
			if (empty($event['roles']) && stristr(strtolower($as), 'author'))
				$event['roles'][] = 'unknown';	
			
			
			$skey = $this->addMoreIds($event, 'event', $as);
			if (empty($this->relRec->events))
				$this->relRec->events = new stdClass;
			
			@$this->relRec->events->$as[$skey] = $event;
			@$this->relRec->events->all[$skey] = $event;
			}
		}
		
	public function relPrepareMagazine($line, $as = null) {
		if (!empty($line['code'])) {
			$as = $as ?? 'publisher';
			$magazine = [];
			$cv = $line['code'];	
				
			if (!empty($cv['x'])) {
				if (is_array($cv['x'])) {
					$cv['x'] = current($cv['x']);
					$this->registerException('field773', $cv['x'], 'array in "issn"');
					} 

				$this->relRec->issn[] = $cv['x'];
				$magazine['ids']['issn'] = $magazine['issn'] = current((array)$cv['x']);
				}
				
			if (!empty($cv['z'])) {
				if (is_array($cv['z'])) {
					$cv['z'] = current($cv['z']);
					$this->registerException('field773', $cv['z'], 'array in "issn"');
					}
				$this->relRec->isbn[] = $cv['z'];
				$magazine['ids']['isbn'] = $magazine['isbn'] = $cv['z'];
				}
				
			if (!empty($cv['s'])) {
				$magazine['title'] = $cv['s'];
				} elseif (!empty($cv['t'])) {
					// exeption 
					if (is_array($cv['t'])) {
						foreach ($cv['t'] as $title)
							$Ttitle[strlen($title)] = $title;
						krsort($Ttitle);	
						$magazine['title'] = current($Ttitle);
						}
					if (is_string($cv['t']))	
						$magazine['title'] = $cv['t'];	
				} elseif (!empty($cv['i']) && (strlen($cv['i'])>3)) {
					$tmp = explode(':',$cv['i']);
					if (count($tmp)>1) 
						$magazine['title'] = chop(trim($tmp[1]));
				}
			if (!empty($cv['w']) && is_string($cv['w']))	
				$magazine['resourceId'] = $cv['w'];
			
			if (!empty($cv['g']))
				$magazine['relatedParts'] = $cv['g'];
			
			
			if (!empty($magazine['title'])) {
				if ($this->relRec->majorFormat == 'Journal article') {
					
					// bufferAdd?
					if (!empty($magazine['issn'])) {
						$key = $this->helper->clearStr($magazine['issn']);
						@$this->buffer->issn['magazine'][$key]['issn'][$magazine['issn']]++;	
						$block = 'issn';
						} else {
						$key = $this->helper->clearStr($magazine['title']);
						$block = 'str';
						}	
					
					$magazine['name'] = current((array)$magazine['title']);
					$skey = $this->addMoreIds($magazine, 'magazine', $as);	
					unset($magazine['name']);
					if (empty($this->relRec->magazines))
						$this->relRec->magazines = new stdClass;
					
					@$this->relRec->magazines->$as[$skey] = $magazine;	
					@$this->relRec->magazines->all[$skey] = $magazine;	
					#$magazine['wikiq'] = $this->wikiSearcher->getIdByLabel('magazine', $magazine['title']);
					if (!empty($magazine['wikiq']))	{
						$wikiQ = $magazine['wikiq'];
						@$this->buffer->wikiQ['magazine'][$wikiQ]['biblio_labels'][$magazine['title']]++;							
						} else if (!empty($magazine['title']))	
						@$this->buffer->$block['magazine'][$key]['biblio_labels'][$magazine['title']]++;	
					if (!empty($magazine['resourceId']))
						@$this->buffer->$block['magazine'][$key]['resourceId'][$magazine['resourceId']]++;		
					
					
					} else {
					$this->relRec->sourceDocument[] = $magazine;	
					}
				}
			}
		}
		
	function relGetMajorFormat($content) {
		if (!empty($this->currentUpdates->overwrite->majorFormat))
			return $this->currentUpdates->overwrite->majorFormat;
		$formats = [
			'a' => 'Book chapter',
			'b' => 'Journal article',
			'm' => 'Book'
			];
		$code = substr($content, 7, 1);	
		if (array_key_exists($code, $formats))
			return $formats[$code];
			else 
			return 'Other';
		}
	
		
	public function relCenturies($line) {
		$finCent = ['yso/fin'];
		$errorText = 'Undectectable';
		$strToRemove = [
					'od ',
					'-luku',
					'-talet',
					'-luvut',
					'.'
					];

		if (!empty($line['code']['a']) && is_string($line['code']['a'])) {
			$era = str_replace($strToRemove, '', strtolower($line['code']['a']));

			if (!empty($line['code']['2'])) 
				$format = $line['code']['2'];
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
				
		if (!empty($res)) {
			if ((count($res)>1) & array_key_exists($errorText, $res))
				unset ($res[$errorText]);
			return $this->removeArrayKeys($res);
			} else 
			return ['Undefined'];
		}	
	
	public function createMulilangString($values) {
		$returnArray = [];
		$values = (array)$values;
		$defStr = $values['en'] ?? '';
		foreach ($this->cms->configJson->settings->multiLanguage->order as $lang) {
			$returnArray[] = $values[$lang] ?? $defStr;
			}
		return implode('|', $returnArray);	
		}
	
	
	public function relAddUDC($content) {
		if (empty($this->udcMeaning)) {
			$this->udcMeaning = json_decode(file_get_contents($this->configPath.'import/udc.json'));
			}
		$udc = [];
		if (!empty($content)) 
			foreach ($content as $line) {
				if (!empty($line['code']['a'])) {
					$arr = (array)$line['code']['a'];
					foreach ($arr as $value) {
						$code = $this->onlyNumbers($value);
						$l = substr($code,0,1);
						if ($l == 5) {
							$k = substr($code,1,1);
							if ($k == 1)
								$udc[] = 'udc_51';
								else 
								$udc[] = 'udc_5x';											
							} else if (is_numeric($l) && ($l<>4))
								$udc[] = 'udc_'.$l;
						}
					}
				}
		if (!empty($udc)) {
			foreach ($udc as $key=>$val) {
				if (!empty($this->udcMeaning->$val))
					$udc[$key] = $this->createMulilangString($this->udcMeaning->$val); 
				}
			
			$this->relAddSubject('UDC', $this->removeArrayKeys($udc));
			}
		}
	
	public function relAddSubject($group, $values, $realValues = '') {
		if (empty($this->relRec->subject))
			$this->relRec->subject = new stdClass;
		
		if (!empty($realValues))
			$this->relRec->subject->$group[$values][] = $realValues;
			else {
			if (is_array($values))	
				foreach ($values as $key=>$value)
					if ($value == 'OPRAVA UCL') {
						unset($values[$key]);
						// 
						}
			$this->relRec->subject->$group[] = $values;
			}
		}
	
	
	public function getRecordContains() {
		$res = [];
		
		$hasSomething = false;
		$fields = ['persons', 'places', 'corporates', 'events', 'magazines'];
		$ondrejTable = [];
		foreach ($fields as $field) {
			$ondrejTable[$field] = 0;
			if (!empty($this->relRec->$field)) {
				$res[] = $field;
				#file_put_contents($this->outPutFolder.'ondrejTable.txt', $this->relRec->id."\n".print_r($this->relRec->$field, 1)."\n\n", FILE_APPEND);
				/*	
				foreach ($this->relRec->$field->all as $k=>$v)
					if (!empty($v['wikiQ']) && ($v['wikiQ'] != 'not found'))
						$ondrejTable[$field] = 1;
				*/	
				}
			}	
		#file_put_contents($this->outPutFolder.'ondrejTable.csv', $this->relRec->id.';'.implode(';', $ondrejTable)."\n", FILE_APPEND);
			
		
		if (!empty($this->relRec->hasDuplicate))
			$res[] = 'has duplicates';
		if (!empty($this->relRec->description))
			$res[] = 'description';
		if (!empty($this->relRec->linkedResources))
			$res[] = 'linkedResources';
		if (!empty($this->relRec->linkedResources['fullText']))
			$res[] = 'fullText link';
		if (!empty($this->relRec->internalResources))
			$res[] = 'internalResources';
		if (!empty($this->relRec->exteralTags))
			$res[] = 'exteralTags';
		
		if (!empty($this->relRec->wikiQ))
			$res[] = 'wikidata '.count($this->relRec->wikiQ);
			else {
			if (empty($res))
				$res[] = "probably nothing important";
			$res[] = 'no wikidata links';
			}
		
		return $this->flattenArray($res);
		}

	
	public function getLicence($currentGroup) {
		if (!empty($this->licences[$currentGroup]))
			return $this->licences[$currentGroup];
		}
	
	public function getLicenceOneLine() {
		$currentGroup = $this->getFileGroupFromName($this->currentFile['name']);
		if (!empty($this->licences[$currentGroup])) {
			$licence = $this->licences[$currentGroup];
			$name = $licence->name ?? '';
			$link = $licence->link ?? '';
			return $name.'|'.$link;
			}
		}
	
	
	public function addNewTopic($field, $line) {
		$subFieldsMeaning = [
				'0' => 'viaf',
				'2' => 'type_of_id',
				'7' => 'value_id',
				];
		$item = [];	
		$item['field'] = $field;
		foreach ($subFieldsMeaning as $key=>$varible) {
			$item[$varible] = null;
			if (!empty($line['code'][$key])) {
				$item[$varible] = implode(', ',(array)$line['code'][$key]);
				unset($line['code'][$key]);
				}
			}				
		$item['value'] = json_encode($line['code']); 
		$lineKey = md5(json_encode($item));
		$this->topicValues[$lineKey] = $item;
		if (empty($this->topicCounts[$lineKey])) {
			$this->topicCounts[$lineKey] = 1;
			$this->psql->query("
				INSERT INTO elb_tmp_subjects (id, field, value, value_id, type_of_id, viaf)
					VALUES ('$lineKey', '$field', {$this->psql->string($item['value'])}, {$this->psql->string($item['value_id'])}, {$this->psql->string($item['type_of_id'])}, {$this->psql->string($item['viaf'])});
				");
			} else {
			$this->topicCounts[$lineKey]++;
			}	
		}
		
	public function saveAllTopicCount() {
		if (!empty($this->topicCounts))
			foreach ($this->topicCounts as $lineKey => $count) {
				$this->psql->query("
					UPDATE elb_tmp_subjects SET items='$count' 
						WHERE id = '{$lineKey}';
					");
				} 
		}	
	
	
	public function saveAllTopics() {
		if (!empty($this->record)) {
			foreach ($this->record as $field => $content) {
				if (($field >= '601') & ($field <= '699')) {
					foreach ($content as $line) {
						$this->addNewTopic($field, $line);
						}		
					}
				}
			}
		}
	
	/*

	to check what's happend use:
	SELECT type_of_id,field,count(*) FROM elb_tmp_subjects GROUP BY type_of_id,field ORDER BY type_of_id, count(*) DESC;
	*/
	
	/* subjects fields:
https://www.loc.gov/marc/bibliographic/bd6xx.html	
600			- subject person	
608:133   	- w ELB wszystkie w stylu:   [u] => http://libri.ucl.cas.cz/Record/b0000005153009   [y] => part of: Pogranicza (nie tylko) Podkarpacia, Rzeszów: Wydawnictwo Uniwersytetu Rzeszowskiego, 2016
610:40868 	- subject corporate
611:22280 	- meeting name
618:148 	- tak samo jak 608
628:125		- tak samo jak 608
630:37810	- Uniform Title (fajne dane ;-)
638:141		- tak samo jak 608
640:3		- czasem daty czasem zwykły subject
647:7		- Named Event (ale coś nie tak w danych) 
648:4147	- Chronological Term
650:216037	- Topical Term (tutaj dużo ID)
651:38864	- Geographic Name
653:63300	- Uncontrolled (często wielopoziomowe - poza ID)
655:21359	- Genre/Form
656:5		- Occupation (ale coś nie tak w danych)
657:7		- Function (ale coś nie tak w danych)
658:332		- Curriculum Objective (chyba tylko polskie dane - dziedzina życia - raczej łatwo połączyć wikidata items)
660:3		- 
667:13		-
668:155		-
670:6
672:1
678:136
680:2
684:1
688:136
690:78
693:3
698:152
699:4
*/

	public function addTitle($title) {
		if (empty($title)) return null;
		if (is_string($title)) {
			if (empty($this->oftenReapeted->title2id[$title])) {
				$t = $this->psql->querySelect("INSERT INTO elb_titles (title) VALUES ({$this->psql->string($title)})
					ON CONFLICT (title) 
					DO UPDATE SET title = elb_titles.title
					RETURNING id;");
				if (is_Array($t)) {
					$row = current($t);
					if ($title == '[Název textu k dispozici na připojeném lístku]')
						$this->oftenReapeted->title2id[$title] = $row['id'];
					return $row['id'];
					}
				} else {
				return $this->oftenReapeted->title2id[$title];
				}
			}
		}
	
	public function addTitleSupplemental($title) {
		if (empty($title)) return null;
		$t = $this->psql->querySelect("INSERT INTO elb_titles_reminder (remainder_of_title) VALUES ({$this->psql->string($title)})
			ON CONFLICT (remainder_of_title) 
			DO UPDATE SET remainder_of_title = elb_titles_reminder.remainder_of_title
			RETURNING id;");
		if (is_Array($t)) {
			$row = current($t);
			return $row['id'];
			}
		}
	
		
	
	public function addStatementOfResponsibility($value) {
		if (empty($value)) return null;
		$t = $this->psql->querySelect("INSERT INTO elb_statement_of_responsibility (content) VALUES ({$this->psql->string($value)})
			ON CONFLICT (content) 
			DO UPDATE SET content = elb_statement_of_responsibility.content
			RETURNING id;");
		if (is_Array($t)) {
			$row = current($t);
			return $row['id'];
			}
		}
	
	public function addPublicationErrors($id, $errors) {
		if (empty($errors)) return null;
		$errors = (array)$errors;
		$return = [];
		foreach ($errors as $error) {
			$id_error = null;
			if (empty($this->oftenReapeted->error2id[$error])) {
				$t = $this->psql->querySelect("INSERT INTO elb_errors (msg) VALUES ({$this->psql->string($error)})
					ON CONFLICT (msg) 
					DO UPDATE SET msg = elb_errors.msg
					RETURNING id;");
				if (is_Array($t)) {
					$row = current($t);
					$id_error = $row['id'];
					$this->oftenReapeted->error2id[$error] = $id_error; 
					}
				} else {
				$id_error = $this->oftenReapeted->error2id[$error];
				}
			if (!empty($id) & !empty($id_error))
				$this->psql->query("INSERT INTO elb_publication_error (id_publication, id_error) VALUES ($id, $id_error);");
			}
		}

	private function addLanguages($id, $languages) {
		if (empty($languages)) return null;
		$roleCodes = [
			'publication' => 1,
			'record' => 2,
			'original' => 3
			];
		$return = [];
		foreach ($languages as $group=>$keys) {
			foreach ($keys as $key)
				if (!empty($key))
					$this->psql->query("INSERT INTO elb_languages (id_publication, lang_role, id_language) VALUES ('$id', {$this->psql->integer($roleCodes[$group])}, {$this->psql->integer($key)});");
			}
		}
		
	private function addLanguagesUnrecognized($id, $languages) {
		if (empty($languages)) return null;
		$roleCodes = [
			'publication' => 1,
			'record' => 2,
			'original' => 3
			];
		foreach ($languages as $group=>$keys) {
			foreach ($keys as $key)
				if (!empty($key))
					$this->psql->query("INSERT INTO elb_languages_unrecognized (id_publication, lang_role, language_string) 
							VALUES ('$id', {$this->psql->integer($roleCodes[$group])}, {$this->psql->string($key)})
							ON CONFLICT (id_publication, lang_role, language_string) 
							DO NOTHING;");
			}
		}
	
	private function addPublicationYear($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && is_numeric($value))
				$this->psql->query("INSERT INTO elb_publication_years (id_publication, year) VALUES ($id, {$this->psql->isNull($value)})
					ON CONFLICT (id_publication, year) 
					DO NOTHING;");
			}
		}
	
	private function addPublicationDates($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && $this->validateDate($value))
				$this->psql->query("INSERT INTO elb_publication_dates (id_publication, pdate) VALUES ($id, {$this->psql->isNull($value)}) ON CONFLICT (id_publication, pdate) DO NOTHING;");
			}
		}
	
	private function addPublicationDateStr($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && is_string($value))
				$this->psql->query("INSERT INTO elb_publication_datestr (id_publication, date_string) VALUES ($id, {$this->psql->string($value)}) ON CONFLICT (id_publication, date_string) DO NOTHING;");
			}
		}
	
	function validateDate($date, $format = 'Y-m-d H:i:s') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
		#return checkdate($m,$d,$y);
		}
	
	private function addMainRole($name) {
		if (empty($name)) return null;
		if (!empty($this->mainRoles[$name])) return $this->mainRoles[$name];
		$name = trim($name);
		$t = $this->psql->querySelect("INSERT INTO dic_main_roles (main_role) VALUES ({$this->psql->string($name)}) 
			ON CONFLICT (main_role) 
			DO UPDATE SET main_role = dic_main_roles.main_role
			RETURNING id_role;");
		if (is_Array($t)) {
			$this->mainRoles[$name] = current($t)['id_role'];
			return $this->mainRoles[$name];
			}
		}
	
	private function addPublicationISBN($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && is_string($value))
				$this->psql->query("INSERT INTO elb_publication_isbn (id_publication, isbn) VALUES ($id, {$this->psql->string($value)}) ON CONFLICT (id_publication, isbn) DO NOTHING;");
			}
		}
	
	private function addPublicationISSN($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && is_string($value))
				$this->psql->query("INSERT INTO elb_publication_issn (id_publication, issn) VALUES ($id, {$this->psql->string($value)}) ON CONFLICT (id_publication, issn) DO NOTHING;");
			}
		}
	
	private function addPublicationUDC($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $value) {
			if (!empty($value) && is_string($value))
				$this->psql->query("INSERT INTO elb_publication_udc (id_publication, udc_code) VALUES ($id, {$this->psql->string($value)}) ON CONFLICT (id_publication, udc_code) DO NOTHING;");
			}
		}
	
	private function addPersonRaw($name = '', $date_range = '') {
		if (empty($name) & empty($date_range)) return null;
		$name = trim($name);
		$date_range = trim($date_range);
		if (empty($date_range))
			$date_range = '-';
		$clearStr = $this->helper->clearLatin($name.' '.$date_range);
		if (!empty($name)) {
			$t = $this->psql->querySelect("INSERT INTO elb_persons_raw (name, date_range, clear_str) VALUES ({$this->psql->string($name)}, {$this->psql->string($date_range)}, {$this->psql->string($clearStr)}) 
				ON CONFLICT (name, date_range) 
				DO UPDATE SET name = elb_persons_raw.name
				RETURNING id;");
			if (is_Array($t)) {
				return current($t)['id'];
				}
			}
		}
	
	private function addPublicationPersons($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $mainRole=>$items) {
			foreach ($items as $item) {
				$personId = $this->addPersonRaw($item['name'], $item['dates']);
				if (!empty($personId)) {
					if (!empty($item['ids']) && is_array($item['ids']))
						foreach ($item['ids'] as $itemOID) {
							$t = $this->psql->querySelect("INSERT INTO elb_persons_raw_ids (id_person_raw, other_id) 
								VALUES ('$personId', {$this->psql->string($itemOID)})
								ON CONFLICT (id_person_raw, other_id)
								DO NOTHING;");
								}
					$t = $this->psql->querySelect("INSERT INTO elb_publication_persons (id_publication, id_person, id_main_role) 
							VALUES ('$id', '$personId', {$this->psql->integer($this->addMainRole($mainRole))})
							ON CONFLICT (id_publication, id_person, id_main_role)
							DO UPDATE SET id_main_role = elb_publication_persons.id_main_role
							RETURNING id;");
					if (is_Array($t)) {
						$itemInPublicationId = current($t)['id'];
						}
					if (!empty($item['creator_roles_code']) && is_array($item['creator_roles_code']))
						foreach ($item['creator_roles_code'] as $role) {
							$t = $this->psql->querySelect("INSERT INTO elb_publication_persons_roles (id_person_in_publication, is_code, creator_role) 
								VALUES ('$itemInPublicationId', true, {$this->psql->string($role)})
								ON CONFLICT (id_person_in_publication, is_code, creator_role)
								DO NOTHING;");
								}
					if (!empty($item['creator_roles_str']) && is_array($item['creator_roles_str']))
						foreach ($item['creator_roles_str'] as $role) 
							if (!empty($role)) {
								$t = $this->psql->querySelect("INSERT INTO elb_publication_persons_roles (id_person_in_publication, is_code, creator_role) 
									VALUES ('$itemInPublicationId', false, {$this->psql->string($role)})
									ON CONFLICT (id_person_in_publication, is_code, creator_role)
									DO NOTHING;");
									}
					} 
				}
			}
		}
	
	private function addCorporateRaw($name = '') {
		$name = trim($name);
		if (empty($name)) return null;
		$clearStr = $this->helper->clearLatin($name);
		$t = $this->psql->querySelect("INSERT INTO elb_corporates_raw (name, clear_str) VALUES ({$this->psql->string($name)}, {$this->psql->string($clearStr)}) 
			ON CONFLICT (name) 
			DO UPDATE SET name = elb_corporates_raw.name
			RETURNING id;");
		if (is_Array($t)) {
			return current($t)['id'];
			}
		}
	
	private function addPublicationCorporates($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $mainRole=>$items) {
			foreach ($items as $item) {
				$itemId = $this->addCorporateRaw($item['name']);
				if (!empty($itemId)) {
					if (!empty($item['ids']) && is_array($item['ids']))
						foreach ($item['ids'] as $itemOID) {
							$t = $this->psql->querySelect("INSERT INTO elb_corporates_raw_ids (id_corporate_raw, other_id) 
								VALUES ('$itemId', {$this->psql->string($itemOID)})
								ON CONFLICT (id_corporate_raw, other_id)
								DO NOTHING;");
								}
					$t = $this->psql->querySelect("INSERT INTO elb_publication_corporates (id_publication, id_corporate_raw, id_main_role) 
							VALUES ('$id', '$itemId', {$this->psql->integer($this->addMainRole($mainRole))})
							ON CONFLICT (id_publication, id_corporate_raw, id_main_role)
							DO UPDATE SET id_main_role = elb_publication_corporates.id_main_role
							RETURNING id;");
					if (is_Array($t)) {
						$itemInPublicationId = current($t)['id'];
						}
					if (!empty($item['creator_roles_code']) && is_array($item['creator_roles_code']))
						foreach ($item['creator_roles_code'] as $role) 
							if (!empty($role)) {
								$t = $this->psql->querySelect("INSERT INTO elb_publication_corporates_roles (id_item_in_publication, is_code, creator_role) 
									VALUES ('$itemInPublicationId', true, {$this->psql->string($role)})
									ON CONFLICT (id_item_in_publication, is_code, creator_role)
									DO NOTHING;");
									}
					if (!empty($item['creator_roles_str']) && is_array($item['creator_roles_str']))
						foreach ($item['creator_roles_str'] as $role) 
							if (!empty($role)) {
								$t = $this->psql->querySelect("INSERT INTO elb_publication_corporates_roles (id_item_in_publication, is_code, creator_role) 
									VALUES ('$itemInPublicationId', false, {$this->psql->string($role)})
									ON CONFLICT (id_item_in_publication, is_code, creator_role)
									DO NOTHING;");
									}
					}
				}
			}
		}
	
	private function addEventRaw($name = '') {
		$name = trim($name);
		if (empty($name)) return null;
		$clearStr = $this->helper->clearLatin($name);
		$t = $this->psql->querySelect("INSERT INTO elb_events_raw (name, clear_str) VALUES ({$this->psql->string($name)}, {$this->psql->string($clearStr)}) 
			ON CONFLICT (name) 
			DO UPDATE SET name = elb_events_raw.name
			RETURNING id;");
		if (is_Array($t)) {
			return current($t)['id'];
			}
		}
	
	private function addPublicationEvents($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $mainRole=>$items) {
			foreach ($items as $item) {
				$itemId = $this->addEventRaw($item['name']);
				if (!empty($itemId)) {
					if (!empty($item['ids']) && is_array($item['ids']))
						foreach ($item['ids'] as $itemOID) {
							$t = $this->psql->querySelect("INSERT INTO elb_events_raw_ids (id_event_raw, other_id) 
								VALUES ('$itemId', {$this->psql->string($itemOID)})
								ON CONFLICT (id_event_raw, other_id)
								DO NOTHING;");
								}
					$t = $this->psql->querySelect("INSERT INTO elb_publication_events (id_publication, id_event_raw, id_main_role) 
							VALUES ('$id', '$itemId', {$this->psql->integer($this->addMainRole($mainRole))})
							ON CONFLICT (id_publication, id_event_raw, id_main_role)
							DO UPDATE SET id_main_role = elb_publication_events.id_main_role
							RETURNING id;");
					if (is_Array($t)) {
						$itemInPublicationId = current($t)['id'];
						}
					if (!empty($item['creator_roles_code']) && is_array($item['creator_roles_code']))
						foreach ($item['creator_roles_code'] as $role) 
							if (!empty($role)) {
								$t = $this->psql->querySelect("INSERT INTO elb_publication_events_roles (id_item_in_publication, is_code, creator_role) 
									VALUES ('$itemInPublicationId', true, {$this->psql->string($role)})
									ON CONFLICT (id_item_in_publication, is_code, creator_role)
									DO NOTHING;");
									}
					if (!empty($item['creator_roles_str']) && is_array($item['creator_roles_str']))
						foreach ($item['creator_roles_str'] as $role) 
							if (!empty($role)) {
								$t = $this->psql->querySelect("INSERT INTO elb_publication_events_roles (id_item_in_publication, is_code, creator_role) 
									VALUES ('$itemInPublicationId', false, {$this->psql->string($role)})
									ON CONFLICT (id_item_in_publication, is_code, creator_role)
									DO NOTHING;");
									}
					}
				}
			}
		}
	
	private function addPlaceRaw($name = '') {
		$name = trim($name);
		if (empty($name)) return null;
		$clearStr = $this->helper->clearLatin($name);
		$t = $this->psql->querySelect("INSERT INTO elb_places_raw (name, clear_str) VALUES ({$this->psql->string($name)}, {$this->psql->string($clearStr)}) 
			ON CONFLICT (name) 
			DO UPDATE SET name = elb_places_raw.name
			RETURNING id;");
		if (is_Array($t)) {
			return current($t)['id'];
			}
		}
	
	private function addPublicationPlaces($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $mainRole=>$items) {
			foreach ($items as $item) 
				if (!empty($item['name'])) {
					$itemId = $this->addPlaceRaw($item['name']);
					if (!empty($itemId)) {
						if (!empty($item['ids']) && is_array($item['ids']))
							foreach ($item['ids'] as $itemOID) {
								$t = $this->psql->querySelect("INSERT INTO elb_places_raw_ids (id_place_raw, other_id) 
									VALUES ('$itemId', {$this->psql->string($itemOID)})
									ON CONFLICT (id_place_raw, other_id)
									DO NOTHING;");
									}
						$t = $this->psql->querySelect("INSERT INTO elb_publication_places (id_publication, id_place_raw, id_main_role) 
								VALUES ('$id', '$itemId', {$this->psql->integer($this->addMainRole($mainRole))})
								ON CONFLICT (id_publication, id_place_raw, id_main_role)
								DO UPDATE SET id_main_role = elb_publication_places.id_main_role
								RETURNING id_place_raw;");
						}
				}
			}
		}
	
	private function addMagazineRaw($name = '') {
		$name = trim($name);
		if (empty($name)) return null;
		$clearStr = $this->helper->clearLatin($name);
		$t = $this->psql->querySelect($Q = "INSERT INTO elb_magazines_raw (name, clear_str) VALUES ({$this->psql->string($name)}, {$this->psql->string($clearStr)}) 
			ON CONFLICT (name) 
			DO UPDATE SET name = elb_magazines_raw.name
			RETURNING id;");
		#echo $Q."\n";		
		if (is_Array($t)) {
			return current($t)['id'];
			}
		}
	
	private function addPublicationMagazines($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $mainRole=>$items) {
			foreach ($items as $item) 
				if (!empty($item['title'])) {
					$itemId = $this->addMagazineRaw($item['title']);
					if (!empty($itemId)) {
						if (!empty($item['ids']) && is_array($item['ids']))
							foreach ($item['ids'] as $idName => $itemOID) {
								if (is_numeric($idName))
									$idName = '';
								$t = $this->psql->querySelect("INSERT INTO elb_magazines_raw_ids (id_magazine_raw, other_id, id_name) 
									VALUES ('$itemId', {$this->psql->string($itemOID)}, {$this->psql->string($idName)})
									ON CONFLICT (id_magazine_raw, other_id)
									DO NOTHING;");
									}
						if (!empty($item['inMagazine']))
							$t = $this->psql->querySelect("INSERT INTO elb_publication_magazines (id_publication, id_magazine_raw, id_main_role, volume, publish_year, issue, page) 
								VALUES ('$id', '$itemId', {$this->psql->integer($this->addMainRole($mainRole))}, {$this->psql->string($item['inMagazine']->volume ?? null)}, {$this->psql->string($item['inMagazine']->publishYear ?? null)}, {$this->psql->string($item['inMagazine']->issue ?? null)}, {$this->psql->string($item['inMagazine']->page ?? null)})
								ON CONFLICT (id_publication, id_magazine_raw, id_main_role)
								DO NOTHING;	");
						}
				}
			}
		}
		
	private function addSubjectRaw($values) {
		if (empty($values)) return null;
		$values_array = json_decode($values, true);
		$string = implode('|', $this->flattenArray($values_array));
		$clearStr = $this->helper->clearLatin($string);
		$key = $this->shortHash($values);			
		$t = $this->psql->querySelect($Q = "INSERT INTO elb_subjects_raw (hash, value, string, clear_str) VALUES ({$this->psql->string($key)}, {$this->psql->string($values)}, {$this->psql->string($string)}, {$this->psql->string($clearStr)}) 
			ON CONFLICT (hash) 
			DO UPDATE SET hash = elb_subjects_raw.hash
			RETURNING id;");
		#echo $Q."\n";	
		if (is_Array($t)) {
			return current($t)['id'];
			}
		}
		
		
	private function addPublicationSubjects($id, $values) {
		if (empty($values)) return null;
		$values = (array)$values;
		foreach ($values as $item) {
			
			if (!empty($item['ELB'])) {
				// to do or not to do?? Currently (2025-04-29), these data appear to be inadequate.
				} else if (!empty($item['value'])) {
				$itemId = $this->addSubjectRaw($item['value']);
				if (!empty($itemId)) {
					if (!empty($item['ids']) && is_array($item['ids']))
						foreach ($item['ids'] as $idName => $itemOID) {
							if (empty($itemOID))
								$itemOID = '-';
							if (is_Array($itemOID)) {
								foreach ($itemOID as $oid) 
									$t = $this->psql->querySelect("INSERT INTO elb_subjects_raw_ids (id_subject_raw, other_id, id_name) 
											VALUES ('$itemId', {$this->psql->string($oid)}, {$this->psql->string($idName)})
											ON CONFLICT (id_subject_raw, other_id, id_name)
											DO NOTHING;");
								} else 		
								$t = $this->psql->querySelect("INSERT INTO elb_subjects_raw_ids (id_subject_raw, other_id, id_name) 
										VALUES ('$itemId', {$this->psql->string($itemOID)}, {$this->psql->string($idName)})
										ON CONFLICT (id_subject_raw, other_id, id_name)
										DO NOTHING;");
							}
					$t = $this->psql->querySelect("INSERT INTO elb_publication_subjects (id_publication, id_subject_raw, field) 
							VALUES ('$id', '$itemId', {$this->psql->string($item['field'])});");
					}
				
				}
			}
			
		}
		
		
	

	public function saveRecordToPSQL() {
		if (!empty($this->relRec)) {
			
			if (empty($this->toSave))
				$this->toSave = new stdClass;
			
			$p = new stdClass;
			$p->raw_id = $this->relRec->rawId;
			$p->id_source_db = $this->currentFile['group'];
			$p->id_source_file = intval($this->currentFile['db_id']);
			$p->time_creation = date("Y-m-d H:i:s");
			$p->id_title = $this->addTitle( $this->relRec->title ?? null);
			$p->id_title_sup = $this->addTitleSupplemental( $this->relRec->title_sub ?? null );
			$p->id_statement_of_responsibility = $this->addStatementOfResponsibility( $this->relRec->statementOfResponsibility ?? null );
			$p->id_major_format = $this->relRec->id_major_format;
			$p->id_format = $this->relRec->id_format;
			
			$t = $this->psql->querySelect("INSERT INTO elb_publication (raw_id, id_source_db, id_source_file, time_creation, id_title, id_title_sup, id_format_major, id_format, id_statement_of_responsibility) 
				VALUES (
				{$this->psql->string($p->raw_id)}, 
				{$this->psql->string($p->id_source_db)}, 
				{$this->psql->isNull($p->id_source_file)}, 
				'{$p->time_creation}', 
				{$this->psql->isNull($p->id_title)}, 
				{$this->psql->isNull($p->id_title_sup)}, 
				{$this->psql->isNull($p->id_major_format)}, 
				{$this->psql->isNull($p->id_format)}, 
				{$this->psql->isNull($p->id_statement_of_responsibility)}
				)
				ON CONFLICT (raw_id, id_source_db)
				DO UPDATE SET 
					id_source_file = {$this->psql->isNull($p->id_source_file)},
					time_edit = now(),
					id_title = {$this->psql->isNull($p->id_title)}, 
					id_title_sup = {$this->psql->isNull($p->id_title_sup)}, 
					id_format_major = {$this->psql->isNull($p->id_major_format)}, 
					id_format = {$this->psql->isNull($p->id_format)}, 
					id_statement_of_responsibility = {$this->psql->isNull($p->id_statement_of_responsibility)}
				RETURNING id;");
			if (is_array($t)) {
				$row = current($t);
				$p->id = $id = $row['id'];
				}
			
			if (!empty($this->relRec->languages)) 		$this->addLanguagesUnrecognized($id, $this->relRec->languages ?? null);
			if (!empty($this->relRec->errors)) 			$this->addPublicationErrors($id, $this->relRec->errors ?? null); 
			if (!empty($this->relRec->publicationYear)) $this->addPublicationYear($id, $this->relRec->publicationYear ?? null);
			if (!empty($this->relRec->publicationDate)) $this->addPublicationDates($id, $this->relRec->publicationDate ?? null);
			if (!empty($this->relRec->publicationDateStr)) $this->addPublicationDateStr($id, $this->relRec->publicationDateStr ?? null);
			if (!empty($this->relRec->isbn)) 			$this->addPublicationISBN($id, $this->relRec->isbn ?? null);
			if (!empty($this->relRec->issn)) 			$this->addPublicationISSN($id, $this->relRec->issn ?? null);
			if (!empty($this->relRec->UDC)) 			$this->addPublicationUDC($id, $this->relRec->UDC ?? null);
			
			if (!empty($this->relRec->persons)) 		$this->addPublicationPersons($id, $this->relRec->persons ?? null);
			if (!empty($this->relRec->corporates)) 		$this->addPublicationCorporates($id, $this->relRec->corporates ?? null); 
			if (!empty($this->relRec->events)) 			$this->addPublicationEvents($id, $this->relRec->events ?? null); 
			if (!empty($this->relRec->places)) 			$this->addPublicationPlaces($id, $this->relRec->places); 
			if (!empty($this->relRec->magazines)) 		$this->addPublicationMagazines($id, $this->relRec->magazines); 
			
			if (!empty($this->relRec->subjects)) 		$this->addPublicationSubjects($id, $this->relRec->subjects ?? null); 
			}
		}
	
	
	public function createRelations() { // only if source is marc21 record
		if (!empty($this->record)) {
			#$this->saveLogTime('createRelations start');
			$this->noCR++;
			$this->relRec = new stdClass;
			$this->relRec->sourceFile = $this->currentFile['name'];
			$this->relRec->prefix = $this->currentFile['group'];
			$this->relRec->editTime = date("Y-m-d H:i:s");
			
			$this->relRec->rawId = $this->recMarc->getId();
			$this->relRec->id = $this->relRec->prefix.'.'.$this->relRec->rawId;
			$this->relRec->title = $this->recMarc->getTitle();
			$this->relRec->title_sub = $this->recMarc->getTitleSupplemental();
			$this->relRec->statementOfResponsibility = $this->recMarc->getStatementOfResponsibility();
			
			$this->relRec->id_major_format = $this->recMarc->getFormatMajorCode();
			$this->relRec->id_format = $this->recMarc->getFormatCode();
			
			$this->relRec->languages = $this->recMarc->getLanguageCodes();
			$publicationDates = $this->recMarc->getPublicationDate();
			if (!empty($publicationDates->year))
				$this->relRec->publicationYear = $publicationDates->year;
			if (!empty($publicationDates->date))
				$this->relRec->publicationDate = $publicationDates->date;
			if (!empty($publicationDates->string))
				$this->relRec->publicationDateStr = $publicationDates->string;
			
			$this->relRec->isbn = $this->recMarc->getISBN();
			$this->relRec->issn = $this->recMarc->getISSN();
			$this->relRec->UDC = $this->recMarc->getUDC();
			
			$this->relRec->persons = $this->recMarc->getPersons();
			$this->relRec->corporates = $this->recMarc->getCorporates(); 
			$this->relRec->events = $this->recMarc->getEvents();
			$this->relRec->places = $this->recMarc->getPlaces();
			$this->relRec->magazines = $this->recMarc->getMagazines();
			
			$this->relRec->subjects = $this->recMarc->getSubjects();
			
			
			$this->relRec->errors = $this->recMarc->getErrors();
			
			
			if (!empty($this->relRec->rawId) && !empty($this->currentUpdates->singleRecords->{$this->relRec->rawId})) {
				$changesJson = (array)$this->currentUpdates->singleRecords->{$this->relRec->rawId};
				foreach ($changesJson as $key=>$blockArray) 
					if (is_array($blockArray))
						foreach ($blockArray as $akey=>$value) 
							$this->relRec->$key[$akey] = $value;
				}
			if (!empty($this->currentUpdates->overwrite)) {
				echo "overwriting ";
				foreach ($this->currentUpdates->overwrite as $key=>$value) {
					if (is_string($value)) {
						$this->relRec->$key = $value;
						}
					if (is_object($value)) {
						foreach ($value as $skey=>$svalue)
							$this->relRec->$key[$skey] = $svalue;
						}
					}
				}
			
			# file_put_contents($this->outPutFolder.'t.record.json', print_r($this->record,1));
			# file_put_contents($this->outPutFolder.'t.relRecord.json', json_encode($this->relRec));
			# $this->saveLogTime('createRelations stop');
			} // if !empty
		}
		
	public function createRelationsZoteroRDF($item) {
		$this->noCR++;
		$this->relRec = new stdClass;
		if (!empty($item['@about'])) {
			$this->relRec->rawId = $item['@about'];
			if (substr($item['@about'], 0, 9) == 'urn:isbn:') {
				$this->relRec->isbn = explode('%20', str_replace('urn:isbn:', '', $item['@about']));
				}
			
			unset($item['@about']);
			}
		$prefix = $this->relRec->prefix = $this->getFileGroupFromName($this->currentFileName);
		$this->relRec->id = $prefix.'.'.str_pad($this->noCR, 10, "0", STR_PAD_LEFT );
		$this->relRec->sourceDB['name'] = $this->configJson->import->source_db->$prefix->name ?? '';
		$this->rawId2Id[$this->relRec->rawId] = $this->relRec->id;
			
		if (!empty($item['title'])) {
			$this->relRec->title = 
			$this->relRec->titleShort = $item['title'];
			unset($item['title']);
			}
		if (!empty($item['shortTitle'])) {
			$this->relRec->titleShort = $item['shortTitle'];
			unset($item['shortTitle']);
			}
		if (!empty($item['language'])) {
			$this->relAddLanguage('publication', $item['language']);
			unset($item['language']);
			}
		if (!empty($item['itemType'])) {
			
			$convertTable = [
				'book' => 'Book',
				'book' => 'Book',
				'journalArticle' => 'Journal article',
				'bookSection' => 'Book chapter',
				'interview' => 'Journal article',
				'blogPost' => 'Journal article',
				'webpage' => 'Journal article',
				];
			
			if (!empty($convertTable[$item['itemType']]))	
				$this->relRec->majorFormat = $convertTable[$item['itemType']];
				else 
				die("unknown majorFormat: \e[31m".$item['itemType']."\e[0m\n");
			unset($item['itemType']);
			}	
		if (!empty($item['date'])) {
			if (strlen($item['date']) == 4)
				$this->relRec->publicationYear[] = $item['date'];
				else {
				$tmp = explode('-', $item['date']);
				$this->relRec->publicationYear[] = $tmp[0];
				$this->relRec->publicationDateStr[] = $item['date'];
				}
			unset($item['date']);
			}
		
		if (!empty($item['numPages'])) {
			$this->relRec->numPages = $item['numPages'];
			unset($item['numPages']);
			}
		if (!empty($item['identifier'])) {
			if (is_Array($item['identifier'])) {
				foreach ($item['identifier'] as $idType=>$idValue) {
					$this->relRec->oids[$idType][] = $idValue;
					$this->relRec->$idType[] = $idValue;
					}
				} else {
				$tmp = explode(' ', $item['identifier']);
				$idType = strtolower($tmp[0]);
				unset($tmp[0]);
				$value = implode(' ', $tmp);
				$this->relRec->oids[$idType][] = $value;
				$this->relRec->$idType[] = $value;
				}
			unset($item['identifier']);
			}
		if (!empty($item['subject'])) {
			$item['subject'] = (array)$item['subject'];
			foreach ($item['subject'] as $subject) {
				if (is_array($subject)) {
					# print_r($item['subject']);
					# echo "subarray in subjects: {$this->relRec->rawId}\n";
					# die();
					}
				if (is_string($subject) && substr($subject, 0, 1) != '[')
					$this->relRec->subject['string'][] = $subject;
				}
			unset($item['subject']);
			}
		
		if (!empty($item['publisher'])) {
			$item['publisher'] = (array)$item['publisher'];
			foreach ($item['publisher'] as $recType => $publisher) {
				$key =  $this->shortHash($publisher['name']);
				$thisItem = [
					'name' => $publisher['name'],
					'role' => 'publisher'
					];
				if (!empty($publisher['adr']['Address']['locality'])) {
					if (stristr($publisher['adr']['Address']['locality'], ' and ')) 
						$thisItem['locality'] = explode(' and ', $publisher['adr']['Address']['locality']);
						else 
						$thisItem['locality'][] = $publisher['adr']['Address']['locality'];
					foreach ($thisItem['locality'] as $locacity)
						$this->relPreparePlace(['name' => $locacity], 'publicationPlace');
					}
				$this->relRec->corporates['all'][] =
				$this->relRec->corporates['publisher'][] = $thisItem;
				}
				
			unset($item['publisher']);
			}
		
		$creativeRoles = [
			'authors' => 'mainAuthor',
			'editors' => 'coAuthor',
			'translators' => 'coAuthor',
			'interviewees' => 'coAuthor',
			'interviewers' => 'coAuthor',
			'contributors' => 'coAuthor'
			];
			
		foreach ($creativeRoles as $rdfRole => $elbRole) {
			if (!empty($item[$rdfRole]['Seq']['li'])) {
				foreach ($item[$rdfRole]['Seq']['li'] as $pkey=>$creator) {
					# print_r($creator);
					if (is_numeric($pkey)) {
						foreach ($creator as $itIs => $values) {
							$this->getPersonRDF($elbRole, $itIs, $values);	
							} 
						} else {
						$this->getPersonRDF($elbRole, $pkey, $creator);	
						}
					}
				unset($item[$rdfRole]);
				}
			}
		
		if (isset($item['isPartOf'])) { // seria or Jurnal
			
			if (!empty($item['isPartOf']['@resource'])) {
				#echo 'has attribute: '.$item['isPartOf']['@resource']."\n";
				$tmp = explode(':', $item['isPartOf']['@resource']);
				$this->relRec->internalResources[] = [$tmp[1] => $tmp[2]];	
				unset($item['isPartOf']['@resource']);
				if (count($item['isPartOf']) == 0)
					unset($item['isPartOf']);
				}
			#print_r($item['isPartOf']);
			#$item['isPartOf'] = (array)$item['isPartOf'];
			if (!empty($item['isPartOf']) && is_array($item['isPartOf']))
				foreach ($item['isPartOf'] as $recType => $parentItem) {
					if (is_string($parentItem)) {
						echo $this->relRec->rawId;
						print_r($item['isPartOf']);
						die();
						}
					if (!empty($parentItem['title'])) {	
						$key =  $this->shortHash($parentItem['title']);
						switch ($recType) {
							case 'Series' : 
								$key =  $this->shortHash($parentItem['title']);
								$this->relRec->seria[] = $parentItem['title']; 
								break;	
							case 'Journal' : 
								$this->relRec->magazines['all'][$key] =
								$this->relRec->magazines['sourceMagazine'][$key] = $parentItem;
								break;
							case 'Book' : 
								$thisItem['title'] = $parentItem['title'];
								if (!empty($parentItem['identifier'])) {
									$tmp = explode(' ', $parentItem['identifier']);
									$thisItem[$tmp[0]] = $tmp[1];
									}
								$this->relRec->book[$key] = $thisItem;
								break;
							default:	
								echo "unknown isPartOf: $recType (in: {$this->relRec->rawId})\n";
							}
						} else if (!empty($parentItem)) {
						echo $this->relRec->rawId."\n";
						print_r($parentItem);
						die();
						}
					}
			unset($item['isPartOf']);
			}
		
		if (!empty($item['relation'])) {
			$field = 'hasRelationWith';
			if ($this->relRec->majorFormat == 'Book')
				$field = 'bookChapters'; 
				else if ($this->relRec->majorFormat == 'Book chapter')
				$field = 'book';
			if (array_is_list($item['relation']))
				foreach ($item['relation'] as $relation) {
					$this->relRec->$field[] = $relation['@resource'];
				} else {
				$tmp = explode(':', $item['relation']['@resource']);
				if (count($tmp) == 3) {
					$this->relRec->$field[] = [$tmp[1] => $tmp[2]];
					}
				}
			unset($item['relation']);
			}
		
		if (!empty($item['medium'])) {
			$key = $item['medium'];
			$this->relRec->magazines['all'][$key] =
			$this->relRec->magazines['sourceMagazine'][$key] = ['title' => $item['medium']];
			unset($item['medium']);
			}
		if (!empty($item['isReferencedBy']['@resource'])) {
			$linkedItem = $this->findAddOnRDF('Memo', $item['isReferencedBy']['@resource']);
			if (!empty($linkedItem)) {
				if (is_string($linkedItem['value']))
					$this->relRec->description = strip_tags($linkedItem['value']);
					else if (empty($linkedItem['value']))
					echo '  empty memo: '.$item['isReferencedBy']['@resource']."\n";	
					else 
					echo '  something wrong with memo: '.$item['isReferencedBy']['@resource']."\n";	
				} else 
				echo '  empty link: '.$item['isReferencedBy']['@resource']."\n";
			unset($item['isReferencedBy']); 
			}
			
		if (!empty($item['link'])) {
			$linkedItem = $this->findAddOnRDF('Attachment', $item['link']['@resource']);
			if (!empty($linkedItem)) {
				$this->relRec->attachment = [
						'link' => $linkedItem['identifier']['URI']['value'],
						'title' => $linkedItem['title'] ?? '', 
						'date' => $linkedItem['dateSubmitted'] ?? '', 
						'linkMode' => $linkedItem['linkMode']
						];
				unset($item['link']);
				}
			}
		
		
		$ignoredFields = ['libraryCatalog', 'dateSubmitted'];
		foreach ($ignoredFields as $field)
			if (!empty($item[$field])) { 
				#$this->relRec->$field = $item[$field];
				unset($item[$field]);
				}
		
		$simpleRewrite = [
				'description', 'abstract', 'edition', 'number', 'volume', 'pages', 'rights', 'alternative', 'type', 'linkMode'
				];
		foreach ($simpleRewrite as $field)
			if (!empty($item[$field])) { 
				$this->relRec->$field = $item[$field];
				unset($item[$field]);
				}
		
		
		
		
		if (!empty($this->currentUpdates->overwrite)) {
			#echo "overwriting ";
			foreach ($this->currentUpdates->overwrite as $key=>$value) {
				if (is_string($value)) {
					$this->relRec->$key = $value;
					#echo "s";
					}
				if (is_object($value)) {
					#echo "o";
					foreach ($value as $skey=>$svalue)
						$this->relRec->$key[$skey] = $svalue;
					}
				}
			}
			
		if (!empty($item)) {
			var_dump($item);
			if (!empty($this->relRec->rawId))
				echo '('.$this->relRec->rawId.') ';
			echo "\e[31m not matched fields left \e[0m\n";
			die();
			}
		
		$this->wholeELBrec[$this->relRec->id] = $this->relRec;
		}
		
	public function createRelationsBibframeRDF($item) {
		$this->noCR++;
		$this->relRec = new stdClass;
		if (!empty($item['@about'])) {
			$this->relRec->rawId = $item['@about'];
			if (substr($item['@about'], 0, 9) == 'urn:isbn:') {
				$this->relRec->isbn = explode('%20', str_replace('urn:isbn:', '', $item['@about']));
				}
			
			unset($item['@about']);
			}
		$prefix = $this->relRec->prefix = $this->getFileGroupFromName($this->currentFile['name']);
		$this->relRec->id = $prefix.'.'.str_pad($this->noCR, 10, "0", STR_PAD_LEFT );
		$this->relRec->sourceDB['name'] = $this->configJson->import->source_db->$prefix->name ?? '';
		$this->rawId2Id[$this->relRec->rawId] = $this->relRec->id;
			
		if (!empty($item['title'])) {
			$this->relRec->title = 
			$this->relRec->titleShort = $item['title'];
			unset($item['title']);
			}
		if (!empty($item['shortTitle'])) {
			$this->relRec->titleShort = $item['shortTitle'];
			unset($item['shortTitle']);
			}
		if (!empty($item['language'])) {
			$this->relAddLanguage('publication', $item['language']);
			unset($item['language']);
			}
		if (!empty($item['itemType'])) {
			
			$convertTable = [
				'work' => 'Work',
				'book' => 'Book',
				'book' => 'Book',
				'journalArticle' => 'Journal article',
				'bookSection' => 'Book chapter',
				'interview' => 'Journal article',
				'blogPost' => 'Journal article',
				'webpage' => 'Journal article',
				];
			
			if (!empty($convertTable[$item['itemType']]))	
				$this->relRec->majorFormat = $convertTable[$item['itemType']];
				else 
				die("unknown majorFormat: \e[31m".$item['itemType']."\e[0m\n");
			unset($item['itemType']);
			}	
		if (!empty($item['date'])) {
			if (strlen($item['date']) == 4)
				$this->relRec->publicationYear[] = $item['date'];
				else {
				$tmp = explode('-', $item['date']);
				$this->relRec->publicationYear[] = $tmp[0];
				$this->relRec->publicationDateStr[] = $item['date'];
				}
			unset($item['date']);
			}
		
		if (!empty($item['numPages'])) {
			$this->relRec->numPages = $item['numPages'];
			unset($item['numPages']);
			}
		if (!empty($item['identifier'])) {
			if (is_Array($item['identifier'])) {
				foreach ($item['identifier'] as $idType=>$idValue) {
					$this->relRec->oids[$idType][] = $idValue;
					$this->relRec->$idType[] = $idValue;
					}
				} else {
				$tmp = explode(' ', $item['identifier']);
				$idType = strtolower($tmp[0]);
				unset($tmp[0]);
				$value = implode(' ', $tmp);
				$this->relRec->oids[$idType][] = $value;
				$this->relRec->$idType[] = $value;
				}
			unset($item['identifier']);
			}
		if (!empty($item['subject'])) {
			$item['subject'] = (array)$item['subject'];
			foreach ($item['subject'] as $subject) {
				if (is_array($subject)) {
					# print_r($item['subject']);
					# echo "subarray in subjects: {$this->relRec->rawId}\n";
					# die();
					}
				if (is_string($subject) && substr($subject, 0, 1) != '[')
					$this->relRec->subject['string'][] = $subject;
				}
			unset($item['subject']);
			}
		
		if (!empty($item['publisher'])) {
			$item['publisher'] = (array)$item['publisher'];
			foreach ($item['publisher'] as $recType => $publisher) {
				$key =  $this->shortHash($publisher['name']);
				$thisItem = [
					'name' => $publisher['name'],
					'role' => 'publisher'
					];
				if (!empty($publisher['adr']['Address']['locality'])) {
					if (stristr($publisher['adr']['Address']['locality'], ' and ')) 
						$thisItem['locality'] = explode(' and ', $publisher['adr']['Address']['locality']);
						else 
						$thisItem['locality'][] = $publisher['adr']['Address']['locality'];
					foreach ($thisItem['locality'] as $locacity)
						$this->relPreparePlace(['name' => $locacity], 'publicationPlace');
					}
				$this->relRec->corporates['all'][] =
				$this->relRec->corporates['publisher'][] = $thisItem;
				}
				
			unset($item['publisher']);
			}
		
		$creativeRoles = [
			'authors' => 'mainAuthor',
			'editors' => 'coAuthor',
			'translators' => 'coAuthor',
			'interviewees' => 'coAuthor',
			'interviewers' => 'coAuthor',
			'contributors' => 'coAuthor'
			];
			
		foreach ($creativeRoles as $rdfRole => $elbRole) {
			if (!empty($item[$rdfRole]['Seq']['li'])) {
				foreach ($item[$rdfRole]['Seq']['li'] as $pkey=>$creator) {
					# print_r($creator);
					if (is_numeric($pkey)) {
						foreach ($creator as $itIs => $values) {
							$this->getPersonRDF($elbRole, $itIs, $values);	
							} 
						} else {
						$this->getPersonRDF($elbRole, $pkey, $creator);	
						}
					}
				unset($item[$rdfRole]);
				}
			}
		
		if (isset($item['isPartOf'])) { // seria or Jurnal
			
			if (!empty($item['isPartOf']['@resource'])) {
				#echo 'has attribute: '.$item['isPartOf']['@resource']."\n";
				$tmp = explode(':', $item['isPartOf']['@resource']);
				$this->relRec->internalResources[] = [$tmp[1] => $tmp[2]];	
				unset($item['isPartOf']['@resource']);
				if (count($item['isPartOf']) == 0)
					unset($item['isPartOf']);
				}
			#print_r($item['isPartOf']);
			#$item['isPartOf'] = (array)$item['isPartOf'];
			if (!empty($item['isPartOf']) && is_array($item['isPartOf']))
				foreach ($item['isPartOf'] as $recType => $parentItem) {
					if (is_string($parentItem)) {
						echo $this->relRec->rawId;
						print_r($item['isPartOf']);
						die();
						}
					if (!empty($parentItem['title'])) {	
						$key =  $this->shortHash($parentItem['title']);
						switch ($recType) {
							case 'Series' : 
								$key =  $this->shortHash($parentItem['title']);
								$this->relRec->seria[] = $parentItem['title']; 
								break;	
							case 'Journal' : 
								$this->relRec->magazines['all'][$key] =
								$this->relRec->magazines['sourceMagazine'][$key] = $parentItem;
								break;
							case 'Book' : 
								$thisItem['title'] = $parentItem['title'];
								if (!empty($parentItem['identifier'])) {
									$tmp = explode(' ', $parentItem['identifier']);
									$thisItem[$tmp[0]] = $tmp[1];
									}
								$this->relRec->book[$key] = $thisItem;
								break;
							default:	
								echo "unknown isPartOf: $recType (in: {$this->relRec->rawId})\n";
							}
						} else if (!empty($parentItem)) {
						echo $this->relRec->rawId."\n";
						print_r($parentItem);
						die();
						}
					}
			unset($item['isPartOf']);
			}
		
		if (!empty($item['relation'])) {
			$field = 'hasRelationWith';
			if ($this->relRec->majorFormat == 'Book')
				$field = 'bookChapters'; 
				else if ($this->relRec->majorFormat == 'Book chapter')
				$field = 'book';
			if (array_is_list($item['relation']))
				foreach ($item['relation'] as $relation) {
					$this->relRec->$field[] = $relation['@resource'];
				} else {
				$tmp = explode(':', $item['relation']['@resource']);
				if (count($tmp) == 3) {
					$this->relRec->$field[] = [$tmp[1] => $tmp[2]];
					}
				}
			unset($item['relation']);
			}
		
		if (!empty($item['medium'])) {
			$key = $item['medium'];
			$this->relRec->magazines['all'][$key] =
			$this->relRec->magazines['sourceMagazine'][$key] = ['title' => $item['medium']];
			unset($item['medium']);
			}
		if (!empty($item['isReferencedBy']['@resource'])) {
			$linkedItem = $this->findAddOnRDF('Memo', $item['isReferencedBy']['@resource']);
			if (!empty($linkedItem)) {
				if (is_string($linkedItem['value']))
					$this->relRec->description = strip_tags($linkedItem['value']);
					else if (empty($linkedItem['value']))
					echo '  empty memo: '.$item['isReferencedBy']['@resource']."\n";	
					else 
					echo '  something wrong with memo: '.$item['isReferencedBy']['@resource']."\n";	
				} else 
				echo '  empty link: '.$item['isReferencedBy']['@resource']."\n";
			unset($item['isReferencedBy']); 
			}
			
		if (!empty($item['link'])) {
			$linkedItem = $this->findAddOnRDF('Attachment', $item['link']['@resource']);
			if (!empty($linkedItem)) {
				$this->relRec->attachment = [
						'link' => $linkedItem['identifier']['URI']['value'],
						'title' => $linkedItem['title'] ?? '', 
						'date' => $linkedItem['dateSubmitted'] ?? '', 
						'linkMode' => $linkedItem['linkMode']
						];
				unset($item['link']);
				}
			}
		
		
		$ignoredFields = ['libraryCatalog', 'dateSubmitted'];
		foreach ($ignoredFields as $field)
			if (!empty($item[$field])) { 
				#$this->relRec->$field = $item[$field];
				unset($item[$field]);
				}
		
		$simpleRewrite = [
				'description', 'abstract', 'edition', 'number', 'volume', 'pages', 'rights', 'alternative', 'type', 'linkMode'
				];
		foreach ($simpleRewrite as $field)
			if (!empty($item[$field])) { 
				$this->relRec->$field = $item[$field];
				unset($item[$field]);
				}
		
		
		
		
		if (!empty($this->currentUpdates->overwrite)) {
			#echo "overwriting ";
			foreach ($this->currentUpdates->overwrite as $key=>$value) {
				if (is_string($value)) {
					$this->relRec->$key = $value;
					#echo "s";
					}
				if (is_object($value)) {
					#echo "o";
					foreach ($value as $skey=>$svalue)
						$this->relRec->$key[$skey] = $svalue;
					}
				}
			}
			
		if (!empty($item)) {
			var_dump($item);
			if (!empty($this->relRec->rawId))
				echo '('.$this->relRec->rawId.') ';
			echo "\e[31m not matched fields left \e[0m\n";
			die();
			}
		
		$this->wholeELBrec[$this->relRec->id] = $this->relRec;
		}
		
		
	
	public function statusErrorRaport() {
		$t = $this->psql->querySelect("SELECT a.id,msg,count(*) FROM elb_errors a JOIN elb_publication_error b ON (a.id = b.id_error) GROUP BY a.id,msg ORDER BY a.id;");
		if (is_array($t)) {
			echo "errors & warrnings:\n";
			foreach ($t as $row)
				echo '   '.$row['id'].'. '.$this->setLen($row['msg'],50).'   '.$this->bashColor('Red').$this->setLenR($this->helper->numberFormat($row['count']),12).$this->bashColor('ColorOff')."\n";
			}
		}

	
	public function statusLinePSQL($group, $i, $total) {
		$workTime = time()-$this->startTime;
		$percent = round(($i/$total)*100);
		return "    - ".$this->setLen($group,27)."   ".$this->bashColor('LightBlue').$this->setLenR($this->helper->numberFormat($i),12).$this->bashColor('ColorOff')."  ".$this->bashColor('Green').$this->setLenR($percent.'%', 5).$this->bashColor('ColorOff')."        ".$this->WorkTime($workTime)." s.  \r";
		}
	
	
	public function statusLine() {
		$totalPercent = round(($this->buffFullSize/$this->totalFilesSize)*100,2);
		
		if ($totalPercent>0) {
			$workTime = time()-$this->startTime;
			$estimatedTotalTime = $workTime / ($totalPercent / 100);
			$estimatedRemainingTime = $estimatedTotalTime - $workTime;
			$estimatedRemainingTimeStr = $totalPercent.'% '.$this->formatTime($estimatedRemainingTime);
			} else {
			$estimatedRemainingTimeStr = '';	
			}
		return $this->setLenR($this->helper->numberFormat($this->noCR),12).
				". \e[92m".$this->setLenR(round(($this->buffSize/$this->fullFileSize)*100),4).
				"% \e[0m  rec: ".$this->setLen($this->id,20)."        ".$this->WorkTime()."      ".$estimatedRemainingTimeStr." ";
		}
	
	
	
	function harmonizePersons ($clear_str) {
		$tnames = [];
		$tdates = [];
		$tid = [];
		$ids = [];
		
		$t = $this->psql->querySelect("SELECT * FROM elb_persons_raw WHERE clear_str ILIKE '%{$clear_str}%';");
		if (is_array($t)) {
			foreach ($t as $row) {
				$tnames[] = $row['name'];
				$tdates[] = $row['date_range'];
				$tid[] = $row['id'];
				$tLabels[$row['id']] = $row;
				}
			$t = $this->psql->querySelect("SELECT * FROM elb_persons_raw_ids WHERE id_person_raw IN ('".implode("', '", $tid)."');");
			if (is_Array($t))
				foreach ($t as $row) {
					$ids[$row['other_id']] = $row['other_id'];	
					}
			$t = $this->psql->querySelect("SELECT * FROM elb_persons_raw_ids WHERE other_id IN ('".implode("', '", $ids)."');");
			if (is_Array($t))
				foreach ($t as $row) {
					$tid[] = $row['id_person_raw'];
				}
			$tid = array_unique($tid);
			$t = $this->psql->querySelect("SELECT * FROM elb_persons_raw WHERE id IN ('".implode("', '", $tid)."');");
			if (is_Array($t))
				foreach ($t as $row) {
					$tnames[] = $row['name'];
					$tdates[] = $row['date_range'];
				}
			}
		
		foreach ($ids as $k=>$v)
			if (is_string($v) && stristr($v, 'viaf')) {
				$viaf = 'viaf:'.$this->viafFromStr($v);
				$ids['viaf'] = $viaf;
				unset($ids[$k]);
				}
			
		print_r(array_unique($tnames));
		print_r(array_unique($tdates));
		print_r(array_unique($ids));
		if (!empty($ids['viaf'])) {
			$response = $this->viafSearcher->getIdByOtherId($ids['viaf'], 'viaf');
			if (!empty($response['wikiQ']))
				$ids['wikiq'] = $response['wikiQ'] ?? null;
				else foreach ($ids as $idToCheck) {
					$response = $this->viafSearcher->getIdByOtherId($idToCheck);
					if (!empty($response['wikiQ'])) {
						$ids['wikiq'] = $response['wikiQ'] ?? null;
						break;
						}
				}	
			}
		
		$t = $this->psql->querySelect("SELECT id_person, count(*) FROM elb_publication_person WHERE id_person IN ('".implode("', '", $tid)."') GROUP BY id_person ORDER BY count(*) DESC;");
		if (is_Array($t)) {
			$row = current($t);
			$name = $tLabels[$row['id_person']]['name'];
			$dates = str_replace(['(', ')'], '', $tLabels[$row['id_person']]['date_range']);
			echo "best label: ".$name." ".$dates."\n";
			if (!empty($ids['wikiq']))
				echo "wikidata id: ".$ids['wikiq']."\n";
			
			}
		
		
		echo "________________________________________________________________________\n".$this->workTime()."\n";
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
	public function currentTimeForSolr() {
		return date("Y-m-d").'T'.date("H:i:s").'Z';
		}
	
		
	private function clearLastChar($string) {
		$charsToRemove = ['/', ';', ':', ','];
		
		if (is_string($string)) {
			$string = str_replace('{dollar}', '$', $string);
			$len = strlen($string);
			if ($len>3) {
				$end = substr($string, $len-2, 3);
				$string = substr($string, 0, $len-2);
				$end = str_replace($charsToRemove, '', $end);
				$string = trim($string.$end);
				} else 
				$string = trim(str_replace($charsToRemove, '', $string));
			return $string;
			}
		return $string;
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
		return array_values(array_unique($array));
		}
	
	function flattenArray($array) {
		$result = [];
		foreach ($array as $value) {
			if (is_array($value)) {
				// Jeśli to jest tablica, użyj rekurencji, aby spłaszczyć ją.
				$result = array_merge($result, $this->flattenArray($value));
				} else {
				// Jeśli to nie jest tablica, dodaj wartość do wynikowej tablicy.
				$result[] = $value;
				}
			}
		return $result;
		}
	
	function onlyNumbers($string) {
		#return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
		return preg_replace("/[^0-9]/", '', $string);
		}
	
	function yearFromStr($string) {
		$res = preg_match('/\b(\d{4})\b/', $string, $matches);
		if ($res === 1) {
			$year = $matches[1];
			return  $year;
			}
		}
	
	function setLen($str, $elen) {
		$len = strlen($str);
		if ($len >= $elen)
			return substr($str, 0, $elen-6).'...'.substr($str, $elen-3, 3);
		if ($elen-$len > 0)
			return $str.str_repeat(' ',$elen-$len);
		}
	
	function setLenR($str, $elen) {
		$len = strlen($str);
		if ($len >= $elen)
			return substr($str, 0, $elen-6).'...'.substr($str, $elen-3, 3);
		if ($elen-$len > 0)
			return str_repeat(' ',$elen-$len).$str;
		}
	
	function remoteFileExists($url) {
		$ch = curl_init($url);
		
		// Ustawienie opcji cURL, aby sprawdzić istnienie pliku bez pobierania jego zawartości.
		curl_setopt($ch, CURLOPT_NOBODY, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Wykonanie żądania cURL.
		$response = curl_exec($ch);
		
		// Sprawdzenie statusu HTTP.
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		
		// Zamknięcie połączenia cURL.
		curl_close($ch);

		// Zwróć true, jeśli status HTTP to 200 OK (plik istnieje).
		return ($httpCode === 200);
		}
	
	
	}



?>
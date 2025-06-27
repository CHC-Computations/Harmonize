<?php
#[AllowDynamicProperties]

class importer {
	public $settings;
	private $configPath = './config/';
	public $bufferAreas = ['wikiQ', 'issn', 'str'];
	public $buffSize = 0;
	public $confingJson;
	public $confingIni;
	public $startTime;
	public $startHRTime;
	
	public $currentFileNumber;
	public $currentUpdates;
	public $filesToImport = [];
	public $licences = [];
	public $udcMeaning;
	public $placesToSave = [];
	public $DDkeys = [];
	public $rawId2Id = [];
	public $wholeRDFrec = [];
	public $wholeELBrec = [];
	
	public $workingStep = 0;
	
	public $titleDrop = [
			'[Title on the picture (retrobi record)]',
			'[Název textu k dispozici na připojeném lístku]',
			'Nazev textu k dispozici na pripojenem listku',
			'title on the picture retrobi record',
			'[no title]',
			'no title'
			];
	
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
	
	public function workTime() {
		$days = '';
		$workTime = time() - $this->startTime;
		$workDays = floor($workTime/86400);
		if ($workDays>0)
			$days =$workDays.'d ';
		
		return $days.date("H:i:s", $workTime).' '; 
		}
	
	public function logTime() {
		return hrtime()[1] - $this->startHRTime[1];
		}
	
	public function saveLogTime($str) {
		file_put_contents($this->outPutFolder.'timeLog.csv', $this->logTime().';'.$str.";\n", FILE_APPEND);
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
		$nameParts = explode('.', $name);
		$this->recFormat = end($nameParts);
		$updateFileName = $this->configJson->import->extentionsFolder.$name.'.json';
		if (file_exists($updateFileName)) {
			$this->currentUpdates = json_decode(file_get_contents($updateFileName));
			echo "Data correction file found.\n";
			#print_r($this->currentUpdates);
			} else 
			$this->currentUpdates = new stdClass; 
		}
	
	function setFileNo($number) {
		$this->currentFileNumber = $number;
		}
	
	function setFilesToImport($array) {
		foreach ($array as $fileName) {
			$this->filesToImport[] = str_replace($this->configJson->import->dataFolder, '', $fileName);
			}
		}
 
	function getFileGroupFromName($fileName) {
		$tmp = explode('_',$fileName);
		return $tmp[0];
		}
 
	function checkLicences() {
		$errors = [];
		foreach ($this->filesToImport as $fileName) {
			$sourceGroupCode = $this->getFileGroupFromName($fileName);
			if (empty($this->licences[$sourceGroupCode])) {
				$jsonFileName = $this->configJson->import->licenceFolder.$sourceGroupCode.'.json';
				if (file_exists($jsonFileName)) {
					$licenceInfo = json_decode(file_get_contents($jsonFileName));
					$this->licences[$sourceGroupCode] = $licenceInfo;
					} else {
					$errors[] = "No licence info for \e[94m$fileName\e[0m. File \e[94m$jsonFileName\e[0m not exists.";
					}
				}
			}
		return $errors;	
		}
 
	function getSourceFile() {
		return $this->currentFileName;
		}
	
	function getSourceGroup() {
		return $this->getFileGroupFromName($this->currentFileName);
		} 
	
	function register($name, $var) {
		$this->$name = $var;
		}
	
	public function addClass($className, $res) {
		if (method_exists($res,'register'))
			$res->register('cms', $this);
		$this->$className = $res;
		}
	
	
	
	function prepareMultiLanguage($field) {
		$return = [];
		if (!empty($res = $this->wikiData->getPropIds($field)) && is_array($res))
			foreach($res as $itemId) {
				$this->wikiDataSub->loadRecord($itemId);
				$return[] = $itemId.'|'.$this->wikiDataSub->getML('labels',  $this->configJson->settings->multiLanguage->order);
				}
		return $return;
		}
	
	
	function saveOrphansRecord($record_type, $key, $collectedData) {
		$this->lp++;
		$this->buffSize++;
		
		$core = 'orphans';
				
		$data = (object) ["id" => $key];
		$data->key = $key; 
		$biblio_labels_array = (array)$collectedData->biblio_labels;
		arsort($biblio_labels_array);
		$data->biblio_labels = json_encode($biblio_labels_array);
		if (!empty($biblio_labels_array)) {
			$len = [];
			foreach ($biblio_labels_array as $label=>$lcount)
				$len[] = strlen($label);
			$len = $this->removeArrayKeys($len);
			$data->biblio_labels_length = $len;
			}
		$data->record_type	= $record_type;
		
		$data->biblio_count = 0;
		if (!empty($collectedData->roles)) {
			foreach ($collectedData->roles as $roleName => $biblioRecords) {
				$countArray = array_unique($biblioRecords);
				$data->{$roleName.'_count'} = count($countArray);
				$data->biblio_count += $data->{$roleName.'_count'};
				}
			}
		$data->first_indexed = $this->currentTimeForSolr(); //date(DATE_ATOM);
		$data->record_length = strLen(json_encode($data));
		$bestLabel = $data->best_biblio_label_str = current(array_keys($biblio_labels_array));
		foreach ($biblio_labels_array as $label=>$lcount) {
			$this->localSearcher->saveBestLabel($label, $lcount, $bestLabel, '', $record_type); 
			}
		
		if (!$this->solr->curlSaveData($core, $data)) {
			file_put_contents($this->outPutFolder.'toSave.'.$core.'.json', $this->solr->curlSavePostData);
			echo "\nfatal error (saveCoreRecord: $core)\n";
			die();
			}
		file_put_contents($this->outPutFolder.'toSave.orphans.json', $this->solr->curlSavePostData);
		
		}	
		
		
	function saveCoreRecord($core, $wikiQ, $collectedData) {
		$this->lp++;
		$this->buffSize++;
		
		$solrClientName = $core.'sSolrCore';
		$this->$solrClientName = $this->$solrClientName ?? $this->solr->createSolrClient($core.'s');
		$this->wikiData->loadRecord($wikiQ);
		
		$data = (object) ["id" => $wikiQ];
		$data->wikiq = $wikiQ; 
		
		
		if (!empty($this->wikiData->getSolrValue('labels'))) {
			$labels = $this->wikiData->getSolrValue('labels');
			if (is_string($labels))
				$data->labels 		 = $labels;
				else
				$data->labels 		 = json_encode($labels);
			$data->labels_search = $this->wikiData->getSolrValue('labels_search');
			}
			
		$langAvaible = $this->configJson->settings->multiLanguage->order;
		foreach ($langAvaible as $langCode) {
			$indexName = $langCode.'_label_sort';
			$data->$indexName = $this->helper->clearLatin($this->wikiData->get('labels', $langCode));
			}
			
		if (!empty($this->wikiData->getSolrValue('aliases'))) {
			$aliases = $this->wikiData->getSolrValue('aliases');
			if (is_string($aliases)) {
				if (strlen($aliases) < 32766)
					$data->aliases 	   = $aliases;	
				} else 
				$data->aliases 	   = json_encode($aliases);
				
			$data->aliases_search = $this->wikiData->getSolrValue('aliases_search');
			}
		if (!empty($this->wikiData->getSolrValue('descriptions'))) {
			$data->descriptions 	   = $this->wikiData->getSolrValue('descriptions');
			$data->descriptions_search = $this->wikiData->getSolrValue('descriptions_search');
			}
		if (!empty($this->wikiData->getStrVal('P1705')))
			$data->native_labels = $this->flattenArray($this->wikiData->getStrVal('P1705')); // new method for "monolingualtext"
		
		
		$biblio_labels_array = (array)$collectedData->biblio_labels;
		arsort($biblio_labels_array);
		$data->biblio_labels = json_encode($biblio_labels_array);
		if (!empty($biblio_labels_array)) {
			$len = [];
			foreach ($biblio_labels_array as $label=>$lcount)
				$len[] = strlen($label);
			$len = $this->removeArrayKeys($len);
			$data->biblio_labels_length = $len;
			}
		if (empty($biblio_labels_array))
			$biblio_labels_array = [];
		$data->labels_suggest_str_mv = $this->removeArrayKeys(array_unique(array_merge($this->wikiData->getAllNames(), array_keys($biblio_labels_array))));
		
		$data->eids 		= $this->wikiData->getSolrValue('eids_any') ?? null;
		$data->eids_nkp 	= $this->wikiData->getStrVal('P691') ?? null;
		$data->ML_self 		= $collectedData->nameML ?? null;
		$data->picture 		= $this->buffer->loadWikiMediaUrl($this->wikiData->getStrVal('P18')) ?? null;
		$data->audio 		= $this->buffer->loadWikiOggUrl($this->wikiData->getStrVal('P443')) ?? null;
		
		$data->biblio_count = 0;
		if (!empty($collectedData->roles)) {
			foreach ($collectedData->roles as $roleName => $biblioRecords) {
				$countArray = array_unique($biblioRecords);
				$data->{$roleName.'_count'} = count($countArray);
				$data->biblio_count += $data->{$roleName.'_count'};
				}
			}
		
		switch ($core) {
			case 'corporate' : 
					$data->location = $this->prepareMultiLanguage('P276');
					$data->country = $this->prepareMultiLanguage('P17');
					$data->headquater_str_mv = $this->prepareMultiLanguage('P159');
					
					$data->year_start = $this->wikiData->getYear('P571') ?? null;
					$data->year_stop = $this->wikiData->getYear('576') ?? null;
					
					if (!empty($data->year_stop) && !empty($data->year_start)) {
						for ($i = $data->year_start; $i<=$data->year_stop; $i++)
							$data->years_activity[] = $i;
						} else if (empty($data->year_stop) && !empty($data->year_start)) {
						for ($i = $data->year_start; $i<=date("Y"); $i++)
							$data->years_activity[] = $i;
						}	
					
					$data->fields_of_activity = $this->prepareMultiLanguage('P101');	
					$data->type_of = $this->prepareMultiLanguage('P31');	
						
					$data->best_biblio_label_str = $data->ML_self.'|'.$wikiQ;
					break;
			case 'event' : 
					$data->location = $this->prepareMultiLanguage('P276');
					$data->country = $this->prepareMultiLanguage('P17');
					
					$data->frequency = json_encode($this->wikiData->getStrVal('P2257')) ?? null;
					$data->year_start = $this->wikiData->getYear('P571') ?? null;
					$data->year_stop = $this->wikiData->getYear('576') ?? null;
					
					if (!empty($data->year_stop) && !empty($data->year_start)) {
						for ($i = $data->year_start; $i<=$data->year_stop; $i++)
							$data->years_activity[] = $i;
						} else if (empty($data->year_stop) && !empty($data->year_start)) {
						for ($i = $data->year_start; $i<=date("Y"); $i++)
							$data->years_activity[] = $i;
						}	
					
					$data->fields_of_activity = $this->prepareMultiLanguage('P101');	
					$data->type_of = $this->prepareMultiLanguage('P31');	
					$data->best_biblio_label_str = $data->ML_self.'|'.$wikiQ;	
					break;
			case 'person' : 
					$data->related_place = $this->prepareMultiLanguage('P551');
					$data->birth_date = $this->wikiData->getDate('P569');
					$data->death_date = $this->wikiData->getDate('P570');
					$data->birth_year = $this->wikiData->getYear('P569');
					$data->death_year = $this->wikiData->getYear('P570');

					$data->birth_place = $this->prepareMultiLanguage('P19');
					$data->death_place = $this->prepareMultiLanguage('P20');
					
					if (!empty($data->birth_place))
						foreach ($data->birth_place as $place)
							$data->related_place[] = $place;
					if (!empty($data->death_place))
						foreach ($data->death_place as $place)
							$data->related_place[] = $place;
					if (!empty($data->related_place))
						$data->related_place = $this->flattenArray($data->related_place);
					$data->gender = $this->prepareMultiLanguage('P21');		
					
					$data->country = $this->prepareMultiLanguage('P27');
					$data->occupation = $this->prepareMultiLanguage('P106');
					$data->genres = $this->prepareMultiLanguage('P136');		
					
					
					// to do! - take only name from more popular label, date-range from wiki and ids from collcted data. 
					$data->best_biblio_label_str = current(array_keys($biblio_labels_array)).'|'.$wikiQ;
					
					
					break;
			case 'place' :
					$value = $this->wikiData->getCoordinates('P625');
					if (!empty($value->longitude)) {
						$data->longitiude = str_replace(',','.',$value->longitude);
						$data->latitiude = str_replace(',','.',$value->latitude);
						}
					$data->country = $this->prepareMultiLanguage('P17');
					
					$data->best_biblio_label_str = $data->ML_self.'|'.$wikiQ;
					
					unset($this->placesToSave[$data->wikiq]);
					break;
			case 'magazine' :
					$data->location = $this->prepareMultiLanguage('P276');
					$data->country = $this->prepareMultiLanguage('P17');
					$data->headquater_str_mv = $this->prepareMultiLanguage('P159');
					
					$data->eids_issn = $this->wikiData->getStrVal('P236') ?? null;
					$data->year_start = $this->wikiData->getYear('P571') ?? null;
					$data->year_stop = $this->wikiData->getYear('576') ?? null;
					
					$data->type_of = $this->prepareMultiLanguage('P31') ?? null; // instance of 
					
					$data->best_biblio_label_str = $data->ML_self.'|'.$wikiQ;
					break;
			}
			
		if (!empty($data->location) && is_string($data->location)) 				@$this->placesToSave[$data->location]++;
		if (!empty($data->country) && is_string($data->country)) 				@$this->placesToSave[$data->country]++;
		if (!empty($data->headquater_str_mv) && is_string($data->headquater_str_mv)) 	@$this->placesToSave[$data->headquater_str_mv]++;
		if (!empty($data->birth_place) && is_string($data->birth_place)) 		@$this->placesToSave[$data->birth_place]++;
		if (!empty($data->death_place) && is_string($data->death_place)) 		@$this->placesToSave[$data->death_place]++;
						
			
		$data->first_indexed = $this->currentTimeForSolr(); //date(DATE_ATOM);
		$data->record_length = strLen(json_encode($data));
		#$res = $this->solr->curlSaveData($core.'s', $data);
		if (!$this->solr->curlSaveData($core.'s', $data)) {
			file_put_contents($this->outPutFolder.'toSave.'.$core.'.json', $this->solr->curlSavePostData);
			#file_put_contents($this->outPutFolder.$core.'_response.json', $this->solr->curlSaveResponse);
			#print_R($data);
			echo "\nfatal error (saveCoreRecord: $core)\n";
			die();
			}
		file_put_contents($this->outPutFolder.'toSave.'.$core.'.json', $this->solr->curlSavePostData);
		
		$bestLabel = $data->best_biblio_label_str;
		foreach ($biblio_labels_array as $label=>$lcount) {
			$this->localSearcher->saveBestLabel($label, $lcount, $bestLabel, $wikiQ, $core); 
			}
		
		}


	function saveNoBiblioRelatedPlaces() {
		$core = 'place';
		$solrClientName = $core.'sSolrCore';
		$this->$solrClientName = $this->$solrClientName ?? $this->solr->createSolrClient($core.'s');
		
		if (!empty($this->placesToSave)) {
			$toSave = count($this->placesToSave);
			$i = 0;
			foreach ($this->placesToSave as $wikiQ) {
				$i++;
				$this->wikiData->loadRecord($wikiQ);
				
				$data = (object) ["id" => $wikiQ];
				$data->wikiq = $wikiQ; 
				
				if (!empty($this->wikiData->getSolrValue('labels'))) {
					$labels = $this->wikiData->getSolrValue('labels');
					if (is_string($labels))
						$data->labels 		 = $labels;
						else
						$data->labels 		 = json_encode($labels);
					$data->labels_search = $this->wikiData->getSolrValue('labels_search');
					}
				if (!empty($this->wikiData->getSolrValue('aliases'))) {
					$aliases = $this->wikiData->getSolrValue('aliases');
					if (is_string($aliases)) {
						if (strlen($aliases) < 32766)
							$data->aliases 	   = $aliases;	
						} else 
						$data->aliases 	   = json_encode($aliases);
						
					$data->aliases_search = $this->wikiData->getSolrValue('aliases_search');
					}
				if (!empty($this->wikiData->getSolrValue('descriptions'))) {
					$data->descriptions 	   = $this->wikiData->getSolrValue('descriptions');
					$data->descriptions_search = $this->wikiData->getSolrValue('descriptions_search');
					}
				if (!empty($this->wikiData->getStrVal('P1705')))
					$data->native_labels = $this->flattenArray($this->wikiData->getStrVal('P1705')); // new method for "monolingualtext"
				
				$data->labels_suggest_str_mv = $this->removeArrayKeys(array_unique(array_merge($this->wikiData->getAllNames(), array_keys($biblio_labels_array))));
				
				$data->ML_self 		= $this->wikiData->getML('labels', $this->configJson->settings->multiLanguage->order);
				$data->picture 		= $this->buffer->loadWikiMediaUrl($this->wikiData->getStrVal('P18')) ?? null;
				$data->audio 		= $this->buffer->loadWikiOggUrl($this->wikiData->getStrVal('P443')) ?? null;
				
				$data->biblio_count = 0;
				$value = $this->wikiData->getCoordinates('P625');
				if (!empty($value->longitude)) {
					$data->longitiude = str_replace(',','.',$value->longitude);
					$data->latitiude = str_replace(',','.',$value->latitude);
					}
				$data->country = $this->prepareMultiLanguage('P17');
				$data->first_indexed = $this->currentTimeForSolr(); //date(DATE_ATOM);
				$data->record_length = strLen(json_encode($data));
				if (!$this->solr->curlSaveData($core.'s', $data)) {
					file_put_contents($this->outPutFolder.'toSave.'.$core.'.json', $this->solr->curlSavePostData);
					echo "\nfatal error (saveCoreRecord: $core)\n";
					die();
					}
				file_put_contents($this->outPutFolder.'toSave.'.$core.'.json', $this->solr->curlSavePostData);
				
				echo 'saving non-biblio related places: '.$wikiQ.' ('.round(($i/$toSave)*100).'%)                     '."\r";
					
				}
			}	
		}


	function addLabelsToViafRecord($viaf, $labels) {
		if (!empty($labels)) {
					
			$data = (object)['viaf' =>$viaf];
			foreach ($labels as $res) {
				$clearStrLabel = $this->helper->clearStr($res->label);
				if (!empty(trim($clearStrLabel)))
					$TsearchLabels[] = $clearStrLabel;
				$tmpLabels[$res->label] = $res->count;
				}
			if (!empty($tmpLabels)) {
				arsort($tmpLabels);
				foreach ($tmpLabels as $strlabel=>$count) {
					$Tlabels[] = $strlabel.'|'.$count;
					}	
				}
			if (!empty($Tlabels)) $data->labels =  (object) ["set" => $this->flattenArray($Tlabels)];
			if (!empty($TsearchLabels)) $data->search_labels =  (object) ["set" => $this->flattenArray($TsearchLabels)];
			if (!$this->solr->curlSaveData('viaf', $data)) {
				file_put_contents($this->outPutFolder.'toSave.viaf.json', $this->solr->curlSavePostData);
				echo "\nfatal error while viaf ($viaf) updating\n";
				#die();
				}
			}
		}

	
	function saveRecord() {
		
		$this->lp++;
		$this->totalRec++;
		$id = $this->relRec->id;
		
		
		if (!empty($this->relRec->id)) { 			// here was: if (!empty($this->record['LEADER'])) {
			$isOK = '  ok  ';
			################################ UPDATING SOLR - START
			
			$data = (object) ["id" => $id];	
			#$this->saveLogTime('saving '.$id.' start');
			foreach ($this->configJson->biblio->facets->solrIndexes as $indexName=>$indexSettings) {
				unset ($val);
				if (!empty($indexSettings->importFunction)) {
					$functionName = $indexSettings->importFunction;
					if (!empty($indexSettings->importParam))
						$val = $this->$functionName($indexSettings->importParam);
						else 
						$val = $this->$functionName();
					#$this->saveLogTime('function: '.$functionName);
					} else if (!empty($indexSettings->importField)) {
					$field = substr($indexSettings->importField, 0, 3);
					$subfield = str_replace($field, '', $indexSettings->importField);
					$sub = [];
					$len = strlen($subfield)-1;
					for ($i = 0; $i<=$len; $i++)
						$sub[] = $subfield[$i];
					$val = $this->getMarcFirstStr($field, $sub);
					#$this->saveLogTime('importField: '.$indexSettings->importField);
					} else if (!empty($indexSettings->relPath)) {
					$val = $this->getRelValue($indexSettings->relPath);
					#$this->saveLogTime('relPath: '.implode('/', (array)$indexSettings->relPath));
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
				echo $this->solrUrl."\nSolr not responding. Try to restart Solr and type Y to continue? (Y/N) - ";

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
				#echo "\n"; 
				echo "\rCommmiting updates to Solr                                         ";
				#echo "\n"; 
				file_get_contents($this->solrUrl.'?commit=true');
				#die(); // it's temporary, for speed tests only 
				}
			} else {
			return "Some error with rec: $id.\n";
			file_put_contents("./import/errors/$id.json", json_encode($record) );
			$isOK = 'error';
			}
		
		$workTime = time()-$this->startTime;
		$persentDone = round(($this->buffSize/$this->fullFileSize)*100);
		$returnStr = $this->setLen(number_format($this->lp,0,'','.'),7).
			". \e[92m".$persentDone."%\e[0m  rec: (".$this->setLen($id,20).")  ".
			$this->WorkTime($workTime)." s.                ";
					
		
		$this->saveImportStatus($id);
		$this->lastLen = strlen($id);
		#$this->saveLogTime('saving '.$id.' stop');
		return $returnStr;
		
		}
	
	function saveImportStatus($id = '') {
		$workTime = time()-$this->startTime;
		if (empty($this->buffSize))
			$this->buffSize = 0;
		if (empty($this->fullFileSize))
			$this->fullFileSize = 1;
		if (empty($this->currentFileName))
			$this->currentFileName = 'no data';
		if (empty($this->currentFileName))
			$this->currentFileName = 'no data';
		if (!empty($this->relRec->id))
			$id = $this->relRec->id;
		if (empty($id))	$id = 0;
		$persentDone = round(($this->buffSize/$this->fullFileSize)*100);
		
		$status = 'step:'.$this->workingStep.
				"\ncount:".$this->lp.
				"\npersent done:".$persentDone.
				"\nstart time:".$this->startTime.
				"\nwork time:".$this->WorkTime($workTime = time()-$this->startTime).
				"\ncurrent file name:".$this->currentFileName.
				"\ncurrent file number:".$this->currentFileNumber.
				"\ntotal files:".count($this->filesToImport).
				"\ncurrent id:".$id;
		
		$key = ftok(__FILE__, 'a');
		$shm_id = shmop_open($key, "c", 0644, 1024);
		shmop_write($shm_id, $status, 0);
		shmop_close($shm_id);
		
		
		#file_put_contents($this->outPutFolder."counter.txt", $status);
		
		}
	
	
	function setLen($str, $elen) {
		$len = strlen($str);
		if ($len >= $elen)
			return substr($str, 0, $elen);
		if ($elen-$len > 0)
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
	
	function RDF2Array(DOMNode $node) {
		// Jeśli węzeł jest tekstowy – zwróć przyciętą wartość.
		if ($node->nodeType === XML_TEXT_NODE) {
			return trim($node->nodeValue);
		}
		
		// Sprawdzamy, czy węzeł posiada choć jedno dziecko będące elementem.
		$hasElementChild = false;
		foreach ($node->childNodes as $child) {
			if ($child->nodeType === XML_ELEMENT_NODE) {
				$hasElementChild = true;
				break;
			}
		}
		
		$result = [];
		
		// Dodajemy atrybuty (jeśli występują) z prefiksem '@'
		if ($node->hasAttributes()) {
			foreach ($node->attributes as $attr) {
				$result["@{$attr->name}"] = $attr->value;
			}
		}
		
		// Jeśli nie ma dzieci typu element:
		if (!$hasElementChild) {
			$text = trim($node->textContent);
			if ($text !== '') {
				// Jeśli nie ma atrybutów – zwracamy skalarny tekst
				if (empty($result)) {
					return $text;
				} else {
					// Gdy mamy zarówno atrybuty, jak i tekst, zapisujemy tekst pod kluczem '#text'
					$result['#text'] = $text;
				}
			}
			// Zwracamy tablicę (nawet jeśli zawiera jedynie atrybuty)
			return $result;
		}
		
		// Przetwarzamy dzieci (tylko elementy)
		foreach ($node->childNodes as $child) {
			if ($child->nodeType !== XML_ELEMENT_NODE) {
				continue;
			}
			
			// Pobieramy nazwę dziecka – usuwamy ewentualny prefix (np. 'rdf:' czy 'dcterms:')
			$childName = $child->nodeName;
			$parts = explode(':', $childName);
			$childName = (count($parts) > 1) ? $parts[1] : $childName;
			
			// Rekurencyjnie przetwarzamy dziecko
			$childValue = $this->RDF2Array($child);
			
			// Jeśli dla danego klucza już istnieje jakaś wartość, konwertujemy ją na tablicę
			if (isset($result[$childName])) {
				if (!is_array($result[$childName]) || (is_array($result[$childName]) && !isset($result[$childName][0]))) {
					$result[$childName] = [$result[$childName]];
				}
				$result[$childName][] = $childValue;
			} else {
				$result[$childName] = $childValue;
			}
		}
		
		return $result;
		}
	
	
	
	function saveIndex($indexname, $id, $value) {
		$indexname = $this->helper->clearStr($indexname);
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
		#$this->recFormat = 'json';
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
		#$this->recFormat = 'mrk';
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
		#$this->recFormat = 'mrk';
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
		if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
			return null;
		if (!empty($this->relRec->title))
			return $this->relRec->title;
			else 
			return "[no title]";
		}
	
	public function getTitleFull() {
		if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
			return null;
		if (!empty($this->relRec->title))
			return $this->relRec->title;
			else 
			return null;	
		return "[no title]";
		}
	
	public function getTitleSort() {
		if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
			return null;
		if (!empty($this->relRec->title))
			return $this->helper->clearStr($this->relRec->title);
			else 
			return null;
		}
	
	public function getWorkKey() {
		if ($this->getRelValue('majorFormat') != 'Book')
			return null;

		if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
			return null;
		
		$keyArray = [];
		$format = $this->getRelValue('majorFormat');
		$mainAuthor = $this->helper->clearStr($this->getMainAuthor());
		if (empty($mainAuthor))
			return null;
		if (!empty($this->relRec->titleOriginal)) {
			foreach ($this->relRec->titleOriginal as $title)
				$keyArray[] = $format.$this->helper->clearStr($this->relRec->titleOriginal).$mainAuthor;
			} elseif (!empty($this->relRec->title))
			$keyArray[] = $format.$this->helper->clearStr($this->relRec->title).$mainAuthor;
		
		
		return $keyArray;
		}
	
	function roman2number($roman){
		$conv = array(
			array("letter" => 'I', "number" => 1),
			array("letter" => 'V', "number" => 5),
			array("letter" => 'X', "number" => 10),
			array("letter" => 'L', "number" => 50),
			array("letter" => 'C', "number" => 100),
			array("letter" => 'D', "number" => 500),
			array("letter" => 'M', "number" => 1000),
			array("letter" => 0, "number" => 0)
		);
		$arabic = 0;
		$state = 0;
		$sidx = 0;
		$len = strlen($roman);

		while ($len >= 0) {
			$i = 0;
			$sidx = $len;
			while ($conv[$i]['number'] > 0) {
				if (strtoupper(@$roman[$sidx]) == $conv[$i]['letter']) {
					if ($state > $conv[$i]['number']) {
						$arabic -= $conv[$i]['number'];
					} else {
						$arabic += $conv[$i]['number'];
						$state = $conv[$i]['number'];
					}
				}
				$i++;
			}
			$len--;
		}
		return($arabic);
		}


	function convertRomanNumberInStr($string) {
		return preg_replace_callback('/\b[0IVXLCDM]+\b/', function($m) {
			   return $this->roman2number($m[0]);
			   },$string);
		}



	function parse773g($line) {
		// look for first 4-digist numer (or two number separated by "/")
		$results = [];
		if (preg_match('/\b(\d{4}(?:\/\d{4})?)\b/', $line, $year_match)) {
			$rok_wydania = $year_match[1]; 
			
			// first number before publishYear should be volume
			$pattern_before_year = '/(\d+)\s*,\s*' . preg_quote($rok_wydania, '/') . '/';
			preg_match($pattern_before_year, $line, $rocznik_match);
			$rocznik = isset($rocznik_match[1]) ? $rocznik_match[1] : null;

			// first number after publishYear should be issue
			$pattern_after_year = '/' . preg_quote($rok_wydania, '/') . '\s*,\s*(\d+)/';
			preg_match($pattern_after_year, $line, $wydanie_match);
			$issue = isset($wydanie_match[1]) ? $wydanie_match[1] : null;

			// Szukamy ostatniej liczby w linii lub zakresu liczb oddzielonego "-"
			if (preg_match('/(\d+(?:-\d+)?)\s*$/', $line, $page_match)) {
				$strona = $page_match[1];
				} else {
				$strona = $this->convertRomanNumberInStr($line);
				}

			// Dodajemy wynik do tablicy
			$results = [
				'volume' => $rocznik ?? '', 
				'publishYear' => $rok_wydania ?? '',
				'issue' => $issue ?? '',
				'page' => $strona ?? '',
			];
			} else {
			if (strtolower(substr($line,0,1)) == 's') {
				$results['page'] = floatval(preg_replace("/[^0-9]/", "", $line));
				}
			}
		
		return (object)$results;
		}
	
	public function getDDkey() {
		$format = $this->getRelValue('majorFormat');
		$keyArray = [];
				
		switch ($format) {
			case 'Book' :
				# if (exists($this->relRec->titleOriginal )) $this->getDDkeyForTitleOriginal();
				
				if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
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
				
				if (isset($this->DDkeys[$ddkey]) && empty($this->DDkeys[$ddkey][$this->relRec->prefix]))
					$this->relRec->hasDuplicate = true;
				if (isset($this->DDkeys[$ddkey][$this->relRec->prefix])) {
					#$this->relRec->hasDuplicate = true;
					$this->DDkeys[$ddkey][$this->relRec->prefix]++;
					} else 
					$this->DDkeys[$ddkey][$this->relRec->prefix] = 1;
				return $ddkey;
			case 'Journal article' : 
				if (empty($this->relRec->title) or in_array($this->relRec->title, $this->titleDrop))
					return '';
				
				$keyArray[] = $this->helper->clearStr($this->getMagazines('sourceMagazine'));
				$keyArray[] = $this->helper->clearStr($this->getMainAuthor());
				if (!empty($this->relRec->publicationDate))
					$keyArray[] = $this->helper->clearStr(current($this->relRec->publicationDate));
					else if (!empty($this->relRec->publicationYear)) {
					$keyArray[] = $this->helper->clearStr(current((array)$this->relRec->publicationYear));	
					}
				
				if (!empty($this->relRec->inMagazine))
					foreach ($this->relRec->inMagazine as $key=>$v)
						$keyArray[] = $this->helper->clearStr($v);
				
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

				break;
			}
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
		if (!empty($this->relRec->persons->mainAuthor)) {
			# print_r(current ($this->relRec->persons->mainAuthor));
			# echo "going to return: ".$this->helper->createPersonStr(current ($this->relRec->persons->mainAuthor))."\n";
			return $this->helper->createPersonStr(current ($this->relRec->persons->mainAuthor));
			}
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
	
	
	
	public function getMainAuthorRole() { // co author because mainAuthor has the same role in all records (function was returning only: unknown)
		if (isset($this->relRec->persons->coAuthor)) {
			$author = current($this->relRec->persons->coAuthor);
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
	
	
	/* to rebuild 
	public function getCountry($wikiq, $year) {
		$wikiId = 'Q'.$wikiq;
		if (!empty($this->buffer->countryYears[$wikiId][$year]))
			return $this->buffer->countryYears[$wikiId][$year];
		
		$this->cms->wikiData->loadRecord($wikiId);
		$wiki = clone $this->cms->wikiData;
		$country = $wiki->getHistoricalCountry($year);
		if (is_array($country))
			$res = current($country);
			else 
			$res = $country;
		
		$this->buffer->countryYears[$wikiId][$year] = $res;
		file_put_contents($this->outPutFolder.'country_years.csv', "$wikiq|$year|$res|\n", FILE_APPEND); 
		return $country;
		}
	*/
	
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
		if (!empty($this->relRec->magazines->$as))
			foreach ($this->relRec->magazines->$as as $magazine)
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
		
	
	#####################################################################################################################################################################
	### From field to data method functions 
	#####################################################################################################################################################################
	
	public function getExternalValue($key) {
		// $this->buffer->externalSource[$key]->{this->relRec->rawId}
		
		return $this->relRec->exteralTags[$key] ?? null;
		}
	
	
	public function bufferAdd ($block, $record, $as, $type) {
		if (($block == 'wikiQ') & ($record['wikiQ'] == 'not found'))
			return null;
		$recId = $record[$block];
		
		@$this->buffer->$block[$type][$recId];
		if (!empty($record['name']))
			@$this->buffer->$block[$type][$recId]['biblio_labels'][$record['name']]++;
		@$this->buffer->$block[$type][$recId]['roles'][$as][] = $this->id;
		@$this->buffer->$block[$type][$recId]['total']++;
		@$this->buffer->$block[$type][$recId][$as]++;
		
		foreach ($record as $key=>$value) 
			@$this->buffer->$block[$type][$recId]['values'][$key][$value]++;
			
		if ($block == 'wikiQ') {
			$wikiQ = $recId;
			if (empty($this->buffer->wikiQ[$type][$wikiQ]['nameML'])) {
				$this->wikiData->loadRecord($wikiQ);
				$this->buffer->wikiQ[$type][$wikiQ]['nameML'] = $this->wikiData->getML('labels', $this->configJson->settings->multiLanguage->order);	
				}
			}
		
		}
	
	
	public function bufferWikiAdd($wikiQ, $name, $as, $type) {
		if (!empty($wikiQ) && ($wikiQ!=='not found')) {
			@$this->buffer->wikiQ[$type][$wikiQ]['biblio_labels'][$name]++;
			@$this->buffer->wikiQ[$type][$wikiQ]['roles'][$as][] = $this->id;
			@$this->buffer->wikiQ[$type][$wikiQ]['total']++;
			@$this->buffer->wikiQ[$type][$wikiQ][$as]++;
			@$this->relRec->wikiQ[$wikiQ][$as]++;	
			@$this->relRec->orgin_labels[] = $name;
			if (empty($this->buffer->wikiQ[$type][$wikiQ]['nameML'])) {
				$this->wikiData->loadRecord($wikiQ);
				$this->buffer->wikiQ[$type][$wikiQ]['nameML'] = $this->wikiData->getML('labels', $this->configJson->settings->multiLanguage->order);	
				}
			}
		}
	
	public function bufferStrAdd($name, $as, $type) {
		$key = $this->helper->clearStr($name);
		@$this->buffer->str[$type][$key]['biblio_labels'][$name]++;
		@$this->buffer->str[$type][$key]['total']++;
		@$this->buffer->str[$type][$key]['roles'][$as][] = $this->id;
		@$this->buffer->str[$type][$key][$as]++;
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
	
	public function loadLanguageMap() {
		$t = $this->psql->querySelect("SELECT * FROM dic_languages;");
		if (is_array($t)) {
			foreach ($t as $row) {
				$keys = [];
				$row['pl'] = str_replace('język ', '', $row['pl']);
				$values = $row['label_en'].'|'.$row['cs'].'|'.$row['pl'].'|'.$row['fi'].'|'.$row['es'];
				if (!empty($row['iso639_2'])) {
					$tmp = explode(',',$row['iso639_2']);
					foreach ($tmp as $lcode)
						$this->config->languageMap[$lcode] = $values;
					}
				if (!empty($row['iso639_1'])) {
					$tmp = explode(',',$row['iso639_1']);
					foreach ($tmp as $lcode)
						$this->config->languageMap[$lcode] = $values;
					}
				}
			} 
		echo "Language map loaded!\n";
		}
	
	public function relAddLanguage($field, $input) {
		
		if (!empty($input) && !empty($this->config->languageMap[$input])) {
			$input = strtolower($input);
			$language = $this->config->languageMap[$input];
			if (empty($this->relRec->language[$field]) or !in_array($language, $this->relRec->language[$field]))
				$this->relRec->language[$field][] = $language;
			} else {
			$this->relRec->language['badValues'][] = $input;	
			}
		}
	
	public function relPreparePerson($line, $as = null) {
		if (!empty($line['code'])) {
		
			$person['name'] = 
			$person['nameML'] = 
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
		
		#echo "\n".$someThing['wikiQ'].'  ('.$someThing['biblio_label'].')  =>  ('.$someThing['bestLabel'].")   {$this->localSearcher->success} \n";
		
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
		if (($type != 'person') & !empty($someThing['nameML']))
				$someThing['bestLabel'] = $someThing['nameML'];

		return $skey;
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
	
	public function relPrepareGenre($relfield, $content) {
		$content = (array)$content;
		foreach ($content as $line) {
			if (!empty($line['code']['a']))
				if (!empty($line['code']['i']) && ($line['code']['i'] == 'Major genre') && !empty($line['code']['a'])) {
					$line['code']['a'] = (array)$line['code']['a'];
					foreach ($line['code']['a'] as $z) 
						$this->relRec->$relfield[] = $z;	
					}	
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
				}
			}	
			
		
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
		
		if (!empty($this->relRec->language['original'])) {
			if (!empty($this->relRec->titleOriginal ))
				$res[] = 'Original language and original title'; 
				else 
				$res[] = 'Original language, but no original title';
					
			}		
		
		return $this->flattenArray($res);
		}

	
	public function getLicence() {
		$currentGroup = $this->getFileGroupFromName($this->currentFileName);
		if (!empty($this->licences[$currentGroup]))
			return $this->licences[$currentGroup];
		}
	
	public function getLicenceOneLine() {
		$currentGroup = $this->getFileGroupFromName($this->currentFileName);
		if (!empty($this->licences[$currentGroup])) {
			$licence = $this->licences[$currentGroup];
			$name = $licence->name ?? '';
			$link = $licence->link ?? '';
			return $name.'|'.$link;
			}
		}
	
	
	function reverseNameOrder($fullName) {
		if (stristr($fullName, ','))
			return $fullName; // fullName has correct order
		$knownSurnamePrefixes = ['de', 'van', 'von', 'da', 'dos', 'del', 'di']; // Można rozszerzyć listę
		$parts = explode(' ', trim($fullName));
		$namePartsCount = count($parts);
		if ($namePartsCount < 2) {
			return $fullName; // nothing to reverse
			}

		// find where the lastname start
		$lastNameStart = $namePartsCount - 1;
		while ($lastNameStart > 0 && in_array(strtolower($parts[$lastNameStart - 1]), $knownSurnamePrefixes)) {
			$lastNameStart--;
			}

		// separate first and last name
		$firstNames = array_slice($parts, 0, $lastNameStart);
		$lastNames = array_slice($parts, $lastNameStart);

		return implode(' ', $lastNames) . ', ' . implode(' ', $firstNames);
		}
	
	
	
	
	public function getPersonRDF($elbRole, $groupKey, $item) {
		$knownCreators = [
			'Person' => 'persons'
			];	
		if (!empty($knownCreators[$groupKey]))
			$group = $knownCreators[$groupKey];
			else {
			echo "unknown creator: \e[31m".$groupKey."\e[0m\n";
			die();
			}
						
		if (isset($item['givenName'])) {
			if (!stristr($item['surname'], $item['givenName']))
				$item['surname'].=', '.$item['givenName'];	
			unset($item['givenName']);
			}
		$name = $this->reverseNameOrder($item['surname']);	
		$key =  $this->shortHash($name);
		$thisItem = [
			'name' => $name,
			'role' => $elbRole
			];	
		$this->relRec->$group['all'][$key] = 
		$this->relRec->$group[$elbRole][$key] = $thisItem;
		unset($item['surname']);
		if (!empty($item)) {
			print_r($item);
			echo "\e[31m not matched fields left in creator \e[0m\n";
			die();
			}
		return $thisItem;
		}
	
	public function checkAddOnRDF($group, $item) {
		if (!empty($this->usedRDFAddOns[$item['@about']]))
			return true;
		switch ($group) {
			case 'Collection' :
				if (!empty($item['title']))
					$title = $item['title'];
				if (!empty($item['hasPart']))
					if (array_is_list($item['hasPart'])) {
						foreach ($item['hasPart'] as $addTo)
							if (!empty($this->wholeELBrec[$this->rawId2Id[$addTo['@resource']]]))
								$this->wholeELBrec[$this->rawId2Id[$addTo['@resource']]]->subject[] = $title;
								else 
								echo '  empty collection link: '.$addTo['@resource']."\n";
						} else {
						$link = $item['hasPart']['@resource'];	
						if (!empty($this->wholeELBrec[$this->rawId2Id[$link]]))
								$this->wholeELBrec[$this->rawId2Id[$link]]->subject[] = $title;
								else 
								echo '  empty collection link: '.$addTo['@resource']."\n";
						}
							
				break;
			default: 
				echo "  \e[31munmached {$item['@about']}\e[0m\n";
				break;
			}
		
		
		#print_r($item);	
		}
		
	public function findAddOnRDF($group, $link) {
		if (array_is_list($this->wholeRDFrec[$group])) {
			foreach ($this->wholeRDFrec[$group] as $k=>$attachment) 
				if ($attachment['@about'] == $link) {
					@$this->usedRDFAddOns[$attachment['@about']]++;
					return $attachment;
					}
			} else if ($this->wholeRDFrec[$group]['@about'] == $link) {
			@$this->usedRDFAddOns[$link]++;
			return $this->wholeRDFrec[$group];
			}		
		}		
		
	
	public function createRelationsRDF($item) {
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
	
	
	
	public function createRelations() {
		
		if (empty($this->buffer->externalSource)) {
			$impPath = './import/dataJson/';
			$glob = glob($impPath.'*.json');
			foreach ($glob as $file) {
				$key = str_replace([$impPath, '.json'], '', $file);
				$this->buffer->externalSource[$key] = json_decode(@file_get_contents($file));
				}
			}
		if (!empty($this->configJson->import->source_db))
			foreach ($this->configJson->import->source_db as $k=>$v)
				$basicSources[$k] = $v->name;
		
		
		
		if (!empty($this->record)) {
			#$this->saveLogTime('createRelations start');
			$this->noCR++;
			$this->relRec = new stdClass;
			$this->relRec->sourceFile = $this->currentFileName;
			$this->relRec->sourceDB['licence'] = $this->getLicence();
			$prefix = $this->relRec->prefix = $this->getFileGroupFromName($this->currentFileName);
			$this->relRec->sourceDB['name'] = $this->configJson->import->source_db->$prefix->name ?? '';
			$this->relRec->editTime = date("Y-m-d H:i:s");
			
			foreach ($this->record as $field => $content) {
				$inSpecial = false;	
				#$this->saveLogTime('createRelations:'.$field);
				switch ($field) {
					case 'LEADER' : 
							$this->relRec->LEADER = $content;
							$this->relRec->majorFormat = $this->relGetMajorFormat($content);
							/*
							$testStr = substr($content, 7, 1);
							if (!empty($this->config->majorFormat[$testStr]))
								$this->relRec->majorFormat = $this->config->majorFormat[$testStr];
								else 
								$this->relRec->majorFormat = 'other';	
							*/
							break;
					case '001' : 
							foreach ($content as $line) {
								$this->relRec->rawId = $rawId = $line;
								$this->relRec->id = $this->id = $this->getFileGroupFromName($this->currentFileName).'.'.$line;
								file_put_contents($this->outPutFolder.'current.id.txt', $this->id);
								if (!empty($this->buffer->externalSource))
									foreach ($this->buffer->externalSource as $key=>$extValues) 
										if (!empty($extValues->$rawId))
											$this->relRec->exteralTags[$key] = $this->flattenArray($extValues->$rawId);
								}
							break;
					case '008' : 
							foreach ($content as $line) {
								$this->relAddLanguage('publication', substr($line, 35, 3));
								
								$cleanedSubstring = str_replace('\\', '-', quotemeta($line));

								$countryCode = substr($cleanedSubstring, 15, 2);
								if ($countryCode != '--')
									$this->relRec->publicationCountry['code'] = $countryCode;
								
								$dateStr = substr($cleanedSubstring, 7, 8);
								$year = substr($dateStr,0,4);
								$month = substr($dateStr,4,2);
								$day = substr($dateStr,6,2);
								if (is_numeric($year)) {
									$this->relRec->publicationYear[] = $year;
									if (is_numeric($month) & is_numeric($day))
										$this->relRec->publicationDate[] = $year.'-'.$month.'-'.$day;
									}
								}
							break;
					case '020' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a']))
									$this->relRec->isbn[] = $line['code']['a'];
								}
							break;
					case '022' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a']))
									$this->relRec->issn[] = $line['code']['a'];
								}
							break;
					case '035' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a'])) {
									$line['code']['a'] = (array)$line['code']['a'];
									foreach ($line['code']['a'] as $code_a)
										if (stristr($code_a, '(OCoLC)'))
											$this->relRec->OCoLC[] = str_replace('(OCoLC)','', $code_a);
									$this->relRec->ctrlNum = $line['code']['a'];
									}
								}
							break;
					case '040' : 
							foreach ($content as $line) {
								if (!empty($line['code']['b'])) {
									$arr = (array)$line['code']['b'];
									foreach ($arr as $value)
										$this->relAddLanguage('record', $value);
									}
								}
							break;
					case '041' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a'])) {
									$arr = (array)$line['code']['a'];
									foreach ($arr as $value)
										$this->relAddLanguage('publication', $value);
									}
								// b ?? 	
								if (!empty($line['code']['h'])) {
									$arr = (array)$line['code']['h'];
									foreach ($arr as $value)
										$this->relAddLanguage('original', $value);
									}
								}
							break;
					case '080' : 
							$udc = [];
							$this->relAddUDC($content);
							break;
					case '100' : //here I em with wiki import
							foreach ($content as $line) {
								$this->relPreparePerson($line, 'mainAuthor');
								}
							break;
					case '110' : 
							foreach ($content as $line) {
								$this->relPrepareCorporate($line, 'mainAuthor'); 
								/*
								if (empty($line['code']['n']))
									$this->relPrepareCorporate($line, 'mainAuthor');
									else 
									$this->relPrepareEvent($line, 'mainAuthor');	
								*/
								}
							break;
					case '111' : 
							foreach ($content as $line) {
								$this->relPrepareEvent($line, 'mainAuthor');
								}
							break;
					case '130' :
					case '240' : 
					case '242' : 
					case '246' : 
							if ($this->relRec->majorFormat == 'Book') {
								foreach ($content as $line) {
									if (!empty($line['code']['a'])) {
										if (is_array($line['code']['a']))
											$title = end($line['code']['a']);
											else 
											$title = $line['code']['a'];
										
										$this->relRec->titleOriginal ['title'][] = $title;
										$this->relRec->titleOriginal ['where'][] = $field.'a';
										}
									}		
								}
							break;	
					case '245' : 
							foreach ($content as $line) {
								$this->relRec->titleShort = '';
								if (!empty($line['code']['a']) && is_string($line['code']['a']))
									if (is_array($line['code']['a']))
										$this->relRec->titleShort = $this->clearLastChar(implode(' ',$line['code']['a']));
										else
										$this->relRec->titleShort = $this->clearLastChar($line['code']['a']);
								if (!empty($this->relRec->titleShort) && ($this->relRec->titleShort == '[Název textu k dispozici na připojeném lístku]')) {
									$this->relRec->titleShort = "[Title on the picture (retrobi record)]"; 
									}	
								
								if (!empty($this->relRec->titleShort) && stristr($this->relRec->titleShort, '[') && !stristr($this->relRec->titleShort, ']')) {
									file_put_contents($this->outPutFolder.'titlesToCorrect.log', $this->relRec->id.' '.$this->relRec->titleShort."\n", FILE_APPEND);
									$this->relRec->titleShort = str_replace('[', '', $this->relRec->titleShort);
									}
								
								if (!empty($line['code']['b'])) {
									$testValues = (array)$line['code']['b'];
									foreach ($testValues as $subtitle)
										$this->relRec->titleSub[] = $this->clearLastChar($subtitle);
									$this->relRec->title = $this->relRec->titleShort.': '.implode(' ',$this->relRec->titleSub);
									} else 
									$this->relRec->title = $this->relRec->titleShort;
								if (!empty($line['code']['c'])) {
									if (empty($this->relRec->StatmentOfResp)) 
										$this->relRec->StatmentOfResp = [];
									$array = (array)$line['code']['c'];
									foreach ($array as $testStr) 
										if (stripos($testStr, ';') !== false) {
											$tmp = explode(';', $testStr);
											array_merge($this->relRec->StatmentOfResp, $tmp);
											} else 
											$this->relRec->StatmentOfResp[] = $line['code']['c'];
									}
								}
							break;
					case '250' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a'])) {
									$this->relRec->edition['str'][] = $line['code']['a'];
									$this->relRec->edition['no'][] = $this->onlyNumbers($line['code']['a']);
									}
								}
							break;
					case '260' : 
					case '264' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a'])) {
									$names = (array)$line['code']['a'];
									foreach ($names as $name) {
										$searchPlace = $this->placeHasDescription($name);
										$this->relPreparePlace($searchPlace, 'publicationPlace');
										}
									}
								if (!empty($line['code']['b'])) {
									$this->relCorporateFromStr($line['code']['b'], 'publisher');
									}
								if (!empty($line['code']['c'])) {
									$testVal = (array)$line['code']['c'];
									foreach ($testVal as $testStr) {
										$this->relRec->publicationDateStr[] = $testStr;
										if (strlen($testStr)<4) 
											$this->relRec->publicationYear[] = $this->onlyNumbers($testStr);
											else 
											$this->relRec->publicationYear[] = $this->yearFromStr($testStr);	
										}
									}
									
								if (!empty($line['code']))
									$this->relRec->publishedIn[] = implode(' ', $this->flattenArray($line['code']));	
								}
							break;
					case '380' : 
							$this->relPrepareGenre('genreMajor', $content);
							break;
					case '381' : 
							$this->relPrepareGenre('genre', $content);
							break;
					case '440' : 
							foreach ($content as $line) {
								if (!empty($line['code']['x']))
									$this->relRec->issn[] = $line['code']['x'];
								}
							break;
					case '490' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a']))
									$this->relRec->seria[] = $line['code']['a'];
								// 'v' = volumen - not important now 
								if (!empty($line['code']['x']))
									$this->relRec->issn[] = $line['code']['x'];
								}
							break;
					case '500' : 
							if ($this->relRec->majorFormat == 'Book') {
								$keyStr = 'Tytuł oryginału:';
								foreach ($content as $line) {
									if (!empty($line['code']['a'])) {
										$testLines = (array)$line['code']['a'];
										foreach ($testLines as $testLine)
											if (stristr($testLine, $keyStr)) {
												$tmp = explode($keyStr, $testLine);
												if (!empty($tmp[1])) {
													$tmp = explode('.', $tmp[1]);
													$title = $tmp[0];
													$this->relRec->titleOriginal ['title'][] = trim($title);
													$this->relRec->titleOriginal ['where'][] = $field.'a';
													}
												}
										}
									}
								}
							break;
					case '520' :
							foreach ($content as $line) {
								if (!empty($line['code']['a']))
									$this->relRec->description[] = $line['code']['a'];
								}
							break;
					case '600' : 
							foreach ($content as $line) {
								$this->relPreparePerson($line, 'subjectPerson');
								}
							break;
					case '610' : 
							$inSpecial = true;
							foreach ($content as $line) {
								if (empty($line['code']['n']))
									$this->relPrepareCorporate($line, 'subjectCorporate');
									else 
									$this->relPrepareEvent($line, 'subjectEvent');	
								}
							break;
					case '611' : 
							$inSpecial = true;
							foreach ($content as $line) {
								$this->relPrepareEvent($line, 'subjectEvent');
								}
							break;
					// 601-699 in if below 
					case '630' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a'])) {
									$this->relAddSubject('work', $line['code']['a']);
									}
								}
							break;
					case '648' : 
							foreach ($content as $line) {
								$centuriesTable = $this->relCenturies($line);
								
								if (!empty($centuriesTable) && empty($this->relRec->subject))
									$this->relRec->subject = new stdClass;
								$this->relRec->subject->centuries = $centuriesTable;
								# $this->relAddSubject('centuries', $this->relCenturies($line));
								}
							break;
					case '650' : 
							foreach ($content as $k=>$line) {
								$goWithTopic = true;
								if (!empty($line['code']['2']) && is_string ($line['code']['2'])) {
									if (trim($line['code']['2']) == 'ELB-g') {
										$this->relAddSubject('elb', 'genre', $line['code']['a']);
										unset($this->record->$field[$k]);
										$goWithTopic = false;
										} else if (trim($line['code']['2']) == 'ELB-n') {
										#echo $line['code']['a']."\n";
										$this->relAddSubject('elb', 'nations',  str_replace(' literature', '', $line['code']['a']));
										unset($this->record->$field[$k]);
										$goWithTopic = false;
										
										}
									}
								if ($goWithTopic) {
									$ignoreSubFields = ['0','2','7'];
									foreach ($ignoreSubFields as $key) 
										if (!empty($line['code'][$key]))
											unset($line['code'][$key]);
									file_put_contents($this->outPutFolder.'topic.csv', implode(';', $this->flattenArray($line['code']))."\n", FILE_APPEND);	
									$this->relAddSubject('topic', $this->flattenArray($line['code']));
									
									}
								}
							$inSpecial = true;
							break;
					case '651' : 
							foreach ($content as $line) {
								$ids = [];
								if (!empty($line['code']['2']) && !empty($line['code']['0']))
									$ids[$line['code']['2']] = $line['code']['0'];
								if (!empty($line['code']['2']) && !empty($line['code']['7']))
									$ids[$line['code']['2']] = $line['code']['7'];
								if (!empty($line['code']['a'])) {
									$searchPlace = $this->placeHasDescription($line['code']['a']);
									if (!empty($ids))
										$searchPlace['ids'] = $ids;
									$this->relPreparePlace($searchPlace, 'subjectPlace');
									}
								}
							break;
					case '653' : 
							$inSpecial = true;
							break;
					case '655' :
							$subfields = [
									'a' => 'name',
									'y' => 'chronological',
									'v' => 'subDiv',
									'7' => 'dataProvenance',
									'2' => 'sourceOfTerm'
									];
							foreach ($content as $line) {
								$form = [];
								foreach ($line['code'] as $key=>$value) 
									if (array_key_exists($key, $subfields))
										$form[$subfields[$key]] = $value;
								if (!empty($form))
									$this->relAddSubject('formGenre', $form);
								if (!empty($line['code']['z'])) {
									$names = (array)$line['code']['z']; // 2024-02-12 in current dataSet we have OLNY ONE case with array (country->region->city), all others has only general region/country name as string 
									foreach ($names as $name) {
										$searchPlace['name'] = $name;
										if (count($names) == 1) 
											$searchPlace['type'] = 'country';
										$this->relPreparePlace($searchPlace, 'subjectPlace');
										}
									}
								}
							$inSpecial = true;
							break;
					case '700' : 
							foreach ($content as $line) {
								$this->relPreparePerson($line, 'coAuthor');
								if (!empty($line['t'])) {
									$testVal = (array)$line['t'];
									foreach ($testVal as $title)
										$this->relRec->titleAlt[] = $title;
									}
								}
							break;
					case '710' : 
							foreach ($content as $line) {
								if (empty($line['code']['n']))
									$this->relPrepareCorporate($line, 'coAuthor');
									else 
									$this->relPrepareEvent($line, 'coAuthor');	
								}
							break;
					case '711' : 
							foreach ($content as $line) {
								$this->relPrepareEvent($line, 'coAuthor');
								}
							break;
					case '765' : 
							if ($this->relRec->majorFormat == 'Book') {
								foreach ($content as $line) {
									if (!empty($line['code']['t'])) {
										if (is_array($line['code']['t']))
											$title = end($line['code']['t']);
											else 
											$title = $line['code']['t'];
										
										$this->relRec->titleOriginal ['title'][] = $title;
										$this->relRec->titleOriginal ['where'][] = $field.'t';
										}
									}		
								}
							break;	
					case '730' : 
					case '776' : 
					case '780' : 
					case '785' : 
							foreach ($content as $line) {
								if (!empty($line['code']['x']))
									$this->relRec->issn[] = $line['code']['x'];
								}
							break;
					case '773' : 
							// leader [9] == b
							foreach ($content as $line) {
								$this->relPrepareMagazine($line, 'sourceMagazine');
								if (!empty($line['code']['g'])) {
									$line['code']['g'] = (array)$line['code']['g'];
									foreach ($line['code']['g'] as $string)
										$this->relRec->inMagazine = $this->parse773g($string);	
									}
								}
							break;
					case '787' : 
							$subfields = [
									'i' => 'type_of',
									'a' => 'name',
									't' => 'title',
									];
							foreach ($content as $line) {
								$refWork = [];
								foreach ($line['code'] as $key=>$value) 
									if (array_key_exists($key, $subfields))
										$refWork[$subfields[$key]] = $value;
								if (!empty($refWork))
									$this->relRec->referedWork[] = $refWork;
								}
							break;
					#case '950' : // "zobacz w polonie"
					case '856' : 
							$internalContentStrings = ['libri.ucl.cas.cz', 'literarybibliography.ue'];
							$fullTextStrings = ['.pdf'];
							foreach ($content as $line) {
								$lr = [];
								if (!empty($line['code']['u']) && is_string($line['code']['u'])) { 
									$testStr = $line['code']['u'];
									$is_internal = false;
									foreach ($internalContentStrings as $str)
										if (stripos($testStr, $str) !== false) {
											$tmp = explode('/', $testStr);
											$lr['id'] = end($tmp);
											$is_internal = true;
											break; 
											}
									$lr['link'] = $testStr;
									foreach ($fullTextStrings as $str)
										if (stripos($testStr, $str) !== false) {
											$lr['fullText'] = true;
											break; 
											}
									}
								if (!empty($line['code']['y'])) {
									$lr['desc'] = $line['code']['y'];
									}
								if (!empty($line['code']['3'])) {
									$lr['materialsSpecified'] = $line['code']['3'];
									}
								if (!empty($line['code']['z'])) {
									$lr['publicNote'] = $line['code']['z'];
									}
									
								if (!empty($lr['link']))
									$this->relRec->linkedResources[] = $lr;	
								if (!empty($lr['id']))
									$this->relRec->internalResources[] = $lr;	
								}
							break;
					case '956' : 
					case '995' : 
							foreach ($content as $line) {
								$lr = [];
								
								if (!empty($this->relRec->sourceDB['name'])) { 
									$sourceDBname = $this->relRec->sourceDB['name'];
									} 
								
								if (!empty($line['code']['a']) && is_string($line['code']['a'])) { 
									$this->relRec->sourceDB['name'] = $line['code']['a'];
									}
								if (!empty($line['code']['b']) && is_string($line['code']['b'])) { 
									$this->relRec->sourceDB['supplemental'] = $line['code']['b'];
									}
								
								if (!empty($sourceDBname) && !empty($line['code']['a']) && is_string($line['code']['a']) && empty($line['code']['b'])) { 
									$this->relRec->sourceDB['name'] = $sourceDBname;
									if (!in_array($line['code']['a'], $basicSources)) 
										$this->relRec->sourceDB['supplemental'] = $line['code']['a'];
									} 
								
								
								}
							break;
					case '964' : 
							foreach ($content as $line) {
								if (!empty($line['code']['a']) & ($this->relRec->prefix == 'cz') & empty($this->relRec->sourceDB['supplemental']))
									$this->relRec->sourceDB['supplemental'][] = $line['code']['a'];
								}
							break;
					} // switch 
					if (!$inSpecial && (($field >= '601') & ($field <= '699'))) {
						/*
						7 - id
						2 - type_of_id
						0 - link (in ELB mostly VIAF)
						
						*/
						
						$ignoreSubFields = ['0','2','7'];
						foreach ($content as $line) {
							foreach ($ignoreSubFields as $key) 
									if (!empty($line['code'][$key]))
										unset($line['code'][$key]);
							$this->relAddSubject('strings', $this->flattenArray($line['code']));
							}		
						}
					$inSpecial = false;		
				} // foreach 
			
			if (!empty($this->relRec->publicationYear) && is_array($this->relRec->publicationYear))
				$this->relRec->publicationYear = array_unique($this->relRec->publicationYear);
			
			if (!empty($this->configJson->import->source_db->$prefix->collections) && !empty($this->relRec->sourceDB['supplemental'])) {
				$collection = '';
				$this->relRec->sourceDB['supplemental'] = (array)$this->relRec->sourceDB['supplemental'];
				foreach ($this->relRec->sourceDB['supplemental'] as $collectionCode)
					if (!empty($this->configJson->import->source_db->$prefix->collections->$collectionCode))
						$collection = $this->configJson->import->source_db->$prefix->collections->$collectionCode;
				$this->relRec->sourceDB['supplemental'] = $collection;
				}
			
			if (!empty($this->currentUpdates->singleRecords->{$this->relRec->rawId})) {
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
						echo "s";
						}
					if (is_object($value)) {
						echo "o";
						foreach ($value as $skey=>$svalue)
							$this->relRec->$key[$skey] = $svalue;
						}
					}
				}
			
			/* #### metoda osobny plik dla każdego rekordu 
			$prefix = $this->relRec->prefix;
			$fileName = $this->relRec->rawId;
			$changesFileName = $this->configJson->import->extentionsFolder.$prefix.'/'.$fileName.'.json';
			if (file_exists($changesFileName)) {
				$changesJson = json_decode(file_get_contents($changesFileName));
				foreach ($changesJson as $key=>$blockArray) 
					if (is_array($blockArray))
						foreach ($blockArray as $akey=>$value) 
							$this->relRec->$key[$akey] = $value;
				}
			*/
			
			$workTime = time()-$this->startTime;
			$returnStr = $this->helper->numberFormat($this->noCR).
				". \e[92m".round(($this->buffSize/$this->fullFileSize)*100).
				"%\e[0m  rec: (".$this->setLen($this->id,20).")    ".
				$this->WorkTime($workTime)." s. ";
			
			file_put_contents($this->outPutFolder.'t.relRecord.json', json_encode($this->relRec));
			#$this->saveLogTime('createRelations stop');
			return $returnStr."\r";
			} // if !empty
			
		
		#file_put_contents($this->outPutFolder.'t.record.json', print_r($this->record,1));
		file_put_contents($this->outPutFolder.'t.relRecord.json', json_encode($this->relRec));
		
		}
	

	
	########################################   
	
	
	
	public function getCurrentTime() {
		return date("Y-m-d").'T'.date("H:i:s").'Z';
		}
		
	
	public function getFullMrc() {
		$id = $this->id;
		$file = file_get_contents('http://localhost/lite/import/marc21/getMRC.php?id='.$id);
		#$this->recFormat = 'mrc';
		return $file;
		}
			
	public function getRecFormat() {
		return $this->recFormat;
		}
		
	public function getSourceMrk() {
		#$this->recFormat = 'mrk';
		if (!empty($this->mrk))
			return $this->mrk;
		}
		
	public function drawTextMarc() {
		#$this->recFormat = 'mrk';
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
		$charsToRemove = ['/', ';', ':', ',', ',.'];
		
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
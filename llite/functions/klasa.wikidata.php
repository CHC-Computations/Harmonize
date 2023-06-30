<?php 

// Dopracuj to ! i mediawiki 
// https://pl.wikipedia.org/wiki/Adam_Mickiewicz
// https://pl.wikipedia.org/w/api.php?action=parse&format=json&page=Adam_Mickiewicz 

class wikidata {
	
	function __construct($wikiId, $restore = false) {
		$this->defLang = 'en';
		$this->userLang = $this->defLang;
		setlocale(LC_ALL, 'pl_PL.UTF8');
			
		if (is_string($wikiId)) {
			$this->settings = json_decode(@file_get_contents('./config/settings.json'));
			if (empty($this->settings)) {
				return "settings.json file not found";
				}
			
			$solr = $this->settings->solr;
			$query = '/solr/'.$solr->cores->wiki.'/select?q=id:'.$wikiId;
			$record = json_decode(@file_get_contents($solr->hostname.':'.$solr->port.$query));
			if (empty($record->response)) {
				return "solr error $query";
				} 
			#echo "<pre>".print_r($record,1).'</pre>';
			$this->restore = $restore;
			if (!empty($record->response->docs[0])) {
				$this->solrRecord = $record->response->docs[0];
				if (!empty($record->response->docs[0]->fullrecord))
					$this->record = json_decode($record->response->docs[0]->fullrecord)->entities->$wikiId;
					else 
					$this->restore = true;
				if (!empty($this->solrRecord->labels))
					$this->labels = json_decode($this->solrRecord->labels);
					else 
					$this->restore = true;
				if (!empty($this->solrRecord->aliases))
					$this->aliases = json_decode($this->solrRecord->aliases);
					else 
					$this->restore = true;
				if (!empty($this->solrRecord->descriptions))
					$this->descriptions = json_decode($this->solrRecord->descriptions);
					else 
					$this->restore = true;
				}  
			if (empty($record->response->docs[0]) or ($this->restore)) {
				$wikiIntId = substr($wikiId,1);
				#echo "int:  $wikiIntId, first letter:".substr($wikiId,0,1)."<br>";
				if ((substr($wikiId,0,1)=='Q') and (intval(substr($wikiId,1))>0)) {
					
					$fileContent = @file_get_contents($this->settings->externalHosts->wikidata."wiki/Special:EntityData/$wikiId.json");
					if (!empty($fileContent)) {
						$json = @json_decode($fileContent);
						if (is_object($json)) {
							$wiki = new wikidata($json);
							if (!empty($wiki->getID())) {
								unset($data);
								$data = (object) ["id" => $wiki->getID()];	
								$data->fullrecord 		= (object) ["set" => $fileContent];
								$data->record_format 	= (object) ["set" => 'json'];
								$data->record_length = (object) ["set" => strlen($fileContent)];
								
								$data->record_type 		= (object) ["set" => $wiki->recType()];
								$data->first_indexed	= (object) ["set" => date("Y-m-d").'T'.date("H:i:s").'Z'];
								$data->last_indexed		= (object) ["set" => date("Y-m-d").'T'.date("H:i:s").'Z'];
								
								$data->labels			= (object) ["set" => json_encode($wiki->getLabels())];
								$data->aliases			= (object) ["set" => json_encode($wiki->getAliases())];
								$data->descriptions		= (object) ["set" => json_encode($wiki->getDescriptions())];
								
								if ($wiki->recType() == 'person') {
									$data->related_place = $wiki->getPropIds('P551');
									$data->birth_date = $wiki->getDate('P569');
									$data->death_date = $wiki->getDate('P570');
									if (!empty($data->birth_date)) {
										$data->birth_year = intval(explode('-',$data->birth_date)[0]);
										}
									if (!empty($data->death_date)) {
										$data->death_year = intval(explode('-',$data->death_date)[0]);
										}
									$data->birth_place = $wiki->getPropIds('P19');
									$data->death_place = $wiki->getPropIds('P20');
									if (!empty($data->birth_place))
										foreach ($data->birth_place as $place)
											$data->related_place[] = $place;
									if (!empty($data->death_place))
										foreach ($data->death_place as $place)
											$data->related_place[] = $place;
									if (!empty($data->related_place))
										$data->related_place = array_unique($data->related_place);
									
									$data->country = $wiki->getPropIds('P27');
									$data->occupation = $wiki->getPropIds('P106');
									$data->gender = $wiki->getPropIds('P136');
									
									}
								
								
								if ($wiki->recType() == 'place') {
									$value = $wiki->getCoordinates('P625');
									if (!empty($value->longitude)) {
										$data->longitiude = str_replace(',','.',$value->longitude);
										$data->latitiude = str_replace(',','.',$value->latitude);
										$data->long_lat = $data->longitiude.','.$data->latitiude;
										$data->long_lat2 = $data->longitiude.','.$data->latitiude;
										}
									$data->country = str_replace('Q', '', $wiki->getPropId('P17'));
									}
								
								
								$postdata = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
								
								$solrPath = $this->settings->solr->hostname.':'.$this->settings->solr->port.'/solr/'.$this->settings->solr->cores->wiki.'/update';
								$ch = curl_init($solrPath); 
								curl_setopt($ch, CURLOPT_POST, 1);
								curl_setopt($ch, CURLOPT_POSTFIELDS, '['.$postdata.']');
								curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
								curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
								$result = curl_exec($ch);
								$resDecoded = json_decode($result);
								if ($resDecoded->responseHeader->status == 0) {
									file_get_contents($solrPath.'?commit=true');
									} 
								curl_close($ch);
								
								$this->record = current($json->entities);
								}
							}
						}
					
					
					
					
					} else 
					return "error - not wikidata entities";
				}
			#echo "<pre>".print_r($this->record,1).'</pre>';
			} else if (is_object($wikiId)) {
			if (!empty($wikiId->entities))
				$this->record = current($wikiId->entities);
				else 
				$this->record = new stdclass;	
			}
		}
		
	function setUserLang($langCode) {
		$this->userLang = $langCode;
		}
	
	function get($source, $lang = '') {
		if ($lang == '')
			$lang = $this->userLang;
		if (!empty($this->record->$source->$lang))
			return $this->getValue($this->record->$source->$lang);
		if (!empty($this->record->$source->{$this->defLang}))
			return $this->getValue($this->record->$source->{$this->defLang});
		if (!empty($this->record->$source))
			return $this->getValue(current($this->record->$source));
		}
	
	function getML($source, $langs = [], $sep = '|') {
		foreach ($langs as $lang) {
			if (!empty($this->record->$source->$lang))
				$return[$lang] = $this->getValue($this->record->$source->$lang);
				else if (!empty($this->record->$source->{$this->defLang}))
				$return[$lang] = $this->getValue($this->record->$source->{$this->defLang});
				else if (!empty($this->record->$source))
				$return[$lang] = $this->getValue(current($this->record->$source));
			}
		return implode($sep,array_values($return));	
		}
	
	
	
	function getID() {
		if (!empty($this->record->id))
			return $this->record->id;
		}
	
	function getIDint() {
		if (!empty($this->record->id))
			return substr($this->record->id,1);
		}
	
	
	function getValue($x, $sep = ', ') {
		if (is_array($x)) {
			foreach($x as $ver)
				$Tver[] = $ver->value;
			return implode($sep, $Tver);
			} else 
			if (!empty($x->value))
				return $x->value;
		}
	
	function getBiblioLabel() {
		if (!empty($this->solrRecord->biblio_labels))
			return end($this->solrRecord->biblio_labels);
		}
	
	function getSolrValue($field) {
		if (!empty($this->solrRecord->$field))
			if (is_array($this->solrRecord->$field))
				return current($this->solrRecord->$field);
				else 
				return $this->solrRecord->$field;
		}
		
	function getActivePersonValues() {
		$activePerson = new stdclass;
		$activePerson->solr_str = $this->getBiblioLabel();
		$activePerson->as_author = $this->getSolrValue('as_author');
		$activePerson->as_author2 = $this->getSolrValue('as_coauthor');
		$activePerson->as_topic = $this->getSolrValue('as_subject');
		$activePerson->rec_total = $this->getSolrValue('biblio_count');
		$activePerson->wikiq = $this->getIDint();
		$activePerson->wikiId = $this->getID();
		$activePerson->viaf_id = $this->getViafId();
		$activePerson->name = $this->get('labels');
		return $activePerson;
		}	
	
	function getLabels() { // return labels table
		$Tnames = [];
		if (!empty($this->record->labels))
			foreach ($this->record->labels as $lang=>$labels) {
				$Tnames[$lang] = $labels->value;
				}
		return $Tnames;
		}
	
	function getDescriptions() { // return descriptions table
		$Tnames = [];
		if (!empty($this->record->descriptions))
			foreach ($this->record->descriptions as $lang=>$descriptions) {
				$Tnames[$lang] = $descriptions->value;
				}
		return $Tnames;
		}
	
	function getAliases() { // return aliases table
		$Tnames = [];
		if (!empty($this->record->aliases))
			foreach ($this->record->aliases as $lang=>$aliases) {
				foreach ($aliases as $alias) {
					$Tnames[$lang][] = $alias->value;
					}
				}
		return $Tnames;
		}
	
	
	function getAllNames() { // return all labels and aliases
		$Tnames = [];
		if (!empty($this->record->labels))
			foreach ($this->record->labels as $lang=>$labels) {
				$val = $labels->value;
				$Tnames[$val] = $val;
				}
		
		if (!empty($this->record->aliases))
			foreach ($this->record->aliases as $lang=>$aliases) {
				foreach ($aliases as $alias) {
					$val = $alias->value;
					$Tnames[$val] = $val;
					}
				}
		
		return $Tnames;
		}
	
	function getAllNamesStr() { // return all labels and aliases
		return implode(', ', $this->getAllNames());
		}
	
	function getSearchString() {
		$t = $this->getAllNames();
		$charsArr = array( '^', "'", '"', '`', '~');
		$Twords = [];
		foreach ($t as $name) {
			$words = explode(' ', $name); 
			foreach ($words as $word) {
				$word = iconv('UTF-8', 'ASCII//TRANSLIT', trim($word)); 
				$word = str_replace( $charsArr, '', $word );
				$word = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($word))));
				$Twords[$word] = trim($word);
				}
			}
		ksort($Twords);	
		return implode(' ', $Twords);	
		}
	
	
	
	function recType() {
		$institutions = [
				'Q2085381', // publisher
				'Q43229', // organization
				'Q79913', // non-governmental organization
				];
		
		if (!empty($this->record->claims->P31)) {
			$id = $this->record->claims->P31[0]->mainsnak->datavalue->value->id;
			foreach ($this->record->claims->P31 as $part) {
				$id = $part->mainsnak->datavalue->value->id;
				if ($id=='Q5')
					return 'person';
				if (in_array($id, $institutions))
					return 'institution';
				}
			}
		if (!empty($this->record->claims->P569)) {
				return 'person';
			}
		if (!empty($this->record->claims->P570)) {
				return 'person';
			}
		if (!empty($this->record->claims->P625))
			return 'place';
		return 'unknow';
		}	
	
	function getStrVal($claim) {
		if (!empty($this->record->claims->$claim[0]->mainsnak->datavalue->type)) {
			return $this->record->claims->$claim[0]->mainsnak->datavalue->value;
			}
		return null;
		}	
	
	function getViafId() {
		return $this->getStrVal('P214');
		}	
	
	function getClearDate($claim) {
		if (!empty($this->record->claims->$claim[0]->mainsnak->datavalue->value->time)) {
			return $this->record->claims->$claim[0]->mainsnak->datavalue->value->time;
			}
		return null;
		}	
	
	function getDate($claim) {
		if (!empty($this->record->claims->$claim[0]->mainsnak->datavalue->value->time)) {
			$sd = $this->record->claims->$claim[0]->mainsnak->datavalue->value->time;
			if (substr($sd,0,1) == '-')
				$bc = '-';
				else 
				$bc = '';
			$d = str_replace(['-', '.', '+'], '', $sd);
			$year = $bc.substr($d,0,4);
			$month = substr($d,4,2);
			$day = substr($d,6,2);
			
			$retDate = $year;
			if ((floatval($month)>0)&&(floatval($month)<13))
				$retDate .='-'.$month;
			if ((floatval($day)>0)&&(floatval($day)<32))
				$retDate .='-'.$day;
			
			return $retDate;
			}
		return null;
		}	
	
	
	#######################################################################################################################

	
	function getHistoricalCityName($time) {
		$time = strtotime($time);
		$res = new stdclass;
		$res->name = $this->get('labels');
		$res->langcode = $this->userLang;
		
		if (!empty($this->record->claims->P1448)) {
			
			foreach ($this->record->claims->P1448 as $histPlace) 
				if (!empty($histPlace->qualifiers)) {
					$data_od = $data_do = $time;
					if (!empty($histPlace->qualifiers->P580[0]->datavalue->value->time))
						$data_od = strtotime($histPlace->qualifiers->P580[0]->datavalue->value->time);
					if (!empty($histPlace->qualifiers->P582[0]->datavalue->value->time))
						$data_do = strtotime($histPlace->qualifiers->P582[0]->datavalue->value->time);
					if (($time>=$data_od)&($time<=$data_do)) {
						$res->name = $histPlace->mainsnak->datavalue->value->text;
						$res->langcode = $histPlace->mainsnak->datavalue->value->language;
						#echo "daty: $data_od, <b>$time</b>, $data_do<Br/>";
						} 
					}
			}
		
		return $res;
		}
	
	function getHistoricalCountries() {
		$Tres = [];
		$res = new stdclass;
		$res->name = $this->get('labels');
		$res->langcode = $this->userLang;
		
		if (!empty($this->record->claims->P17)) {
			
			foreach ($this->record->claims->P17 as $histPlace) {
				if (!empty($histPlace->qualifiers)) {
					$data_od = '-9999-00-00T00:00:00Z';
					$data_do = '+'.date("Y-m-d").'T00:00:00Z';
					if (!empty($histPlace->qualifiers->P580[0]))
						$data_od = $histPlace->qualifiers->P580[0]->datavalue->value->time;
					if (!empty($histPlace->qualifiers->P582[0]))
						$data_do = $histPlace->qualifiers->P582[0]->datavalue->value->time;
					$res = new stdclass;
					$res->dateFrom = $data_od;
					$res->dateTo = $data_do;
					$res->wikiId = $histPlace->mainsnak->datavalue->value->id;
					
					$Tres[$data_do]=$res;
					}
				}
			krsort($Tres);
			}
		
		return $Tres;
		}
	
	function getHistoricalCountry($year) {
		$Tres = [];
		$ChRes = [];
		$res = new stdclass;
		$res->name = $this->get('labels');
		$res->langcode = $this->userLang;
		
		if ($year>0)
			$year = '+'.$year;
		$stime = $year.'-00-00T00:00:00Z';
		
		
		if (!empty($this->record->claims->P17)) {
			foreach ($this->record->claims->P17 as $histPlace) {
				if (!empty($histPlace->qualifiers)) {
					$data_od = '-9999-00-00T00:00:00Z';
					$data_do = '+'.date("Y-m-d").'T00:00:00Z';
					if (!empty($histPlace->qualifiers->P580[0]->datavalue->value->time))
						$data_od = $histPlace->qualifiers->P580[0]->datavalue->value->time;
					if (!empty($histPlace->qualifiers->P582[0]->datavalue->value->time))
						$data_do = $histPlace->qualifiers->P582[0]->datavalue->value->time;
					$res = new stdclass;
					$res->dateFrom = $data_od;
					$res->dateTo = $data_do;
					$res->wikiId = $histPlace->mainsnak->datavalue->value->id;
					$Tres[$data_do] = $res;
					if (($stime>=$data_od)and($stime<=$data_do))
						$ChRes[$data_do] = $histPlace->mainsnak->datavalue->value->id;
					}
				}
			if (empty($ChRes)) {
				$ChRes = $this->getPropId('P17');
				}
			#krsort($Tres);
			}
		return $ChRes;
		}
	
	
	function getHistoricalNames() {
		$Tres = [];
		$res = new stdclass;
		$res->name = $this->get('labels');
		$res->langcode = $this->userLang;
		
		if (!empty($this->record->claims->P1448)) {
			
			foreach ($this->record->claims->P1448 as $histPlace) {
				if (!empty($histPlace->qualifiers)) {
					$data_od = '-9999-00-00T00:00:00Z';
					$data_do = '+'.date("Y-m-d").'T00:00:00Z';
					if (!empty($histPlace->qualifiers->P580[0]->datavalue->value->time))
						$data_od = $histPlace->qualifiers->P580[0]->datavalue->value->time;
					if (!empty($histPlace->qualifiers->P582[0]->datavalue->value->time))
						$data_do = $histPlace->qualifiers->P582[0]->datavalue->value->time;
					$res = new stdclass;
					$res->dateFrom = $data_od;
					$res->dateTo = $data_do;
					$res->name = $histPlace->mainsnak->datavalue->value->text;
					$res->langcode = $histPlace->mainsnak->datavalue->value->language;
					$Tres[$data_do]=$res;
					}
				
				}
			krsort($Tres);
			}
		
		return $Tres;
		}
	
	function getCoordinates($claim) {
		if (!empty($this->record->claims->$claim[0]->mainsnak->datavalue->value)) {
			$d = $this->record->claims->$claim[0]->mainsnak->datavalue->value;
			return $d;
			}
		return null;
		}	
	
	function getPropId($claim) {
		$arr = $this->getPropIds($claim);
		if (!empty($arr))
			return $arr[0];
		}	
	
	function getPropIds($claim) {
		$Tres = [];
		if (!empty($this->record->claims->$claim)) {
			foreach ($this->record->claims->$claim as $v)
				if (!empty($v->mainsnak->datavalue->value->id) && (($v->rank=='normal')or($v->rank=='preferred'))) {
					$Tres[] = $v->mainsnak->datavalue->value->id;
					}
			return $Tres;
			}
		return null;
		}	
	
	function getSiteLink() {
		$lang = $this->userLang.'wiki';
		if (!empty($this->record->sitelinks->$lang->url))
			return $this->record->sitelinks->$lang->url;
		$lang = $this->defLang.'wiki';
		if (!empty($this->record->sitelinks->$lang->url))
			return $this->record->sitelinks->$lang->url;
		return null;
		}
	
	function isPlace() {
		// check if has P625 - coordinates
		if (!empty($this->record->claims->P625))
			return true;
			else 
			return false;
		}
	
	
	function getClaim($claim) {
		
		}
	
	function getSitelinks() {
		
		}
	

	
	}

?>
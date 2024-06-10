<?php 


class wikidata {
	
	private $mainBlocks = ['labels', 'aliases', 'descriptions'];
	public $cms;
	public $solrClient;
	
	public $record;
	public $solrRecord;
	
	private $restore;
	
	public $labels;
	public $aliases;
	public $descriptions;
	public $localRecord;
	public $returnLang;
	
	function __construct($cms) {
		$this->cms = $cms;
		$this->solrClient = $this->solrClient ?? $this->cms->solr->createSolrClient('wiki');
		}
	

	public function register($name, $var) {
		$this->$name = $var;
		}
	

	function loadRecord($wikiq, $restore = false) {
		$return = false;
		$this->localRecord = 'internal';
		$this->record = 
		$this->solrRecord = new stdclass;
		if (is_string($wikiq)) {
			if (!$restore) {
				$query = new SolrQuery();
				$query->setQuery('id:'.$wikiq);
				$query->setStart(0);
				$query->setRows(1);

				$record = $this->solrClient->query($query)->getResponse();
				if (!empty($record->response->docs[0])) {
					$this->restore = $restore;
					$this->solrRecord = $record->response->docs[0];
					#echo 'loadRecord:solrRecord<pre>'.print_r($this->solrRecord,1).'</pre>';
					if (!empty($record->response->docs[0]->fullrecord)) {
						$this->record = json_decode($record->response->docs[0]->fullrecord)->entities->$wikiq;
						} else 
						$this->restore = true;

					$atLeastOne = false;
					foreach ($this->mainBlocks as $blockName)
						if (!empty($this->solrRecord->$blockName)) {
							$this->$blockName = json_decode($this->solrRecord->$blockName);
							$atLeastOne = true;
							} // else if (!empty($this->record->$blockName)) $this->restore = true;
							
					if (!$atLeastOne)
						$this->restore = true;
					$return = true;
					} 
				}
				
			if (empty($record->response->docs[0]) or ($this->restore)) {
				$wikiqInt = intval(substr($wikiq,1));
				if ((substr($wikiq,0,1) == 'Q') and ($wikiqInt > 0)) {
					$this->localRecord = 'external';
					if ($this->restore) $this->localRecord = 'double';
					$jsonContent = json_decode(@file_get_contents($ehost = $this->cms->configJson->wikidata->host."wiki/Special:EntityData/$wikiq.json"));
					if (!empty($jsonContent) && is_object($jsonContent)) {
						
						$this->saveRecord($jsonContent);
						}
					
					} else {
					$this->error = "not wikidata entity given";
					return false;
					}
				}
			} else if (is_object($wikiq)) {
			if (!empty($wikiq->entities))
				$this->record = current((array)$wikiq->entities);
				else 
				$this->record = new stdclass;	
			}
		return $return;
		}
	
		
	function saveRecord($fileContent) {
		$this->solrRecord = $fileContent;
		# $this->record->id = $id = key((array)$this->solrRecord->entities);
		$this->record = current((array)$fileContent->entities);
		$id = $this->record->id;		
		foreach ($this->mainBlocks as $blockName)
			if (!empty($this->solrRecord->entities->$id->$blockName))
				foreach ($this->solrRecord->entities->$id->$blockName as $key => $content) {
					if (is_object($content))
						$this->solrRecord->$blockName[$key] = $content->value;
					if (is_array($content))
						foreach ($content as $alias)
							$this->solrRecord->$blockName[$key][] = $alias->value;
					}

		#echo '<pre>'.print_r($this->solrRecord,1).'</pre>';
		
		if (!empty($this->getID())) {
			unset($data);
			$data = (object) ["id" => $this->getID()];	
			$data->fullrecord 		= (object) ["set" => json_encode($fileContent)];
			$data->record_format 	= (object) ["set" => 'json'];
			$data->record_length 	= (object) ["set" => strlen(json_encode($fileContent))];
			
			$data->record_type 		= (object) ["set" => $this->recType()];
			$data->first_indexed	= (object) ["set" => date("Y-m-d").'T'.date("H:i:s").'Z'];
			$data->last_indexed		= (object) ["set" => date("Y-m-d").'T'.date("H:i:s").'Z'];
			
			foreach ($this->mainBlocks as $blockName)
				if (!empty($this->solrRecord->$blockName))
					$data->$blockName = (object) ["set" => json_encode($this->solrRecord->$blockName, JSON_INVALID_UTF8_SUBSTITUTE)]; 
			foreach ($this->mainBlocks as $blockName) {
				$blockNameFull = $blockName.'_search'; 
				if (!empty($this->solrRecord->$blockName))
					$data->$blockNameFull = (object) ["set" => $this->removeArrayKeys($this->solrRecord->$blockName)]; 
				}
			
			$ids = [];
			foreach ($this->cms->configJson->wikidata->ids as $key=>$property)
				if (!empty($this->getValOrId($property))) {
					$dkey = 'eids_'.$key;
					$data->$dkey = (object) ["set" => $this->getValOrId($property)];  
					$ids[$key] = $this->getValOrId($property);
					}
			if (!empty($ids))
				$data->eids_any = (object) ["set" => $this->removeArrayKeys($ids)]; 

			#file_put_contents('import/outputfiles/wiki/wikiTry.'.$wiki->getID().'.json', $postdata);
			$this->cms->solr->curlSaveData('wiki', $data);
			
			}
		}
	
		

	
	function get($source, $lang = '') {
		if ($lang == '')
			$lang = $this->cms->userLang;
		if (!empty($this->record->$source->$lang)) {
			$this->returnLang = $lang;
			return $this->getValue($this->record->$source->$lang);
			}
		if (!empty($this->record->$source->{$this->cms->defaultLanguage})) {
			$this->returnLang = $this->cms->defaultLanguage;
			return $this->getValue($this->record->$source->{$this->cms->defaultLanguage});
			}
		if (!empty($this->record->$source)) {
			if (!empty($langCode = current((array)$this->record->$source)['language']))
				$this->returnLang = $langCode;
			return $this->getValue(current((array)$this->record->$source));
			}
		}
	
	function getML($source, $langs = [], $sep = '|') {
		$return = [];
		foreach ($langs as $lang) {
			if (!empty($this->record->$source->$lang))
				$return[$lang] = $this->getValue($this->record->$source->$lang);
				else if (!empty($this->record->$source->{$this->cms->defaultLanguage}))
				$return[$lang] = $this->getValue($this->record->$source->{$this->cms->defaultLanguage});
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
		
	function getSolrValues($field) {
		if (!empty($this->solrRecord->$field))
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
		
		$propertyAndValue  = [
			'P31:Q5' => 'person', // instance of: human
			'P569:*' => 'person', // born date
			'P570:*' => 'person', // death date
	
			// places we don't want to 
			'P31:Q2376564' => 'otherPlace', // instance of: interchange 
			'P31:Q728937' => 'otherPlace', // instance of: railway line 
			'P31:Q55488' => 'otherPlace', // instance of: railway station 
			'P31:Q353070' => 'otherPlace', // instance of: junction 
			'P31:Q1248784' => 'otherPlace', // instance of: airport  
			'P31:Q13424400' => 'otherPlace', // instance of: trumpet interchange 
			'P31:Q3146899' => 'otherPlace', // instance of: diocese of the Catholic Church
			
			'P31:Q43229' => 'corporate', // instance of: organization
			'P31:Q79913' => 'corporate', // instance of: non-governmental organization
			'P31:Q163740' => 'corporate', // instance of: nonprofit organization
			'P31:Q178790' => 'corporate', // instance of: labor union
			'P31:Q7210356' => 'corporate', // instance of: political organization
			'P31:Q48204' => 'corporate', // instance of: voluntary association
			'P31:Q1194093' => 'corporate', // instance of: international non-governmental organization
			'P31:Q3152824' => 'corporate', // instance of: cultural institution
			'P31:Q13406660' => 'corporate', // instance of: civil rights movement
			'P31:Q748019' => 'corporate', // instance of: scientific society
			'P31:Q3918' => 'corporate', // instance of: university 
			'P31:Q902104' => 'corporate', // instance of: private university 
			'P31:Q22806' => 'corporate', // instance of: national library
			'P31:Q28564' => 'corporate', // instance of: public library
			'P31:Q1438040' => 'corporate', // instance of: research library
			'P31:Q7075' => 'corporate', // instance of: library
			'P31:Q2085381' => 'corporate', // instance of: publisher
			'P31:Q164950' => 'corporate', // instance of: dynasty
			'P31:Q37002670' => 'corporate', // instance of: unicameral legislature
			'P31:Q215380' => 'corporate', // instance of: musical group 		(do we want them here?)
			'P31:Q105390172' => 'corporate', // instance of: Roman Catholic metropolitan archdiocese
			'P31:Q33506' => 'corporate', // instance of: museum 
			'P31:Q207694' => 'corporate', // instance of: art museum 
			'P31:Q192283' => 'corporate', // instance of: news agency
			# 'P31:Q11812394' => 'corporate', // instance of: theatre company
			'P31:Q2069494' => 'corporate', // instance of: steel mill 
			'P31:Q25212275' => 'corporate', // instance of: Regional Court
			
			
			'P31:Q41298' => 'magazine', // instance of: magazine
			'P31:Q1002697' => 'magazine', // instance of: periodical
			'P31:Q1616075' => 'magazine', // instance of: tv station 
			'P31:Q15265344' => 'magazine', // instance of: broadcaster
			'P31:Q16024164' => 'magazine', // instance of: medical journal
			'P7363:*' => 'magazine', // has issn
	
			'P31:Q1656682' => 'event',  // instance of: event 
			'P31:Q378427' => 'event',  // instance of: literary award !! important
			'P31:Q21573747' => 'event',  // instance of: historical reenactment in Spain
			'P31:Q16543246' => 'event',  // instance of: literary competition  !! important 
			'P31:Q132241' => 'event',  // instance of: festival 
			'P31:Q1190554' => 'event',  // instance of: occurrence  
			'P31:Q182683' => 'event',  // instance of: biennale   
			'P31:Q220505' => 'event',  // instance of: film festival   
			'P279:Q4801521' => 'event',  // subclass of: arts festival
			'P2517:*' => 'event',  // category for recipients of this award
			'P2257:*' => 'event',  // frequecy
			
			
			'P31:Q98929991' => 'place',  // instance of: place
			'P31:Q2221906' => 'place',  // instance of: geographic location
			'P31:Q7930989' => 'place',  // instance of: city or town
			'P31:Q515' => 'place',  // instance of: city
			'P31:Q1549591' => 'place',  // instance of: big city
			'P31:Q6256' => 'place',  // instance of: country
			'P31:Q123705' => 'place',  // instance of: neighborhood 
			'P31:Q3257686' => 'place',  // instance of: locality  
			
			'P31:Q3624078' => 'place',  // instance of: sovereign state
			'P31:Q150093' => 'place',  // instance of: voivodeship of Poland
			'P31:Q56061' => 'place',  // instance of: administrative territorial entity
			'P31:Q3024240' => 'place',  // instance of: historical country
			
			'P31:Q47461344' => 'biblio',  // instance of: written work
			
			'P625:*' => 'maybePlace',  // coordinates (must be on the end, not olny places has coordinates)
			];
		
		
		foreach ($propertyAndValue as $checkKey => $recType) {
			$test = explode(':',$checkKey);
			$property = $test[0];
			$val = $test[1];
			if (!empty($this->record->claims->$property)) {
				if ($val == '*') 
					return $recType;
				
				foreach ($this->record->claims->$property as $part) { // instance of 
					if (!empty($part->mainsnak->datavalue->value->id)) {
						$id = $part->mainsnak->datavalue->value->id;
						if ($id == $val) 
							return $recType;
						}
					}
				}
			}

		return 'unknown';
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
	
	function getYear($claim) {
		if (!empty($this->record->claims->$claim[0]->mainsnak->datavalue->value->time)) {
			$sd = $this->record->claims->$claim[0]->mainsnak->datavalue->value->time;
			if (substr($sd,0,1) == '-')
				$bc = '-';
				else 
				$bc = '';
			$d = str_replace(['-', '.', '+'], '', $sd);
			$year = $bc.substr($d,0,4);
			return $year;
			}
		return null;
		}	
	
	function getValOrId($claim) {
		if (!empty($this->record->claims->$claim)) {
			foreach ($this->record->claims->$claim as $val) {
				if (!empty($val->mainsnak->datavalue->value->time))
					$res[] = $val->mainsnak->datavalue->value->time;
					elseif (!empty($val->mainsnak->datavalue->type)) {
					switch ($val->mainsnak->datavalue->type) {
						case 'string' :	$res[] = $val->mainsnak->datavalue->value; break;
						case 'wikibase-entityid' : 	$res[] = $val->mainsnak->datavalue->value->id; break;
						default : $res[] = "type: ".$val->mainsnak->datavalue->type;
						}
					}
				}
			return $res ?? null;
			}
		return null;
		}
	
	
	#######################################################################################################################

	
	function getHistoricalCityName($time) {
		/*
		Jeśli istnieje $histPlace->qualifiers->P2241 (wartość odrzucona)
		datavalue->value->id = identyfikator ID powodu odrzucenia 
		
		
		if ($histPlace->rank == "preferred") - czasami pomoże wybrać najlepszy
		*/
		
		
		if (!empty($time) && is_string($time)) {
			$time = strtotime($time);
			$res = new stdclass;
			
			if (!empty($this->record->claims->P1448)) {
				
				foreach ($this->record->claims->P1448 as $histPlace) {
					$data_od = -219951936000; // B.C. 5000-01-01
					$data_do = $time;
					if (empty($histPlace->qualifiers->P2241) && empty($histPlace->qualifiers->P3831)) {
						if (!empty($histPlace->qualifiers->P580[0]->datavalue->value->time))
							$data_od = strtotime($histPlace->qualifiers->P580[0]->datavalue->value->time);
						if (!empty($histPlace->qualifiers->P582[0]->datavalue->value->time))
							$data_do = strtotime($histPlace->qualifiers->P582[0]->datavalue->value->time);
							
						if (($time>=$data_od)&($time<=$data_do)) {
							
							$res->name = $histPlace->mainsnak->datavalue->value->text;
							$res->langcode = $histPlace->mainsnak->datavalue->value->language;
							
							# echo "daty: $data_od, <b>$time</b>, $data_do : $res->name ($res->langcode)<Br/>";
							#if ($histPlace->rank == "preferred")
								return $res;
							} 
						}
					}
				}
			if (empty($res->name)) {
				$res->name = $this->get('labels');
				$res->langcode = $this->cms->userLang;
				}
			return $res;
			} else {
			return (object) ['name'=>$this->get('labels')];	
			}
		}
	
	function getHistoricalCountries() {
		$Tres = [];
		$res = new stdclass;
		$res->name = $this->get('labels');
		$res->langcode = $this->cms->userLang;
		
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
		$res->langcode = $this->cms->userLang;
		
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
		$res->langcode = $this->cms->userLang;
		
		if (!empty($this->record->claims->P1448)) {
			
			foreach ($this->record->claims->P1448 as $histPlace) {
				$res = new stdclass;
				
				$data_od = '-9999-00-00T00:00:00Z';
				$data_do = '+'.date("Y-m-d").'T00:00:00Z';
				if (!empty($histPlace->qualifiers->P580[0]->datavalue->value->time))
					$data_od = $histPlace->qualifiers->P580[0]->datavalue->value->time;
				if (!empty($histPlace->qualifiers->P582[0]->datavalue->value->time))
					$data_do = $histPlace->qualifiers->P582[0]->datavalue->value->time;
				$res->dateFrom = $data_od;
				$res->dateTo = $data_do;
				if (!empty($histPlace->qualifiers->P2241))
					$deprecated = true;
					else 
					$deprecated = false;	
				
				$name = (object)[
							'language'=> $histPlace->mainsnak->datavalue->value->language, 
							'value' => $histPlace->mainsnak->datavalue->value->text,
							'dateFrom' => $data_od,
							'deprecated' => $deprecated
							];
				#$res->langcode = $histPlace->mainsnak->datavalue->value->language;
				if (empty($Tres[$data_do])) {
					$res->names[$histPlace->mainsnak->datavalue->value->language] = $name;
					$Tres[$data_do] = $res;
					} else 
					$Tres[$data_do]->names[$histPlace->mainsnak->datavalue->value->language] = $name;	
			
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
		$lang = $this->cms->userLang.'wiki';
		if (!empty($this->record->sitelinks->$lang->url))
			return $this->record->sitelinks->$lang->url;
		$lang = $this->cms->defaultLanguage.'wiki';
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
	
	function removeArrayKeys($array) {
		if (!empty($array)) {
			$array = (array)$array;
			return array_values(array_unique($this->flattenArray($array)));
			}
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
	
	}

?>
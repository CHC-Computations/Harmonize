<?php 


class wikiSearcher {
	private $settings;
	private $cms;
	private $outPutFolder = './import/outputfiles/';
	private $dataBufferFile = './import/outputfiles/buffer/wikiBuffer.json';
	
	private $apiSearchConstStr = 'w/api.php?action=query&format=json&list=search&srlimit=6&srprop=size&formatversion=2&';
	
	private $internalIdsList = ['viaf', 'czenas', 'yso'];
	
	public $buffer;
	public $client;
	
	
	function __construct(object $cms) {
		$this->cms = $cms;
		$this->client = $this->client ?? $this->cms->solr->createSolrClient('wiki');
		$this->settings = $this->settings ?? $this->cms->settings;
		
		#$this->buffer = json_decode(@file_get_contents($this->dataBufferFile), true);
		$this->buffer = [];
		}
	
	function register(string $name, object $var) {
		$this->$name = $var;
		}
	
	function getIdByOtherId($ids, $type = null) {
		if (!empty($ids)) {
			$ids = (array)$ids;
			// in memory buffer?
			foreach ($ids as $field=>$value) {
				if (!is_numeric($field) && !empty($this->buffer->ids->$field[$value]))
					return $this->buffer['eids_'.$field.':'.$value]; // The answer "not found" is a possible and GOOD answer (save time and don't look again! today)
				}
			// in oursolr ?
			foreach ($this->cms->configJson->wikidata->ids as $field=>$property) {
				if (!empty($ids[$field])) {
					$wikiq = $this->internalQuery('eids_'.$field, $ids[$field], $type);
					return $wikiq;
					}
				}
			
			if (isset($this->configJson->import->useExternalSearch->wikidata->id) && $this->configJson->import->useExternalSearch->wikidata->id) {
				foreach ($this->cms->configJson->wikidata->ids as $field=>$property) {
					if (!empty($lookfor['ids'][$field])) {
						$wikiq = $this->externalQueryIds($property, $ids[$field]);
						return $wikiq;
						}
					}
				}	
			}
		}
	
	function getIdByLabel($type, $lookfor, $conditions = []) {
		
		$sstring = $basesstring = $this->cms->helper->clearStr($lookfor);
		if (!empty($sstring)) {
			$Tstrings = explode(' ', $sstring);
			
			switch ($type) {
				case 'person':
					$searchKey = 
							$type.'|'.
							$sstring.'|'.
							$conditions['year_born'] ?? ''.'|'.
							$conditions['year_death'] ?? ''.'|';
					if (!empty($conditions['year_born']))
						$Tstrings[] = $conditions['year_born'];
					if (!empty($conditions['year_death']))
						$Tstrings[] = $conditions['year_death'];
					break;
				default: 
					$searchKey = $type.'|'.$sstring;
					break;
				}
			
			// in memory buffer?
			if (!empty($this->buffer[$searchKey]))
				return $this->buffer[$searchKey];
			
			// in oursolr ?
			
			if (!empty($Tstrings)) {
				$query = new SolrQuery();
				$query->setStart(0);
				$query->setRows(50);
				$query	->addField('id')
						->addField('labels')
						->addField('aliases')
						->addField('labels_search')
						->addField('aliases_search')
						->addField('descriptions_search')
						->addField('descriptions')
						->addField('eids_viaf')
						->addField('eids_yso')
						->addField('eids_czenas')
						->addField('eids_any');
				$query->addSortField('record_length', 1);		

				########################################################################################################################################################################################################################
				##  
				##  local search by name
				##  this is how the query should look: record_type:"person" AND (labels_search:"zeromski" AND  labels_search:"stefan") OR (aliases_search:"zeromski" AND aliases_search:"stefan")
				##  
				########################################################################################################################################################################################################################
				$queryString = implode(' AND ', $Tstrings).' AND record_type:"'.$type.'"'; 
				$query->setQuery($queryString);
				
				$queryResponse = $this->client->query($query);
				$this->response = $queryResponse->getResponse();
				$i = 0;
				if (!empty($this->getRecFound()) && !empty($this->response->response->docs)) {
					$toMatch = (object)['name' => $basesstring];
					$matchLevel = $this->cms->configJson->biblio->import->matchLevel;
					$resTable = [];
					
					foreach ($this->response->response->docs as $doc) {
						$matchOK = 0;
						$testedId = $doc->id;
						$this->cms->wikiData->loadRecord($testedId);
						
						$searchArray = array_merge($this->cms->wikiData->getSolrValues('labels_search') ?? [], $this->cms->wikiData->getSolrValues('aliases_search') ?? []);
						$currentMatchLevel = $this->cms->helper->matchLevelStr($toMatch, $searchArray);
						if ($currentMatchLevel >= $matchLevel) {
							$resTable[$testedId] = $currentMatchLevel;
							file_put_contents($this->outPutFolder.'wikiSolrQueries.'.$type.'.success.csv', "$testedId;$type;$lookfor;$queryString;\n", FILE_APPEND);
							} else {
							file_put_contents($this->outPutFolder.'wikiSolrQueries.'.$type.'.rejected.csv', "$testedId;$type;$lookfor;$queryString;\n", FILE_APPEND);	
							}
						if (!empty($resTable)) {
							arsort($resTable);
							$testedId = key($resTable);
							file_put_contents($this->outPutFolder.'wikiSolrQueries.'.$type.'.chosen.csv', "$testedId;$type;$lookfor;$queryString;\n", FILE_APPEND);
							$this->buffer[$searchKey] = $testedId;
							return $testedId;
							}
						/*
						if (!empty($conditions['year_born']) && ($conditions['year_born'] == $this->cms->wikiData->getYear('P569'))) {
							$matchOK = 1;
							}
						*/	
						
						}
					} else 
					file_put_contents($this->outPutFolder.'wikiSolrQueries.'.$type.'.failure.csv', "$type;$lookfor;$queryString;\n", FILE_APPEND);
				
				$this->buffer[$searchKey] = 'not found';
				file_put_contents($this->dataBufferFile, json_encode($this->buffer) );
				
				}
			if (isset($this->configJson->import->useExternalSearch->wikidata->label) && $this->configJson->import->useExternalSearch->wikidata->label) {
				if (in_array($type, ['magazine', 'event', 'place']))
					if (!empty($res = $this->externalQuery($type, $sstring))) {
						$this->buffer[$searchKey] = $res;
						return $this->buffer[$searchKey];
						}
				}
			}
		}
	
	function externalQuery($type, $sstring) {
		$res = json_decode(@file_get_contents($F = $this->cms->configJson->wikidata->host.$this->apiSearchConstStr.'srsearch='.$sstring));
		$hit = false;
		
		if (!empty($res->batchcomplete) && ($res->query->searchinfo->totalhits>0)) {
			foreach ($res->query->search as $key=>$result) {
				$this->cms->wikiData->loadRecord($result->title); 
				if ($this->cms->wikiData->recType() == $type) {
					file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.success.csv', "$type;$sstring;".$this->cms->wikiData->getId()."\n", FILE_APPEND);
					$this->hasWikiRec = clone $this->cms->wikiData;
					return $this->cms->wikiData->getId();
					} else {
					file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.rejected.csv', "$type;$sstring;{$this->cms->wikiData->recType()};{$this->cms->wikiData->getId()}\n", FILE_APPEND);	
					}
				}
			} 
		file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.failure.q.csv', "$F;\n", FILE_APPEND);
		}	
	
	
	
	function query($type, $lookfor, $conditions = []) {
		
		}
	
	function hasWikiRec() {
		return $this->hasWikiRec ?? null;
		}
	
	
	function internalQuery($field, $value, $type = null) {
		$query = new SolrQuery();
		$query->setStart(0);
		$query->setRows(5);
		$query	->addField('id')
				->addField('record_type')
				->addField($field);
		
		$skey = $field.':'.$value;
		if (!empty($type)) 
			$query->setQuery('record_type:'.$type.' AND '.$field.':"'.$value.'"');	
			else 
			$query->setQuery($field.':'.$value);
		$queryResponse = $this->client->query($query);
		$this->response = $queryResponse->getResponse();
		if (!empty($this->getRecFound()) && ($this->getRecFound() == 1)) {
			
			#$this->cms->wikiData->loadRecord($this->getFirstId());
			$this->buffer[$skey] = $this->wikiq = $this->getFirstId();
			return $this->buffer[$skey];
			}
		}
	
	function externalQueryIds($property, $value) {
		$res = json_decode(@file_get_contents($F = $this->cms->wikidata->host.$this->apiSearchConstStr.'srsearch='.$value));
		$hit = false;
		
		if (!empty($res->batchcomplete) && ($res->query->searchinfo->totalhits>0)) {
			foreach ($res->query->search as $key=>$result) {
				$this->cms->wikiData->loadRecord($result->title); 
				if ($this->cms->wikiData->getStrVal($property) == $value) {
					file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.success.csv', "$property;$value;".$this->cms->wikiData->getId()."\n", FILE_APPEND);
					$this->hasWikiRec = clone $this->cms->wikiData;
					return $this->cms->wikiData->getId();
					} else {
					file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.rejected.csv', "$property;$value;{$this->cms->wikiData->recType()};{$this->cms->wikiData->getId()}\n", FILE_APPEND);	
					}
				}
			} 
		file_put_contents($this->outPutFolder.'wikiAPIQueries.'.$type.'.failure.q.csv', "$F;\n", FILE_APPEND);
		}	
		
	
		
		
		
	
	function getRecFound() {
		return $this->response->response->numFound ?? 0;
		}
		
	function getFirstId() {
		if (!empty($this->response->response->docs)) {
			$doc = current((array)$this->response->response->docs);
			return $doc->id ?? null;
			}
		}	
	
	}

?> 
<?php 


class viafSearcher {
	private $settings;
	private $cms;
	public $client;
	public $response;
	public $buffer;
	public $outPutFolder = './import/outputfiles/';
	public $dataBufferFile = './import/outputfiles/buffer/viafBuffer.json';
	public $dataOrigin;
	
	private $apiHost = 'https://viaf.org/viaf/';
	private $apiFormat = '/viaf.xml';
	private $viafDropStr  = 'ns1:';
	
	
	function __construct(object $cms) {
		$this->cms = $cms;
		$this->settings = $this->cms->configJson->settings;
		$this->client = $this->cms->solr->createSolrClient('viaf');
		$this->buffer = json_decode(@file_get_contents($this->dataBufferFile), true);
		$this->buffer = $this->buffer ?? new stdClass();
		}
	
	function register($name, $var) {
		@$this->$name = $var;
		}
	
	function getIdByOtherId($id, $typeOfId = '') {
		if (!empty($id)) {
			if (!empty($this->buffer->$id))
				return $this->buffer->$id;
				else {
				$query = new SolrQuery();
				$query->setStart(0);
				$query->setRows(5);
				$query	->addField('viaf')
						->addField('wikiq');

				$searchField = 'eid_any';
				if (!empty($typeOfId))
					if ($typeOfId == 'viaf')
						$searchField = 'viaf';
						else 
						$searchField = 'eid_'.$typeOfId;
					else 
					$searchField = 'eid_any';
				$search = $searchField.':"'.urlencode ($this->cms->helper->clearStr($id)).'"';
				
				// search in viaf buffer
				$query->setQuery($search);
				$queryResponse = $this->client->query($query);
				$this->response = $queryResponse->getResponse();
				if (!empty($this->getRecFound()) && ($this->getRecFound() == 1)) {
					$response = $this->getFirstId();
					file_put_contents($this->cms->outPutFolder.'viafQueries.ids.csv', "$typeOfId;$id;$search;".print_r($response,1)."\n", FILE_APPEND);
					
					$this->buffer->$id = $response;
					return $response;
					}
				// search in viaf API 
				}
			}
		file_put_contents($this->cms->outPutFolder.'viafQueries.ids.failed.csv', "$id;$typeOfId;\n", FILE_APPEND);
					
		return false;
		}
	
	function getIdByLabel($label, $type = '') {
		if (!empty($label)) {
			$sstring = $this->cms->helper->clearStr($label);
			$skey = str_replace(' ', '_', $sstring);
			if (!empty($this->buffer->$skey))
				return $this->buffer->$skey;
				else {
				$query = new SolrQuery();
				$query->setStart(0);
				$query->setRows(5);
				$query	->addField('viaf')
						->addField('wikiq');

				$searchField = 'search_labels';
				$searchQuery = $searchField.':"'.$sstring.'"';
				
				// search in viaf buffer
				$query->setQuery($searchQuery);
				$queryResponse = $this->client->query($query);
				$this->response = $queryResponse->getResponse();
				if (!empty($this->getRecFound()) && ($this->getRecFound() == 1)) {
					$response = $this->getFirstId();
					file_put_contents($this->cms->outPutFolder.'viafQueries.labels.csv', "$type;$label;$searchQuery;".print_r($response,1)."\n", FILE_APPEND);
					
					$this->buffer->$skey = $response;
					return $response;
					}
				// search in viaf API 
				}
			}
		file_put_contents($this->cms->outPutFolder.'viafQueries.labels.failed.csv', "$label;$type;\n", FILE_APPEND);
					
		return false;
		}
		

	function getRecFound() {
		return $this->response->response->numFound ?? 0;
		}
		
	function getFirstId() {
		if (!empty($this->response->response->docs)) {
			$doc = current((array)$this->response->response->docs);
			if (!empty($doc->wikiq))
				$wikiQ = current($doc->wikiq);
				
			return [
				'viaf' => $doc->viaf ?? null,
				'wikiQ' => $wikiQ ?? null
				];
			}
		}

	
	function getLabels($id = '') {
		if (!empty($id)) {
			$query = new SolrQuery();
			$query->setStart(0);
			$query->setRows(5);
			$query	->addField('viaf')
					->addField('wikiq')
					->addField('labels')
					->addField('search_labels');

			if (stristr($id, ':'))
				$search = $id;
				else 
				$search = 'viaf:'.$id;
			$query->setQuery($search);
			$queryResponse = $this->client->query($query);
			$this->response = $queryResponse->getResponse();
			if (!empty($this->getRecFound()) && ($this->getRecFound() == 1)) {
				if (!empty($this->response->response->docs)) {
					$doc = current((array)$this->response->response->docs);
					if (!empty($doc->labels)) {
						$this->dataOrigin = 'local';
						$tRes = [];
						foreach ($doc->labels as $label) {
							$tmp = explode('|', $label);
							$tRes[] = (object)['label' => $tmp[0], 'count' => $tmp[1]];
							}
						return $tRes;
						}
					}
				}
			// search in viaf API 
			$viafSourceFile = $this->apiHost.$id.$this->apiFormat;
			$viafSourceFileContent = str_replace($this->viafDropStr, '', @file_get_contents($viafSourceFile));
			$record = simplexml_load_string($viafSourceFileContent);
			$lp = 0;
			if (!empty($record->mainHeadings->data)) {
				foreach ($record->mainHeadings->data as $option) {
					$count = count($option->sources->sid);
					$lp++;
					$Tlabels[] = (object)[
						'label' => current((array)$option->text),
						'count' => $count
						];
					}
					
				$this->dataOrigin = 'external';
				return $Tlabels;
				}
			}
		}
	
	function getAllIds($id = '') {
		if (!empty($id)) {
			$query = new SolrQuery();
			$query->setStart(0);
			$query->setRows(5);
			$query	->addField('viaf')
					->addField('wikiq')
					->addField('eid_any');

			if (stristr($id, ':'))
				$search = $id;
				else 
				$search = 'viaf:'.$id;
			$query->setQuery($search);
			$queryResponse = $this->client->query($query);
			$this->response = $queryResponse->getResponse();
			if (!empty($this->getRecFound()) && ($this->getRecFound() == 1)) {
				if (!empty($this->response->response->docs)) {
					$doc = current((array)$this->response->response->docs);
					if (!empty($doc->eid_any)) {
						return array_merge($doc->eid_any, [$id]);
						}
					}
				}
			}
		return [];
		}

	
	function saveIdsToSolr($viaf, $row, $withLabels = false) {
		if (!empty($row)) {
			
			if (empty($this->couter)) {
				$this->couter = 0;
				}
			$doc = new SolrInputDocument();
			$this->couter++;
			$doc->addField('viaf', $viaf);
			
			if (array_key_exists('WKP', $row)) {
				foreach ($row['WKP'] as $wikiq)
					$doc->addField('wikiq', $wikiq);
				} else {
				$wikiq = null;
				}
			# file_put_contents($this->cms->outPutFolder.'viafmatchesPrints.txt', "***************************\n$viaf".print_r($row,1)."\n", FILE_APPEND);
			foreach ($row as $eidkey => $Teid) {
				foreach ($Teid as $eid) {
					$doc->addField('eid_'.$eidkey, $eid);
					$doc->addField('eid_any', $eid);
					}
				}
			if ($withLabels)
				$doc->addField('labels', json_encode($this->getLabels($viaf), JSON_INVALID_UTF8_SUBSTITUTE));
			$updateResponse = $this->client->addDocument($doc);
			#if ($updateResponse->responseHeader->status == 0) return $updateResponse->responseHeader->QTime;
			}
		}
	
	function saveIdsToPSQL($viaf, $row, $imp) {
		if (!empty($row)) {
			if (array_key_exists('WKP', $row)) {
				$wikiq = substr(current($row['WKP']),1);
				} else {
				$wikiq = null;
				}
			# file_put_contents($this->cms->outPutFolder.'viafmatchesPrints.txt', "***************************\n$viaf".print_r($row,1)."\n", FILE_APPEND);
			foreach ($row as $eidkey => $Teid) {
				foreach ($Teid as $eid) {
					$this->imp->psql->query($Q = "INSERT INTO viaf_wiki_ids (viaf, wikiq, eidkey, eid, lastcheck) VALUES ({$imp->psql->isNull($viaf)}, {$imp->psql->isNull($wikiq)}, {$imp->psql->isNull($eidkey)}, {$imp->psql->isNull($eid)}, now());");
					# file_put_contents($this->cms->outPutFolder.'viafmatchesSQL.txt', $Q."\n", FILE_APPEND);
					}
				}
			}
		}


	}

?>
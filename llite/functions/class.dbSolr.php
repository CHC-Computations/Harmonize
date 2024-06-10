<?php


class dbSolr {

	public function __construct(object $cms) {
		$this->cms = $cms;
		$this->config = $cms->config;
		$this->settings = $cms->settings;
		}
	
	public function register(string $key, object $value) {
		$this->$key = $value;
		}
	
	function createSolrClient(string $core) {
		return new SolrClient([
			'hostname' 	=> $this->cms->settings->solr->hostname,
			'port'     	=> $this->cms->settings->solr->port,
			'path' 		=> 'solr/'.$this->cms->settings->solr->cores->$core
			]);
		}
		
		
	function curlSaveData(string $core, object $data) {
		$this->curlSaveStatus = false;
		$solrPath = $this->cms->settings->solr->host.':'.$this->cms->settings->solr->port.'/solr/'.$this->settings->solr->cores->$core.'/update';
		$postdata = json_encode($data, JSON_INVALID_UTF8_SUBSTITUTE);
								
		$ch = curl_init($solrPath); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, '['.$postdata.']');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		$result = curl_exec($ch);
		$resDecoded = json_decode($result);
		if ($resDecoded->responseHeader->status == 0) {
			file_get_contents($solrPath.'?commit=true');
			$this->curlSaveStatus = true;
			} else {
			$this->curlSaveStatus = false;
			}
		curl_close($ch);
		return $this->curlSaveStatus;
		}		
	
	
	
	public function querySelect($core, $query) {
		$core = $this->getOption('coresPrefix').$core;
		
		$TQ = [];
		#echo "querySolr<pre>".print_r($query,1).'</pre>';
		foreach ($query as $k=>$v) {
			if (!empty($v['field']) && !empty($v['value']))
				$TQ[] = $v['field'] .'='.urlencode($v['value']);
			}
		
		$path = $this->options->host.':'.$this->options->port."/solr/".$core."/select?".implode('&',$TQ);
		$this->alert[] = "<a href='$path' target=_blank>solr query</a>"; 
		
		$er = error_reporting();
		error_reporting(0);
		$file = @file_get_contents($path);
		error_reporting($er);
		
		#echo implode('<br/>', $this->alert);
		#echo '<pre>'.print_r($file,1).'</pre>';
		
		$this->responseFile = $file;
		
		if ($file) {
			return json_decode( $file );
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}

	function clearStr( $str, $replace = " " ){
		if (!empty($str)) {
			$oldStr = $str;
			setlocale(LC_ALL, 'pl_PL.UTF8');
			$str = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			$charsArr = array( '^', "'", '"', '`', '~');
			$str = str_replace( $charsArr, '', $str );
			$return = trim(preg_replace('# +#',' ',preg_replace('/[^a-zA-Z0-9\s]/','',strtolower($str))));
			
			return str_replace(' ', $replace, $return);
			}
        }
		
	
	public function getQuery($core, $query) {
		$json = $this->querySelect($core, $query);
		if (!empty($json->response)) {
			#echo "<pre>".print_r($json,1)."</pre>";
			
			foreach ($json->response->docs as $k=>$v) {
				$json->response->docs[$k]->lp = $k+$json->response->start+1;
				/*
				if (!empty($v->title))
					$json->response->docs[$k]->title = $this->removeLastSlash($v->title);
				if (!empty($v->title_sub))
					$json->response->docs[$k]->title_sub = $this->removeLastSlash($v->title_sub);
				*/
				}
			
			$this->response = $json->response;
			$this->responseHeader = $json->responseHeader;
			if (!empty($json->facet_counts))
				$this->facet_counts = $json->facet_counts;
			return 1;
			} else 
			return 0;
		}
	
		
	public function firstResultNo() {
		if (!empty($this->response))
			return 1+$this->response->start;
		}
	
	public function lastResultNo() {
		if (!empty($this->response))
			return count($this->response->docs)+$this->response->start;
		}
	
	public function totalResults() {
		if (!empty($this->response)) {
			return $this->response->numFound;
			} else 
			return null;
		}
	
	public function facetsList() {
		if (!empty($this->facet_counts->facet_fields)) {
			foreach ($this->facet_counts->facet_fields as $k=>$v) {
				foreach ($v as $k2=>$v2)
					if ($k2 % 2 == 0) {
						$key = $v2;
						} else 
						$Tres[$k][$key] = $v2;
				}
			$this->facets = new stdClass;
			if (!empty($Tres)) {
				$this->facets->list = $Tres;	
				return $Tres;
				}
			}  			
		return [];
		}
	
	public function resultsList() {
		if (!empty($this->response)) {
			return $this->response->docs; 
			} else 
			return null;
		}
	
	public function idList() {
		if (!empty($this->response->docs) && is_array($this->response->docs)) {
			foreach ($this->response->docs as $rec) 
				$Ids[$rec->id] = $rec->id;
			return $Ids;
			} else 
			return [];
		}
	
	public function getRecord($core, $id) {
		$query[]=[ 
				'field' => 'q',
				'value' => 'id:'.$id
				];
		$result = $this->querySelect($core, $query);
		if (!empty($result->response->docs[0]))
			return $result->response->docs[0];
			else 
			return null;
		}

	
	public function getFacets($core, $facets = array(), $options = []) {
		$Tres = array();
		if (is_array($facets)) {
			$query['q']=[
				'field' => 'q',
				'value' => '*:*'
				];
			$query[]=[ 
				'field' => 'facet',
				'value' => 'true'
				];
			$query[]=[ 
				'field' => 'rows',
				'value' => '0'
				];
			$query[]=[ 
				'field' => 'facet.mincount',
				'value' => '1'
				];
			$query['limit']=[
				'field' => 'facet.limit',
				'value' => $this->cms->settings->facets->defaults->facetLimit
				];
		
			foreach ($facets as $facet) {
				$query[]=[ 
					'field' => 'facet.field',
					'value' => $facet
					];
				}
			
			if (count($options)>0) {
				$query = array_merge($query, $options);
				}
		
			
			$json = $this->querySelect($core, $query);
			if (!empty($json->facets))
				$this->facets = $json->facets;
				else 
				$this->facets = new stdclass;
			if (!empty($json->facet_counts->facet_pivot))
				$this->facet_pivot = $json->facet_counts->facet_pivot;
				else 
				$this->facet_pivot = new stdclass;
			
			#echo "getFacets<pre>".print_r($query,1)."</pre>";
			#echo "getFacets:options:<pre>".print_r($options,1)."</pre>";
			
			if (!empty($json->facet_counts->facet_fields)) {
				$this->response = $json->response;
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				$this->facets->list = $Tres;	
				return $Tres;	
				} else 			
				return [];
			}
		return [];
		}
		
	public function facetsCountCode($currFacet) {
		return [ 
				'field' => 'json.facet', 
				'value' => '{'.$currFacet.'_x:"unique('.$currFacet.')"}'
				];
		}	
		 
		
	public function getFacetsCount($currFacet) {
		$string = $currFacet.'_x';
		if (!empty($this->facets->$string))
				return $this->facets->$string;
		}
		

	public function getSolrVersion() {
		return solr_get_version();
		}
 
 
	}

?>
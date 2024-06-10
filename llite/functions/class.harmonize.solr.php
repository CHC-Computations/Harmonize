<?php


class harmonizeSolr {

	public function __construct($config = []) {
		$required_fields = ['hostname', 'port', 'path'];
		foreach ($required_fields as $field) 
			if (empty($config[$field])) die('solr '.$field.' required');
		
		$this->client = new SolrClient($config);
		$this->query = $query = new SolrQuery();
		}
	
	public function register($key, $value) {
		$this->$key = $value;
		}
	
	public function querySelect($query) {
		
		$TQ = [];
		#echo "querySolr<pre>".print_r($query,1).'</pre>';
		foreach ($query as $k=>$v) {
			if (!empty($v['field']) && !empty($v['value']))
				$TQ[] = $v['field'] .'='.urlencode($v['value']);
			}
		
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$core."/select?".implode('&',$TQ);
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
	
	public function searchFieldToIndex($type, $word = '') {
		switch ($type) {
			case 'inkedresource' : return 'info_resource_str_mv:'.$word; // related_document_txt_mv	
			case 'articleresource' : return 'article_resource_txt_mv:'.$word;
			case 'yearstr' : return 'publishDate:'.$word;
			case 'subject' : return 'topic_search_str_mv:'.$word;
			case 'allfields' : 
			case 'AllFields' : 
				return $word;
				#return 'author:'.$word.'^1 OR title:'.$word.'^0.9 OR topic:'.$word.'^0.6 OR spellingShingle:'.$word.'^0.5'; 
				
			default : return strtolower($type).':'.$word;
			}
		}
	
	
	public function advandedSearch($jsonQuery) {
		if (!empty($jsonQuery)) 
			$jsonQuery = json_decode($jsonQuery);
		if (is_object($jsonQuery)) {
			$queryValue = '';
			$operators = $jsonQuery->operator;
			unset ($jsonQuery->operator);
			if (empty($operators->g)) $operators->g = 'or';
			
			if (!empty($jsonQuery)) {
				foreach ($jsonQuery as $gk=>$query) {
					$qstr = [];
					foreach ($query as $k=>$v) {
						switch ($v->meth) { 
							case 'contains' :
									$qstr[] = $this->searchFieldToIndex($v->type, '"'.$v->lookfor.'"');
									break;
							case 'is' :
									$qstr[] = $this->searchFieldToIndex($v->type, '"'.$v->lookfor.'"');
									break;
							case 'isnot' :
									$qstr[] = 'NOT '.$this->searchFieldToIndex($v->type, '"'.$v->lookfor.'"');
									break;
							case 'iscontains' :
									$qstr[] = 'NOT '.$this->searchFieldToIndex($v->type, $v->lookfor);
									break;
							}		
						}
					if (empty($operators->$gk)) $operators->$gk = 'or';
					$Qstr[] = implode(' '.strtoupper($operators->$gk).' ', $qstr);
					}
				} 
			$queryValue = '('.implode(' '.strtoupper($operators->g).' ', $Qstr).')';
			
			#echo "query<pre>".print_R($jsonQuery,1)."</pre>";
			#echo "operators<pre>".print_R($operators,1)."</pre>";
			#echo "qstr<pre>".print_R($Qstr,1)."</pre>";
			#echo "queryValue<pre>".print_R($queryValue,1)."</pre>";
				
			return $queryValue;
			}
		}


	/*
	function unaccent($string)  { // normalizes (romanization) accented chars
		$oldStr = $string;
		if (strpos($string = htmlentities($string, ENT_QUOTES, 'UTF-8'), '&') !== false) {
			$string = html_entity_decode(preg_replace('~&([a-z]{1,2})(?:acute|cedil|circ|grave|lig|orn|ring|slash|tilde|uml);~i', '$1', $string), ENT_QUOTES, 'UTF-8');
			}
		$string = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $string);	
		#echo "<pre>$oldStr => $string </pre>";	
		return $string;
		}

	function str2url($string, $slug = '-', $extra = null) {
		return strtolower(trim(preg_replace('~[^0-9a-z' . preg_quote($extra, '~') . ']+~i', $slug, $this->unaccent($string)), $slug));
		}
	*/
	
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
		
	public function lookFor($lookfor, $type) {
		// wagi pól przykład: q=(title:mickiewicz)^1 and (spelling:mickiewicz)^0.5
		if (is_array($lookfor))
			$lookfor = current($lookfor);
		if (is_string($lookfor) or empty($lookfor)) {
			$ltype = @strtolower($type);
			if (!empty($lookfor)) {
				/*
				$wordList = explode(' ',trim($this->clearStr($lookfor)));
				foreach ($wordList as $word) {
					$searchString[]=$this->searchFieldToIndex($type, $word);
					}
				*/	
				
				$query=[ 
						'field' => 'q',
						'value' => $this->searchFieldToIndex($ltype, $this->clearStr($lookfor))
						];
				} else 
				$query=[ 
						'field' => 'q',
						'value' => '*:*'
						];
			return $query;
			}
		}
	
	public function getQuery($core, $query) {
		$json = $this->querySelect($core, $query);
		if (!empty($json->response)) {
			#echo "<pre>".print_r($json,1)."</pre>";
			
			foreach ($json->response->docs as $k=>$v) {
				$json->response->docs[$k]->lp = $k+$json->response->start+1;
				if (!empty($v->title))
					$json->response->docs[$k]->title = $this->removeLastSlash($v->title);
				if (!empty($v->title_sub))
					$json->response->docs[$k]->title_sub = $this->removeLastSlash($v->title_sub);
				}
			
			$this->response = $json->response;
			$this->responseHeader = $json->responseHeader;
			if (!empty($json->facet_counts))
				$this->facet_counts = $json->facet_counts;
			return 1;
			} else 
			return 0;
		}
	
	public function cleanQuery($core) {
		
		$query[]=[ 
				'field' => 'q',
				'value' => '*:*'
				];
		$query[]=[ 
				'field' => 'facet',
				'value' => 'false'
				];
		$query[]=[ 
				'field' => 'rows',
				'value' => '20'
				];
		$query[]=[ 
				'field' => 'facet.limit',
				'value' => '10'
				];		
		$json = $this->querySelect($core, $query);
		if (!empty($json->response)) {
			#echo "<pre>".print_r($json,1)."</pre>";
			foreach ($json->response->docs as $k=>$v) {
				$json->response->docs[$k]->lp = $k+$json->response->start+1;
				if (!empty($v->title))
					$json->response->docs[$k]->title = $this->removeLastSlash($v->title);
				if (!empty($v->title_sub))
					$json->response->docs[$k]->title_sub = $this->removeLastSlash($v->title_sub);
				}
			$this->response = $json->response;
			$this->responseHeader = $json->responseHeader;
			
			return 1;
			} else 
			return 0;
		}
		
	public function removeLastSlash($t1, $slash = '/') {
		$t2 = '';
		$t1 = (string)$t1;
		$pos = strrpos($t1, $slash);
		
		if (($pos>0)and($pos>=strlen($t1)-3))
			return substr($t1, 0, $pos);
			else 
			return $t1;
		}
		
	public function firstResultNo() {
		#echo "res<pre>".print_r($this->response,1)."</pre>";
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
		#echo '<pre>'.print_r($this->facet_counts,1).'</pre>';
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
	
	public function getRoles($core, $field, $string) {
		$string = $string.'|';
		$query['q']=[
				'field' => 'q',
				'value' => '*:*'
				];
		$query['limit']=[
				'field' => 'facet.limit',
				'value' => 100
				];
		$query['prefix']=[
				'field' => 'facet.prefix',
				'value' => $string
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
				'field' => 'facet.field',
				'value' => $field
				];		
		$json = $this->querySelect($core, $query);
		
		if (!empty($json->facet_counts->facet_fields->$field)) {
			#echo '<pre>'.print_r($json->facet_counts->facet_fields->$field,1).'</pre>';
			
			foreach ($json->facet_counts->facet_fields->$field as $k=>$v) {
				if ($k % 2 == 0) {
					$key = str_replace($string, '', $v);
					} else 
					$Tres[$key] = $v;
				}
			return $Tres;	
			
			}  			
		return [];
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
		
	public function getFullFacetName($id) {
		$searchFields = ['author_facet_s', 'topic_person_str_mv'];
		$id = str_replace('viaf_id', 'viaf/', $id);
		foreach ($searchFields as $facet) {
			$path = $this->options->hostname.':8983/solr/'.$this->options->cores->biblio.'/select?facet.contains='.$id.'&facet.field='.$facet.'&facet.sort=count&facet=true&q.op=OR&q=*%3A*&rows=0';
			
			try {
				$er = error_reporting();
				error_reporting(0);
				$file = file_get_contents($path);
				error_reporting($er);
				
				if ($file) {
					$json =  json_decode( $file );
					#echo "<pre>".print_R($json->facet_counts->facet_fields,1).'</pre>';
					if (!empty($json->facet_counts->facet_fields->$facet))
						return current($json->facet_counts->facet_fields->$facet);
					} else {
					$this->error = "The connection to the Solr has failed";
					return null;
					}
				} 
			catch (Exception $e) {
				return 'Wystąpił błąd:'.  $e->getMessage(). "\n";
				}	
			
			}
			
		return null;
		}	
	


	
	public function getStats($core, $searchStrs, $searchFields, $statFields, $limit = 5) {
		$Tres = array();
		$res = new stdclass;
		
		if (!is_array($searchStrs)) {
				$this->alert[] = 'No search strings array'; 
				return null;
				}
		if (!is_array($searchFields)) {
				$this->alert[] = 'No search fields array'; 
				return null;
				}
		if (!is_array($statFields)) {
				$this->alert[] = 'No stat fields array'; 
				return null;
				}
		
		foreach ($searchFields as $searchField)
			foreach ($searchStrs as $searchStr) 
				$urlqueries[] = '('.$searchField.':"'.$searchStr.'")';
				
		$urlquery = urlencode(implode(' OR ',$urlqueries));
			 
		$facetFields = implode('&facet.field=', $statFields);
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->cores->$core."/select?q=*:*&fq={$urlquery}&facet=true&facet.mincount=1&rows=0&facet.limit={$limit}&facet.field={$facetFields}";
	 
		#echo "$fullName<br/>";
		#echo "$path<br/>";
	 
		$this->alert[] = "<div class='alertTechnicalLink'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = @file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->name = $searchStrs;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			$res->facets = $Tres;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	

	
	public function getPlaceStats($name) {
		$Tres = array();
		$res = new stdclass;
		
		$ulrqout = urlencode('"');
		
		if (is_Array($name)) {
			foreach ($name as $n) {
				$urlqueries[] = '(geographic_facet:"'.$n.'")';
				$urlqueries[] = '(geographic:"'.$n.'")';
				}
			$urlquery = urlencode(implode(' OR ',$urlqueries));
			} else 
			$urlquery = urlencode('(geographic_facet:"'.$name.'")OR(geographic:"'.$name.'")');
		
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->core."/select?q=*:*&fq={$urlquery}&facet=true&facet.mincount=1&rows=0&facet.limit=5&facet.field=info_resource_str_mv&facet.field=format_major&facet.field=article_resource_str_mv&facet.field=author_facet&facet.field=author_facet_c&facet.field=genre_major&facet.field=subject_person_str_mv&facet.field=container_title_2&facet.field=language&facet.field=era_facet&facet.field=geographic_facet";
	 
		#echo "$fullName<br/>";
		#echo "$path<br/>";
	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->name = $name;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			$res->facets = $Tres;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	

	public function getTopicStats($names) {
		$Tres = array();
		$res = new stdclass;
		
		$ulrqout = urlencode('"');
		
		foreach ($names as $name)
			$urlqueries[] = '(subjects_str_mv:"'.$name.'")';
		
		$urlquery = urlencode(implode(' OR ',$urlqueries));
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->core."/select?q=*:*&fq={$urlquery}&facet=true&facet.mincount=1&rows=0&facet.limit=5&facet.field=info_resource_str_mv&facet.field=format_major&facet.field=article_resource_str_mv&facet.field=author_facet_s&facet.field=author_facet_c&facet.field=genre_major&facet.field=subject_person_str_mv&facet.field=container_title_2&facet.field=language&facet.field=era_facet&facet.field=geographic_facet";
	 
		#echo "$fullName<br/>";
		#echo "$path<br/>";
	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->name = $name;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			$res->facets = $Tres;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	

	public function getFullListFiltered($facetName, $names) {
		$Tres = array();
		$res = new stdclass;
		foreach ($names as $name)
			$urlqueries[] = '('.$facetName.':"'.$name.'")';
		$urlquery = urlencode(implode(' OR ',$urlqueries));
		
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->core."/select?q=*:*&fq={$urlquery}&facet=true&facet.mincount=1&rows=0&facet.limit=1000&facet.field={$facetName}&facet.sort=count";
		echo '<a href="'.$path.'">Link</a>';
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->name = $facetName;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$key] = $v2;
					}
				} 
			$res->sum = array_sum($Tres);
			$res->results = $Tres;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	
	
	
	public function getFullList($facetName) {
		$Tres = array();
		$res = new stdclass;
		
		// &facet.limit=10
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->cores->biblio."/select?q=*:*&facet=true&rows=0&facet.field={$facetName}&facet.limit=10000";
	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = @file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->name = $facetName;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$key] = $v2;
					}
				} 
			$res->sum = array_sum($Tres);
			$res->results = $Tres;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	
	
	
	
	public function getPersonStats($id,$name,$date) {
		$Tres = array();
		$res = new stdclass;
		
		$name = $this->removeLastSlash($name, ',');
		
		$fullName = $this->getFullFacetName($id);
		$id = str_replace('viaf_id', 'http://viaf.org/viaf/', $id);
		$ulrqout = urlencode('"');
		
		$urlquery = urlencode('(subject_person_str_mv:"'.$fullName.'")OR(author_facet_s:"'.$fullName.'")');
		 
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->cores->biblio."/select?q=*:*&fq={$urlquery}&facet=true&facet.mincount=1&rows=0&facet.limit=5&facet.field=info_resource_str_mv&facet.field=format_major&facet.field=article_resource_str_mv&facet.field=author_facet_s&facet.field=author_facet_c&facet.field=genre_major&facet.field=subject_person_str_mv&facet.field=container_title_2&facet.field=language&facet.field=era_facet&facet.field=geographic_facet";
	 
		#echo "$fullName<br/>";
		#echo "$path<br/>";
	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$er = error_reporting();
		error_reporting(0);
		$file = file_get_contents($path);
		error_reporting($er);
		
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->id = $id;
			$res->name = $name;
			$res->date = $date;
			
			$res->as_author = 0;
			$res->as_author_pr = 0;
			$res->author_facet_s = $name;
			
			$res->as_topic_person = 0;
			$res->as_topic_person_pr = 0;
			$res->topic_person_str_mv = $name;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			foreach ($Tres as $kat=>$fac)	
				foreach ($fac as $k=>$v) {
					if ($v==0)
						unset($Tres[$kat][$k]);
					if (($kat == 'author_facet_s') && (stristr($k, $name)) &&(empty($res->as_author))) {
						$res->as_author = $v;
						$res->as_author_pr = ($v/$res->numFound)*100;
						$res->author_facet_s = $k;
						}
					if (($kat == 'subject_person_str_mv') && (stristr($k, $name)) &&(empty($res->as_topic_person))) {
						$res->as_topic_person = $v;
						$res->as_topic_person_pr = ($v/$res->numFound)*100;
						$res->subject_person_str_mv = $k;
						}
					
					}
			unset($Tres['author_facet_s']);
			unset($Tres['subject_person_str_mv']);
			$res->facets = $Tres;
			$res->fullRecivedName = $fullName;
			# unset($json->responseHeader);
			# $res->fullRes = $json;
			
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}		
		
	public function getPersonRoles($id) {
		$Tres = array();
		$res = new stdclass;
		
		$fullName = $this->getFullFacetName($id);
		$urlquery = urlencode('(author_facet_s:"'.$fullName.'")');
		
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->core."/select?q=*:*&fq={$urlquery}&facet=true&rows=0&facet.limit=50&facet.field=author_role";

	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			echo "<pre>".print_R($json,1)."</pre>";
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->id = $id;
			$res->name = $name;
			$res->date = $date;
			
			$res->as_author = 0;
			$res->as_author_pr = 0;
			$res->author_facet_s = $name;
			
			$res->as_topic_person = 0;
			$res->as_topic_person_pr = 0;
			$res->topic_person_str_mv = $name;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			foreach ($Tres as $kat=>$fac)	
				foreach ($fac as $k=>$v) {
					if ($v==0)
						unset($Tres[$kat][$k]);
					if (($kat == 'author_facet_s') && (stristr($k, $name)) &&(empty($res->as_author))) {
						$res->as_author = $v;
						$res->as_author_pr = ($v/$res->numFound)*100;
						$res->author_facet_s = $k;
						}
					if (($kat == 'topic_person_str_mv') && (stristr($k, $name)) &&(empty($res->as_topic_person))) {
						$res->as_topic_person = $v;
						$res->as_topic_person_pr = ($v/$res->numFound)*100;
						$res->topic_person_str_mv = $k;
						}
					
					}
			unset($Tres['author_facet_s']);
			unset($Tres['topic_person_str_mv']);
			$res->facets = $Tres;
			$res->fullRecivedName = $fullName;
			# unset($json->responseHeader);
			# $res->fullRes = $json;
			
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}
		}	

		
	public function getPersonStatsNoID($name,$date) {
		$Tres = array();
		$res = new stdclass;
		
		$name = $this->removeLastSlash($name, ',');
		$uname = urlencode($name);
		
		$path = $this->options->hostname.':'.$this->options->port."/solr/".$this->options->core."/select?q=\"{$uname}\"&facet=true&rows=0&facet.limit=5&facet.field=info_resource_str_mv&facet.field=format&facet.field=article_resource_str_mv&facet.field=author_facet_s&facet.field=author_facet_c&facet.field=genre_facet&facet.field=topic_person_str_mv&facet.field=container_title_2&facet.field=language&facet.field=era_facet&facet.field=geographic_facet";
	 
		$this->alert[] = "<div style='padding:5px; background-color:rgba(255,255,255,0.8); position:absolute; left:5px; top:5px; z-index:10000; border:solid 1px black; border-radius:5px;'><a href='$path' target=_blank>solr query</a></div>"; 
		$file = file_get_contents($path);
		if ($file) {
			$json =  json_decode( $file );
			
			if (!empty($json->response->numFound))
				$res->numFound = $json->response->numFound;
				else 
				$res->numFound = 0;
			$res->id = 'null';
			$res->name = $name;
			$res->date = $date;
			
			$res->as_author = 0;
			$res->as_author_pr = 0;
			$res->author_facet_s = $name;
			
			$res->as_topic_person = 0;
			$res->as_topic_person_pr = 0;
			$res->topic_person_str_mv = $name;
			
			if (!empty($json->facet_counts->facet_fields)) {
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else 
							$Tres[$k][$key] = $v2;
					}
				} 
			foreach ($Tres as $kat=>$fac)	
				foreach ($fac as $k=>$v) {
					if ($v==0)
						unset($Tres[$kat][$k]);
					if (($kat == 'author_facet_s') && (stristr($k, $name)) &&(empty($res->as_author))) {
						$res->as_author = $v;
						$res->as_author_pr = ($v/$res->numFound)*100;
						$res->author_facet_s = $k;
						}
					if (($kat == 'topic_person_str_mv') && (stristr($k, $name)) &&(empty($res->as_topic_person))) {
						$res->as_topic_person = $v;
						$res->as_topic_person_pr = ($v/$res->numFound)*100;
						$res->topic_person_str_mv = $k;
						}
					
					}
			unset($Tres['author_facet_s']);
			unset($Tres['topic_person_str_mv']);
			$res->facets = $Tres;
			
			# unset($json->responseHeader);
			# $res->fullRes = $json;
			return $res;
			} else {
			$this->error = "The connection to the Solr has failed";
			return null;
			}

		}
	
	
	public function getFullList2($core, $facetName, $outquery) {
		$Tres = array();
		$res = new stdclass;
		#echo "<pre>".print_r($outquery,1)."</pre>";
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
		$query['limit']=[
			'field' => 'facet.limit',
			'value' => '10000'
			];
		$query[]=[
			'field' => 'facet.sort',
			'value' => 'index'
			];
	
		$query[]=[ 
			'field' => 'facet.field',
			'value' => $facetName
			];
		if (!empty($outquery['limit']))
			unset($outquery['limit']);
		$query = array_merge($query, $outquery);
		
		$json = $this->querySelect($core, $query);
			
		if (!empty($json->response->numFound))
			$res->numFound = $json->response->numFound;
			else 
			$res->numFound = 0;
		$res->name = $facetName;
		
		if (!empty($json->facet_counts->facet_fields)) {
			foreach ($json->facet_counts->facet_fields as $k=>$v) {
				foreach ($v as $k2=>$v2)
					if ($k2 % 2 == 0) {
						$key = $v2;
						} else 
						$Tres[$key] = $v2;
				}
			} 
		$res->sum = array_sum($Tres);
		$res->results = $Tres;
		return $res;
			 
		}	
	
	
	public function getCleanedYears($core, $facets = array(), $options = []) {
		$Tres = array();
		if (is_array($facets)) {
			$query[]=[ 
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
				'field' => 'facet.limit',
				'value' => '10000'
				];
			$query[]=[
				'field' => 'facet.sort',
				'value' => 'index'
				];
		
			foreach ($facets as $facet) {
				$query[]=[ 
					'field' => 'facet.field',
					'value' => $facet
					];
				}
			
			if (count($options)>0) {
				$query = array_merge($options,$query);
				}
			#echo "<pre>".print_r($query,1).'</pre>';
			$json = $this->querySelect($core, $query);
			$Terr = [];
			$empty = false;
			# echo "getFacets<pre>".print_r($json,1)."</pre>";
			if (!empty($json->facet_counts->facet_fields)) {
				$this->response = $json->response;
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else {
							$fv = floatval($key);
								
							if (is_numeric($key) and (($v2>0)or($empty)) and ($fv>1500) and ($fv<=date("Y"))) {
								$Tres[$k][$key]=$v2; 
								$empty=true;
								} else 
								$Terr[$key]=$v2;
							}
					if (!empty($Tres[$k])) {
						$min = min(array_keys($Tres[$k]));
						$max = max(array_keys($Tres[$k]));
						for ($i=$min; $i<=$max; $i++) {
							if (empty($Tres[$k][$i]))
								$Tres[$k][$i]=0;
							}
						ksort($Tres[$k]);	
						}
					}
				#echo "<pre>".print_r($Terr,1)."</pre>";
				
				
				return $Tres;	
				} else 			
				return [];
			}
		return [];
		}
		
	public function getCleanedYearsExport($core, $facets = array(), $options = []) {
		$Tres = array();
		if (is_array($facets)) {
			$query[]=[ 
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
			$query[]=[
				'field' => 'facet.limit',
				'value' => '10000'
				];
			$query[]=[
				'field' => 'facet.sort',
				'value' => 'index'
				];
		
			foreach ($facets as $facet) {
				$query[]=[ 
					'field' => 'facet.field',
					'value' => $facet
					];
				}
			
			if (count($options)>0) {
				$query = array_merge($options,$query);
				}
			#echo "<pre>".print_r($query,1).'</pre>';
			$json = $this->querySelect($core, $query);
			$Terr = [];
			$empty = false;
			# echo "getFacets<pre>".print_r($json,1)."</pre>";
			if (!empty($json->facet_counts->facet_fields)) {
				$this->response = $json->response;
				foreach ($json->facet_counts->facet_fields as $k=>$v) {
					foreach ($v as $k2=>$v2)
						if ($k2 % 2 == 0) {
							$key = $v2;
							} else {
							$fv = floatval($key);
								
							if (is_numeric($key) and (($v2>0)or($empty)) and ($fv>1500) and ($fv<=date("Y"))) {
								$Tres[$k][$key]=$v2; 
								$empty=true;
								} else 
								$Terr[$key]=$v2;
							}
					}
				
				return $Tres;	
				} else 			
				return [];
			}
		return [];
		}
	
	
	public function results() {
		}	
		

		
	public function getSolrVersion() {
		return solr_get_version();
		}
 
 

 
}

?>
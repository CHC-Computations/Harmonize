<?php


class bibliographicRecord {
	
	public $solrRecord;
	public $elbRecord;
	public $marcJson;
	
	public $bottomLists;
	
	public function __construct($solrRecord, $marcJson = null) {
		
		$this->marcJson = $marcJson;
		$this->solrRecord = $solrRecord;
		$this->elbRecord = json_decode($solrRecord->relations);
		
		# echo "<pre>".print_r($this->record->marcFields, 1). "</pre>";
		$this->bottomLists = new stdclass;
		}
	
	public function register($key, $value) {
		@$this->$key = $value;
		}
	

	public function getLeader() {
		return $this->marcJson->LEADER;
		}

	public function getId() {
		return $this->solrRecord->id ?? null;
		}
	
	public function getTitle() {
		return $this->elbRecord->title ?? '[no title]';
		}
	
	
	public function hasSimilar() {
		$similar = [];
		if (!empty($this->solrRecord->title_sort)) {
			$query['q']=[ 
					'field' => 'q',
					'value' => 'title_sort:"'.$this->solrRecord->title_sort.'" AND -id:'.$this->solrRecord->id
					];
			$query['fl']=[ 
					'field' => 'fl',
					'value' => "id,title,author"
					];
			$this->cms->solr->getQuery('biblio', $query);
			$similars = $this->cms->solr->resultsList();
			if (!empty($similars)) {
				$this->elbRecord->similars = $similars;
				return true;
				}
				return false;
			}
		return false;	
		}
	
	public function getSimilar() {
		if (empty($this->elbRecord->similars)) $this->hasSimilar();
		return $this->elbRecord->similars ?? [];
		}
	
	function get($block, $group = null) {
		if (empty($group))
			return $this->elbRecord->$block ?? null;
			else 
			return $this->elbRecord->$block->$group ?? null;	
		}
		



	public function creativeRolesSynonyms($role) {
		$auth = $this->cms->getConfig('properties/author-classification');
		#echo "<pre>".print_r($auth,1).'</pre>';
		if (!empty($auth['RelatorSynonyms'][$role]))
			return $auth['RelatorSynonyms'][$role];
			else 
			return $role;
		}
	

	public function getNamePart($part, $name) {
		switch ($part) {
			case 'surname':
			case 'last name':
			case 'family':
			case 'family name' :
				$count = 0;
				break;
			case 'first':
			case 'first name':
			case 'given':
			case 'given name':
				$count = 1;
				break;
			default :
				$count = 99;
			}
		
		$tmp = explode(',', $name);
		if (!empty($tmp[$count]))
			return trim($tmp[$count]);
		
		return $name;
		}	 
	
	public function getRETpic() {
		
		if ($this->getTitle() == '[Název textu k dispozici na připojeném lístku]') {
			$field = 856;
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
				$desc = current($this->record->marcFields->$field);
				$ret = $source = $desc->code->u;
				$ret = str_replace(
					'http://retrobi.ucl.cas.cz/retrobi/katalog/listek/',
					'http://retrobi.ucl.cas.cz/retrobi/resources/retrobi/cardimg?listek=',
					$ret).'&obrazek=1o&sirka=800&orez=false';
				$outpic = "<div class='thumbnail'><img src='$ret' class='img-responsive'>source: <a href='$source'>$source</a></div>";	
				return $outpic;
			
				} 
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
		
		return null;	
		}
		
	
	
	
	public function getSeria() {
		$field = 490;
		$rec = [];
		$lp = 0;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) {
				$lp++;
				$ln = [];
				if (!empty($row->code->a) && is_array($row->code->a)) 
					$ln[] = implode(' ',$row->code->a);
				if (!empty($row->code->a) && !is_array($row->code->a)) 
					$ln[] = $row->code->a;
				
				if (!empty($row->code->v)) 
					$ln[] = $row->code->v;
				#echo "<pre>".print_R($ln,1)."</pre>";
				if (is_array($ln))
					$rec[] = implode(' ', $ln);
					else 
					$rec[] = $ln;
				} 
				
			return $rec;		
			}
		}
	
	public function getRegion() {
		$field = '651';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->a))
					if (is_Array($sf->code->a)) {
						foreach ($sf->code->a as $z) 
							$res[] = $z;	
						}
						else 
						$res[] = $sf->code->a;
				}
			}
			
		return $res;	
		}
	
	public function getSubjects() {
		$res = [];
		$min = 601;
		$max = 699;
		$lp = 0;
		for ($field = $min; $field<=$max; $field++) {
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
				foreach ($this->record->marcFields->$field as $sf) {
					$lp++;
					$ln = [];
					$lnk = [];
					$uri = [];
					foreach ($sf->code as $k=>$z) {
						switch ($k) {
							case '2': 
							case '7': break;
							case '0':
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = urlencode($sz);
										$lnk[] = "<a href=\"{$sz}\" title='{$sz}'><i class='ph-link-bold'></i></a>"; break;
										}
									} else {	
									$uri[] = urlencode($z);
									$lnk[] = "<a href=\"{$z}\" title='{$z}'><i class='ph-link-bold'></i></a>"; break;
									}
								break;
							default: 
								if (is_Array($z)) {
									foreach ($z as $sk=>$sz) {
										$uri[] = urlencode($sz);
										$ln[] = "<a href=\"{$this->cms->baseUrl('results', ['core'=>'biblio'])}?type=Subject&lookfor=".implode('+',$uri)."\">$sz</a>"; break;
										}
									} else {	
									$uri[] = urlencode($z);
									$ln[] = "<a href=\"{$this->cms->baseUrl('results', ['core'=>'biblio'])}?type=Subject&lookfor=".implode('+',$uri)."\">$z</a>"; break;
									}
							}
						
						}	
					$res[] = '<div class="subject-line" property="keywords">'.implode(' &gt; ',$ln).' '.implode(' ',$lnk).'</div>';
					}
				}
			}
		
		$field = '773';
		
		return implode("\n",$res);	
		}
	
	
	
	public function getELaA_full() { // Electronic Location and Access
		$field = 856;
		$rec = [];
		$lp = 0;
		if (is_object($this->marcJson) && (!empty($this->marcJson->$field))) {
			$line = $this->marcJson->$field;
			$row = (array)current($line);
			foreach ($line as $row) {
				$lp++;
				$ln = (array)$row->code;
				
				if (!empty($row->code->u)) {
					$ln['link'] = $row->code->u;
					$tmp = explode('/', $row->code->u);
					$ln['id'] = end($tmp);
					}
				if (!empty($row->code->y)) {
					$tmp = explode(':',$row->code->y);
					if (count($tmp)>1) {
						$group = current($tmp);
						$str = str_replace($group.': ', '', $row->code->y);
						
						$ln['full_str'] = $str;
						$ln['group'] = $group;
						
						$tmp = explode(', ',$str);
						
						$ln['author'] = current($tmp); 	unset($tmp[0]);
						$c = count($tmp);
						if ($c>=5) {
							$ln['pages'] = $tmp[$c]; 		unset($tmp[$c]);
							$c--;
							$ln['nr']=$tmp[$c]; 			unset($tmp[$c]);
							$c--;
							$ln['place']=$tmp[$c]; 			unset($tmp[$c]);
							$c--;
							$ln['publisher']=$tmp[$c]; 		unset($tmp[$c]);
							$c--;
							
							$ln['title'] = implode(', ',$tmp);
							} else {
							$ln['title'] = $tmp[1];	
							if (!empty($tmp[2]))
								$ln['publisher'] = $tmp[2];
							if (!empty($tmp[3]))
								$ln['place'] = $tmp[3];
							
							}
						$rec[$group][] = $ln;
						}
					}
				} 
			return $rec;		
			}
		
		}
			
	
	
	
	
	public function getTags() {
		return "No Tags, Be the first to tag this record";	
		}
	
	
	
	
	
	
	
	######################## persons 
	
	public function getDateOfBrith() {
		$desc = $this->getMarcFirst('046');
		if (!empty($desc['f'])) {
			$d = $desc['f'];
			return substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
			}
			
		}
	public function getDateOfDeath() {
		$desc = $this->getMarcFirst('046');
		if (!empty($desc['g'])) {
			$d = $desc['g'];
			return substr($d,0,4).'-'.substr($d,4,2).'-'.substr($d,6,2);
			}
	
		}
	
	public function getPlaceOfBrith() {
		$field = 370;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec = (array)$row->code;
					
					$tmp = explode('/',$rec[1]);
					$rec['geocode'] = end($tmp);
					$rec['name'] = $rec['a'];
					return $rec;
					
					} 
			}
		}
		
	public function getPlaceOfDeath() {
		$field = 370;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->b)) {
					$rec = (array)$row->code;
					
					if (!empty($rec[1])){
						$tmp = explode('/',$rec[1]);
						$rec['geocode'] = end($tmp);
						}
					$rec['name'] = $rec['b'];
					return $rec;
					
					} 
			}
		}	
		
	public function getRelationship() {
		$field = 370;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->f)) {
					$trec = (array)$row->code;
					$trec['name'] = $trec['f'];
					if (!empty($trec[1])){
						$tmp = explode('/',$trec[1]);
						$trec['geocode'] = end($tmp);
						}
					$rec[] = $trec;
					} 
			return $rec;		
			}
		}
	
	public function getCitizenship() {
		$field = 370;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->c)) {
					$rec[] = $row->code->c;
					
					} 
			return $rec;		
			}
		}
	
	public function getOccupation() {
		$field = 374;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
					} 
			return $rec;		
			}
		}
	
	public function getGender() {
		$field = 375;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
					} 
			return $rec;		
			}
		}
	
	public function getLanguages() {
		$field = 377;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) {
				if (!empty($row->code->a)) 
					$rec[] = $row->code->a;
				if (!empty($row->code->h)) 
					$rec[] = $row->code->h;
		
				} 
			return $rec;		
			}
		}
	
	public function getSources() {
		$field = 670;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
					} 
			return $rec;		
			}
		}
	
	
	########################################   
	
	public function list($rec, $nr = true) {
		if (count($rec)>1)
			if ($nr)
				return "<ol><li>".implode('</li><li>',$rec)."</li></ol>";
				else 
				return "<ul><li>".implode('</li><li>',$rec)."</li></ul>";	
			else 
			return implode(', ',$rec);
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
	
	public function getCoinsOpenURL() {
		if ($this->get('format')=='Journal article') {
			$tmp = $this->getMarcFirst(773, ['g']);
			if (count($tmp)>1) {
				$spage = array_pop($tmp);
				} else 
				$spage = '';
			$table = array (
				'url_ver' 		=> 'Z39.88-2004',
				'ctx_ver' 		=> 'Z39.88-2004',
				'ctx_enc' 		=> 'info:ofi/enc:UTF-8',
				'rfr_id' 		=> 'info:sid/vufind.svn.sourceforge.net:generator',
				'rft.date' 		=> $this->get('publishDate'),
				'rft_val_fmt' 	=> 'info:ofi/fmt:kev:mtx:journal',
				'rft.genre' 	=> $this->getMarcFirst(655),
				'rft.issn' 		=> (string)$this->getISSN(),
				'rft.isbn' 		=> (string)$this->getISBN(),
				'rft.volume' 	=> $this->getMarcFirstStr(773, ['v']),
				'rft.issue' 	=> $this->getMarcFirstStr(773, ['l']),
				'rft.spage' 	=> $spage,
				'rft.jtitle' 	=> $this->getMarcFirst(773, ['t']),
				'rft.atitle' 	=> $this->getTitle(), 
				'rft.au' 		=> $this->getMarcFirstStr('100', []),
				'rft.format' 	=> $this->get('format'),
				'rft.language' 	=> $this->get('language','publication')
				);
			#echo "<pre>".print_r($table,1)."</pre>";
			return http_build_query($table);
			} 
		}
	
	
	
	################################################################# places	
	

	public function getPlaceName() {
		$field = 151;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $sline) {
				$row = (array)$sline->code;
				if (!empty($sline->code->a)) {
					$rec = $row['a'];
					$this->fullName = $row['a'];
					}
				} 
			return $rec;		
			}
		}	
		
		public function getPlaceType() {
		$field = 151;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) {

				if (!empty($row->code->i)) 
					$rec = $row->code->i;
				} 
			return $rec;		
			}
		}
	
	public function getPlaceFields() {
		$coreFields = array (
			'getPlaceName' => 'Name of place',
			'getPlaceType' => 'Type of place',
			);
		
			
		$result = array();
		foreach ($coreFields as $k=>$v) {
			$val = $this->$k();
			if (!empty($val)) {
			
				if (is_array($val))
					$sval = $this->list($val);
					else 
					$sval = $val;
				$result[]=[
					'label' => $v,
					'content' => $sval
					];
				}
			}
			
		
		return $result;
		}	
		

	public function drawMarc() {
		if (!empty($this->marcJson)) {
			$result = '<table class="table table-striped">
					<thead><tr><td style="text-align:right"><b>LEADER</b></td><td colspan=3>'.$this->marcJson->LEADER.'</td></tr></thead>
					<tbody>
					';
			foreach ($this->marcJson as $field=>$subarr) {
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
	
	function removeArrayKeys($array) {
		if (is_Array($array)) {
			$n_array = [];
			foreach ($array as $k=>$v)
				$n_array[] = $v;
			return $n_array;	
			}
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
	
		
	}

?>
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
	
	public function getIdStr() {
		return str_replace('.', '_', $this->getId());
		}
	
	public function getTitle() {
		return $this->elbRecord->title ?? '[no title]';
		}
	
	public function getLanguage($type = '') {
		if (!empty($this->elbRecord->language->$type))
			return current($this->elbRecord->language->$type);
	
		#return $this->elbRecord->language ?? [];
		}
	
	public function getPublicationYear() {
		if (!empty($this->elbRecord->publicationYear))
			return current($this->elbRecord->publicationYear);
	
		#return $this->elbRecord->language ?? [];
		}
	
	public function getPlaces($as) {
		if (!empty($this->elbRecord->places->$as)) {
			$res = [];
			foreach ($this->elbRecord->places->$as as $place)
				$res[] = $this->cms->helper->createPlaceStr($place);
			return $res;
			}
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
	
	function get() {
		$keys = func_get_args();
		$value = (array)$this->elbRecord;
		foreach ($keys as $key) {
			if (is_object($value))
				$value = (array)$value;
			if (!isset($value[$key])) {
				return null;
				}
			$value = $value[$key];
			}
		return $value;
		}
		

	function getFirstAsString($params) {
		return current((array)$this->get($params));
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
		if (!empty($name)) {
			$tmp = explode(',', $name);
			if (!empty($tmp[$count]))
				return trim($tmp[$count]);
			} else 
			return '';
		return $name;
		}	 
	
	public function getRETpic($showLink = true) {
		#if (($this->getTitle() == '[Název textu k dispozici na připojeném lístku]')or($this->getTitle() == '[Title on the picture (retrobi record)]')) {

		if (substr($this->getId(),0,7) == 'cz.RET-') {
			$linkedResources = $this->get('linkedResources');
			foreach ($linkedResources as $result) 
				if (stristr($result->link, 'retrobi.ucl.cas.cz')) {
					$ret = str_replace(
							'http://retrobi.ucl.cas.cz/retrobi/katalog/listek/',
							'http://retrobi.ucl.cas.cz/retrobi/resources/retrobi/cardimg?listek=',
							$result->link
							);
					$ret.='&obrazek=1o&sirka=800&orez=false';
					if ($showLink)
						$outpic = "<div class='thumbnail'><img src='$ret' class='img-responsive'>source: <a href='{$result->link}'>{$result->link}</a></div>";	
						else
						$outpic = "<img src='$ret' class='img-responsive' alt='{$result->desc}'>";		
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
		
		}
	
	public function getArticleISSN() {
		
		}
	
	public function getISBN() {
		}
	
	public function getFixedLink($id = '') {
		if (empty($id))
			$id = $this->getId();
		return $this->cms->HOST.'id/'.$id;
		}
		
	public function getMetaAlternate() {
		$return[] = '<link rel="canonical" href="'.$this->cms->selfUrl().'" />';
		$return[] = '<link rel="alternate" type="application/json" href="'.$this->cms->selfUrl('.html', '.json').'" />';
		$return[] = '<link rel="alternate" type="application/json" href="'.$this->cms->selfUrl('.html', '.json?elb').'" />';
		$return[] = '<link rel="alternate" type="application/xml" href="'.$this->cms->selfUrl('.html', '.xml').'" />';
		$return[] = '<link rel="alternate" type="text/plain" href="'.$this->cms->selfUrl('.html', '.mrk').'" />';
		return "\n\t\t".implode("\n\t\t", $return)."\n\n";
		}
	
	public function getMetaZotero() {
		#https://www.zotero.org/support/dev/exposing_metadata
		
		
		$return = [];
		$return[] = '<meta name="citation_title" content="'.$this->getTitle().'" />';
		#title citation_title citation_journal_title citation_book_title citation_inbook_title citation_series_title
				
		if (!empty($this->get('persons', 'mainAuthor'))) 
			foreach ($this->get('persons', 'mainAuthor') as $key => $person) {
				#echo $this->cms->helper->pre($person);
				$return[] = '<meta name="citation_author" content="'.$person->name.'" />';
				}
		if (!empty($this->get('persons', 'coAuthor'))) 
			foreach ($this->get('persons', 'coAuthor') as $key => $person) {
				#echo $this->cms->helper->pre($person);
				$return[] = '<meta name="citation_author" content="'.$person->name.'" />';
				}
		if (!empty($this->elbRecord->publicationYear)) {
			$years = (array)$this->elbRecord->publicationYear;
			foreach ($years as $year) 
				$return[] = '<meta name="citation_year" content="'.$year.'" />';
			}
		if (($this->get('majorFormat') == 'Journal article') && !empty($this->get('magazines', 'sourceMagazine')))
			foreach ($this->get('magazines', 'sourceMagazine') as $magazine) {
				if (!empty($magazine->title)) 
					$return[] = '<meta name="citation_journal_title" content="'.$magazine->title.'" />';
				}
		if (!empty($this->get('language','publication'))) 
			foreach ($this->get('language','publication') as $language) {
				$return[] = '<meta name="citation_language" content="'.$language.'" />';
				}
		
		if (!empty($this->get('issn'))) 
			foreach ($this->get('issn') as $issn) {
				$return[] = '<meta name="citation_issn" content="'.$issn.'" />';
				}
		
		
		return "\n\t\t".implode("\n\t\t", $return)."\n\n";
		}	
		
		
		
	public function getCoinsOpenURL() {
		/*
		TO DO!
		
		11. rft.volume - Tom

			Numer tomu czasopisma.
			Przykład: rft.volume=42

		12. rft.issue - Numer wydania

			Numer wydania w danym tomie czasopisma.
			Przykład: rft.issue=3

		13. rft.spage i rft.epage - Strony początkowa i końcowa

			Oznaczają zakres stron artykułu.
			Przykład: rft.spage=123&rft.epage=130

		14. rft.pages - Zakres stron (alternatywnie)

			Możesz również użyć pola rft.pages dla podania zakresu stron w jednym polu.
			Przykład: rft.pages=123-130
		*/
		
		
		$table = array (
				'url_ver' 		=> 'Z39.88-2004',
				'ctx_ver' 		=> 'Z39.88-2004',
				'ctx_enc' 		=> 'info:ofi/enc:UTF-8',
				'rfr_id' 		=> $this->getFixedLink(),
				);
		if (!empty($this->get('major_genre')))
			$table['rft.genre']	= implode(',', $this->get('major_genre'));
		if (!empty($this->get('publishDate')))
			$table['rft.date']	= implode(',', $this->get('publishDate'));
		if (!empty($this->get('isbn'))) // books or book chapter
			$table['rft.isbn']	= implode(',', $this->get('isbn'));
		if (!empty($this->get('language', 'publication'))) {
			$lang = (array)$this->get('language', 'publication');
			$table['rft.language']	= implode(',', $lang);
			}
		if (!empty($this->get('places', 'publicationPlace'))) {
			$place = current((array)$this->get('places', 'publicationPlace'));
			if (!empty($place->name))
				$table['rft.place'] = $place->name;
			if (!empty($place->nameML))
				$table['rft.place']	= $this->cms->helper->formatMultiLangStr($place->nameML);
			}
			
		if (!empty($this->get('persons', 'mainAuthor'))) {
			$person = current((array)$this->get('persons', 'mainAuthor'));
			if (!empty($person->name))
				$table['rft.au'][0] = $person->name;
			if (!empty($person->nameML))
				$table['rft.au'][0] = $this->cms->helper->formatMultiLangStr($person->nameML);
			if (!empty($person->dates))
				$table['rft.au'][0] .= ' '.$person->dates; 
			}
		if (!empty($this->get('persons', 'coAuthor'))) {
			$persons = (array)$this->get('persons', 'coAuthor');
			$i = 0;
			foreach ($persons as $person) {
				$i++;
				if (!empty($person->name))
					$table['rft.au'][$i] = $person->name;
				if (!empty($person->nameML))
					$table['rft.au'][$i] = $this->cms->helper->formatMultiLangStr($person->nameML);
				if (!empty($person->dates))
					$table['rft.au'][$i] .= ' '.$person->dates; 
				}
			} 
		
		if (!empty($this->get('edition', 'no')))
			$table['rft.edition'] = current((array)$this->get('edition', 'no'));
		
		if (!empty($this->get('corporates', 'corporates'))) {
			$corporates = (array)$this->get('corporates', 'corporates');
			$i = 0;
			foreach ($corporates as $coporate) {
				if (!empty($coporate->name))
					$table['rft.pub'][$i] = $coporate->name;
				if (!empty($coporate->nameML))
					$table['rft.pub'][$i] = $this->cms->helper->formatMultiLangStr($coporate->nameML);
				$i++;
				}
			} 
		
		
		
		
		switch ($this->get('majorFormat')) {
			case ('Journal article')  : {
				$table['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:journal';
				$table['rft.atitle'] = $this->getTitle();
				if (!empty($this->get('magazines', 'sourceMagazine'))) {
					$magazines = $this->get('magazines', 'sourceMagazine');
					$magazine = current((array)$magazines);
					$table['rft.jtitle'] = $magazine->title;
					$table['rft.rft.issn'] = $magazine->issn;
					}
				} break;
			case ('Book')  : {
				$table['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:book';
				$table['rft.btitle'] = $this->getTitle();
				
				} break;
			case ('Book chapter')  : {
				$table['rft_val_fmt'] = 'info:ofi/fmt:kev:mtx:bookitem';
				$table['rft.atitle'] = $this->getTitle();
				$table['rft.btitle'] = $this->getTitle();
				} break;
			
			}	
				
			
		return http_build_query($table);
			 
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
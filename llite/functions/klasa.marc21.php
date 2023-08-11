<?php

#require_once 'File/MARC.php';
#  extends CMS

class marc21 {
	
	
	public function __construct($fullRecord, $solrRecord = null) {
		
		$this->fullRecord = $fullRecord;
		$this->solrRecord = $solrRecord;
		$this->record = new stdclass;
		if (!empty($fullRecord->LEADER))
			$this->record->LEADER = $fullRecord->LEADER;
			else 
			$this->record->LEADER = NULL;
		$this->record->marcFields = $fullRecord;
		
		$file = file ('./languages/lang_codes.csv'); 
		foreach ($file as $line) {
			$ln = explode('|', $line);
			$this->Lang[$ln[0]] = $ln['2']; // ISO 639-2 Code
			$this->lang[$ln[1]] = $ln['2']; // ISO 639-1 Code
			}
		
		# echo "<pre>".print_r($this->record->marcFields, 1). "</pre>";
		$this->bottomLists = new stdclass;
		}
	
	public function register($key, $value) {
		$this->$key = $value;
		}
	
	public function setBasicUri($link=null) {
		$this->basicUri = $link;
		}
	
	/*
	public function getRecord($id) {
		$t = $this->pgs->query_select("SELECT * FROM biblio WHERE id='$id';");
		if (is_array($t)) {
			$this->rec = current($t);
			$this->marcrec = new File_MARC($this->rec['full_record'], File_MARC::SOURCE_STRING);

			$record = $this->marcrec->next(); 

			$this->record = new stdclass;
			$this->record->LEADER = $record->getLeader();
			$this->record->sql = $this->rec;
			
			$this->record->fieldsList='';
			
			foreach ($record->getFields() as $field => $subfields) {
				$this->record->fieldsList.="$field,";
				
				$Ts = array();
				if (method_exists($subfields,'getIndicator')) {
					$ind1=$subfields->getIndicator(1);
					$ind2=$subfields->getIndicator(2);
					$Ts['ind1'] = $ind1;
					$Ts['ind2'] = $ind2;	
					} else 
					$ind1=$ind2='';
				
				
				if (method_exists($subfields,'getSubfields')) {
					foreach ($subfields->getSubfields() as $subfield => $value) {
						$cv=$value->getData();
						$Ts['code'][$subfield] = $this->removeLastSlash($cv);
						}
						
					} else {
					$Ts = $subfields->getData();	
					}
					
				$Tf[$field][]=$Ts;
						
				}
			$this->record->marcFields = $Tf;
				
			return $this->record;	
			} else {
			$rec = new stdclass;
			$rec->title = $id;
			$rec->id = $id;
			return $rec;
			}
		}
	*/
	
	public function getMarcLine($field, $subfields=array(), $sep=' ', $sepLn='<br/>') {
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			
			foreach ($line as $row) {
				$codes = array();
				if (!empty($row->code)) {
					foreach ($row->code as $code=>$val) {
						if (count($subfields)>0) {
							if (in_array($code,$subfields))
								$codes[] = $val;
							} else 
							$codes[] = $val;
						}
					$result .= implode($sep, $codes).$sepLn;
					} 
				if (!is_object($row))
					$result .= $row.$sepLn;
				}
				
			return $result;	
			} else 
			return null;
		}
	
	public function getMarcFirst($field, $subfields=array(), $sep=' ') {
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
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
			return [];
		} 	
		
	public function getMarcFirstStr($field, $subfields=array(), $sep=' ') {
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
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
				$result .= implode($sep, $codes);
				} 
			if (!is_array($row))
				$result .= $row;
			
				
			return $result;	
			} else 
			return null;
		} 
	
	public function getMarcArr($field, $subfields) {
		
		}
	
	public function getLeader() {
		return $this->record->LEADER;
		}

	public function getTitle() {
		$title = $this->removeLastSlash($this->getMarcFirstStr('245', ['a', 'b']));

		if (!empty($title))
			return $title;
			// return mb_convert_encoding($title, 'UTF-8', 'ISO-8859-1');
			else 
			return "[no title]";
		}
	
	public function getDescription () {
		$desc = $this->getMarcLine('520', ['a']);
		
		if (!empty($desc))
			return "<p>".$desc."</p>";
			else 
			return null;
		}
	
	
	public function getStatmentOfResp() {
		$desc = $this->getMarcFirstStr('245', ['c']);
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}
	
	private function checkViafId(&$desc) {
		$desc['viaf'] = '';
		if (stristr($desc['1'], 'viaf')) {
			$desc['viaf'] = preg_replace("/[^0-9]/", '', $desc['1']);	
			$desc['viaflink'] = $desc['1'];
			$desc['id'] = $desc['viaf'];	
			$desc['id_type']='viaf';
			} else {
			$desc['id'] = $desc['1'];	
			}
		}
	
	public function createGoogleLink($person) {
		if (!empty($person['name']))
			return 'https://www.google.com/search?q='.urlencode($person['name']);
		}
	
	public function createViafLink($person) {
		if (!empty($person['viaf']))
			return "https://viaf.org/viaf/".$person['viaf'];
		} 
	
	public function createWikiLink($person) {
		if (!empty($person['wikiq']))
			return 'https://www.wikidata.org/wiki/Q'.$person['wikiq'];
		}
	
	public function createLibriLink($person) {
		if (!empty($person['wikiq']))
			return $this->cms->buildUrl('wiki/record/Q'.$person['wikiq']);
		}
	
	
	public function relatorSynonyms($role) {
		$auth = $this->cms->getConfig('properties/author-classification');
		#echo "<pre>".print_r($auth,1).'</pre>';
		if (!empty($auth['RelatorSynonyms'][$role]))
			return $auth['RelatorSynonyms'][$role];
			else 
			return $role;
		}
	
	
	function viaf2wiki($viafId) {
		if (empty($this->viafs[$viafId])) {
			$t = $this->cms->psql->querySelect("SELECT wikiid FROM viaf2wiki WHERE viafid='$viafId';");
			if (is_array($t)) {
				$res = current($t);
				$this->viafs[$viafId] = $res['wikiid'];
				return $res['wikiid'];
				}
			} else 
			return $this->viafs[$viafId];
		}
	
	function getWiki4Name($name) {
		$name = $this->clearName($name);
		$return = null;
		$Q = '';
		if (empty($this->wikiNames[$name])) {
			$t = $this->cms->psql->querySelect($Q = "SELECT wiki FROM places_wiki WHERE name={$this->psql->isNull($name)};");
			if (is_array($t)) {
				$res = current($t);
				$this->wikiNames[$name] = $res['wiki'];
				$return = $res['wiki'];
				}
			} else 
			$return = $this->wikiNames[$name];
		
		if (!empty($return)) {
				$this->work->geoWikiFull[$name] = "$name|$return";
				$this->work->geoWiki[$return] = $return;
				}
		file_put_contents($this->outPutFolder.'geoWiki.csv', "$name|$return|\n", FILE_APPEND);
		return "$name|$return";
		}
	
	public function preparePerson(&$desc) {
		$desc['name'] = 
		$desc['date'] = '';
		
		if (!empty($desc['a'])) {
			if (is_array($desc['a']))
				$desc['name'] = implode(' ', $desc['a']);
				else 
				$desc['name'] = $desc['a'];
			}
		if (!empty($desc['c'])) 
			$desc['name'] .= ' '.$desc['c'];
		
		$tmp = explode(',', $desc['name']);
		if (count($tmp)>=2) {
			$desc['first_name'] = trim($tmp[1]);
			$desc['last_name'] = trim($tmp[0]);
			$desc['full_name'] = trim($desc['first_name']).' '.trim($desc['last_name']);
			} else {
			$desc['full_name'] = $desc['first_name'] = $desc['name'];
			$desc['last_name'] = '';
			}
		
		if (!empty($desc['1'])) 
			$this->checkViafId($desc);
			
		if (!empty($desc['viaf'])) 
			$desc['wikiq'] = $this->viaf2wiki($desc['viaf']);
		
		$desc['googlelink'] = $this->createGoogleLink($desc);
		$desc['viaflink'] = $this->createViafLink($desc);
		$desc['wikilink'] = $this->createWikiLink($desc);
		$desc['librilink'] = $this->createLibriLink($desc);
		
		if (!empty($desc['d'])) {
			if (is_array($desc['d']))
				$desc['d']=implode(' ',$desc['d']);
			$desc['date'] = $desc['d'];
			$desc['googlelink'] .= '+'.urlencode($desc['d']);
			}
		if (!empty($desc['4'])) {
			if (is_Array($desc['4'])) {
				foreach ($desc['4'] as $role) {
					$desc['role_code'][] = $role;
					$desc['role'][] = $this->relatorSynonyms($role);
					}
				} else {
				$desc['role_code'] = $desc['4'];
				$desc['role'] = $this->relatorSynonyms($desc['4']);
				}
			} else if (!empty($desc['e'])) {
				if (is_Array($desc['e'])) {
					foreach ($desc['e'] as $role) {
						$desc['role'][] = $role;
						}
					} else {
					$desc['role'] = $desc['e'];
					}
				}
				
		if (!empty($desc['7'])) {
			$desc['oid'] = $desc['7'];	
			$desc['id'] = $desc['7'];	
			}
		if (!empty($desc['0'])) {
			$desc['oid'] = $desc['0'];	
			$desc['id'] = $desc['0'];	
			}
			
		if (!empty($desc['i'])) {
			$desc['relation'] = $desc['i'];	
			}
		if ((!empty($desc['role'])) && is_array($desc['role']))
			array_unique($desc['role']);
		
		if (!empty($desc['wikiq'])) {
			$desc['field'] = "personBoxQ".$desc['wikiq'];
			$this->bottomLists->persons[$desc['wikiq']] = $desc;
			} else {
			$desc['field'] = "personBoxB".hash('crc32b',$desc['name'].$desc['date']);	
			}
		#echo "preparePerson<pre>".print_R($desc,1).'</pre>';	
		
		}
	
	public function personFromStr($str) {
		$rec = explode('|', $str); 
		$desc['name'] = $rec[0];
		$desc['year_born'] = $rec[1];
		$desc['year_death'] = $rec[2];
		$desc['viaf_id'] = $rec[3];
		$desc['wikiq'] = trim($rec[4]);
		$desc['date'] = trim($rec[5]);
		if (!empty($desc['wikiq'])) {
			$desc['field'] = "personBoxQ".$desc['wikiq'];
			} else {
			$desc['field'] = "personBoxB".hash('crc32b',$desc['name'].$desc['date']);	
			}
		return $desc;
		}
	
	
	public function getMainAuthor($result = '') {
		$desc = [];
		#if (!empty($this->solrRecord->author)) 
		#	$desc = $this->personFromStr($this->solrRecord->author[0]);
		$desc = $this->getMarcFirst('100', []);
		#$sumDesc = array_merge($desc,$desc2);
		$this->preparePerson($desc);
		$this->MainAuthor = $desc;
			
		return $desc;	
		}
	
	public function getMainAuthorLink() {
		$desc = $this->getMainAuthor();
		
		if (!empty($desc)) {
			return $this->cms->render('record/author-link.php',['author' => $desc ]);
			} else 
			return null;
		}
	
	public function getSubjectPersons() { //subject_person_str_mv
		$auth = $this->cms->getConfig('author-classification');
		$desc = [];
		if (!empty($this->solrRecord->subject_person_str_mv)) 
			foreach ($this->solrRecord->subject_person_str_mv as $personStr)
				$desc = $this->personFromStr($personStr);
		
		$desc2 = $this->getMarcFirst('100', []);
		$sumDesc = array_merge($desc,$desc2);
		$this->preparePerson($sumDesc);
		
		
		$field = 600;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $this->cms->render('record/author-link.php',['author' => $desc ]);
				
				} 
				
			return $authors;	
			} else 
			return null;
		}
	
	public function getOtherAuthors() {
		$field = 700;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $this->cms->render('record/author-link.php',['author' => $desc ]);
				}
				
			return $authors;	
			} else 
			return null;
		}
	
	public function getPublisher() {
		if (!empty($this->solrRecord->corporate_publisher_fs_wiki) ) {
			$corporations = $this->corporateToObjects('corporate_publisher_fs_wiki');
			return $this->cms->render('record/corporate-link.php',['corporations' => $corporations ]);
			} 
		return null;
		}
	public function getCorporateAuthor() {
		
		if (!empty($this->solrRecord->corporate_author_fs_wiki) ) {
			$corporations = $this->corporateToObjects('corporate_author_fs_wiki');
			return $this->cms->render('record/corporate-link.php',['corporations' => $corporations ]);
			}
		return null;
		}
		
	
	public function getMainAuthorName() {
		$desc = $this->getMainAuthor();
		if (!empty($desc)) {
			return $this->cms->render('record/author-name.php',['author' => $desc ]);
			} else 
			return null;
		}
		
	
	public function corporateToObjects($field) {
		$array = $this->solrRecord->$field;
		$return = [];
		if (is_string($array)) {
			$t[] = $array;
			$array = $t;
			}
		if (is_array($array)) 
			foreach ($array as $str) {
				$tmp = explode ('|', $str); 
				$res = new stdclass;
				$res->name = $tmp[0];
				$res->viaf = $tmp[1];
				$res->wikiq = $tmp[2]; 
				$res->roles = explode(',', $tmp[3]);
				$res->field = $field;
				$res->solr_str = $str;
				$return[] = $res;
				}
		return $return;
		}
	

	public function getEventAuthor() {
		$desc = $this->getMarcFirst('111');
		if (!empty($desc)) {
			
			$auth = $this->cms->getConfig('author-classification');
			
			$desc['name'] = '';
			if (!empty($desc['a'])) 
				$desc['name'] = $desc['a'];
			if (!empty($desc['c'])) 
				$desc['name'] .= ' '.$desc['c'];
			if (!empty($desc['d'])) 
				$desc['date'] = $desc['d'];
			if (!empty($desc['4'])) {
				$desc['role_code'] = $desc['4'];
				$desc['role'] = $auth['RelatorSynonyms'][$desc['4']];
				}
			if (!empty($desc['7'])) {
				$desc['oid'] = $desc['7'];	
				}
			if (!empty($desc['0'])) {
				$desc['oid'] = $desc['0'];	
				}
			
			#echo "<pre>".print_r($auth,1)."</pre>";
			#echo "<pre>".print_r($desc,1)."</pre>";
			return $this->cms->render('record/event-author-link.php',['author' => $desc ]);
			} else 
			return null;
		}
	
		
	public function getOtherAuthorsData() {
		$field = 700;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $desc ;
				}
			return $authors;	
			} else 
			return null;
		
		
		}
	
	
	public function getRelations() {
		$auth = $this->cms->getConfig('author-classification');
		$field = 500;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $this->cms->render('record/author-link.php',['author' => $desc ]);
				
				} 
				
			return $authors;	
			} else 
			return null;
		
		
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
		
	public function getImage() {
		
		$field = 856;
		$pic = '';
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$img = '';
			$title = '';
			foreach ($line as $obj) {
				$desc = $obj->code;
				#echo "<pre>".print_r($desc,1)."</pre>";
				
				if ( (!empty($desc->y)) & (($desc->y=='Signature')or($desc->y=='Image')) & (!stristr($desc->u, 'file:')) ) {
					 
					$img = $desc->u;
					$title = $this->transEsc($desc->y.' of').' '.$this->MainAuthor['name'];
					$picB = base64_encode('<div class="text-center"><img src="'.$img.'" title="'.$title.'" class="img img-responsive"></div>');
					$OC = "OnClick=\"results.InModal('$title','$picB');\"";
					$pic .= '<img src="'.$img.'" title="'.$title.'" style="cursor:pointer;" '.$OC.'>';
					}
				}
			} 
		if ($pic == '') {
			$img = $this->HOST.'themes/default/images/no_face.jpg';
			$title = $this->transEsc('no image found');
			$pic = '<img src="'.$img.'" title="'.$title.'" style="opacity:0.2">';
			}
		
		return $pic;
		}
	

	public function getSound() {
		
		$field = 856;
		$oog = '';
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			
			$line = $this->record->marcFields->$field;
			$img = '';
			$title = '';
			foreach ($line as $obj) {
				$desc = $obj->code;
				#echo "<pre>".print_r($desc,1)."</pre>";
				
				if ( (!empty($desc->y)) & ($desc->y=='Audio pronounciation') & (!stristr($desc->u, 'file:')) ) {
					 
					$aud = $desc->u;
					$title = $this->transEsc($desc->y.' of').' '.$this->MainAuthor['name'];
					$oog .= ' <audio controls>
							  <source src="'.$aud.'" type="audio/ogg">
							'.$this->transEsc('Your browser does not support the audio element.').'
							</audio> ';
					}
				}
				
			return $oog;
			}
		return null;	
		}

	public function getMajorFormat() {
		$formats = [
			'a' => 'Book chapter',
			'b' => 'Journal article',
			'm' => 'Book'
			];
		$code = substr($this->getLeader(), 7, 1);	
		if (array_key_exists($code, $formats))
			return $formats[$code];
			else 
			return 'other';
		}
	
	public function getFormat() {
		$formats = [
			'a' => 'Book chapter',
			'b' => 'Journal article',
			'm' => 'Book'
			];
		$code = substr($this->getLeader(), 7, 1);	
		if (array_key_exists($code, $formats))
			return $formats[$code];
			else 
			return 'Other';	
		}

	public function getFormatTranslated() {
		return $this->cms->transEsc($this->getFormat());
		}
	
	public function translateLangCode($code) {
		#$code = $this->str2url($code);
		$Tmap = $this->Lang;
		if (!empty($Tmap[$code])) { 
			#echo $Tmap[$code].".";
			return $Tmap[$code];
			} else {
			#echo $code.".";
			if (stristr($code,'\\'))
				return 'undefined';
				else 
				return $code;
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
		
	
	
	public function getLanguage() {
		
		$langs = [];
		if (!empty($this->record->solrRecord->language)) {
			$langs = $this->record->solrRecord->language;
			} else {
			
			$langs = [];
			
			$field = '008';
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field[0])) ) {
				$z = substr($this->record->marcFields->$field[0],35,3);
				$langs[$z] = $this->translateLangCode($z);	
				}
			$field = '041';
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
				foreach ($this->record->marcFields->$field as $sf) {
					
					if (!empty($sf->code->a))
						if (is_Array($sf->code->a))
							foreach ($sf->code->a as $z) {
								$langs[$z] = $this->translateLangCode($z);	
								}
							else {
							$z = $sf->code->a;
							$langs[$z] = $this->translateLangCode($z);	
							}
							
						
					if (!empty($sf->code->h))
						if (is_Array($sf->code->h))
							foreach ($sf->code->h as $z) {
								$langs[$z] = $this->translateLangCode($z);		
								} 
							else {
							$langs[$z] = $this->translateLangCode($z);	
							}
					}
				}
			$field = '040';
			if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
				foreach ($this->record->marcFields->$field as $sf) {
					
					if (!empty($sf->code->b))
						if (is_Array($sf->code->b))
							foreach ($sf->code->b as $z) {
								$langs[$z] = $this->translateLangCode($z);	
								}
							else {
							$z = $sf->code->b;
							$langs[$z] = $this->translateLangCode($z);	
							}
					}
				}
			
			}
		
		$TNlangs = [];
		foreach ($langs as $lang) {
			if ($lang<>'undefined')
				$TNlangs[] = $lang;
			}
		if (count($TNlangs)>0)
			$langs = $TNlangs;
		return $this->removeArrayKeys($langs);	
		}
		
		
	public function getGenre() { 
		$field = '655';
		$res = [];
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
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
	
	public function getPublished() {
		$field = '260';
		$res = [];
		$lp = 0;
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				$lp++;
				$res[$lp] = '';
				foreach ($sf->code as $z) {
					if (is_array($z))
						$res[$lp] .= ' '.implode(' ',$z);
						else 
						$res[$lp] .= ' '.$z;
					}	
				}
			}
			
		return $res;	
		}
	
	public function getEdition() {
		$field = '250';
		$res = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				foreach ($sf->code as $z) {
					$res[] .= $z;
					}	
				}
			}
			
		return implode(' ',$res);	
		}
	
		
		
	public function getIn() {
		$field = '773';
		$res = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				$sres = [];
				if (!empty($sf->code->t) && !empty($sf->code->s) && (trim(str_replace('.','',$sf->code->t))==trim($sf->code->s)))
					unset($sf->code->s);
				
				foreach ($sf->code as $k=>$z) {
					if (is_Array($z))
						$z = implode(', ', $z);
					switch ($k) {
						
						case 'x' : 
							$sres[]= 'ISSN '.$z; 
							$this->record->ISSN = $z;
							break;
						case 'i':
						case 'q':
						case '7':
						case 'w':
						case 's':
						case '9':
							break;
						default: $sres[]= $z; break;
						}
					
					}	
				$res[] = implode(' ',$sres);	
				}
			}
		return implode('<br/>',$res);	
		}
		

	
	public function getRefferedWork() {
		$field = 787;
		$rec = [];
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			
			$row = (array)current($line);
			foreach ($line as $row) {
				$n = '';
				if (!empty($row->code->a)) 
					$n = $row->code->a;
				if (!empty($row->code->t)) 
					$n .= ' <b>'.$row->code->t.'</b>';
					
					 
				$rec[] = $n;
				}
			return $rec;		
			}
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
										$ln[] = "<a href=\"{$this->cms->baseUrl('search/results')}?type=Subject&lookfor=".implode('+',$uri)."\">$sz</a>"; break;
										}
									} else {	
									$uri[] = urlencode($z);
									$ln[] = "<a href=\"{$this->cms->baseUrl('search/results')}?type=Subject&lookfor=".implode('+',$uri)."\">$z</a>"; break;
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
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			$line = $this->record->marcFields->$field;
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
	
	public function getCoreFields($core = 'biblio') {
		$coreFields['biblio'] = array (
			'getStatmentOfResp' => 'Statement of Responsibility',
			'getMainAuthorLink' => 'Main Author', 
			'getEventAuthor' => 'Event as Author', 
			'getCorporateAuthor' => 'Corporate Author', 
			'getOtherAuthors' => 'Other Authors', 
			# 'getPublisher' => 'Publisher', 
			'getFormatTranslated' => 'Format',
			'getLanguage' => 'Language',
			'getGenre' => 'Form / Genre',
			'getPublished' => 'Published',
			'getEdition' => 'Edition',
			'getIn' => 'In',
			'getRefferedWork' => 'Referred work',
			'getSeria' => 'Seria',
			'getSubjectPersons' => 'Subject persons',
			'getSubjects' => 'Subjects',
			
			);
		#'getTags' => 'Tags'
			
		$result = array();
		foreach ($coreFields[$core] as $k=>$v) {
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
		if ($this->getMajorFormat()=='Journal article') {
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
				'rft.date' 		=> $this->getPublishDate(),
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
				'rft.format' 	=> $this->getFormat(),
				'rft.language' 	=> $this->getLanguages()
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
		if (!empty($this->record->marcFields)) {
			$result = '<table class="table table-striped">
					<thead><tr><td style="text-align:right"><b>LEADER</b></td><td colspan=3>'.$this->record->LEADER.'</td></tr></thead>
					<tbody>
					';
			foreach ($this->record->marcFields as $field=>$subarr) {
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
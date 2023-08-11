<?php

#require_once 'File/MARC.php';


class person extends CMS {
	
	
	public function __construct($fullRecord) {
		
		parent::__construct();
		
		if (empty($fullRecord))
			$fullRecord = new stdClass;
		$fullRecord->LEADER = '01422nz--a2200301n--450'; // ustalony (z PH:202204 @ google chat) staly dla osób 
		$this->record = new stdclass;
		$this->record = $fullRecord;
		$this->record->marcFields = new stdclass; // $this->record; 
		
		$file = file ('./languages/lang_codes.csv'); 
		foreach ($file as $line) {
			$ln = explode('|', $line);
			$this->Lang[$ln[0]] = $ln['2']; // ISO 639-2 Code
			$this->lang[$ln[1]] = $ln['2']; // ISO 639-1 Code
			}
		
		#echo "<pre>".print_r($this->record->marcFields, 1). "</pre>";
		}
	
	public function getName() {
		if (!empty($this->record->names->{$this->userLang}->name))
			return $this->record->names->{$this->userLang}->name;
		if (!empty($this->record->names->en->name))
			return $this->record->names->en->name;
		if (!empty($this->record->names)) {
			$cn = current($this->record->names);
			return $cn->name;
			}
		return 'no name';	
		}	
	
	public function getLName() {
		if (!empty($this->record->lname))
			return $this->record->lname;
		$tmp = explode(' ',$name = $this->getName());
		$lname = end($tmp);
		return trim($lname.', '.str_replace($lname,'',$name));	
		}
		
	public function getDescription () {
		if (!empty($this->record->desc->{$this->userLang}))
			return $this->record->desc->{$this->userLang};
		if (!empty($this->record->desc->en))
			return $this->record->desc->en;
		if (!empty($this->record->desc)) {
			return current($this->record->desc);
			}
		
		$desc = $this->getMarcLine('520', ['a']);
		
		if (!empty($desc))
			return $desc;
			else 
			return null;
		}	

	/*
	########################################################################################
	## 								MARC21 functions 
	########################################################################################
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
			return null;
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
	

	
	public function createGoogleLink($person) {
		if (!empty($person['name']))
			return 'https://www.google.com/search?q='.urlencode($person['name']);
		}
	
	public function createViafLink($person) {
		if (!empty($person['name']))
			return 'https://www.google.com/search?q='.urlencode($person['name']);
		}
	
	public function createWikiLink($person) {
		if (!empty($person['name']))
			return 'https://www.google.com/search?q='.urlencode($person['name']);
		}
	
	public function createLibriLink() {
		return $this->basicUri().'persons/record/'.$this->urlName($this->getName()).'/viaf_id'.$this->record->viafid.'/';
		}
	
	public function preparePerson(&$desc) {
		$auth = parent::getConfig('author-classification');
			
		$desc['name'] = '';
		if (!empty($desc['a'])) 
			$desc['name'] = $desc['a'];
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
					$desc['role'][] = $auth['RelatorSynonyms'][$role];
					}
				} else {
				$desc['role_code'] = $desc['4'];
				$desc['role'] = $auth['RelatorSynonyms'][$desc['4']];
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
			
		if (!empty($desc['1'])) {
			$this->checkViafId($desc);
			}
	
		if ((!empty($desc['role'])) && is_array($desc['role']))
			array_unique($desc['role']);
		if (!empty($desc['1'])) {
			$this->checkViafId($desc);
			}
		if (empty($desc['date']))
			$desc['date'] = '';
		
		}
	
	
	public function getMainAuthor() {
		$desc = $this->getMarcFirst('100', []);
		if (!empty($desc)) {
			
			
			$this->preparePerson($desc);
			
			#echo "<pre>".print_r($auth,1)."</pre>";
			#echo "<pre>".print_r($desc,1)."</pre>";
			$this->MainAuthor = $desc;
			return $desc;
			} else 
			return null;
		}
	
	public function getMainAuthorLink() {
		$desc = $this->getMainAuthor();
		
		if (!empty($desc)) {
			return $this->render('record/author-link.php',['author' => $desc ]);
			} else 
			return null;
		}
	
	public function getMainAuthorName() {
		$desc = $this->getMainAuthor();
		if (!empty($desc)) {
			return $this->render('record/author-name.php',['author' => $desc ]);
			} else 
			return null;
		}
		
		
	public function getRelations() {
		$auth = $this->getConfig('author-classification');
		$field = 500;
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			
			$line = $this->record->marcFields->$field;
			$result = '';
			$authors = [];
			foreach ($line as $author) {
				$desc = (array)$author->code;
				$this->preparePerson($desc);
				$authors[] = $this->render('record/author-link.php',['author' => $desc ]);
				
				} 
				
			return $authors;	
			} else 
			return null;
		
		
		}
	
	
	public function getLinkPanel($addLink = '') {
		if (!empty($this->record->ids->wikiId))
			$wikiLink = '
					<li><a href="https://www.wikidata.org/wiki/'.$this->record->ids->wikiId.'" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>
					<li><a href="'.$this->basicUri().'/wiki/record/'.$this->record->ids->wikiId.'" title="WikiData on Libri"><i class="ph-address-book-bold"></i></a></li>
					';
			else 
			$wikiLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>';	
		if ($addLink!=='')
			$addLink = '<li>'.$addLink.'</li>';
		return '
			<div class="bulkActionButtons">
				<ul class="action-toolbar">
					<li><a href="https://www.google.com/search?q='.urlencode($this->getName()).'" title="Google"><i class="ph-google-logo-bold"></i></a></li>
					'.$wikiLink.'
					<li><a href="https://viaf.org/viaf/'.$this->record->viafid.'" title="VIAF"><i class="ph-identification-card-bold"></i></a></li>
					<li><a href="'.$this->createLibriLink().'" title="more with libri"><i class="ph-user-focus-bold"></i></a></li>
					'.$addLink.'
				</ul>
			</div>';
		}
	
	public function getLinkPanelBig($addLink = '') {
		if (!empty($this->record->ids->wikiId))
			$wikiLink = '
					<li><a href="https://www.wikidata.org/wiki/'.$this->record->ids->wikiId.'" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>
					<li><a href="'.$this->basicUri().'/wiki/record/'.$this->record->ids->wikiId.'" title="WikiData on Libri"><i class="ph-address-book-bold"></i></a></li>
					';
			else 
			$wikiLink = '<li><a style="opacity:0.2; filter: grayscale(100%);" title="WikiData"><i class="glyphicon glyphicon-barcode"></i></a></li>';	
		if ($addLink!=='')
			$addLink = '<li>'.$addLink.'</li>';
		return '
			<div class="bulkActionButtons">
				<ul class="action-toolbar">
					'.$wikiLink.'
					<li><a href="https://viaf.org/viaf/'.$this->record->viafid.'" title="VIAF"><i class="ph-identification-card-bold"></i></a></li>
					<li><a href="https://www.google.com/search?q='.urlencode($this->getName()).'" title="Google"><i class="ph-google-logo-bold"></i></a></li>
					'.$addLink.'
				</ul>
			</div>';
		}
	
	public function getImage() {
		
		$img = $this->HOST.'themes/default/images/no_photo.svg';
		$title = $this->transEsc('no image found'); 
		$pic = '<img src="'.$img.'" title="'.$title.'" style="opacity:0.2;  filter: grayscale(100%);">';
		
		if (!empty($this->record->media->image)) {
			$pic = '';
			$om = current($this->record->media->image);
			$img = $om->url;
			$title = $this->transEsc($om->tom.' of').' '.$this->getName();
			$picB = base64_encode('<div class="text-center"><img src="'.$img.'" title="'.$title.'" class="img img-responsive"></div>');
			$OC = "OnClick=\"results.InModal('$title','$picB');\"";
			$pic = '<img src="'.$img.'" title="'.$title.'" style="cursor:pointer;" '.$OC.'>';
			return $pic;
			}
		
		
		$field = 856;
		
		if (!empty($this->record->marcFields) && is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field))) {
			$pic = '';
			$line = $this->record->marcFields->$field;
			$img = '';
			$title = '';
			foreach ($line as $obj) {
				$desc = $obj->code;
				#echo "<pre>".print_r($desc,1)."</pre>";
				
				if ( (!empty($desc->y)) & (($desc->y=='Signature')or($desc->y=='Image')) & (!stristr($desc->u, 'file:')) ) {
					 
					$img = $desc->u;
					$title = $this->transEsc($desc->y.' of').' '.$this->getName();
					$picB = base64_encode('<div class="text-center"><img src="'.$img.'" title="'.$title.'" class="img img-responsive"></div>');
					$OC = "OnClick=\"results.InModal('$title','$picB');\"";
					$pic .= '<img src="'.$img.'" title="'.$title.'" style="cursor:pointer;" '.$OC.'>';
					}
				}
			} 
		
		return $pic;
		}	
	
	public function getImageBG() {
		
		$img = $this->HOST.'themes/default/images/no_photo.svg';
		$title = $this->transEsc('no image found'); 
		$pic = '<div style="background-image: url('.$img.'); opacity:0.2;  filter: grayscale(100%);width:100%; height:100%; background-size: contain; background-position: center; background-repeat:no-repeat;" title="'.$title.'" ></div>';
		
		if (!empty($this->record->media->image)) {
			$pic = '';
			$om = current($this->record->media->image);
			$img = $om->url;
			$title = $this->transEsc($om->tom.' of').' '.$this->getName();
			$picB = base64_encode('<div class="text-center"><img src="'.$img.'" title="'.$title.'" class="img img-responsive"></div>');
			$OC = "OnClick=\"results.InModal('$title','$picB');\"";
			$pic = '<div style="background-image: url('.$img.'); cursor:pointer; width:100%; height:100%; background-size:cover; background-position:top center;" title="'.$title.'" '.$OC.'></div>';
			return $pic;
			}
		
		return $pic;
		}
	

	public function getSound() {
		
		$field = 856;
		$oog = '';
		
		if (!empty($this->record->marcFields) && is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			
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

	
	public function getFormat() {
		$formats = [
			'ab' => 'Article',
			'am' => 'Book',
			'aa' => 'Book Chapter',
			'cm' => 'Musical Score', 
			'km' => 'Photo', 
			'as' => 'Serial', 
			'em' => 'Map',
			'gb' => 'Slide'
			];
		$code = substr($this->getLeader(), 6, 2);	
		if (!empty($this->GetMarcFirst(111)))
			$cp = ' <label class="label label-primary">'.$this->transEsc('Conference Proceeding').'</label>';
			else 
			$cp = '';
		if (array_key_exists($code, $formats))
			return '<label class="label label-primary">'.$this->transEsc($formats[$code]).'</label>'.$cp;
			else 
			return '<label class="label label-danger">'.$code.'</label>'.$cp;
			
		}
		
		
	public function getLanguage() {
		
		$field = '008';
		$langs = [];
		
	
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field[0])) ) {
			$z = substr($this->record->marcFields->$field[0],35,3);
			if (!empty($this->Lang[$z]))
				$langs[$z] = $this->Lang[$z];
				else
				$langs[$z] = $z;	
			}
		$field = '041';
		
		if (is_object($this->record->marcFields) && (!empty($this->record->marcFields->$field)) ) {
			foreach ($this->record->marcFields->$field as $sf) {
				if (!empty($sf->code->a) && is_Array($sf->code->a))
					foreach ($sf->code->a as $z) {
						if (!empty($this->Lang[$z]))
							$langs[$z] = $this->Lang[$z];
							else
							$langs[$z] = $z;	
						}
					
				if (!empty($sf->code->h) && is_Array($sf->code->h))
					foreach ($sf->code->h as $z) {
						if (!empty($this->Lang[$z]))
							$langs[$z] = $this->Lang[$z];
							else
							$langs[$z] = $z;	
						}
					}
			}
			
		#echo "<pre>".print_R($langs,1)."</pre>";	
			
		return $langs;	
		}
		
		
	######################## persons 
	
	public function getDateRange() {
		if (!empty($this->record->ldate))
			return $this->record->ldate;
		
		if (!empty($this->record->dates->range))
			return $this->record->dates->range;
		}
	
	public function dateFormat($d) {
		$d = str_replace(['-', '.'], '', $d);
		#echo "$d<br><br>";
		$year = substr($d,0,4);
		$month =substr($d,4,2);
		$day = substr($d,6,2);
		
		$retDate = $year;
		if ((floatval($month)>0)&&(floatval($month)<13))
			$retDate .='-'.$month;
		if ((floatval($day)>0)&&(floatval($day)<31))
			$retDate .='-'.$day;
		
		return $retDate;
		}
	
		
	public function getDateOfBirth() {
		if (!empty($this->record->dates->birth))
			return $this->dateFormat($this->record->dates->birth);
		
		$desc = $this->getMarcFirst('046');
		if (!empty($desc['f'])) {
			$d = $desc['f'];
			return $this->dateFormat($d);
			}
			
		}
	public function getDateOfDeath() {
		if (!empty($this->record->dates->death))
			return $this->record->dates->death;
		
		$desc = $this->getMarcFirst('046');
		if (!empty($desc['g'])) {
			$d = $desc['g'];
			return $this->dateFormat($d);
			}
	
		}
	
	public function getPlaceOfBirth() {
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
			foreach ($line as $row) 
				if (!empty($row->code->a)) {
					$rec[] = $row->code->a;
					
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
	
	public function getPre() {
		return print_R($this->record,1);
		}
	
	public function drawMarc() {
		return "<div class='alert alert-info'>This is an automatic unverified record. <br/> There is no MARC21 data for this record.</div>";
		}
	
	public function getCoreFields() {
		$coreFields = array (
			'getDateOfBirth' => 'Date of Birth',
			'getPlaceOfBirth' => 'Place of Birth',
			'getDateOfDeath' => 'Date of Death',
			'getPlaceOfDeath' => 'Place of Death',
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
		
	}

?>
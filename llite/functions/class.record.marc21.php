<?php 

class marc21 {
	
	public $record;
	public $errors = [];
	public $defaultValues;
	public $relRec;
	
	
	public function __construct() {
		$this->relRec = new stdClass;
		$this->defaultValues = new stdClass;
		}
	
	public function loadRecord($record) {
		$this->record = $record;
		$this->errors = [];
		}
	
	public function loadDefaultValues($psql) {
		$this->defaultValues->languages = [];
		$this->defaultValues->majorFormat = [];
		$this->defaultValues->format = [];
		
		$t = $psql->querySelect("SELECT DISTINCT id,iso639_2,iso639_1 FROM dic_languages;");
		if (is_array($t)) {
			foreach ($t as $row) {
				$this->defaultValues->languages[] = $row['iso639_2'];
				$this->defaultValues->languagesConvert1[$row['iso639_1']] = $row['id'];
				$this->defaultValues->languagesConvert2[$row['iso639_2']] = $row['id'];
				}
			} 
		
		}
	
	
	
	public function getErrors() {
		return $this->errors ?? [];
		}
	
	public function getId() {
		$field = '001';
		if (!empty($this->record->$field)) {
			if (count($this->record->$field)>1)
				$this->errors[] = 'Array in 001';
			foreach ($this->record->$field as $line)
				return $line;
			}
		}
	
	public function getFormatMajorCode() {
		$this->relRec->majorFormat = strtolower(substr($this->record->LEADER, 7, 1));
		return $this->relRec->majorFormat;
		}
	
	public function getFormatCode() {
		return strtolower(substr($this->record->LEADER, 6, 2));	
		}
	
	public function getTitle() {
		$field = '245';
		if (!empty($this->record->$field)) {
			if (count($this->record->$field)>1)
				$this->errors[] = 'Repeated 245';
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->a)) {
					if (is_array($line->code->a)) {
						$this->errors[] = 'Repeated 245a';
						$line->code->a = current($line->code->a);
						} 
					return $this->clearLastChar($line->code->a);
					}
				}
			}
		}
	
	public function getTitleSupplemental() {
		$field = '245';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->b)) {
					if (is_array($line->code->b)) {
						$this->errors[] = 'Repeated 245b';
						$line->code->b = current($line->code->b);
						}
					return $line->code->b;
					}
				}
			}
		}
	
					   
	public function getStatementOfResponsibility() {
		$field = '245';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->c)) {
					if (is_array($line->code->c)) {
						$this->errors[] = 'Repeated 245c';
						$line->code->c = current($line->code->c);
						}
					return $line->code->c;
					}
				}
			}
		}
	
	public function getLanguageId($value) {
		$code = $this->onlyLetters($value);
		if ((strlen($code) == 2) && !empty($this->defaultValues->languagesConvert1[$value]))
			return $this->defaultValues->languagesConvert1[$value];
		if ((strlen($code) == 3) && !empty($this->defaultValues->languagesConvert2[$value]))
			return $this->defaultValues->languagesConvert2[$value];
		return null;
		}
	
	public function getLanguageCodes() {
		$language = [];
		$publication = [];
		$record = [];
		$original = [];
		$unrecognized = [];
		$recognized = [];
		$field = '008';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				
				$quot = quotemeta($line);
				$cleanedString = str_replace('\\', '-', $line);
				$value = substr($cleanedString, 35, 3);  // code 3 char 
				$unrecognized['publication'][] = $value;
				}
			}
		$field = '040';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->b)) {
					$arr = (array)$line->code->b; // code 3 char
					foreach ($arr as $value) {
						$unrecognized['record'][] = $value;
						}
					}
				}
			}
		$field = '041';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->a)) {
					$arr = (array)$line->code->a; // code 3 char
					foreach ($arr as $value) {
						$unrecognized['publication'][] = $value;
						}
					}
				if (!empty($line->code->h)) {
					$arr = (array)$line->code->h; // code 3 char
					foreach ($arr as $value) {
						$unrecognized['original'][] = $value;
						}
					}
				
				}
			}
		foreach ($unrecognized as $k=>$v)
			$unrecognized[$k] = array_unique($v);
			
		return $unrecognized;
		}
	
	function validateDate($date, $format = 'Y-m-d H:i:s') {
		$d = DateTime::createFromFormat($format, $date);
		return $d && $d->format($format) == $date;
		#return checkdate($m,$d,$y);
		}
	
	public function getPublicationDate() {
		$publicationYear = [];
		$publicationDate = [];
		$publicationDateStr = [];
		$field = '008';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				$cleanedString = str_replace('\\', '-', quotemeta($line));
				$dateStr = substr($cleanedString, 7, 8);
				$year = substr($dateStr,0,4);
				$month = substr($dateStr,4,2);
				$day = substr($dateStr,6,2);
				if (is_numeric($year)) {
					$publicationYear[$dateStr] = $year;
					if (is_numeric($month) & is_numeric($day) && ($month!=99 or $day!=99)) {
						$fulldate = $year.'-'.$month.'-'.$day;
						if ($this->validateDate($fulldate))
							$publicationDate[$dateStr] = $fulldate;
							else 
							$this->errors[] = "In field 008, there are numbers in place of the publication date, but they do not form a correct date.";
						}
					}
				}
			}
		$fields = ['260', '264'];
		foreach ($fields as $field)
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) {
					if (!empty($line->code->c)) {
						$testVal = (array)$line->code->c;
						foreach ($testVal as $testStr) {
							$publicationDateStr[] = $testStr;
							if (strlen($testStr)<4) 
								$publicationYear[] = $this->onlyNumbers($testStr);
								else 
								$publicationYear[] = $this->yearFromStr($testStr);	
							}
						}
					}
				}
		return (object)[
			'year' => array_unique($publicationYear),
			'date' => array_unique($publicationDate),
			'string' => array_unique($publicationDateStr),
			];							
		
		
		}
	
	public function getISBN() {
		$ISBN = [];
		$field = '020';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->a)) {
					$arr = (array)$line->code->a; 
					foreach ($arr as $value) {
						$ISBN[] = $value;
						}
					}
				}
			}
		$field = '776';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->z)) {
					$arr = (array)$line->code->z; 
					foreach ($arr as $value) {
						$ISBN[] = $value;
						}
					}
				}
			}
		return array_unique($ISBN);	
		}
	
	public function getISSN() {
		$ISSN = [];
		$fieldsWithX = [
			'773' => 'host', 
			'776' => 'equal', 
			'780' => 'Predecessor',
			'785' => 'Successor',
			'730' => 'related'
			];
		
		$field = '022';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->a)) {
					$arr = (array)$line->code->a; 
					foreach ($arr as $value) {
						$ISSN[] = $value;
						}
					}
				}
			}
		$field = '773';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->x)) {
					$arr = (array)$line->code->x; 
					foreach ($arr as $value) {
						$ISSN[] = $value;
						}
					}
				}
			}
		return array_unique($ISSN);	
		}
	
	public function getUDC() {
		$udc = [];
		$field = '080';
		if (!empty($this->record->$field)) {
			foreach ($this->record->$field as $line) {
				if (!empty($line->code->a)) {
					$arr = (array)$line->code->a; 
					foreach ($arr as $value) {
						$code = $this->onlyNumbers($value);
						$l = substr($code,0,1);
						if ($l == 5) {
							$k = substr($code,1,1);
							if ($k == 1)
								$udc[] = '51';
								else 
								$udc[] = '5x';											
							} else if (is_numeric($l) && ($l<>4))
								$udc[] = $l;
						}
					}
				}
			}
		return array_unique($udc);
		}
	
	public function getPersons() {
		$persons = [];
		$subFieldMatching = [
			'a' => 'name',
			'b' => '+name',
			'd' => 'dates'
			];
		
		$idSubFields = [
			'0', '1', '7'
			];
		
		$fields = [
			'100' => 'mainAuthor',
			'600' => 'subjectPerson',
			'700' => 'coAuthor',
			];
		
		foreach ($fields as $field=>$personRole)
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) {
					
					$person['name'] = 
					$person['dates'] = '';
					$person['ids'] = [];
					$person['mainrole'] = $personRole;
					$person['creator_roles_code'] = [];
					$person['creator_roles_str'] = [];
			
					foreach ($subFieldMatching as $subField=>$writeTo)
						if (!empty($line->code->$subField)) {
							if (is_array($line->code->$subField)) {
								$line->code->$subField = current($line->code->$subField);
								$this->errors[] = 'Repeated '.$field.$subField; 
								}
							if (substr($writeTo,0,1) == '+') {	
								$writeTo =  $str = substr($writeTo, 1);
								$person[$writeTo] .= trim($line->code->$subField);
								} else 
								$person[$writeTo] = trim($line->code->$subField);
							}

					foreach ($idSubFields as $subField)
						if (!empty($line->code->$subField)) {
							$line->code->$subField = (array)$line->code->$subField;
							foreach ($line->code->$subField as $idValue)
								$person['ids'][] = $idValue;
							}
					
					$subField = '4';
					if (!empty($line->code->$subField)) {
						$line->code->$subField = (array)$line->code->$subField;
						foreach ($line->code->$subField as $code)
							if (!empty($code))
								$person['creator_roles_code'][] = $code;
								else 
								$this->errors[] = 'exists '.$field.$subField.' but is empty';
						}	
					$subField = 'e';
					if (!empty($line->code->$subField)) {
						$line->code->$subField = (array)$line->code->$subField;
						foreach ($line->code->$subField as $code)
							if (!empty($code))
								$person['creator_roles_str'][] = $code;
								else 
								$this->errors[] = 'exists '.$field.$subField.' but is empty';
						}	
					if (empty(trim($this->sanitizeString($person['name']))))
						$this->errors[] = 'exists '.$field.' but there is no person name';
					$persons[$personRole][] = $person;
					}
				}
		return $persons;
		}
	
	public function getCorporates() {
		$items = [];
		
		$idSubFields = [
			'0', '1', '7'
			];
		
		$fields = [
			'110' => 'mainAuthor',
			'610' => 'subjectCorporate',
			'710' => 'coAuthor',
			];
		
		foreach ($fields as $field=>$itemRole)
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) 
					if (empty($line->code->n) & empty($line->code->d)) {
						$item['name'] = ''; 
						$item['ids'] = []; 
						$item['mainrole'] = $itemRole;
						$item['creator_roles_code'] = [];
						$item['creator_roles_str'] = [];
				
						if (!empty($line->code->a) && is_array($line->code->a)) {
							$line->code->a = current($line->code->a);
							$this->errors[] = 'Repeated '.$field.'a'; 
							}
						
						if (!empty($line->code->c)) {
							if (is_array($line->code->c)) {
								$line->code->c = current($line->code->c);
								$this->errors[] = 'Repeated '.$field.'c'; 
								}
							$item['name'] = trim(str_replace(['[',']'], '', $line->code->c));
							if (!empty($line->code->a))
								$item['creator_roles_str'][] = $line->code->a;
							} else if (!empty($line->code->a)) {
							$item['name'] = trim($line->code->a);	
							}

						foreach ($idSubFields as $subField)
							if (!empty($line->code->$subField)) {
								$line->code->$subField = (array)$line->code->$subField;
								foreach ($line->code->$subField as $idValue)
									$item['ids'][] = $idValue;
								}
						
						$subField = '4';
						if (!empty($line->code->$subField)) {
							$line->code->$subField = (array)$line->code->$subField;
							foreach ($line->code->$subField as $code)
								if (!empty($code))
									$item['creator_roles_code'][] = $code;
									else 
									$this->errors[] = 'exists '.$field.$subField.' but is empty';
							}	
						$subField = 'e';
						if (!empty($line->code->$subField)) {
							$line->code->$subField = (array)$line->code->$subField;
							foreach ($line->code->$subField as $code)
								if (!empty($code))
									$item['creator_roles_str'][] = $code;
									else 
									$this->errors[] = 'exists '.$field.$subField.' but is empty';
							}	
						$items[$itemRole][] = $item;
						}
				}
		return $items;
		}
	
	public function getEvents() {
		$items = [];
		$idSubFields = [
			'0', '1', '7'
			];
		
		$fields = [
			'110' => 'mainAuthor',
			'111' => 'mainAuthor',
			'610' => 'subjectEvent',
			'611' => 'subjectEvent',
			'710' => 'coAuthor',
			'711' => 'coAuthor',
			];
		
		foreach ($fields as $field=>$itemRole)
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) 
					if (!empty($line->code->n) or !empty($line->code->d)) {
						$event['name'] = 
						$event['year'] = 
						$event['edition'] = '';
						$item['ids'] = []; 
						$item['mainrole'] = $itemRole;
						$item['creator_roles_code'] = [];
						$item['creator_roles_str'] = [];
				
						if (!empty($line->code->a)) {
							if (is_array($line->code->a)) {
								$line->code->a = current($line->code->a);
								$this->errors[] = 'Repeated '.$field.'a'; 
								}
							$item['name'] = trim($line->code->a);
							}
						if (!empty($line->code->n)) {
							if (is_array($line->code->n)) {
								$line->code->n = current($line->code->n);
								$this->errors[] = 'Repeated '.$field.'n'; 
								}
							$item['edition'] = $this->onlyNumbers($line->code->n);
							}
						if (!empty($line->code->d)) {
							if (is_array($line->code->d)) {
								$line->code->d = current($line->code->d);
								$this->errors[] = 'Repeated '.$field.'d'; 
								}
							$item['year'] = $this->yearsFromString($line->code->d);
							}

						foreach ($idSubFields as $subField)
							if (!empty($line->code->$subField)) {
								$line->code->$subField = (array)$line->code->$subField;
								foreach ($line->code->$subField as $idValue)
									$item['ids'][] = $idValue;
								}
						
						$subField = '4';
						if (!empty($line->code->$subField)) {
							$line->code->$subField = (array)$line->code->$subField;
							foreach ($line->code->$subField as $code)
								if (!empty($code))
									$item['creator_roles_code'][] = $code;
									else 
									$this->errors[] = 'exists '.$field.$subField.' but is empty';
							}	
						$subField = 'e';
						if (!empty($line->code->$subField)) {
							$line->code->$subField = (array)$line->code->$subField;
							foreach ($line->code->$subField as $code)
								if (!empty($code))
									$item['creator_roles_str'][] = $code;
									else 
									$this->errors[] = 'exists '.$field.$subField.' but is empty';
							}	
						$items[$itemRole][] = $item;
						}
				}
		return $items;
		}
	
	public function getPlaces() {
		$items = [];
		$idSubFields = [
			'0', '1', '2', '7'
			];
		//  TO REMEMBER use: $this->placeHasDescription($line['code']['a']);
		$fields = [
			'260a' => ['publicationPlace'],
			'264a' => ['publicationPlace'],
			'651a' => ['subjectPlace'],
			'655z' => ['subjectPlace'], // country 
			'110c' => ['publicationPlace','eventPlace'],
			'111c' => ['publicationPlace','eventPlace'],  // need more conditions (magazine name or place name)
			'610c' => ['subjectPlace','eventPlace'], // citi + country (but not always)
			'611c' => ['subjectPlace','eventPlace'], 
			'710c' => ['eventPlace'], // need more conditions (magazine name or place name)
			'711c' => ['eventPlace'],
			];
		$moreConditions = ['710', '111'];	
		$takeIDS = ['651'];
		
		foreach ($fields as $fullField=>$itemRoles) {
			
			$field = substr($fullField, 0, 3);
			$subField = substr($fullField, -1);
			
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) {
					if (!in_array($field, $moreConditions) or (in_array($field, $moreConditions) & (!empty($line->code->n) or !empty($line->code->d)))) {
						if (!empty($line->code->$subField)) {
							if (is_array($line->code->$subField)) {
								foreach ($line->code->$subField as $rawName) {
									$item['name'] = 
									$item['ids'] = []; 
						
									$item['name'] = $this->sanitizeString($rawName);
									foreach ($itemRoles as $itemRole)
										$items[$itemRole][] = $item;
									}
								} else {
								$item['name'] = 
								$item['ids'] = []; 
						
								$item['name'] = $this->sanitizeString($line->code->$subField);
								if (in_array($field, $takeIDS))
									foreach ($idSubFields as $subField)
										if (!empty($line->code->$subField)) {
											$line->code->$subField = (array)$line->code->$subField;
											foreach ($line->code->$subField as $idValue)
												$item['ids'][] = $idValue;
											}
								foreach ($itemRoles as $itemRole)
									$items[$itemRole][] = $item;
						
								}
							}
						}
					}
				}
			}
		return $items;
		}
	
	
	function roman2number($roman){
		$conv = array(
			array("letter" => 'I', "number" => 1),
			array("letter" => 'V', "number" => 5),
			array("letter" => 'X', "number" => 10),
			array("letter" => 'L', "number" => 50),
			array("letter" => 'C', "number" => 100),
			array("letter" => 'D', "number" => 500),
			array("letter" => 'M', "number" => 1000),
			array("letter" => 0, "number" => 0)
		);
		$arabic = 0;
		$state = 0;
		$sidx = 0;
		$len = strlen($roman);

		while ($len >= 0) {
			$i = 0;
			$sidx = $len;
			while ($conv[$i]['number'] > 0) {
				if (strtoupper(@$roman[$sidx]) == $conv[$i]['letter']) {
					if ($state > $conv[$i]['number']) {
						$arabic -= $conv[$i]['number'];
					} else {
						$arabic += $conv[$i]['number'];
						$state = $conv[$i]['number'];
					}
				}
				$i++;
			}
			$len--;
		}
		return($arabic);
		}
	
	public function convertRomanNumberInStr($string) {
		return preg_replace_callback('/\b[0IVXLCDM]+\b/', function($m) {
			   return $this->roman2number($m[0]);
			   },$string);
		}

	
	public function parse773g($line) {
		// look for first 4-digist numer (or two number separated by "/")
		$results = [];
		if (preg_match('/\b(\d{4}(?:\/\d{4})?)\b/', $line, $year_match)) {
			$rok_wydania = $year_match[1]; 
			
			// first number before publishYear should be volume
			$pattern_before_year = '/(\d+)\s*,\s*' . preg_quote($rok_wydania, '/') . '/';
			preg_match($pattern_before_year, $line, $rocznik_match);
			$rocznik = isset($rocznik_match[1]) ? $rocznik_match[1] : null;

			// first number after publishYear should be issue
			$pattern_after_year = '/' . preg_quote($rok_wydania, '/') . '\s*,\s*(\d+)/';
			preg_match($pattern_after_year, $line, $wydanie_match);
			$issue = isset($wydanie_match[1]) ? $wydanie_match[1] : null;

			// Szukamy ostatniej liczby w linii lub zakresu liczb oddzielonego "-"
			if (preg_match('/(\d+(?:-\d+)?)\s*$/', $line, $page_match)) {
				$strona = $page_match[1];
				} else {
				$strona = $this->convertRomanNumberInStr($line);
				}

			// Dodajemy wynik do tablicy
			$results = [
				'volume' => $rocznik ?? '', 
				'publishYear' => $rok_wydania ?? '',
				'issue' => $issue ?? '',
				'page' => $strona ?? '',
			];
			} else {
			if (strtolower(substr($line,0,1)) == 's') {
				$results['page'] = floatval(preg_replace("/[^0-9]/", "", $line));
				}
			}
		
		return (object)$results;
		}
	
	public function getMagazines() {
		if ($this->relRec->majorFormat == 'b') {
			$items = [];
			$field = '773';
			$itemRole = 'sourceMagazine';
			
						
			if (!empty($this->record->$field)) {
				foreach ($this->record->$field as $line) {
					$item['issn'] = '';
					$item['title'] = '';
					$item['resourceId'] = '';
					$item['inMagazine'] = '';
					
					if (!empty($line->code->x)) {
						if (is_array($line->code->x)) {
							$line->code->x = current($line->code->x);
							$this->errors[] = 'Repeated '.$field.'x'; 
							}
						$item['ids']['issn'] = $line->code->x;
						}
					if (!empty($line->code->w) && is_string($line->code->w)) {	
						$line->code->w = (array)$line->code->w;
						foreach($line->code->w as $string) 
							$item['ids'][] = $string;
						}
					
					
					if (!empty($line->code->s)) {
						if (is_array($line->code->s)) {
							$line->code->x = current($line->code->s);
							$this->errors[] = 'Repeated '.$field.'s'; 
							}
						$item['title'] = $line->code->s;
						} else if (!empty($line->code->t)) {
							if (is_array($line->code->t)) {
								$line->code->t = current($line->code->t);
								$this->errors[] = 'Repeated '.$field.'t'; 
								}
							$item['title'] = $line->code->t;
							} else if (!empty($line->code->i)) {
								$line->code->i = (array)$line->code->i;
								foreach ($line->code->i as $string) {
									$tmp = explode(':',$string);	
									if ((count($tmp)>1) && (strlen($tmp[1])>3))
										$item['title'] = chop($tmp[1]);
									}
								}
					
					
					
					if (!empty($line->code->g)) {
						$line->code->g = (array)$line->code->g;
						foreach ($line->code->g as $string)
							$item['inMagazine'] = $this->parse773g($string);	
						}
					
					}
				$items[$itemRole][] = $item;
						
				}
			return $items;
			}
		}
	

	public function addTopic($field, $line) {
		$item = [];	
		$sf = 2;
		
		if (!empty($line->code->$sf) && is_array($line->code->$sf))
			$line->code->$sf = current($line->code->$sf);
			
		if (!empty($line->code->$sf) && is_string($line->code->$sf) && (substr($line->code->$sf,0,3) == 'ELB')) {
			$code = substr($line->code->$sf,-1);
			$item['ELB'][$code] = $line->code->a;
			} else {
			$subFieldsMeaning = [
				'0' => 'viaf',
				'2' => 'type_of_id',
				'7' => 'value_id',
				];
		
			$item['field'] = $field;
			$sf = '2';
			$content = (array)$line->code;
			if (!empty($content['2'])) {
				$idsName = $content['2'];
				} else {
				$idsName = 0;	
				}
			if (!empty($content['7'])) {
				$item['ids'][$idsName] = $content['7'];
				} else if ($idsName != 0) {
				$item['ids'][$idsName] = '';	
				}
			foreach ($subFieldsMeaning as $key=>$varible) {
				unset($line->code->$key);
				}				

			$item['value'] = json_encode($line->code); 
			}
		return $item;
		}
		
	
	public function getSubjects() {
		$items = [];
		$skip = [600, 610, 611, 653, 655];
		if (!empty($this->record)) {
			foreach ($this->record as $field => $content) {
				if (($field >= '601') & ($field <= '699')) {
					foreach ($content as $line) {
						$items[] = $this->addTopic($field, $line);
						}		
					}
				}
			}
		return $items;	
		}
		
	
	
	##############################################################################################
	
	private function sanitizeString(string $input): string {
		// Użyj Unicode property \p{L} dla liter i \p{N} dla cyfr
		return trim(preg_replace('/[^\p{L}\p{N}\-, ]/u', '', $input));
		}
		
	private function clearLastChar($string) {
		$charsToRemove = ['/', ';', ':', ',', ',.'];
		
		if (is_string($string)) {
			$string = str_replace('{dollar}', '$', $string);
			$len = strlen($string);
			if ($len>3) {
				$end = substr($string, $len-2, 3);
				$string = substr($string, 0, $len-2);
				$end = str_replace($charsToRemove, '', $end);
				$string = trim($string.$end);
				} else 
				$string = trim(str_replace($charsToRemove, '', $string));
			return $string;
			}
		return $string;
		}
	
	function onlyNumbers($string) {
		#return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
		return preg_replace("/[^0-9]/", '', $string);
		}
	
	function onlyLetters($string) {
		#return (int) filter_var($string, FILTER_SANITIZE_NUMBER_INT);
		return preg_replace("/[^A-Za-z]/", '', $string);
		}
	
	function yearFromStr($string) {
		$res = preg_match('/\b(\d{4})\b/', $string, $matches);
		if ($res === 1) {
			$year = $matches[1];
			return  $year;
			}
		}
	
	function yearsFromString(string $input): array {
		$clean = trim($input);
		$clean = preg_replace('/[^\d\-]/', '', $clean); // tylko cyfry i myślniki
		$years = [];
		if (preg_match('/^(\d{4})-(\d{4})$/', $clean, $matches)) {
			$start = (int)$matches[1];
			$end = (int)$matches[2];
			if ($start <= $end) {
				$years = range($start, $end);
				}
			} elseif (preg_match('/^\d{4}$/', $clean)) {
			$years[] = (int)$clean;
			}
		return $years;
		}
	
	}

?>	
<?PHP


class marcBuffer{
	
	public $cAPI = 'http://testlibri.ucl.cas.cz/lite/functions/marc21/marc2jsonF.php';
	
	public function __construct() {
		$dirList = [
			'./files',
			'./files/marc',
			'./files/json',
			'./files/downloaded',
			'./files/downloaded/viaf',
			'./files/downloaded/wikidata',
			'./files/downloaded/wikidata/html',
			'./files/downloaded/wikidata/json',
			'./files/downloaded/geonames'
			];
		foreach ($dirList as $dir)
			if (!is_dir($dir)) {
				mkdir($dir);
				chmod($dir, 0775);
				}
		
		$this->geonames['userid'] = 'marcingierssz';
		$this->bufferTime = 86400*30;
		}
	
	public function register($name, $var) {
		$this->$name = $var;
		}
	
	public function getJsonRecord($id, $marc) {
		$fileCode = substr($id, 0, 5);
		
		#file_get_contents($this->cAPI.'?'.$fileCode.'/'.$id);
		
		if (!is_dir('./files/json/'.$fileCode)) {
			mkdir('./files/json/'.$fileCode);
			chmod('./files/json/'.$fileCode, 0775);
			}
		if (!is_dir('./files/marc/'.$fileCode)) {
			mkdir('./files/marc/'.$fileCode);
			chmod('./files/marc/'.$fileCode, 0775);
			}
		$jsonfile = './files/json/'.$fileCode.'/'.$id.'.json';	
		if (file_exists($jsonfile)) {
			# echo "Reading $id";	
			$json = file_get_contents($jsonfile);
			if ($json!=='') 
				return json_decode($json);
			} 
		file_put_contents('./files/marc/'.$fileCode.'/'.$id.'.mrc', $marc);
		#echo "Creating $id";	
		$json = json_decode(file_get_contents($this->cAPI.'?'.$fileCode.'/'.$id));
		$json->errors = "Error reading buffer";
		return $json;
		}
	
	public function getRecord($core,$id) {
		if ($core == 'biblio') {
			$fileCode = substr($id, 0, 5);
			if (file_exists('./files/json/'.$fileCode.'/'.$id.'.json')) {
				$json = file_get_contents('./files/json/'.$fileCode.'/'.$id.'.json');
				return json_decode($json);
				} else 
				return null;
			}
		}
	
	public function getRecordJsonFile($core,$id) {
		if ($core == 'biblio') {
			$fileCode = substr($id, 0, 5);
			if (file_exists('./files/json/'.$fileCode.'/'.$id.'.json')) {
				$json = file_get_contents('./files/json/'.$fileCode.'/'.$id.'.json');
				return $json;
				} else 
				return null;
			}
		}
	
 
	public function shortHash($str) {
		return hash('crc32b', trim($str));
		}
		
		

	public function createFacetsCode($sql, $facets) {
		sort($facets);
				
		foreach ($facets as $k=>$v)
			$facets[$k] = urlencode($v);
		$str = http_build_query	($facets);
		
		
		if ($str == '')
			$key = '';
			else 
			$key = $this->shortHash($str);
		
		$res = $sql->query("SELECT * FROM lite_facets_queries WHERE code='$key';");
		
		if ($res->num_rows==0) {
			$sql->query("INSERT INTO lite_facets_queries (code, query) VALUES ('$key', '$str');");
			}
		return $key;	
		}
	
	public function setSql($sql) {
		$this->sql = $sql;
		}	

	public function facetLine($group, $value) {
		return $group.':"'.$value.'"';
		}
	
	public function isActiveFacet($group, $value) {
		$sline = $this->facetLine($group, $value);
		if (!empty($this->Tfq[$group]) && in_array($sline, $this->Tfq[$group])) {
			return true;
			}
			
		return false;
		}

	public function addFacet($group, $value) {
		if (!empty($this->usedFacetsStr) && is_array($this->usedFacetsStr))
			$res = $this->usedFacetsStr;
			
		$res[] = $this->facetLine($group, $value);
		return $res;
		}
	
	
	public function removeFacet($group, $value) {
		if (!empty($this->usedFacetsStr) && is_array($this->usedFacetsStr)) {
			$res = $this->usedFacetsStr;
			if (substr($value,0,1)=='[')
				$ln = $group.':'.$value;
				else 
				$ln = $this->facetLine($group, $value);
			unset($res[$ln]);
			} else 
			$res = [];
		#echo "rmF res:$ln<pre>".print_r($res,1)."</pre>";	
		return $res; 
		}


	public function getFacetsFromStr($str) {
		parse_str($str, $fl);
		foreach ($fl as $filter) {
			$filter = urldecode($filter);
			$filter_u=str_replace('~','',$filter);
			$tmp = explode(':',$filter_u);
				$Tfq[$tmp[0]][]=$filter_u;
			if (stristr($filter, '~')) {
				$Top[$tmp[0]]=' OR ';
				} else {
				$Top[$tmp[0]]=' AND ';
				}
					
			}
		if (is_Array($Tfq))	
			foreach ($Tfq as $k=>$v) {
				$fq[] = implode($Top[$k],$v); 
				}	
		$value = '('.implode(') AND (',$fq).')';
		return [
				'field' => 'fq',
				'value' => $value
				];	
		}

	public function getFacets($sql, $facetsCode) {
		
		$res = $sql->query("SELECT * FROM lite_facets_queries WHERE code='{$facetsCode}';");
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			if ($row['query']<>'') {
				parse_str($row['query'], $fl);
				foreach ($fl as $filter) {
					$filter = urldecode($filter);
					$filter_u=str_replace('~','',$filter);
					$this->usedFacetsStr[$filter_u] = $filter;
					$tmp = explode(':',$filter_u);
						$Tfq[$tmp[0]][]=$filter_u;
					if (stristr($filter, '~')) {
						$Top[$tmp[0]]=' OR ';
						$this->usedFacets[$tmp[0]][]= [
							'operator' => 'or',
							'value' => str_replace($tmp[0].':', '', $filter_u)
							];
						} else {
						$Top[$tmp[0]]=' AND ';
						$this->usedFacets[$tmp[0]][]= [
							'operator' => 'and',
							'value' => str_replace($tmp[0].':', '', $filter_u)
							];
							
						}
					$this->usedFacetsValues[] = str_replace([$tmp[0].':', '"'], '', $filter_u);		
					}
				if (is_Array($Tfq))	
					foreach ($Tfq as $k=>$v) {
						
						$fq[] = implode($Top[$k],$v); 
						}	
				$this->Top = $Top;
				$this->Tfq = $Tfq;
				$this->fq = $fq;
				#echo "<prE>".print_r($this,1)."</prE>";
				
				$value = '('.implode(') AND (',$fq).')';
				return [
						'field' => 'fq',
						'value' => $value
						];
				}					
			}
		}
	
	
	public function recordsInSql() {
		
		$res = $this->sql->query($Q="SELECT count(id_biblio) FROM libri_biblio_core; ");
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			return $row['count'];	
			}
		
		}
	
	
	##################################################################################################################
	##						MyLists 
	##################################################################################################################
	
	
	

	public function myListCount($name='mylist') {
		if (!empty($_SESSION['results']) && is_Array($_SESSION['results'])) {
			#echo "<pre>".print_R($_SESSION,1)."</pre>";
			return count($_SESSION['results']['mylist']);
			} else 
			return 0;

		}

	public function myListResults($name='mylist') {
		if (!empty($_SESSION['results']) && is_Array($_SESSION['results'])) {
			#echo "<pre>".print_R($_SESSION,1)."</pre>";
			return $_SESSION['results'][$name];
			} else 
			return [];
		}

	public function isOnMyLists($id) {
		if (!empty($_SESSION['results']) && is_Array($_SESSION['results']))
			foreach ($_SESSION['results'] as $listName=>$arr)
				if (array_key_exists( $id, $arr ))
					$res[$listName] = $listName;
		if (!empty($res))
			return $res;
			else 
			return null;
		}
	
	public function resultCheckBox($result) {
		if (empty($_SESSION['results']) or !is_Array($_SESSION['results']))
			$_SESSION['results']['mylist'] = [];
		
		if (is_array($this->isOnMyLists($result->id))) {
			$myClass = "ph-check-square-bold";
			} else {
			$myClass = "ph-square-bold";
				
			}
		return "
				<div class=\"result-number\" onClick=\"results.myList('{$result->id}');\">
					<label>
						<i class=\"{$myClass}\" id=\"ch_{$result->id}\"></i>
						<span>{$result->lp}</span>
					</label>
				</div>
				";	
		}
	
	
	public function isNull($v) {
		if (empty($v)or($v == ''))
			return 'NULL';
			else 
			return "'".$v."'";
		}
	
	
	public function getSourceId($type, $source) {
		if (!empty($this->SourceId[$type][$source]))
			return $this->SourceId[$type][$source];
		
		$res = $this->sql->query($Q="SELECT * FROM libri_sources WHERE type='$type' AND name='$source'; ");
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			$this->SourceId[$type][$source] = $row['id'];
			return $row['id'];
			} else {
			$res = $this->sql->query("SELECT id FROM libri_sources ORDER BY id DESC LIMIT 1");
			$row = mysqli_fetch_assoc($res);
			$id = $row['id']+1;
			$this->SourceId[$type][$source] = $id;
			$this->sql->query("INSERT INTO libri_sources (id, type, name) VALUES ('$id', '$type', '$source');");
			return $id;
			}
		}
	
	function loadPerson($id) {
		$id = str_replace('viaf_id','', $id);
		$res = $this->sql->query($Q="SELECT * FROM libri_persons WHERE viafid='$id'; ");
					
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			
			$person = (object)$row;
			$person->dates = new stdClass;
			if (!empty($row['date_birth']))
				$person->dates->birth = $row['date_birth'];
			if (!empty($row['date_death']))
				$person->dates->death = $row['date_death'];
			if (!empty($row['date_range']))
				$person->dates->range = $row['date_range'];
			
			if (!empty($row['date_birth']) && !empty($row['date_death'])) {
				$person->dates->wasLiving = date("Y", strtotime($row['date_death']))-date("Y",strtotime($row['date_birth']));
				if (empty($person->dates->range))
					$person->dates->range = '('. date("Y", strtotime($row['date_birth'])) .'-'. date("Y",strtotime($row['date_death'])) .')';
				}
			if (!empty($row['date_birth']) && empty($row['date_death'])) {
				$person->dates->age = date("Y") - date("Y", strtotime($row['date_birth']));
				if (empty($person->dates->range))
					$person->dates->range = '('.date("Y", strtotime($row['date_birth'])).'-)';
				}
			
			
			$res = $this->sql->query($Q="SELECT * FROM libri_persons_names WHERE id_person='{$person->id}'; ");
			if ($res->num_rows>0) {
				$person->names = new stdClass;
				while ($row = mysqli_fetch_assoc($res)) {
					if (!empty($row['ton']))
						$typeOfName = $row['ton'];
						else 
						$typeOfName = 'name';
					$person->names->{$row['id_lang']} = new stdClass;
					$person->names->{$row['id_lang']}->$typeOfName = $row['name'];
					}
				}
				
			$res = $this->sql->query($Q="SELECT * FROM libri_persons_desc WHERE id_person='{$person->id}'; ");
			if ($res->num_rows>0) {
				$person->desc = new stdClass;
				while ($row = mysqli_fetch_assoc($res)) {
					$person->desc->{$row['id_lang']} = $row['description'];
					}
				}
				
			$res = $this->sql->query($Q="SELECT * FROM libri_persons_ids WHERE id_person='{$person->id}'; ");
			if ($res->num_rows>0) {
				$person->ids = new stdClass;
				while ($row = mysqli_fetch_assoc($res)) {
					$person->ids->{$row['toi']} = $row['idvalue'];
					}
				}
			
			$res = $this->sql->query($Q="SELECT tom,description,url FROM libri_persons_media WHERE id_person='{$person->id}'; ");
			if ($res->num_rows>0) {
				$person->media = new stdClass;
				while ($row = mysqli_fetch_assoc($res)) {
					$person->media->{$row['tom']}[] = (object)$row;
					}
				}
				
			return $person;
			} else 
			return null;
		}
	
	
	public function saveBiblio(
						$id_biblio, 
						$leader,
						$publish_year,
						$id_source,
						$date_c
						) {
		$res = $this->sql->query("SELECT * FROM libri_biblio WHERE id_biblio='$id_biblio' AND leader='$leader' AND publish_year='$publish_year' AND id_source='$id_source' AND date_c='$date_c';");
		if ($res->num_rows>0) {
			// record exist !
			return 'exists';
			} else {
			$this->sql->query($Q="INSERT INTO libri_biblio (id_biblio, leader, publish_year, id_source, date_c) 
					VALUES (
						".$this->isNull($id_biblio).", 
						".$this->isNull($leader).", 
						".$this->isNull($publish_year).", 
						".$this->isNull($id_source).", 
						".$this->isNull($date_c)." ); ");	
			return 'added';
			}
		}
	
	function addBiblioPerson($id_biblio, $person) {
		$p = $person;
		// nadanie wartości domyśnych // sets default values 
		isset($p['name']) || $p['name'] = null;
		isset($p['date']) || $p['date'] = null;
		isset($p['viafid']) || $p['viafid'] = null;
		
		
		$res = $this->sql->query($Q="SELECT * FROM libri_biblio_persons WHERE name={$this->isNull($p['name'])} AND dates={$this->isNull($p['date'])} AND viafid={$this->isNull($p['viafid'])}; ");
		if (!empty($res->num_rows) && ($res->num_rows>0)) {
			$row = mysqli_fetch_assoc($res);
			return $row['id_person'];	
			} else {
			$this->sql->query($Q="INSERT INTO libri_biblio_persons (name, dates, viafid) 
					VALUES (
						".$this->isNull($p['name']).", 
						".$this->isNull($p['date']).", 
						".$this->isNull($p['viafid'])."
							); ");	
			#echo " $Q\n";				 
			$res = $this->sql->query($Q="SELECT * FROM libri_biblio_persons WHERE name={$this->isNull($p['name'])} AND dates={$this->isNull($p['date'])} AND viafid={$this->isNull($p['viafid'])}; ");
			if (!empty($res->num_rows) && ($res->num_rows>0)) {
				$row = mysqli_fetch_assoc($res);
				return $row['id_person'];	
				}
				
			}
			
		}	
	
	function addAcIndex($index_id, $value) {
		$res = $this->sql->query($Q="SELECT * FROM libri_ac_indeks WHERE index_id={$this->isNull($index_id)} AND string={$this->isNull($value)}; ");
		#echo " $Q\n";				 
		if (!empty($res->num_rows) && ($res->num_rows>0)) {
			$row = mysqli_fetch_assoc($res);
			$id = $row['id'];
			$meter = $row['meter']+1;
			$this->sql->query($Q="UPDATE libri_ac_indeks SET meter='$meter' WHERE id='$id'; ");
			return $meter;	
			} else {
			$this->sql->query($Q="INSERT INTO libri_ac_indeks (index_id, string, meter) 
					VALUES (".$this->isNull($index_id).", ".$this->isNull($value).", ".$this->isNull('1')."	); ");	
			#echo " $Q\n";				 
			return 1;	
			}
			
		}
	
	function savePerson($id, $person) {
		$res = $this->sql->query($Q="SELECT * FROM libri_persons WHERE viafid='$id'; ");
		if ($res->num_rows>0) {
			// person exist = update
			$this->sql->query("UPDATE libri_persons SET  
						date_birth={$this->isNull($person->dates->birth)}, 
						date_death={$this->isNull($person->dates->death)}, 
						gender={$this->isNull($person->gender)}, 
						id_source_doc={$this->isNull($person->id_source_doc)}  
					WHERE viafid='$id';");
			} else {
			// no-person = insert	
			$this->sql->query($Q="INSERT INTO libri_persons (viafid, date_birth, date_death, gender, id_source_doc) 
					VALUES (
						".$this->isNull($id).", 
						".$this->isNull(substr($person->dates->birth, 1, 10)).", 
						".$this->isNull(substr($person->dates->death, 1, 10)).", 
						".$this->isNull($person->gender).", 
						".$this->isNull($person->id_source_doc)." ); ");
			echo "$Q";
			$res = $this->sql->query($Q="SELECT * FROM libri_persons WHERE viafid='$id'; ");
			if ($res->num_rows>0) {
				$row = mysqli_fetch_assoc($res);
				$id = $row['id'];
				if (!empty($person->ids) && is_object($person->ids))
					foreach ($person->ids as $k=>$v)
						$this->sql->query("INSERT INTO libri_persons_ids (id_person, toi, idvalue) VALUES ('$id', '$k', '$v');");
				
				if (!empty($person->desc) && is_object($person->desc))
					foreach ($person->desc as $id_lang=>$value)
						if (!empty($value))
							$this->sql->query("INSERT INTO libri_persons_desc (id_person, id_lang, description) VALUES ('$id', '$id_lang', '$value');");
				
				if (!empty($person->name) && is_object($person->name))
					foreach ($person->name as $id_lang=>$value)
						if (!empty($value))
							$this->sql->query("INSERT INTO libri_persons_names (id_person, id_lang, name) VALUES ('$id', '$id_lang', '$value');");
				
				if (!empty($person->image) && is_object($person->image)) {
					$img = $person->image;
					if (!empty($img->fname))
						$this->sql->query("INSERT INTO libri_persons_media (id_person, tom, url, description) VALUES ('$id', 'image', '{$img->fname}', 'image {$img->width},{$img->height}');");
					}
				}
			}
		
		}
	
	
	function partOf($t) {
		foreach ($t as $row) {
			$T[$row] = strlen($row);
			}
		
		asort($T);
		$len = current($T);
		$key = key($T);
		$copy = $T;
		unset($copy[$key]);
		
		$str = '';
		for ($i = 0; $i< $len; $i++) {
			$str .= $key[$i];
			foreach ($copy as $k=>$v) {
				if (!stristr($k, $str))
					return $str;
				}
			}
		return $str;
		}	
	
	public function getSearchString($facetName, $ss = array()) {
		$fv = "facet_value LIKE '%". implode("%' AND facet_value LIKE '%", $ss) ."%'";
		
		$res = $this->sql->query($Q = "SELECT facet_value FROM `libri_facets_as` WHERE facet_name='$facetName' AND ($fv) ORDER BY power DESC LIMIT 10;");
		#echo "$Q";
		if ($res->num_rows>0) {
			while ($row = mysqli_fetch_assoc($res)) {
				$T[] = $row['facet_value'];
				}
			return $this->partOf($T);	
			}
		return implode(' ',$ss);
		}
		
	public function getPlaceParams($placeName, $options = '') {
		$res = $this->sql->query($Q = "SELECT * FROM `libri_places` WHERE name='$placeName';");
		if ($res->num_rows>0) {
			$row = mysqli_fetch_assoc($res);
			
			if ($options == 'extended') {
				$res2 = $this->sql->query($Q = "SELECT * FROM `libri_places` WHERE display_name='{$row['display_name']}' AND name<>'$placeName';");
				if ($res->num_rows>0) {
					while ($row2 = mysqli_fetch_assoc($res2)) {
						$row['other_names'][] = $row2['name'];
						}
					}
				}
			
			return $row;	
			} else {
			$opts = array('http'=>array('header'=>"User-Agent: LiBRI 3.0\r\n"));
			$context = stream_context_create($opts);
			$res = json_decode(file_get_contents('http://nominatim.openstreetmap.org/search/?format=json&q='.urlencode($placeName), false, $context));
			if (!empty($res[0])) {
				$row['name'] = $placeName;
				$row['lat'] = $res[0]->lat;
				$row['lon'] = $res[0]->lon;
				$row['lat_min'] = $res[0]->boundingbox[0];
				$row['lat_max'] = $res[0]->boundingbox[1];
				$row['lon_min'] = $res[0]->boundingbox[2];
				$row['lon_max'] = $res[0]->boundingbox[3];
				$row['display_name'] = $res[0]->display_name;
				$this->sql->query($Q = "INSERT INTO libri_places (name, lat, lon, lat_min, lat_max, lon_min, lon_max, display_name)
					VALUES ('{$row['name']}', '{$row['lat']}', '{$row['lon']}', '{$row['lat_min']}', '{$row['lat_max']}', '{$row['lon_min']}', '{$row['lon_max']}', '{$row['display_name']}'); ");
				# echo $Q."<pre>".print_R($res[0],1).'</pre>';
				return $row;
				} 
			}
		}
	
	public function getNeighborhoodPlaces($place, $dist = 0.5) {
		if (!empty($place['lon']) & is_numeric($dist)) {
			$lon_min = str_replace(',','.',$place['lon']-$dist);
			$lon_max = str_replace(',','.',$place['lon']+$dist);
			$lat_min = str_replace(',','.',$place['lat']-$dist);
			$lat_max = str_replace(',','.',$place['lat']+$dist);
			
			if ($place['lat']<0)
				$placeLat = '+'.abs($place['lat']);
				else 
				$placeLat = '-'.$place['lat'];
			if ($place['lon']<0)
				$placeLon = '+'.abs($place['lon']);
				else 
				$placeLon = '-'.$place['lon'];
			$orderBy = "(lat$placeLat)^2+(lon$placeLon)^2";
			
			$t = $this->cms->psql->query($Q = "SELECT * FROM places_on_map WHERE lon>'$lon_min' AND lon<'$lon_max' AND lat>'$lat_min' AND lat<'$lat_max' AND name<>'$place[name]' ORDER BY $orderBy DESC LIMIT 20;");
			if (is_Array($t)) {
				foreach ($t as $row) {
					$placesList[] = $row['name'];
					}
				return $placesList;	
				}
			}
		}
	
	
	public function getGPS($rec) {
		#echo "<pre>".print_r($rec,1).'</pre>';
		$name = $rec['name'];
		if (!empty($rec['geocode'])) {
			$geocode = floatval($rec['geocode']);
			
			$id_link = $geocode;
			$gps['geolink'] = "https://www.geonames.org/".$geocode;
			
			$res = $this->sql->query("SELECT lat,lon FROM libri_geo_gps WHERE geocode='$geocode';");
			if ($res->num_rows>0) {
				$gps = mysqli_fetch_assoc($res);
				} else {
				$link = "https://sws.geonames.org/$geocode/about.rdf";
				$file = file_get_contents($link);
			
				$xml = $this->xml2array($file);
				$gps['lat']= $lat = $xml['rdf:RDF']['gn:Feature']['wgs84_pos:lat'];
				$gps['lon']= $lon = $xml['rdf:RDF']['gn:Feature']['wgs84_pos:long'];
				$this->sql->query("INSERT INTO libri_geo_gps (geocode, name, lat, lon) VALUES ('$geocode', '$name', '$lat', '$lon');");
				
				} 
			if (!empty($gps['lat'])) {
				$gps['googlelink'] = "https://www.google.pl/maps/place/{$gps['lat']},{$gps['lon']}/@{$gps['lat']},{$gps['lon']},10z/";
				}
			return $gps;
			} 
		}	
	
	
	
	public function loadFromViaf($viafId) {
		$bufferTime = $this->bufferTime;
		
		$fl = substr($viafId,0,2);
		$dir = "./files/downloaded/viaf/$fl";
		$localFile = "$dir/$viafId.xml";
		if (!is_dir($dir)) {
			mkdir($dir);
			chmod($dir, 0775);
			}
	
		$local = false;
		
		if (file_exists($localFile)) {
			$localTime = filemtime($localFile);
			if (time()-$localTime < $bufferTime) {
				$xml = file_get_contents($localFile);
				$local = true;
				}
			}
		if (!$local) {
			$xml = file_get_contents("http://viaf.org/viaf/$viafId/marc21.xml");
			file_put_contents($localFile, $xml);
			}
		return $xml;	
		}
	
	
	public function loadFromWikidata($wikiId) {
		$bufferTime = $this->bufferTime; 
		$fl = substr($wikiId,0,3);
		$dir = "./files/downloaded/wikidata/json/$fl";
		$localFile = "$dir/$wikiId.json";
		if (!is_dir($dir)) {
			mkdir($dir);
			chmod($dir, 0775);
			}
		$local = false;
		
		if (file_exists($localFile)) {
			$localTime = filemtime($localFile);
			if (time()-$localTime < $bufferTime) {
				$json = file_get_contents($localFile);
				$local = true;
				}
			} 
		if (!$local) {
			$json = file_get_contents("https://www.wikidata.org/wiki/Special:EntityData/$wikiId.json");
			@file_put_contents($localFile, $json);
			}
		return $json;	
		}
	
	
	
	function loadWikiMediaUrl($fileName) {
		if (!empty($fileName)) {
			# $fileName = $wiki->getStrVal($claim);
			$t = $this->cms->psql->querySelect("SELECT url,width,height FROM wiki_media_urls WHERE file_name={$this->cms->psql->isNull($fileName)}; ");
			if (is_array($t))
				return current($t)['url'];
				else {
				$file = @file_get_contents("https://en.wikipedia.org/w/api.php?action=query&format=json&prop=imageinfo&iilimit=5&iiprop=timestamp|size|url&titles=File:".urlencode($fileName));
				$json = json_decode($file);
				if (!empty($json->query->pages->{'-1'}->imageinfo[0]->url)) {
					$url = $json->query->pages->{'-1'}->imageinfo[0]->url;
					$width = $json->query->pages->{'-1'}->imageinfo[0]->width;
					$height = $json->query->pages->{'-1'}->imageinfo[0]->height;
					$this->cms->psql->query("INSERT INTO wiki_media_urls (file_name, url, width, height, time) VALUES ({$this->cms->psql->isNull($fileName)}, {$this->cms->psql->isNull($url)}, {$this->cms->psql->isNull($width)}, {$this->cms->psql->isNull($height)}, now())");
					return $url;
					}
				}
			}
		}

	/*
	public function loadMediaFromWikidata($wikiId) {
		$bufferTime = $this->bufferTime;
		$fl = substr($wikiId,0,3);
		$dir = "./files/downloaded/wikidata/html/$fl";
		$localFile = "$dir/$wikiId.html";
		if (!is_dir($dir)) {
			mkdir($dir);
			chmod($dir, 0775);
			}
		$local = false;
		$Tres = [];
		
		if (file_exists($localFile)) {
			$localTime = filemtime($localFile);
			if (time()-$localTime < $bufferTime) {
				$json = file_get_contents($localFile);
				$local = true;
				}
			} 
		if (!$local) {
			$json = file_get_contents("https://www.wikidata.org/wiki/$wikiId");
			file_put_contents($localFile, $json);
			}

		$sData = $json;
		if(preg_match('/<head.[^>]*>.*<\/head>/is', $sData, $aHead)) {   
			$sDataHtml = preg_replace('/<(.[^>]*)>/i', strtolower('<$1>'), $aHead[0]);
			$lines = explode("\n", $sDataHtml);
			$lp = 0;
			foreach ($lines as $line) 
				if (stristr($line, '<meta')) {
					$nline = explode('"',$line);
					
					switch ($nline[1]) {
						case 'og:image' : $lp++; 	$Tres[$lp]['fname'] = $nline[3]; break;
						case 'og:image:width' : 	$Tres[$lp]['width'] = $nline[3]; break;
						case 'og:image:height' : 	$Tres[$lp]['height'] = $nline[3]; break;
						}	
					}
			}
		return $Tres;	
		}
	*/
	
	public function loadFromGeonames($Id) {
		$bufferTime = $this->bufferTime;
		$localFile = "./files/downloaded/geonames/$Id.json";
		$local = false;
		
		if (file_exists($localFile)) {
			$localTime = filemtime($localFile);
			if (time()-$localTime < $bufferTime) {
				$json = file_get_contents($localFile);
				$local = true;
				}
			} 
		if (!$local) {
			$json = file_get_contents("http://api.geonames.org/getJSON?formatted=true&geonameId=$Id&username={$this->geonames['userid']}&style=full"); //
			file_put_contents($localFile, $json);
			}
		return $json;	
		}
	
	
	public function geocodeOnGeonames($placeName) {
		$bufferTime = $this->bufferTime;
		$localFile = "./files/downloaded/geonames/$Id.json";
		$local = false;
		
		if (file_exists($localFile)) {
			$localTime = filemtime($localFile);
			if (time()-$localTime < $bufferTime) {
				$json = file_get_contents($localFile);
				$local = true;
				}
			} 
		if (!$local) {
			$json = file_get_contents("http://api.geonames.org/geocodeJSON?q={$placeName}&username={$this->geonames['userid']}"); //
			file_put_contents($localFile, $json);
			}
		return $json;	
		}
	
	
		
	
	public function drawMarcLine($k, $v, $rec) { 
			if (!empty ($v['field']))
				$value = $this->getObjectValue($v['field'], $rec);
				elseif (!empty($v['value']))
					$value = $v['value'];
					elseif (!empty($v['subfields'])) {
						$value = 'retrieving subfields';
						$res = [];
						foreach ($v['subfields'] as $sk=>$arr) {
							if (!empty($arr['field']))
								$res[$sk] = " <b>|$sk</b> <span title='$arr[label]'>".$this->getObjectValue($arr['field'], $rec).'</span>';
							}
						$value = implode(' ', $res);	
						} else 
						$value = 'error!';
			$ind1=$ind2='<td></td>';
			if (!empty($v['ind1']))
				$ind1= "<td>$v[ind1]</td>";
			if (!empty($v['ind2']))
				$ind2= "<td>$v[ind2]</td>";
			
			
			return '
				<tr>
					<td class="text-right"><b>'.$k.'</b></td>
					'.$ind1.$ind2.'
					<td>'.$value.'</td>
					<td class="text-right small">'.$v['label'].'</td>
				</tr>
				';
			}
	
	
	function getObjectValue($x, $rec) { // $x source field path, $rec - object 
		$sep = '->';
		$t = explode($sep, $x);
		
		
		#echo "$x <pre>".print_r($rec,1)."</pre>";
		
		if (count($t)>1) {
			$nt = $t[0];
			if (!empty($rec->$nt) && (is_object($rec->$nt)) )  {
					unset($t[0]);
					return $this->getObjectValue(implode($sep,$t),$rec->$nt);
					} 
			if (!empty($rec->$nt) && (is_Array($rec->$nt)) )  {
					$i = $t[1];
					unset($t[0]);
					unset($t[1]);
					return $this->getObjectValue(implode($sep,$t),$rec->$nt[$i]);
					} 
			
			return null;
			// return $x;
			
			} else {
			if (!empty($rec->$x))
				return $rec->$x;
				else 
				return null;
			}
		}	
	
	
	
	public function addToBottomSummary($result) {
		if (empty($this->bottomLists))
			$this->bottomLists = new stdClass;
		
		if (!empty($result->persons_str_mv))
			foreach ($result->persons_str_mv as $record) {
				$t = explode('|', $record);
				if (!empty($t[4]))
					$this->bottomLists->persons['.'.$t[4]] = $record;
					else
					$this->bottomLists->persons['z'.$this->shortHash($record)] = $record;
				}
		
		if (!empty($result->geographic))
			foreach ($result->geographic as $record)
				$this->bottomLists->places[$this->shortHash($record)] = $record;
		
		if (!empty($result->magazines_str_mv))
			foreach ($result->magazines_str_mv as $record)
				$this->bottomLists->magazines[$this->shortHash($record)] = $record;
		
		}

	public function getBottomList($listName) {
		if (!empty($this->bottomLists->$listName))
			return $this->bottomLists->$listName;
			else 
			return [];
		}
	
	
		
	/**
	 * xml2array() will convert the given XML text to an array in the XML structure.
	 * Link: http://www.bin-co.com/php/scripts/xml2array/
	 * Arguments : $contents - The XML text
	 *                $get_attributes - 1 or 0. If this is 1 the function will get the attributes as well as the tag values - this results in a different array structure in the return value.
	 *                $priority - Can be 'tag' or 'attribute'. This will change the way the resulting array sturcture. For 'tag', the tags are given more importance.
	 * Return: The parsed XML in an array form. Use print_r() to see the resulting array structure.
	 * Examples: $array =  xml2array(file_get_contents('feed.xml'));
	 *              $array =  xml2array(file_get_contents('feed.xml', 1, 'attribute'));
	 */
	function xml2array($contents, $get_attributes=1, $priority = 'tag') {
		if(!$contents) return array();

		if(!function_exists('xml_parser_create')) {
			//print "'xml_parser_create()' function not found!";
			return array();
		}

		//Get the XML parser of PHP - PHP must have this module for the parser to work
		$parser = xml_parser_create('');
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); # http://minutillo.com/steve/weblog/2004/6/17/php-xml-and-character-encodings-a-tale-of-sadness-rage-and-data-loss
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parse_into_struct($parser, trim($contents), $xml_values);
		xml_parser_free($parser);

		if(!$xml_values) return;//Hmm...

		//Initializations
		$xml_array = array();
		$parents = array();
		$opened_tags = array();
		$arr = array();

		$current = &$xml_array; //Refference

		//Go through the tags.
		$repeated_tag_index = array();//Multiple tags with same name will be turned into an array
		foreach($xml_values as $data) {
			unset($attributes,$value);//Remove existing values, or there will be trouble

			//This command will extract these variables into the foreach scope
			// tag(string), type(string), level(int), attributes(array).
			extract($data);//We could use the array by itself, but this cooler.

			$result = array();
			$attributes_data = array();

			if(isset($value)) {
				if($priority == 'tag') $result = $value;
				else $result['value'] = $value; //Put the value in a assoc array if we are in the 'Attribute' mode
			}

			//Set the attributes too.
			if(isset($attributes) and $get_attributes) {
				foreach($attributes as $attr => $val) {
					if($priority == 'tag') $attributes_data[$attr] = $val;
					else $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
				}
			}

			//See tag status and do the needed.
			if($type == "open") {//The starting of the tag '<tag>'
				$parent[$level-1] = &$current;
				if(!is_array($current) or (!in_array($tag, array_keys($current)))) { //Insert New tag
					$current[$tag] = $result;
					if($attributes_data) $current[$tag. '_attr'] = $attributes_data;
					$repeated_tag_index[$tag.'_'.$level] = 1;

					$current = &$current[$tag];

				} else { //There was another element with the same tag name

					if(isset($current[$tag][0])) {//If there is a 0th element it is already an array
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
						$repeated_tag_index[$tag.'_'.$level]++;
					} else {//This section will make the value an array if multiple tags with the same name appear together
						$current[$tag] = array($current[$tag],$result);//This will combine the existing item and the new item together to make an array
						$repeated_tag_index[$tag.'_'.$level] = 2;

						if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well
							$current[$tag]['0_attr'] = $current[$tag.'_attr'];
							unset($current[$tag.'_attr']);
						}

					}
					$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1;
					$current = &$current[$tag][$last_item_index];
				}

			} elseif($type == "complete") { //Tags that ends in 1 line '<tag />'
				//See if the key is already taken.
				if(!isset($current[$tag])) { //New Key
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					if($priority == 'tag' and $attributes_data) $current[$tag. '_attr'] = $attributes_data;

				} else { //If taken, put all things inside a list(array)
					if(isset($current[$tag][0]) and is_array($current[$tag])) {//If it is already an array...

						// ...push the new element into that array.
						$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;

						if($priority == 'tag' and $get_attributes and $attributes_data) {
							$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
						}
						$repeated_tag_index[$tag.'_'.$level]++;

					} else { //If it is not an array...
						$current[$tag] = array($current[$tag],$result); //...Make it an array using using the existing value and the new value
						$repeated_tag_index[$tag.'_'.$level] = 1;
						if($priority == 'tag' and $get_attributes) {
							if(isset($current[$tag.'_attr'])) { //The attribute of the last(0th) tag must be moved as well

								$current[$tag]['0_attr'] = $current[$tag.'_attr'];
								unset($current[$tag.'_attr']);
							}

							if($attributes_data) {
								$current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_attr'] = $attributes_data;
							}
						}
						$repeated_tag_index[$tag.'_'.$level]++; //0 and 1 index is already taken
					}
				}

			} elseif($type == 'close') { //End of tag '</tag>'
				$current = &$parent[$level-1];
			}
		}

		return($xml_array);
	}
	
	
	}
?>
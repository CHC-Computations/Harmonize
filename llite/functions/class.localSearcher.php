<?php 


class localSearcher {
	
	private $settings;
	private $cms;
	private $buffer;

	public $success;
	
	function __construct() {
		$this->buffer = new stdClass;
		}
	
	function register(string $name, object $var) {
		$this->$name = $var;
		}
	
	function getWikiQ($data, $type = null) {
		
		if (!empty($data['biblio_label']) && !empty($this->buffer->matchResults[$data['biblio_label']]))
			return $this->buffer->matchResults[$data['biblio_label']];
		
		if ($this->cms->workingStep == 3) {
			$t = $this->cms->psql->querySelect($Q="SELECT a.*, s.string, s.clearstring FROM 
					matching_results a
					JOIN matching_strings s ON a.string_id=s.id
					WHERE s.string = {$this->cms->psql->string($data['biblio_label'])}
					ORDER BY match_level DESC 
					LIMIT 10;
					");
			if (is_Array($t)) {
				$i = 0;
				foreach ($t as $result) {
					$i++;
					if ($i == 1) {
						$result = current($t);
						$this->buffer->matchResults[$data['biblio_label']] = $return = $result['match_result'];
						} else {
						$this->cms->psql->query($Q = "DELETE FROM matching_results WHERE id = {$result['id']};");
						file_put_contents($this->cms->outPutFolder.'localSearcher.psql.deletes.log', $Q."\n", FILE_APPEND);
						}
					}
				#file_put_contents($this->cms->outPutFolder.'localSearcher.psql.string.log', $data['biblio_label']."\n\n".$Q."\n\n".$result['match_result']."\n\n", FILE_APPEND);
				return $return;
				}		
			}
		
		if (!empty($data['ids'])) {
			#file_put_contents($this->cms->outPutFolder.'localSearcher.getWikiQ.ids.csv', print_r($data,1), FILE_APPEND);
			$ids = (array)$data['ids'];
			// in memory buffer?
			foreach ($ids as $field=>$value) {
				if (is_numeric($field)) 
					$field = 'eid';
				$t = $this->cms->psql->querySelect("SELECT a.* 
							FROM matching_manual a 
							JOIN matching_fields f ON a.field = f.id 
							WHERE a.value = {$this->cms->psql->isNull($value)} AND f.fieldname = '$field';");
				if (is_array($t) && (count($t)==1)) {
					$localRec = current($t);
					if ($localRec['target'] == 0)
						$return = 'not found';
						else 
						$return = $localRec['target'];
					$this->saveMatching($data['biblio_label'], $type, 'by id', 'local', $return, 100);
					return $return;
					}
				}
			}
		if (empty($data['name']) & !empty($data['title']))
			$data['name'] = $data['title'];
		if (!empty($data['name'])) {
			$value = $data['name'];
			#file_put_contents($this->cms->outPutFolder.'localSearcher.getWikiQ.name.csv', print_r($data,1), FILE_APPEND);
			if (!empty($data['dates']))
				$value .= '|'.$data['dates'];
			$clearValue = $this->cms->helper->clearLatin($value);
			$t = $this->cms->psql->querySelect("SELECT a.* 
							FROM matching_manual a 
							JOIN matching_fields f ON a.field = f.id 
							WHERE (a.value = {$this->cms->psql->isNull($value)} or a.value = '$clearValue') AND f.fieldname = 'text';"); 
			if (is_array($t)) 
				foreach ($t as $localRec) {
					$localRec = current($t);
					if ($localRec['target'] == 0)
						$return = 'not found';
						else 
						$return = $localRec['target'];
					if (($localRec['valuetype'] == 2) & ($localRec['value'] == $clearValue)) {
						$this->saveMatching($data['biblio_label'], $type, 'by cleared text', 'local', $return, 100);
						return $return;
						}
					if (($localRec['valuetype'] == 1) & ($localRec['value'] == $value)) {
						$this->saveMatching($data['biblio_label'], $type, 'by raw text', 'local', $return, 100);
						return $return;
						}
					}
			}
			
		return null;
		}
		
	function saveBestLabel($label, $count, $bestLabel, $wikiQ = null, $label_type = null) {
		$str_id = $this->getStringId($label);
		$t = $this->cms->psql->querySelect("SELECT * FROM matching_strings_best_label WHERE id_string = '$str_id' AND label_type={$this->cms->psql->string($label_type)}");
		if (is_array($t)) {
			$row = current($t);
			$newduplication = $row['duplication']+1;
			$this->cms->psql->query("UPDATE matching_strings_best_label SET duplication='$newduplication' WHERE id_string = '$str_id' AND label_type={$this->cms->psql->string($label_type)};");
			} else
			$this->cms->psql->query("INSERT INTO matching_strings_best_label (id_string, count, string_to_use, wikiq, label_type, duplication) 
				VALUES ('$str_id', '$count', {$this->cms->psql->string($bestLabel)}, {$this->cms->psql->string($wikiQ)}, {$this->cms->psql->string($label_type)}, 0);");
			
		}
	
	function getBestLabel($string, $rec_type) {
		$this->success = false;
		if (!empty($this->buffer->bestLabel[$string][$rec_type])) {
			$this->success = true;
			return $this->buffer->bestLabel[$string][$rec_type];
			} else 
			return $string;
		}
	
	function getWikiQLabelMethod($string, $rec_type) {
		if (!empty($this->buffer->matchedWikiQ[$string][$rec_type]))
			return $this->buffer->matchedWikiQ[$string][$rec_type];
			else 
			return null;
		}
	
	function loadAllbestLabels() {
		$bestLabelFileName = $this->cms->outPutFolder.'/buffer/bestLabel.json';
		$matchedWikiQFileName =  $this->cms->outPutFolder.'/buffer/matchedWikiQ.json';
		if (file_exists($bestLabelFileName)) {
			$bestLabelsContent = utf8_encode(file_get_contents($bestLabelFileName));
			$this->buffer->bestLabel = json_decode($bestLabelsContent, true);
			echo "was error ".json_last_error()."\n";
			if (file_exists($matchedWikiQFileName)) 
				$this->buffer->matchedWikiQ = json_decode(file_get_contents($matchedWikiQFileName), true);
			echo "counting bestLabels:".count((array)$this->buffer->bestLabel)."\n";
			echo "counting matchedWikiQ:".count((array)$this->buffer->matchedWikiQ)."\n";
		
			return true;
			}
		
		$step = 123;
		echo "\n";
		$t = $this->cms->psql->querySelect($Q="SELECT count(*) FROM matching_strings_best_label;");
		$max = $this->cms->psql->getCount($t);
		$steps = ceil($max/$step);
		for ($i = 0; $i<=$steps; $i++) {
			$offset = $i*$step;
			$t = $this->cms->psql->querySelect($Q="SELECT b.string, a.* 
					FROM matching_strings_best_label a 
					LEFT JOIN matching_strings b ON a.id_string = b.id
					ORDER BY id
					LIMIT $step OFFSET $offset;");
			$persent = round(($i/$steps)*100);
			echo "loading best labels: {$persent}% (".$i*$step." in ".$this->cms->WorkTime(time()-$this->cms->startTime).")\r";
			if (is_Array($t)) {
				foreach ($t as $result) {
					$this->buffer->bestLabel[$result['string']][$result['label_type']] = $result['string_to_use'];
					$this->buffer->matchedWikiQ[$result['string']][$result['label_type']] = $result['wikiq'];
					}
				}
			}
		file_put_contents($bestLabelFileName, json_encode($this->buffer->bestLabel));
		file_put_contents($matchedWikiQFileName, json_encode($this->buffer->matchedWikiQ));
		echo "\n";				
		}
	
	
	
	function getStringId($string, $isClearedString = false) {
		if (!empty($this->buffer->string2ID[$string]))
			return $this->buffer->string2ID[$string];
		file_put_contents($this->cms->outPutFolder.'localSearcher.getStringId.csv', $string."\n", FILE_APPEND);	
		if ($isClearedString)
			$t = $this->cms->psql->querySelect("SELECT id,clearstring FROM matching_strings WHERE clear_string = {$this->cms->psql->string($string)}");
			else 
			$t = $this->cms->psql->querySelect("SELECT id,clearstring FROM matching_strings WHERE string = {$this->cms->psql->string($string)}");
		if (empty($t)) {
			$clear_string = $this->cms->helper->clearLatin($string);
			$stringID = $this->cms->psql->nextVal('matching_strings_id_seq');
			$this->cms->psql->query($Q = "INSERT INTO matching_strings (id, string, clearstring) VALUES ('$stringID', {$this->cms->psql->string($string)}, {$this->cms->psql->string($clear_string)})");
			#file_put_contents($this->cms->outPutFolder.'localSearcher.getStringId.sql.csv', $Q."\n", FILE_APPEND);	
			} else {
			$row = current($t);
			$stringID = $row['id'];
			}
			
		$this->buffer->string2ID[$string] = $stringID;	
		return $stringID;
		}	
		
	function getRecTypeId($type) {
		if (!empty($this->buffer->recTypes2ID[$type]))
			return $this->buffer->recTypes2ID[$type];
		#file_put_contents($this->cms->outPutFolder.'localSearcher.saveMatching.csv', $type."\n", FILE_APPEND);	
		$t = $this->cms->psql->querySelect("SELECT id FROM dic_rec_types WHERE rec_type_name = {$this->cms->psql->string($type)}");
		if (empty($t)) {
			$typeID = $this->cms->psql->nextVal('dic_rec_types_id_seq');
			$this->cms->psql->query("INSERT INTO dic_rec_types (id, rec_type_name) VALUES ('$typeID', {$this->cms->psql->string($type)})");
			} else {
			$row = current($t);
			$typeID = $row['id'];
			}
		$this->buffer->recTypes2ID[$type] = $typeID;
		return $typeID;
		}	
		
	function saveMatching($biblio_label, $type, $match_type, $match_source, $match_result, $match_level = 0) {
		if (empty($this->buffer->matchResults[$biblio_label])) {
			$this->buffer->matchResults[$biblio_label] = $match_result;
			$stringID = $this->getStringId($biblio_label, false);
			$recTypeID = $this->getRecTypeId($type);
			
			$matchID = $this->cms->psql->nextVal('matching_results_id_seq');
			$this->cms->psql->query($Q = "INSERT INTO matching_results (id, string_id, rectype_id, match_type, match_source, match_level, match_result)
				VALUES ('$matchID', '$stringID', '$recTypeID', {$this->cms->psql->string($match_type)}, {$this->cms->psql->string($match_source)}, {$this->cms->psql->real($match_level)}, {$this->cms->psql->string($match_result)});
				");
			file_put_contents($this->cms->outPutFolder.'localSearcher.saveMatching.csv', $Q."\n", FILE_APPEND);
			}
		} 	
	
	
	function loadAllMatching() {
		$step = 123;
		echo "\n";
		$t = $this->cms->psql->querySelect($Q="SELECT count(*) FROM matchig_results_with_strings;");
		$max = $this->cms->psql->getCount($t);
		$steps = ceil($max/$step);
		for ($i = 0; $i<=$steps; $i++) {
			$offset = $i*$step;
			$t = $this->cms->psql->querySelect($Q="SELECT * FROM matchig_results_with_strings
					ORDER BY id
					LIMIT $step OFFSET $offset;");
			$persent = round(($i/$steps)*100);
			echo "loading matching results: {$persent}% (".$i*$step." in ".$this->cms->WorkTime(time()-$this->cms->startTime).")\r";
			if (is_Array($t)) {
				foreach ($t as $result) {
					$this->buffer->matchResults[$result['string']] = $return = $result['match_result'];
					}
				}
			}
		echo "\n";				
		}
	
	
	
	}

?> 
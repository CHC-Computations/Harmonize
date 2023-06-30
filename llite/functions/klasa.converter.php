<?php

class converter {
	
	
	public function mrk2json($mrk) {
		$this->mrk = $mrk;
		$Tmrk = explode("\n", $mrk);
		foreach ($Tmrk as $line) {
			$part = $this->mrkLine($line);
			if (is_array($part)) {
				if (key($part) == 'LDR') {
					$this->newRecord($part);
					} else if (key($part) == '001') 
						$this->id = $this->recordId($part);
						else 
						$this->recordAddValue($part);				
				} 
			}
		#return json_encode($this->record, JSON_INVALID_UTF8_SUBSTITUTE);
		return $this->record;
		}	
	
	function mrkLine($line) {
		$line = trim(chop($line));
		if (substr($line,0,1) == '=') {
			$field = substr($line,1,3);
			$data = substr($line, 6);
			if (($field == 'LDR')or(substr($field,0,2) == '00'))
				return [$field => $data];
				else {
				$tmp = explode('$',$data);
				$ind1 = substr($data, 0, 1);
				$ind2 = substr($data, 1, 1);
				unset ($tmp[0]);
				$arr = [];
				foreach ($tmp as $part) {
					$subfield = substr($part, 0, 1);
					$value = substr($part, 1);
					if (!array_key_exists($subfield, $arr)) 
						$arr[$subfield] = $value;
						else if (!is_array($arr[$subfield])){
							$oldval = $arr[$subfield];
							$arr[$subfield] = [];
							$arr[$subfield][]=$oldval;
							$arr[$subfield][]=$value;
							} else 
							$arr[$subfield][]=$value;	
					}
				
				return [$field => (object)[
					'ind1' => $ind1,
					'ind2' => $ind2,
					'code' => (object)$arr
					]];	
				}
			} else 
			return null;
		}
	
	
	function newRecord($part) {
		$this->work = new stdClass;
		$this->record = new stdClass;
		$this->record->LEADER = $part['LDR'];
		}
	
	function recordId($part) {
		$val = current($part);
		#$record['ID'] = $val;
		$this->record->{key($part)}[] = $val;
		return $val;
		}
	
	function recordAddValue($part) {
		$val = current($part);
		$this->record->{key($part)}[] = $val;
		}
	
	}
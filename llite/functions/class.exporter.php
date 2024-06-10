<?php

class exporter {
	
	
	
	public function __construct() {
		$this->config = [];
		$this->startTime = time();
		}
	
	public function workTime() {
		return date("H:i:s", (time() - $this->startTime)+82800).' ';
		}
	
	################################################# MARCXML 

	function XMLheader() {
		return '<?xml version = "1.0" encoding = "UTF-8"?>'."\r";
		}
		
	function XMLcollection($xml) {
		$content = '<collection xmlns="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/MARC21/slim http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">'."\r";
		$content.= $xml;
		$content.= '</collection>'."\r";
		return $content;
		}

	function toMARCXML($json) {
		$content = '<record xmlns="http://www.loc.gov/MARC21/slim" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.loc.gov/standards/marcxml/schema/MARC21slim.xsd">';
		if (!empty($json->hiddenfield))
			unset($json->hiddenfield);
		
		foreach ($json as $field=>$arr) {
			if ($field == 'LEADER') {
				$content .= '<leader>'.$arr."</leader>\r";
				} elseif (is_Array($arr)) {
				$content .= $this->xmlLine($field, $arr);
				} else 
				$content .= $field."  error:".print_r($arr,1)."\r";
			}
		$content .= "</record>\r";
		return $content;
		}



	private function xmlLine($field,$line) {
		$content = '';
		if (is_Array($line))
			foreach ($line as $values) 
				if (!is_object($values))
					$content.='<controlfield tag="'.$field.'">'.$values."</controlfield>\r";
					else 
					$content.='<marc:datafield tag="'.$field.'"  ind1="'.$values->ind1.'" ind2="'.$values->ind2.'">'."\r".$this->xmlLineReCode($values->code)."</marc:datafield>\r";
		return $content;
		}
		
	private function xmlLineReCode($subfields)	{
		$content = '';
		foreach ($subfields as $sf_k => $sf_v) 
			if (!is_array($sf_v))
				$content.="\t".'<marc:subfield code="'.$sf_k.'">'.$sf_v."</marc:subfield>\r";
				else foreach ($sf_v as $v)
				$content.="\t".'<marc:subfield code="'.$sf_k.'">'.$v."</marc:subfield>\r";
					
		return $content;	
		}

	################################################# MRK 

	public function toMRK($json) {
		$content = '';
		if (!empty($json->hiddenfield))
			unset($json->hiddenfield);
		
		foreach ($json as $field=>$arr) {
			if ($field == 'LEADER') {
				$content .= "=LDR  ".$arr."\r";
				} elseif (is_Array($arr)) {
				$content .= $this->mrkLine($field, $arr);
				} else 
				$content .= $field."  error:".print_r($arr,1)."\r";
			}
		$content .= "\r";
		return $content;
		}

	private function mrkLine($field,$line) {
		$content = '';
		if (is_Array($line))
			foreach ($line as $values) 
				if (!is_object($values))
					$content.='='.$field."  ".$values."\r";
					else 
					$content.='='.$field."  ".$values->ind1.$values->ind2.$this->mkrLineReCode($values->code)."\r";
		return $content;
		}
		
	private function mkrLineReCode($subfields)	{
		$content = '';
		foreach ($subfields as $sf_k => $sf_v) 
			if (!is_array($sf_v))
				$content.='$'.$sf_k.$sf_v;
				else foreach ($sf_v as $v)
				$content.='$'.$sf_k.$v;
					
		return $content;	
		}
		
		
		
	}




?>
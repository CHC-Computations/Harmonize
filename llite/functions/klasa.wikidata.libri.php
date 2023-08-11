<?php 


class wikiLibri {
	
	function __construct($lang, $solrRecord) {
		$this->defLang = 'en';
		$this->userLang = $lang;
		
		$this->solrRecord = $solrRecord;
		
		}
	
	function getSolrValue($field) {
		if (!empty($this->solrRecord->$field))
			if (is_array($this->solrRecord->$field))
				return current($this->solrRecord->$field);
				else 
				return $this->solrRecord->$field;
		}
	
	function getStr($field) {
		$res = $this->getSolrValue($field);
		if (!empty($res)) {
			$object = json_decode($res);
			
			if (!empty($object->{$this->userLang}))
				return $object->{$this->userLang};
			if (!empty($object->{$this->defLang}))
				return $object->{$this->defLang};
			return current($object);
			}
		}
	
	function getSolrValues($field) {
		if (!empty($this->solrRecord->$field))
			return $this->solrRecord->$field;
		}
		
	function getID() {
		return $this->getSolrValue('id');
		}
	
	function getIDint() {
		return substr($this->getSolrValue('id'),1);
		}
	
	
	function getBiblioLabel() {
		if (!empty($this->solrRecord->biblio_labels))
			return end($this->solrRecord->biblio_labels);
		}
	
		
	function getActivePersonValues() {
		$activePerson = new stdclass;
		$activePerson->solr_str = $this->getBiblioLabel();
		$activePerson->as_author = $this->getSolrValue('as_author');
		$activePerson->as_author2 = $this->getSolrValue('as_coauthor');
		$activePerson->as_topic = $this->getSolrValue('as_subject');
		$activePerson->rec_total = $this->getSolrValue('biblio_count');
		$activePerson->wikiq = $this->getIDint();
		$activePerson->wikiId = $this->getID();
		$activePerson->viaf_id = $this->getSolrValue('viaf');
		$activePerson->name = $this->getStr('labels');
		return $activePerson;
		}	
	
	
	
	}

?>
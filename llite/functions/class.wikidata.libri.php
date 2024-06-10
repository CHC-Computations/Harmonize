<?php 


class wikiLibri {
	
	public $defLang;
	public $userLang;
	public $solrRecord;
	
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
			if (substr($res,0,1)!=='{') return $res;
			
			$object = json_decode($res);
			
			if (!empty($object->{$this->userLang}))
				return "<span title='user:{$this->userLang}'>".$object->{$this->userLang}.'</span>';
			if (!empty($object->{$this->defLang}))
				return "<span title='def:{$this->defLang}'>".$object->{$this->defLang}.'</span>';
			
			$arr = (array)$object;
			return current($arr);
			}
		}
	
	function getSolrValues($field) {
		if (!empty($this->solrRecord->$field))
			return $this->solrRecord->$field;
		}
		
	function getID() {
		return $this->getSolrValue('wikiq');
		}
	
	function getIDint() {
		if (!empty($this->getSolrValue('wikiq')))
			return substr($this->getSolrValue('wikiq'),1);
		}
	
	
	function getBiblioLabel() {
		if (!empty($this->solrRecord->biblio_labels))
			return end($this->solrRecord->biblio_labels);
		}
	
	function linkPanel() {
		$currentObject = new stdclass;
		$currentObject->solr_str = $this->getBiblioLabel();
		
		$currentObject->rec_total = $this->getSolrValue('biblio_count');
		$currentObject->wikiq = $this->getIDint();
		$currentObject->wikiId = $this->getID();
		$currentObject->viaf_id = $this->getSolrValue('viaf');
		$currentObject->name = $this->getStr('labels');
		
		if (!empty($this->solrRecord))
			foreach ($this->solrRecord as $key=>$value)
				if (substr($key,-6) == '_count') {
					$as = substr($key, 0, -6);
					if ($as !== 'biblio')
						$currentObject->as[substr($key, 0, -6)] = $value;
					}
		return $currentObject;
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
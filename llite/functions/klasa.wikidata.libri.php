<?php 


class wikiLibri extends wikidata {
	
	function __construct($lang, $solrRecord) {
		$this->defLang = 'en';
		$this->userLang = $lang;
		
		$this->solrRecord = $solrRecord;
		$this->record = json_decode($solrRecord->fullrecord)->entities->{$solrRecord->id};
		}
		
	
	function getBiblioLabel() {
		if (!empty($this->solrRecord->biblio_labels))
			return end($this->solrRecord->biblio_labels);
		}
	
	function getSolrValue($field) {
		if (!empty($this->solrRecord->$field))
			if (is_array($this->solrRecord->$field))
				return current($this->solrRecord->$field);
				else 
				return $this->solrRecord->$field;
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
		$activePerson->viaf_id = $this->getViafId();
		$activePerson->name = $this->get('labels');
		return $activePerson;
		}	
	
	
	
	}

?>
<?php 

class wikiDataCores {
	private $cms;
	private $solrClient;
	private $options;
	
	function __construct($cms, $core) {
		$this->cms = $cms;
		if (!empty($this->cms->configJson->$core))
			$this->options = $this->cms->configJson->$core;
			else 
			return false;
		$this->solrClient = $this->cms->solr->createSolrClient($core);
		}
		
	function loadRecord($wikiq) {
		
		}	
	
}
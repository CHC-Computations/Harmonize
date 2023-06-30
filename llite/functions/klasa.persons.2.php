<?php

#require_once 'File/MARC.php';


class persons {
	
	
	public function __construct($config = []) {
		
		$this->options = new stdclass;
		$this->config = $config;
		
		}
	
	public function register($name, $value) {
		$this->$name = $value;
		}
	
	public function where ($arr, $arr2=[]){
		$arr = array_merge($arr,$arr2);
		if (count($arr)>0)
			return "WHERE ".implode(' AND ',$arr);
			else 
			return '';
		}
	
	public function getResultsCount($arr, $arr2=[]) {
		$arr = array_merge($arr,$arr2);
		$t = $this->psql->querySelect("SELECT count(*) as ile FROM  persons {$this->where($arr)};");
		if (is_array($t)) {
			return current($t)['ile'];
			} else {
			return 0;	
			}
		}
	
	
	public function getName() {
		if (!empty($this->record->name))
			return $this->record->name;
		return 'no name';	
		}	
	
	public function getSolrStr() {
		if (!empty($this->record->solr_str))
			return $this->record->solr_str;
		return 'no name';	
		}	
	
	}

?>
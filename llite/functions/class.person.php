<?php

#require_once 'File/MARC.php';


class person {
	
	protected $cms;
	public function __construct() {
		
		}
	
	public function register($name, $var) {
		$this->$name = $var;
		
		#if (!empty($this->cms))	echo print_r($this->cms->settings);
		
		}
	
	
	
		
	}

?>
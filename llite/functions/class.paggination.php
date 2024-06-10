<?PHP


class paggination extends cms {
	
	public function __construct() {
		parent::__construct();
		echo "<pre>".print_r($this,1).'</pre>';
		}
	
	public function getLastPage() {
		$results = $this->solr->response->numFound;
		$rpp = $this->config['config']['pagination']['default_rpp'];
		
		return ceil($results/$rpp);
		}
		
	public function getCurrentPage() {
		if (!empty($this->GET['page']))
			return $this->GET['page'];
			else 
			return 1;
		}
	
	}


?>
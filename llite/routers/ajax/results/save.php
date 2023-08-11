<?php 
if (empty($this)) die;


$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->config)); 

#  echo "_COOKIE <pre>".print_R($_COOKIE,1)."</pre>";
#  echo "_SESSION <pre>".print_R($_SESSION,1)."</pre>";

if ( (!empty($_SESSION['p_GET']['lookfor'])) && (!empty($_SESSION['p_GET']['type'])) ) {
	$lookfor = $_SESSION['p_GET']['lookfor'];
	$type = $_SESSION['p_GET']['type'];
	} else 
	$lookfor=$type='';
$query[] = $this->solr->lookFor($lookfor, $type);		
	
$this->facetsCode = $_SESSION['p_facetsCode'];
$sort = $_SESSION['p_sort'];

if (!empty($this->getIniParam('search', 'sortoptions')[$sort])) {
	$sort = $this->getIniParam('search', 'sortoptions')[$sort];
	if ($sort!=='relevance') 
		$query[]=[ 
				'field' => 'sort',
				'value' => $sort
				];
	}

if (!empty($this->facetsCode)) {
	$query[] = $this->buffer->getFacets($this->facetsCode);	
	} else 
	$this->facetsCode = 'null';		
	

			
$query[]=[ 
		'field' => 'fl',
		'value' => 'id'
		];
$query[]=[ 
		'field' => 'facet',
		'value' => 'false'
		];
$query[]=[ 
		'field' => 'rows',
		'value' => 10000000
		];
$query[]=[ 
		'field' => 'start',
		'value' => 0
		];		

$cmsKey = $_COOKIE['cmsKey'];

$run1=$this->runTime();	
$results = $this->solr->getQuery('biblio',$query);
$results = $this->solr->resultsList();
$run2=$this->runTime();	

echo "<textarea style='width:100%'>";
foreach ($results as $v) {
	echo $v->id.",";
	}
echo "</textarea>";
#echo "results $cmsKey<pre>".print_R($results,1)."</pre>";
echo implode(' ', $this->solr->alert);
#echo "Solr run ". $this->solr->responseHeader->Qtime;


echo "All run time: ".$run=$this->runTime();
echo " (run1: $run1, run2:$run2)";
?>
<?php 
if (empty($this)) die;
require_once('functions/klasa.helper.php');
require_once('functions/klasa.forms.php');

$export = $this->getConfig('export');
$facets = $this->getConfig('search');
$facets = $this->getConfig('facets');

$this->addClass('buffer', 	new marcBuffer()); 
$this->addClass('solr', 	new solr($this->config)); 
$this->addClass('helper', 	new helper()); 
$this->addClass('forms', 	new forms()); 
$this->buffer->setSql($this->sql);
$this->forms->values($this->GET);

$currentPart = $this->routeParam[0];

$sortSymbol = $this->getUserParam('sort');
if (stristr($sortSymbol, ',')) { // mulit sort
	$sorts = explode(',',$sortSymbol);
	foreach ($sorts as $sortSymbol) {
		if ((!empty($this->getIniParam('search', 'sortoptions')[$sortSymbol])) && ($sortSymbol !== 'r')) {
			$sortTable[] = $this->getIniParam('search', 'sortoptions')[$sortSymbol];
			}
		$query[]=[ 
				'field' => 'sort',
				'value' => implode(',',$sortTable)
				];	
		}
	} elseif (!empty($this->getIniParam('search', 'sortoptions')[$sortSymbol])) { // single sort
	$sort = $this->getIniParam('search', 'sortoptions')[$sortSymbol];
	if ($sort!=='relevance') 
		$query[]=[ 
				'field' => 'sort',
				'value' => $sort
				];
	}


if (!empty($this->GET['swl'])) { // start with letter ...
	$sl = strtolower(substr($this->GET['swl'],0,1));
	#echo "Starting with: $sl<br/>";
	switch ($sort) {
		case 'author_sort asc': $sfield = 'author_sort'; break;
		case 'title_sort asc': $sfield = 'title_sort'; break;
		default : $sfield = '';
		}
	if ($sfield<>'')
		$query[] = [
				'field' => 'q',
				'value' => "($sfield:$sl*)"
				];
	}

if (!empty($this->routeParam[3])) {
	$this->facetsCode = $this->routeParam[3];	
	$query[] = $this->buffer->getFacets( $this->facetsCode);	
	} else 
	$this->facetsCode = 'null';		

$lookfor = $this->getParam('GET', 'lookfor');
$type = $this->getParam('GET', 'type');

#echo "lookfor: $lookfor, $type<Br>";

if (!empty($this->GET['sj'])) {
	#echo "Advanced: <pre>".print_r(json_decode($this->GET['sj']),1)."</pre>";
	# echo $this->solr->advandedSearch($this->GET['sj']);
	$query[] = [ 
			'field' => 'q',
			'value' => $this->solr->advandedSearch($this->GET['sj'])
			];
	}

$query[] = $this->solr->lookFor($lookfor, $type );			
		
		
$query[]=[ 
		'field' => 'facet',
		'value' => 'true'
		];
$query[]=[ 
		'field' => 'facet.limit',
		'value' => '999999'
		];
		
	

$results = $this->solr->getQuery('biblio',$query); 
$this->setTitle("Libri ".$this->transEsc('results'));



$results = $this->solr->resultsList();

switch ($currentPart) {
	case 'persons' : 
			echo "persons";
			$query[]=[ 
				'field' => 'facet.field',
				'value' => 'persons_str_mv'
				];
			$query[]=[ 
				'field' => 'rows',
				'value' => '0'
				];
			$TQ = [];
			foreach ($query as $k=>$v) {
				$TQ[] = $v['field'] .'='.urlencode($v['value']);
				}	
			$core = 'lite.biblio';	
			$path = $this->solr->options->hostname.':'.$this->solr->options->port."/solr/".$core."/select?".implode('&',$TQ);
			print_r($query);
			#$blocks = $this->solr->getFullList2('biblio', 'persons_str_mv', $query);
			$file = file_get_contents($path);
			$res = json_decode($file);
			$list = $res->facet_counts->facet_fields->persons_str_mv;
			echo "<hr>";
			foreach ($list as $k=>$v)
				echo "$k. $v<br/>";
			
			
			break;
	case 'ids' :
			$query[]=[ 
					'field' => 'rows',
					'value' => $this->getUserParam('limit')
					];
					
			if (!empty($this->getCurrentPage()>1))
				$query[]=[ 
					'field' => 'start',
					'value' => $this->getCurrentPage()*$this->getUserParam('limit') - $this->getUserParam('limit')
					];		

			$results = $this->solr->resultsList();
			break;
	default :
		echo $this->render('head.php');
		echo $this->render('core/header.php');
		echo $currentPart;
		
		echo $this->render('core/footer.php');

	}

?>
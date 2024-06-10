<?php 

if (empty($this)) die;

require_once('functions/class.helper.php');
require_once('functions/class.forms.php');
require_once('functions/class.converter.php');
require_once('functions/class.record.bibliographic.php');

$this->addClass('buffer', 	new buffer($this)); 
$this->addClass('solr', 	new solr($this)); 
$this->addClass('helper', 	new helper($this)); 
$this->addClass('forms', 	new forms($this)); 
$this->addClass('convert', 	new converter($this));

$currentCore = 'biblio';

$idList = [];

if (!empty($this->POST['pdata'])) {
	
	
	foreach ($this->POST['pdata'] as $rec) {
		#echo $this->helper->pre($rec);
		$idList[] = $rec['id'];
		}

	$query['facet']=[ 
				'field' => 'facet',
				'value' => 'false'
				];
			
	$query['rows']=[ 
			'field' => 'rows',
			'value' => count($idList)
			];
	
	$query['q.op']=[ 
			'field' => 'q.op',
			'value' => 'OR'
			];
	
	$query['q']=[ 
			'field' => 'q',
			'value' => 'id:('.implode(' OR ', $idList).')'
			];
	
	$results = $this->solr->getQuery('biblio',$query); 

	$results = $this->solr->resultsList();
	
	$current_view = $this->getUserParam($currentCore.':view') ?? 'list';
	echo '<div class="results-'. $current_view .'">';
	foreach ($results as $result) {
		$this->addClass('record', new bibliographicRecord($result, $this->convert->mrk2json($result->fullrecord)));
		echo $this->render('search/results/'.$current_view.'.php', ['result'=>$result, 'record'=>json_decode($result->relations)] );
		}
	
	echo '</div>';
	
	
	
	} else {
	$results = new stdClass;
	$results->exception = $this->transEsc('For reasons of server performance, we can now only present the first 100 pages of results').'. '.$this->transEsc('Try using filters or search').'. ';
	}

?>
<?php
if (empty($this)) die;
require_once('functions/class.maps.php');
require_once('functions/class.wikidata.libri.php');
require_once('functions/class.wikidata.php');
#require_once('functions/class.wikiDataCores.php');
require_once('functions/class.viafSearcher.php');

$wikiq = $this->routeParam[0];
$recTypeExpected = $this->routeParam[1] ?? null;
if (!empty($this->routeParam[2]) && ($this->routeParam[2]=='reload'))
	$refreshRecord = true;
	else 
	$refreshRecord = true;	

$wikiIdInt = substr($wikiq,1);
$this->clearGET();

$this->addClass('buffer', 	new buffer()); 
$this->addClass('helper', 	new helper()); 
$this->addClass('maps', 	new maps()); 
$this->addClass('solr', 	new solr($this));  
$this->addClass('wiki', 	new wikidata($this)); 
$this->addClass('wikiRec', 	new wikidata($this)); 
$this->addClass('viafSearcher', new viafSearcher($this)); 
#$this->addClass('wikiDataCores',	new wikiDataCores($this));

$this->wiki->loadRecord($wikiq, $refreshRecord);

/*
$recType = $this->wiki->recType();
$recCore = $recType.'s';
$coreRecord = $this->solr->getWikiRecord($recCore, $wikiq);
$this->addClass('coreRecord', new wikiLibri($this->userLang, $coreRecord));
#echo $this->helper->pre($this->wiki->labels);

$this->setTitle($this->wiki->get('labels').' | '.$this->transEsc($recType));

# echo '<h1>record</h1>'.$this->helper->pre($this->wiki->record);
# echo '<h1>solrRecord</h1>'.$this->helper->pre($this->wiki->solrRecord);
$dataQualityStr = $this->render('wiki/tab-dataQuality.php');


$query = [];
$query['q'] 			= ['field' => 'q',				'value' => '*:*' ];
$query['facet'] 		= ['field' => 'facet',			'value' => 'true'];
$query['facet.field'] 	= ['field' => 'facet.field',	'value' => 'with_roles_wiki'];		
$query['facet.limit']	= ['field' => 'facet.limit',	'value' => 9999 ];
$query['facet.prefix']	= ['field' => 'facet.prefix', 	'value' => $wikiq.'|' ];
$this->solr->getQuery('biblio', $query); 
$results = $this->solr->resultsList();
$facets = $this->solr->facetsList();		
*/
$json = json_encode($this->wiki->record);
$json = '{"entities":{"'.$wikiq.'":'.$json.'}}';
#echo $json;

$len=strlen($json);
header("Content-type: application/json");
header("Content-Length: $len");
header("Content-Disposition: inline; filename={$wikiq}.json");
print $json;	

?>


